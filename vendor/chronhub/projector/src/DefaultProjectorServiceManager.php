<?php

declare(strict_types=1);

namespace Chronhub\Projector;

use Illuminate\Support\Arr;
use Chronhub\Projector\Factory\Option;
use Chronhub\Chronicler\ChroniclerManager;
use Chronhub\Messager\Support\Clock\Clock;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Chronhub\Projector\Model\ProjectionProvider;
use Illuminate\Contracts\Foundation\Application;
use Chronhub\Chronicler\Driver\EventStreamProvider;
use Chronhub\Projector\Exception\InvalidArgumentException;
use function is_array;
use function is_string;

final class DefaultProjectorServiceManager implements ProjectorServiceManager
{
    /**
     * @var array<string,callable>
     */
    private array $customProjectors = [];

    /**
     * @var array<string,ProjectorManager>
     */
    private array $projectors = [];

    private array $config;

    public function __construct(private Application $app, array $config = null)
    {
        $this->config = $config ?? $app->get(Repository::class)->get('projector', []);
    }

    public function create(string $driver = 'default'): ProjectorManager
    {
        if ($projector = $this->projectors[$driver] ?? null) {
            return $projector;
        }

        $config = $this->fromProjector("projectors.$driver");

        if (! is_array($config)) {
            throw new InvalidArgumentException("Invalid configuration for projector manager $driver");
        }

        return $this->projectors[$driver] = $this->resolveProjectorManager($driver, $config);
    }

    public function extends(string $driver, callable $manager): void
    {
        $this->customProjectors[$driver] = $manager;
    }

    private function resolveProjectorManager(string $driver, array $config): ProjectorManager
    {
        if ($customProjector = $this->customProjectors[$driver] ?? null) {
            return $customProjector($this->app, $this->config);
        }

        return $this->createDefaultProjectorManager($config);
    }

    private function createDefaultProjectorManager(array $config): ProjectorManager
    {
        $dispatcher = null;

        if (true === ($config['dispatch_projector_events'] ?? false)) {
            $dispatcher = $this->app->get(Dispatcher::class);
        }

        return new DefaultProjectorManager(
            $this->app->get(ChroniclerManager::class)->create($config['chronicler']),
            $this->determineEventStreamProvider($config),
            $this->determineProjectionProvider($config),
            $this->app->make($config['scope']),
            $this->app->get(Clock::class),
            $dispatcher,
            $this->determineProjectorOptions($config['options'])
        );
    }

    private function determineProjectorOptions(?string $optionKey): array|Option
    {
        $options = $this->fromProjector("options.$optionKey") ?? [];

        return is_array($options) ? $options : $this->app->make($options);
    }

    private function determineEventStreamProvider(array $config): EventStreamProvider
    {
        $eventStreamKey = $config['event_stream_provider'];

        $eventStream = $this->app[Repository::class]->get("chronicler.provider.$eventStreamKey");

        if (! is_string($eventStream)) {
            throw new InvalidArgumentException("Event stream provider with key $eventStreamKey must be a string");
        }

        return $this->app->make($eventStream);
    }

    private function determineProjectionProvider(array $config): ProjectionProvider
    {
        $projectionKey = $config['provider'];

        $projection = $this->fromProjector("provider.$projectionKey") ?? null;

        if (! is_string($projection)) {
            throw new InvalidArgumentException("Unable to determine projection provider with key $projectionKey");
        }

        return $this->app->make($projection);
    }

    private function fromProjector(string $key): mixed
    {
        return Arr::get($this->config, $key);
    }
}
