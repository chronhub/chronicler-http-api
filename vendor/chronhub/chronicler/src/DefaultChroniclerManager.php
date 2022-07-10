<?php

declare(strict_types=1);

namespace Chronhub\Chronicler;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\ConnectionInterface;
use Chronhub\Chronicler\Tracking\StreamTracker;
use Illuminate\Contracts\Foundation\Application;
use Chronhub\Chronicler\Driver\StreamPersistence;
use Chronhub\Chronicler\Driver\WriteLockStrategy;
use Chronhub\Chronicler\Driver\EventStreamProvider;
use Chronhub\Chronicler\Exception\RuntimeException;
use Chronhub\Chronicler\Exception\InvalidArgumentException;
use Chronhub\Chronicler\Tracking\TransactionalStreamTracker;
use Chronhub\Chronicler\Driver\Connection\Loader\CursorQueryLoader;
use Chronhub\Chronicler\Driver\Connection\Loader\StreamEventLoader;
use Chronhub\Chronicler\Driver\Connection\WriteLock\PgsqlWriteLock;
use function is_array;
use function is_string;

final class DefaultChroniclerManager implements ChroniclerManager
{
    private array $customChroniclers = [];

    private array $chroniclers = [];

    private array $config;

    public function __construct(private Application $app, array $config = null)
    {
        $this->config = $config ?? $app->get(Repository::class)->get('chronicler');
    }

    public function create(string $driver = 'default'): Chronicler
    {
        if ('default' === $driver) {
            $driver = $this->fromChronicler('connections.default');
        }

        if (! is_string($driver) || '' === $driver) {
            throw new InvalidArgumentException('Invalid chronicler driver');
        }

        if ($chronicler = $this->chroniclers[$driver] ?? null) {
            return $chronicler;
        }

        return $this->chroniclers[$driver] = $this->resolveChronicleDriver($driver);
    }

    public function extends(string $driver, callable $chronicler): void
    {
        $this->customChroniclers[$driver] = $chronicler;
    }

    private function resolveChronicleDriver(string $driver): Chronicler
    {
        if ($customChronicler = $this->customChroniclers[$driver] ?? null) {
            return $customChronicler($this->app, $this->config);
        }

        $config = $this->fromChronicler("connections.$driver");

        if (! is_array($config)) {
            throw new RuntimeException("Chronicle store connection $driver not found");
        }

        $chronicler = $this->resolveChronicleStore($driver, $config);

        if ($chronicler instanceof EventableChronicler) {
            $this->attachStreamSubscribers($chronicler, $config);
        }

        return $chronicler;
    }

    private function resolveChronicleStore(string $name, array $config): Chronicler
    {
        $driver = $config['driver'];

        $method = 'create'.Str::studly($driver.'Driver');

        /* @covers createInMemoryDriver */
        /* @covers createPgsqlDriver */
        if (! method_exists($this, $method)) {
            throw new RuntimeException("Unable to resolve chronicle store with name $name and driver $driver");
        }

        $chronicler = $this->$method($config);

        if ('in_memory' === $driver) {
            return $chronicler;
        }

        return $this->resolveEventChroniclerDecorator($chronicler, $config);
    }

    private function resolveEventChroniclerDecorator(Chronicler $chronicler, array $config): Chronicler
    {
        $options = $config['options'] ?? [];

        if (false === $options || false === ($options['use_event_decorator'] ?? false)) {
            return $chronicler;
        }

        $tracker = $this->determineTracker($config);

        if (! $tracker instanceof StreamTracker) {
            throw new RuntimeException('Use of event chronicler decorator require a valid stream tracker');
        }

        if ($chronicler instanceof TransactionalChronicler && $tracker instanceof TransactionalStreamTracker) {
            return new GenericTransactionalEventChronicler($chronicler, $tracker);
        }

        if ($chronicler instanceof EventableChronicler) {
            return new GenericEventChronicler($chronicler, $tracker);
        }

        throw new RuntimeException('Unable to configure chronicler event decorator');
    }

    private function createInMemoryDriver(array $config): Chronicler
    {
        throw new \RuntimeException('not set');
//        $options = $config['options'] ?? false;
//
//        $eventStreamProvider = $this->createEventStreamProvider($config);
//
//        if (false === $options) {
//            return new InMemoryChronicler($eventStreamProvider);
//        }
//
//        if (true === $options['use_transaction']) {
//            return new InMemoryTransactionalChronicler($eventStreamProvider);
//        }
//
//        $useEventDecorator = $options['use_event_decorator'] ?? false;
//        $tracker = $this->determineTracker($config);
//
//        if (true === $useEventDecorator && $tracker instanceof StreamTracker) {
//            if ($tracker instanceof TransactionalStreamTracker) {
//                return new GenericTransactionalEventChronicler(
//                    new InMemoryTransactionalChronicler($eventStreamProvider),
//                    $tracker
//                );
//            }
//
//            return new GenericEventChronicler(
//                new InMemoryChronicler($eventStreamProvider), $tracker
//            );
//        }
//
//        throw new RuntimeException('Unable to configure chronicler event decorator');
    }

    private function createPgsqlDriver(array $config): Chronicler
    {
        /** @var ConnectionInterface $connection */
        $connection = $this->app['db']->connection('pgsql');

        return $this->resolveConnection($connection, PgsqlChronicler::class, $config);
    }

    private function resolveConnection(ConnectionInterface $connection, string $chroniclerClass, array $config): Chronicler
    {
        return new $chroniclerClass(
            $connection,
            $this->createEventStreamProvider($config),
            $this->createStreamPersistence($config),
            $this->createStreamEventLoader($config),
            $this->createWriteLock($connection, $config),
        );
    }

    private function createWriteLock(ConnectionInterface $connection, array $config): ?WriteLockStrategy
    {
        $writeLock = $config['options']['write_lock'] ?? false;

        if (false === $writeLock) {
            return null;
        }

        if (true === $writeLock) {
            $driver = $connection->getDriverName();

            return match ($driver) {
                'pgsql' => new PgsqlWriteLock($connection),
                default => throw new RuntimeException("Unavailable write lock strategy for driver $driver"),
            };
        }

        return $this->app->make($writeLock);
    }

    private function createStreamPersistence(array $config): StreamPersistence
    {
        $strategyKey = $config['strategy'] ?? 'default';

        if ('default' === $strategyKey) {
            $strategyKey = $this->fromChronicler('strategy.default');
        }

        $strategy = $this->fromChronicler("strategy.$strategyKey");

        if (null === $persistence = $strategy['persistence'] ?? null) {
            throw new RuntimeException('Unable to determine persistence strategy');
        }

        if (! class_exists($persistence) && ! $this->app->bound($persistence)) {
            throw new RuntimeException('Persistence strategy must be a valid class name or a service registered in ioc');
        }

        return $this->app->make($persistence);
    }

    private function createStreamEventLoader(array $config): StreamEventLoader
    {
        $eventLoader = $config['query_loader'] ?? null;

        if (is_string($eventLoader)) {
            return $this->app->make($eventLoader);
        }

        return $this->app->make(CursorQueryLoader::class);
    }

    private function createEventStreamProvider(array $config): EventStreamProvider
    {
        $eventStreamKey = $config['provider'] ?? null;

        $eventStream = $this->fromChronicler("provider.$eventStreamKey");

        if (! is_string($eventStream) && ! $this->app->bound($eventStream)) {
            throw new RuntimeException('Unable to determine stream provider');
        }

        return $this->app->get($eventStream);
    }

    private function determineTracker(array $config): ?StreamTracker
    {
        $tracker = $config['tracking']['tracker_id'] ?? null;

        return is_string($tracker) ? $this->app->make($tracker) : null;
    }

    private function attachStreamSubscribers(EventableChronicler $chronicler, array $config): void
    {
        $subscribers = $config['tracking']['subscribers'] ?? [];

        array_walk($subscribers, function (string $subscriber) use ($chronicler): void {
            $this->app->make($subscriber)->attachToChronicler($chronicler);
        });
    }

    private function fromChronicler(string $key): mixed
    {
        return Arr::get($this->config, $key);
    }
}
