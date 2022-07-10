<?php

declare(strict_types=1);

namespace Chronhub\Projector\Factory;

interface Timer
{
    public function start(): void;

    public function isExpired(): bool;
}
