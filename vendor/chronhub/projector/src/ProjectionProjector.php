<?php

declare(strict_types=1);

namespace Chronhub\Projector;

use Chronhub\Messager\Message\DomainEvent;

interface ProjectionProjector extends PersistentProjector
{
    public function emit(DomainEvent $event): void;

    public function linkTo(string $streamName, DomainEvent $event): void;
}
