<?php

declare(strict_types=1);

namespace Chronhub\Projector\Factory;

use Iterator;
use Generator;
use Chronhub\Messager\Message\Header;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Projector\Exception\RuntimeException;

final class StreamEventIterator implements Iterator
{
    private ?DomainEvent $currentEvent = null;

    private int $currentKey = 0;

    public function __construct(private Generator $eventStreams)
    {
        $this->next();
    }

    public function current(): ?DomainEvent
    {
        return $this->currentEvent;
    }

    public function next(): void
    {
        $this->currentEvent = $this->eventStreams->current();

        if ($this->currentEvent instanceof DomainEvent) {
            $position = (int) $this->currentEvent->header(Header::INTERNAL_POSITION->value);

            if ($position <= 0) {
                throw new RuntimeException("Stream event position must be greater than 0, current is $position");
            }

            $this->currentKey = $position;
        } else {
            $this->resetProperties();
        }

        $this->eventStreams->next();
    }

    public function key(): bool|int
    {
        if (null === $this->currentEvent || 0 === $this->currentKey) {
            return false;
        }

        return $this->currentKey;
    }

    public function valid(): bool
    {
        return null !== $this->currentEvent;
    }

    public function rewind(): void
    {
    }

    private function resetProperties(): void
    {
        $this->currentKey = 0;
        $this->currentEvent = null;
    }
}
