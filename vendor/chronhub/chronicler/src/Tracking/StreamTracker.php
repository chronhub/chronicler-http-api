<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Tracking;

use Chronhub\Messager\Tracker\Tracker;

interface StreamTracker extends Tracker
{
    public function newContext(string $eventName): ContextualStream;
}
