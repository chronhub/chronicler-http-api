<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Tracking;

use Chronhub\Chronicler\EventableChronicler;
use Chronhub\Messager\Subscribers\Subscriber;

interface StreamSubscriber extends Subscriber
{
    public function attachToChronicler(EventableChronicler $chronicler): void;
}
