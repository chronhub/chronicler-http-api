<?php

declare(strict_types=1);

namespace Chronhub\Projector\Factory;

use Chronhub\Projector\Concerns\InteractWithOption;

final class DefaultOption implements Option
{
    use InteractWithOption;

    public function __construct(
        public readonly bool $dispatchSignal = false,
        public readonly int $streamCacheSize = 1000,
        public readonly int $lockTimeoutMs = 1000,
        public readonly int $sleepBeforeUpdateLock = 10000,
        public readonly int $persistBlockSize = 1000,
        public readonly int $updateLockThreshold = 0,
        public array|string $retriesMs = [0, 5, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 2000, 3000],
        public readonly string $detectionWindows = 'PT1H')
    {
        $this->setUpRetriesMs($retriesMs);
    }
}
