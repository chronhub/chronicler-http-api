<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Aggregate;

use Generator;
use Chronhub\Messager\Message\DomainEvent;
use function get_class;

trait HasAggregateRoot
{
    private int $version = 0;

    private array $recordedEvents = [];

    protected function __construct(private AggregateId $aggregateId)
    {
    }

    public function aggregateId(): AggregateId
    {
        return $this->aggregateId;
    }

    public function version(): int
    {
        return $this->version;
    }

    protected function apply(DomainEvent $event): void
    {
        $parts = explode('\\', get_class($event));

        $this->{'apply'.end($parts)}($event);

        $this->version++;
    }

    protected function recordThat(DomainEvent $event): void
    {
        $this->apply($event);

        $this->recordedEvents[] = $event;
    }

    public function releaseEvents(): array
    {
        $releasedEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $releasedEvents;
    }

    public static function reconstituteFromEvents(AggregateId $aggregateId, Generator $events): ?AggregateRoot
    {
        $aggregateRoot = new static($aggregateId);

        foreach ($events as $event) {
            $aggregateRoot->apply($event);
        }

        $aggregateRoot->version = (int) $events->getReturn();

        return $aggregateRoot->version() > 0 ? $aggregateRoot : null;
    }
}
