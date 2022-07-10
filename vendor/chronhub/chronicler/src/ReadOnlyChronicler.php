<?php

declare(strict_types=1);

namespace Chronhub\Chronicler;

use Generator;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Chronicler\Driver\QueryFilter;
use Chronhub\Chronicler\Aggregate\AggregateId;

interface ReadOnlyChronicler
{
    /**
     * @return Generator<DomainEvent>
     */
    public function retrieveAll(StreamName $streamName,
                                AggregateId $aggregateId,
                                string $direction = 'asc'): Generator;

    /**
     * @return Generator<DomainEvent>
     */
    public function retrieveFiltered(StreamName $streamName, QueryFilter $queryFilter): Generator;

    /**
     * @return StreamName[]
     */
    public function fetchStreamNames(StreamName ...$streamNames): array;

    /**
     * @return string[]
     */
    public function fetchCategoryNames(string ...$categoryNames): array;

    public function hasStream(StreamName $streamName): bool;
}
