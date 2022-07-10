<?php

declare(strict_types=1);

namespace Chronhub\Projector\Factory;

final class NoOpTimer implements Timer
{
    public function start(): void
    {
    }

    public function isExpired(): bool
    {
        return false;
    }
}
