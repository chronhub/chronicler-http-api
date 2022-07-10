<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver\Connection;

use Generator;
use Illuminate\Support\Enumerable;
use Chronhub\Chronicler\StreamName;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Chronicler\Driver\QueryFilter;
use Illuminate\Database\ConnectionInterface;
use Chronhub\Chronicler\Aggregate\AggregateId;
use Chronhub\Chronicler\Exception\QueryFailure;
use Chronhub\Chronicler\TransactionalChronicler;
use Chronhub\Chronicler\Driver\StreamPersistence;
use Chronhub\Chronicler\Driver\WriteLockStrategy;
use Chronhub\Chronicler\Exception\StreamNotFound;
use Chronhub\Chronicler\Driver\EventStreamProvider;
use Chronhub\Chronicler\Support\DetectStreamCategory;
use Chronhub\Chronicler\Exception\StreamAlreadyExists;
use Chronhub\Chronicler\Driver\Connection\WriteLock\NoWriteLock;
use Chronhub\Chronicler\Driver\Connection\Loader\StreamEventLoader;

abstract class ChroniclerConnection implements \Chronhub\Chronicler\ChroniclerConnection
{
    use DetectStreamCategory;

    public function __construct(protected ConnectionInterface $connection,
                                protected EventStreamProvider $eventStreamProvider,
                                protected StreamPersistence $persistenceStrategy,
                                protected StreamEventLoader $streamEventLoader,
                                protected ?WriteLockStrategy $writeLockStrategy)
    {
        $this->writeLockStrategy = $writeLockStrategy ?? new NoWriteLock();
    }

    public function retrieveAll(StreamName $streamName, AggregateId $aggregateId, string $direction = 'asc'): Generator
    {
        $query = $this->queryBuilder($streamName);

        if (! $this->persistenceStrategy->isOneStreamPerAggregate()) {
            $query = $query->where('aggregate_id', $aggregateId->toString());
        }

        $query = $query->orderBy('no', $direction);

        try {
            return yield from $this->streamEventLoader->query($query, $streamName);
        } catch (StreamNotFound $exception) {
            $this->handleStreamNotFound($exception);
        }
    }

    public function retrieveFiltered(StreamName $streamName, QueryFilter $queryFilter): Generator
    {
        $builder = $this->queryBuilder($streamName);

        $queryFilter->filter()($builder);

        try {
            return yield from $this->streamEventLoader->query($builder, $streamName);
        } catch (StreamNotFound $exception) {
            $this->handleStreamNotFound($exception);
        }
    }

    public function fetchStreamNames(StreamName ...$streamNames): array
    {
        $streamNames = array_map(
            fn (StreamName $streamName): string => $streamName->toString(), $streamNames
        );

        return array_map(
            fn (string $streamName): StreamName => new StreamName($streamName),
            $this->eventStreamProvider->filterByStreams($streamNames)
        );
    }

    public function fetchCategoryNames(string ...$categoryNames): array
    {
        return $this->eventStreamProvider->filterByCategories($categoryNames);
    }

    public function hasStream(StreamName $streamName): bool
    {
        return $this->eventStreamProvider->hasRealStreamName($streamName->toString());
    }

    protected function createEventStream(StreamName $streamName, string $tableName): void
    {
        try {
            $category = $this->detectStreamCategory($streamName->toString());

            $result = $this->eventStreamProvider->createStream($streamName->toString(), $tableName, $category);

            if (! $result) {
                throw new QueryFailure("Unable to insert data in $tableName event stream table");
            }
        } catch (QueryException $exception) {
            match ($exception->getCode()) {
                '23000', '23505' => throw StreamAlreadyExists::withStreamName($streamName),
                default => throw QueryFailure::fromQueryException($exception)
            };
        }
    }

    protected function upStreamTable(StreamName $streamName, string $tableName): void
    {
        try {
            $this->persistenceStrategy->up($tableName);
        } catch (QueryException $exception) {
            $this->connection->getSchemaBuilder()->drop($tableName);

            $this->eventStreamProvider->deleteStream($streamName->toString());

            throw $exception;
        }
    }

    protected function serializeStreamEvents(Enumerable $streamEvents): array
    {
        return $streamEvents->map(
            fn (DomainEvent $event): array => $this->persistenceStrategy->serializeMessage($event)
        )->toArray();
    }

    protected function handleStreamNotFound(StreamNotFound $exception): void
    {
        // fixMe
        // any queries will raise exception: In failed sql transaction: PDOException: SQLSTATE[25P02]:
        // when transaction handler subscriber is attach to the event store
        if ($this->persistenceStrategy->isOneStreamPerAggregate()
            && $this instanceof TransactionalChronicler
            && $this->connection->transactionLevel() > 0
        ) {
            $this->connection->rollBack();
        }

        throw $exception;
    }

    protected function queryBuilder(StreamName $streamName): Builder
    {
        $tableName = $this->persistenceStrategy->tableName($streamName);

        return $this->connection->table($tableName);
    }
}
