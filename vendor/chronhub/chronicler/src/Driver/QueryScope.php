<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver;

interface QueryScope
{
    public function fromToPosition(int $from, int $to, string $direction = 'asc'): QueryFilter;

    public function matchAggregateGreaterThanVersion(string $aggregateId,
                                                     string $aggregateType,
                                                     int $aggregateVersion,
                                                     string $direction = 'asc'): QueryFilter;
}
