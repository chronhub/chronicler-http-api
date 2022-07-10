<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api\QueryFilter;

use Illuminate\Database\Query\Builder;
use Chronhub\Chronicler\Driver\QueryFilter;

final class FromToVersion
{
    public function __invoke(string $aggregateId, int $from, int $to, string $direction): QueryFilter
    {
        return new class($aggregateId, $from, $to, $direction) implements QueryFilter
        {
            public function __construct(private string $aggregateId,
                                        private int $from,
                                        private int $to,
                                        private string $direction)
            {
            }

            public function filter(): callable
            {
                return function (Builder $query): void {
                    $query->where('aggregate_id', $this->aggregateId);
                    $query->orderBy('aggregate_version', $this->direction);
                    $query->whereBetween('aggregate_version', [$this->from, $this->to]);
                };
            }
        };
    }
}
