<?php

declare(strict_types=1);

namespace Chronhub\Projector\Factory;

enum EnumOption : string
{
    case DISPATCH_SIGNAL = 'dispatchSignal';
    case STREAM_CACHE_SIZE = 'streamCacheSize';
    case SLEEP_BEFORE_UPDATE_LOCK = 'sleepBeforeUpdateLock';
    case PERSIST_BLOCK_SIZE = 'persistBlockSize';
    case LOCK_TIMEOUT_MS = 'lockTimeoutMs';
    case UPDATE_LOCK_THRESHOLD = 'updateLockThreshold';
    case RETRIES_MS = 'retriesMs';
    case DETECTION_WINDOWS = 'detectionWindows';
}
