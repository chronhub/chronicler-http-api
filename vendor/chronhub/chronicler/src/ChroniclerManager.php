<?php

declare(strict_types=1);

namespace Chronhub\Chronicler;

interface ChroniclerManager
{
    public function create(string $driver = 'default'): Chronicler;

    public function extends(string $driver, callable $chronicler): void;
}
