<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Aggregate;

interface AggregateCache
{
    public function put(AggregateRoot $aggregateRoot): void;

    public function get(AggregateId $aggregateId): ?AggregateRoot;

    public function forget(AggregateId $aggregateId): void;

    public function flush(): bool;

    public function has(AggregateId $aggregateId): bool;

    public function count(): int;
}
