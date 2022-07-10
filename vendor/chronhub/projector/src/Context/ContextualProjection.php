<?php

declare(strict_types=1);

namespace Chronhub\Projector\Context;

use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Messager\Support\Clock\Clock;
use Chronhub\Projector\ProjectionProjector;

final class ContextualProjection
{
    private ?string $currentStreamName = null;

    public function __construct(private ProjectionProjector $projector,
                                private Clock $clock,
                                ?string &$currentStreamName)
    {
        $this->currentStreamName = &$currentStreamName;
    }

    public function stop(): void
    {
        $this->projector->stop();
    }

    public function linkTo(string $streamName, DomainEvent $event): void
    {
        $this->projector->linkTo($streamName, $event);
    }

    public function emit(DomainEvent $event): void
    {
        $this->projector->emit($event);
    }

    public function streamName(): ?string
    {
        return $this->currentStreamName;
    }

    public function clock(): Clock
    {
        return $this->clock;
    }
}
