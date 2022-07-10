<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support;

use Chronhub\Messager\Message\Header;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Projector\Exception\RuntimeException;
use Chronhub\Chronicler\Driver\InMemoryQueryFilter;
use Chronhub\Chronicler\Driver\InMemory\InMemoryQueryScope;

final class InMemoryProjectionQueryScope extends InMemoryQueryScope implements ProjectionQueryScope
{
    public function fromIncludedPosition(): ProjectionQueryFilter
    {
        return new class() implements ProjectionQueryFilter, InMemoryQueryFilter
        {
            private int $currentPosition = 0;

            public function filter(): callable
            {
                $position = $this->currentPosition;

                if ($position <= 0) {
                    throw new RuntimeException("Position must be greater than 0, current is $position");
                }

                return function (DomainEvent $event) use ($position): ?DomainEvent {
                    $isGreaterThanPosition = $event->header(Header::INTERNAL_POSITION->value) >= $position;

                    return $isGreaterThanPosition ? $event : null;
                };
            }

            public function setCurrentPosition(int $position): void
            {
                $this->currentPosition = $position;
            }

            public function orderBy(): string
            {
                return 'asc';
            }
        };
    }
}
