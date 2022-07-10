<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Strategy;

use Chronhub\Chronicler\Stream;
use Chronhub\Chronicler\StreamName;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Chronicler\Aggregate\AggregateId;

interface StreamProducer
{
    public function determineStreamName(string $aggregateId): StreamName;

    public function produceStream(AggregateId $aggregateId, iterable $events): Stream;

    public function isFirstCommit(DomainEvent $firstEvent): bool;

    public function isOneStreamPerAggregate(): bool;
}
