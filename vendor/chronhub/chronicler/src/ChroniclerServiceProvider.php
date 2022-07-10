<?php

declare(strict_types=1);

namespace Chronhub\Chronicler;

use Illuminate\Support\ServiceProvider;
use Chronhub\Chronicler\Support\Facade\Chronicle;
use Illuminate\Contracts\Support\DeferrableProvider;

final class ChroniclerServiceProvider extends ServiceProvider implements DeferrableProvider
{
    private string $chroniclerPath = __DIR__.'/../config/chronicler.php';

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([$this->chroniclerPath => config_path('chronicler.php')]);

            if (true === config('chronicler.console.load_migrations') ?? false) {
                $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
            }

            $this->commands(config('chronicler.console.commands', []));
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->chroniclerPath, 'chronicler');

        $this->app->singleton(ChroniclerManager::class, DefaultChroniclerManager::class);
        $this->app->alias(ChroniclerManager::class, Chronicle::SERVICE_NAME);
        $this->app->singleton(RepositoryManager::class, DefaultRepositoryManager::class);
    }

    public function provides(): array
    {
        return [
            ChroniclerManager::class,
            Chronicle::SERVICE_NAME,
            RepositoryManager::class,
        ];
    }
}
