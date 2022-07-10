<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Aggregate;

use Generator;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Chronicler\Driver\QueryFilter;
use Chronhub\Chronicler\Exception\StreamNotFound;

trait HasReconstituteAggregate
{
    protected function reconstituteAggregateRoot(AggregateId $aggregateId,
                                                 ?QueryFilter $queryFilter = null): ?AggregateRoot
    {
        try {
            $history = $this->fromHistory($aggregateId, $queryFilter);

            if (! $history->valid()) {
                return null;
            }

            /** @var AggregateRoot&static $aggregateRoot */
            $aggregateRoot = $this->aggregateType->determineFromEvent($history->current());

            return $aggregateRoot::reconstituteFromEvents($aggregateId, $history);
        } catch (StreamNotFound) {
            return null;
        }
    }

    /**
     * @param  AggregateId  $aggregateId
     * @param  QueryFilter|null  $queryFilter
     * @return Generator<DomainEvent>
     */
    protected function fromHistory(AggregateId $aggregateId, ?QueryFilter $queryFilter): Generator
    {
        $streamName = $this->streamProducer->determineStreamName($aggregateId->toString());

        return yield from $queryFilter instanceof QueryFilter
            ? $this->chronicler->retrieveFiltered($streamName, $queryFilter)
            : $this->chronicler->retrieveAll($streamName, $aggregateId);
    }
}
