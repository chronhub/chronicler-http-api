<?php

declare(strict_types=1);

namespace Chronhub\Projector\Concerns;

use Chronhub\Projector\Factory\EnumOption;
use function is_array;

trait InteractWithOption
{
    public function toArray(): array
    {
        return [
            EnumOption::DISPATCH_SIGNAL->value => $this->dispatchSignal,
            EnumOption::STREAM_CACHE_SIZE->value => $this->streamCacheSize,
            EnumOption::LOCK_TIMEOUT_MS->value => $this->lockTimeoutMs,
            EnumOption::SLEEP_BEFORE_UPDATE_LOCK->value => $this->sleepBeforeUpdateLock,
            EnumOption::UPDATE_LOCK_THRESHOLD->value => $this->updateLockThreshold,
            EnumOption::PERSIST_BLOCK_SIZE->value => $this->persistBlockSize,
            EnumOption::RETRIES_MS->value => $this->retriesMs,
            EnumOption::DETECTION_WINDOWS->value => $this->detectionWindows,
        ];
    }

    protected function setUpRetriesMs(array|string $retriesMs): void
    {
        if (is_array($retriesMs)) {
            $this->retriesMs = $retriesMs;
        } else {
            [$start, $end, $step] = explode(',', $retriesMs);

            $this->retriesMs = range((int) $start, (int) $end, (int) $step);
        }
    }
}
