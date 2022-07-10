<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api\QueryFilter;

use Illuminate\Database\Query\Builder;
use Chronhub\Chronicler\Driver\QueryFilter;

final class AllPaginated
{
    public function __invoke(int $limit,
                             int $offset,
                             string $direction): QueryFilter
    {
        return new class($limit, $offset, $direction) implements QueryFilter
        {
            public function __construct(private int $limit,
                                        private int $offset,
                                        private string $direction)
            {
            }

            public function filter(): callable
            {
                return function (Builder $query): void {
                    $query->orderBy('no', $this->direction);

                    $query->limit($this->limit);

                    if (0 !== $this->offset) {
                        $query->offset($this->offset);
                    }
                };
            }
        };
    }
}
