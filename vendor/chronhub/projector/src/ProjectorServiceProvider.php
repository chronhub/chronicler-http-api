<?php

declare(strict_types=1);

namespace Chronhub\Projector;

use Illuminate\Support\ServiceProvider;
use Chronhub\Projector\Support\Facade\Project;
use Illuminate\Contracts\Support\DeferrableProvider;

final class ProjectorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [$this->getConfigPath() => config_path('projector.php')],
                'config'
            );

            $console = config('projector.console') ?? [];

            if (true === $console['load_migrations'] ?? false) {
                $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
            }

            if (true === $console['load_commands'] ?? false) {
                $this->commands($console['commands'] ?? []);
            }
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'projector');

        $this->app->bind(ProjectorServiceManager::class, DefaultProjectorServiceManager::class);
        $this->app->alias(ProjectorServiceManager::class, Project::SERVICE_NAME);
    }

    public function provides(): array
    {
        return [ProjectorServiceManager::class, Project::SERVICE_NAME];
    }

    private function getConfigPath(): string
    {
        return __DIR__.'/../config/projector.php';
    }
}
