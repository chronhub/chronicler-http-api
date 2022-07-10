<?php

declare(strict_types=1);

namespace Chronhub\Projector;

interface ProjectorServiceManager
{
    public function create(string $driver = 'default'): ProjectorManager;

    public function extends(string $driver, callable $manager): void;
}
