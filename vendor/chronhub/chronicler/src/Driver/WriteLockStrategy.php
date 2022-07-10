<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver;

interface WriteLockStrategy
{
    public function acquireLock(string $tableName): bool;

    public function releaseLock(string $tableName): bool;
}
