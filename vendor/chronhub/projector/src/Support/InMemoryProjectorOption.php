<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support;

use Chronhub\Projector\Factory\Option;
use Chronhub\Projector\Concerns\InteractWithOption;

final class InMemoryProjectorOption implements Option
{
    use InteractWithOption;

    private bool $dispatchSignal = false;

    private int $streamCacheSize = 1000;

    private int $lockTimeoutMs = 0;

    private int $sleepBeforeUpdateLock = 1000;

    private int $persistBlockSize = 1;

    private int $updateLockThreshold = 0;

    private array $retriesMs = [];

    private string $detectionWindows = 'PT1S';
}
