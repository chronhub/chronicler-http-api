<?php

declare(strict_types=1);

namespace Chronhub\Chronicler;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Config\Repository;
use Chronhub\Chronicler\Aggregate\AggregateRoot;
use Chronhub\Chronicler\Aggregate\AggregateType;
use Chronhub\Chronicler\Strategy\StreamProducer;
use Illuminate\Contracts\Foundation\Application;
use Chronhub\Chronicler\Aggregate\AggregateCache;
use Chronhub\Chronicler\Exception\RuntimeException;
use Chronhub\Chronicler\Aggregate\NullAggregateCache;
use Chronhub\Chronicler\Aggregate\AggregateRepository;
use Chronhub\Chronicler\Aggregate\GenericAggregateType;
use Chronhub\Chronicler\Aggregate\GenericAggregateCache;
use Chronhub\Messager\Message\Decorator\MessageDecorator;
use Chronhub\Chronicler\Aggregate\GenericAggregateRepository;
use Chronhub\Messager\Message\Decorator\ChainMessageDecorators;
use function is_array;
use function is_string;

final class DefaultRepositoryManager implements RepositoryManager
{
    private array $repositories = [];

    private array $customRepositories = [];

    private array $config;

    public function __construct(private Application $app,
                                private ChroniclerManager $chroniclerManager)
    {
        $this->config = $app->get(Repository::class)->get('chronicler');
    }

    public function create(string $streamName): AggregateRepository
    {
        if ($repository = $this->repositories[$streamName] ?? null) {
            return $repository;
        }

        $config = $this->fromChronicler("repository.repositories.$streamName");

        if (! is_array($config) || empty($config)) {
            throw new RuntimeException("Invalid repository config for stream name $streamName");
        }

        return $this->repositories[$streamName] = $this->resolveAggregateRepository($streamName, $config);
    }

    public function extends(string $streamName, callable $repository): void
    {
        $this->customRepositories[$streamName] = $repository;
    }

    private function resolveAggregateRepository(string $streamName, array $config): AggregateRepository
    {
        if ($customRepository = $this->customRepositories[$streamName] ?? null) {
            return $customRepository($this->app, $config);
        }

        $aggregateRepository = null;
        $snapshotStoreId = null;

        if ($this->isSnapshotProvided($config)) {
            // $aggregateRepository = $config['snapshot']['repository'] ?? AggregateSnapshotRepository::class;
           // $snapshotStoreId = $this->app->get($config['snapshot']['store']);
        }

        if (null === $aggregateRepository) {
            $aggregateRepository = GenericAggregateRepository::class;
        }

        if (! class_exists($aggregateRepository)) {
            throw new RuntimeException("Invalid aggregate repository class $aggregateRepository");
        }

        $aggregateType = $this->makeAggregateType($config['aggregate_type']);

        return new $aggregateRepository(
            $aggregateType,
            $this->chroniclerManager->create($config['chronicler']),
            $this->makeStreamProducer($streamName, $config),
            $this->makeAggregateCacheDriver($aggregateType->aggregateRootClassName(), $config['cache'] ?? []),
            $this->makeStreamEventDecorators($streamName),
            $snapshotStoreId,
        );
    }

    private function makeAggregateType(string|array $aggregateType): AggregateType
    {
        if (is_string($aggregateType)) {
            if (is_subclass_of($aggregateType, AggregateRoot::class)) {
                return new GenericAggregateType($aggregateType);
            }

            return $this->app->make($aggregateType);
        }

        return new GenericAggregateType($aggregateType['root'], $aggregateType['children']);
    }

    private function makeStreamProducer(string $streamName, array $config): StreamProducer
    {
        $streamProducer = $this->determineStreamProducerDriver($config);

        if (! is_string($streamProducer)) {
            throw new RuntimeException('Unable to determine stream producer strategy');
        }

        return new $streamProducer(new StreamName($streamName));
    }

    private function makeAggregateCacheDriver(string $aggregateType, array $cache): AggregateCache
    {
        if (0 === ($cache['size'] ?? 0)) {
            return new NullAggregateCache();
        }

        $tag = $cache['tag'] ?? 'identity-'.Str::snake(class_basename($aggregateType));

        return new GenericAggregateCache($aggregateType, $tag, $cache['size']);
    }

    private function makeStreamEventDecorators(string $streamName): MessageDecorator
    {
        $messageDecorators = [];

        if (true === $this->fromChronicler('use_foundation_decorators') ?? false) {
            $messageDecorators = $this->app['config']->get('reporter.messaging.decorators', []);
        }

        $eventDecorators = array_map(
            fn (string $decorator) => $this->app->make($decorator),
            array_merge(
                $messageDecorators,
                $this->fromChronicler('event_decorators') ?? [],
                $this->fromChronicler("repository.repositories.$streamName.event_decorators") ?? []
            )
        );

        return new ChainMessageDecorators(...$eventDecorators);
    }

    private function determineStreamProducerDriver(array $config): ?string
    {
        $connection = $this->fromChronicler('connections.'.$config['chronicler']);

        if ('default' === $connection) {
            $connection = $this->fromChronicler('connections.default');
        }

        $strategy = is_array($connection)
            ? $connection['strategy']
            : $this->fromChronicler("connections.$connection.strategy");

        if ('default' === $strategy) {
            $strategy = $this->fromChronicler('strategy.default') ?? null;
        }

        return $this->fromChronicler("strategy.$strategy.producer");
    }

    private function isSnapshotProvided(array $config): bool
    {
        return false;
        //return isset($config['snapshot']) && true === ($config['snapshot']['use_snapshot'] ?? false);
    }

    private function fromChronicler(string $key): mixed
    {
        return Arr::get($this->config, $key);
    }
}
