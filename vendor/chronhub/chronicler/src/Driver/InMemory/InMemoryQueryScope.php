<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver\InMemory;

use Chronhub\Messager\Message\Header;
use Chronhub\Chronicler\Driver\QueryScope;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Chronicler\Driver\InMemoryQueryFilter;
use Chronhub\Chronicler\Exception\InvalidArgumentException;

class InMemoryQueryScope implements QueryScope
{
    public function fromToPosition(int $from, int $to, string $direction = 'asc'): InMemoryQueryFilter
    {
        if ($from <= 0) {
            throw new InvalidArgumentException('From position must be greater or equal than 0');
        }

        if ($to <= $from) {
            throw new InvalidArgumentException('To position must be greater than from position');
        }

        $callback = function (DomainEvent $message) use ($from, $to): ?DomainEvent {
            $position = $message->header(Header::INTERNAL_POSITION->value);

            return $position >= $from && $position <= $to ? $message : null;
        };

        return $this->wrap($callback, $direction);
    }

    public function matchAggregateGreaterThanVersion(string $aggregateId,
                                                     string $aggregateType,
                                                     int $aggregateVersion,
                                                     string $direction = 'asc'): InMemoryQueryFilter
    {
        $callback = function (DomainEvent $event) use ($aggregateId, $aggregateType, $aggregateVersion): ?DomainEvent {
            $currentAggregateId = (string) $event->header(Header::AGGREGATE_ID->value);

            if ($currentAggregateId !== $aggregateId) {
                return null;
            }

            if ($event->header(Header::AGGREGATE_TYPE->value) !== $aggregateType) {
                return null;
            }

            return $event->header(Header::INTERNAL_POSITION->value) > $aggregateVersion ? $event : null;
        };

        return $this->wrap($callback, $direction);
    }

    public function wrap(callable $query, string $direction = 'asc'): InMemoryQueryFilter
    {
        return new class($query, $direction) implements InMemoryQueryFilter
        {
            /**
             * @var callable
             */
            private $query;

            public function __construct($query, private string $direction)
            {
                $this->query = $query;
            }

            public function filter(): callable
            {
                return $this->query;
            }

            public function orderBy(): string
            {
                return $this->direction;
            }
        };
    }
}
