<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Tracking;

final class TrackTransactionalStream extends TrackStream implements TransactionalStreamTracker
{
    public function newContext(string $eventName): TransactionalContextualStream
    {
        return new GenericTransactionalContextualStream($eventName);
    }
}
