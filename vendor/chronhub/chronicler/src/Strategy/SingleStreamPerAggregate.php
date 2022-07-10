<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Strategy;

use Chronhub\Chronicler\Stream;
use Chronhub\Chronicler\StreamName;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Chronicler\Aggregate\AggregateId;

final class SingleStreamPerAggregate implements StreamProducer
{
    public function __construct(private StreamName $streamName)
    {
    }

    public function determineStreamName(string $aggregateId): StreamName
    {
        return $this->streamName;
    }

    public function produceStream(AggregateId $aggregateId, iterable $events): Stream
    {
        $streamName = $this->determineStreamName($aggregateId->toString());

        return new Stream($streamName, $events);
    }

    public function isFirstCommit(DomainEvent $firstEvent): bool
    {
        return false;
    }

    public function isOneStreamPerAggregate(): bool
    {
        return false;
    }
}
