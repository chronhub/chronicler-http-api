<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api\QueryFilter;

use Illuminate\Database\Query\Builder;
use Chronhub\Chronicler\Driver\QueryFilter;

final class FromToPosition
{
    public function __invoke(int $from, int $to, string $direction): QueryFilter
    {
        return new class($from, $to, $direction) implements QueryFilter
        {
            public function __construct(private int $from,
                                        private int $to,
                                        private string $direction)
            {
            }

            public function filter(): callable
            {
                return function (Builder $query): void {
                    $query->whereBetween('no', [$this->from, $this->to]);
                    $query->orderBy('no', $this->direction);
                };
            }
        };
    }
}
