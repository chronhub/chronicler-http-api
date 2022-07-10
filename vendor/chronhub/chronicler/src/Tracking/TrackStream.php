<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Tracking;

use Chronhub\Messager\Tracker\HasTracker;

class TrackStream implements StreamTracker
{
    use HasTracker;

    public function newContext(string $eventName): ContextualStream
    {
        return new GenericContextualStream($eventName);
    }
}
