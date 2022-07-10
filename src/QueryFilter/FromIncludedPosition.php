<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api\QueryFilter;

use Illuminate\Database\Query\Builder;
use Chronhub\Chronicler\Driver\QueryFilter;

final class FromIncludedPosition
{
    public function __invoke(int $position): QueryFilter
    {
        return new class($position) implements QueryFilter
        {
            public function __construct(private int $position)
            {
            }

            public function filter(): callable
            {
                return function (Builder $query): void {
                    $query
                        ->where('no', '>=', $this->position)
                        ->orderBy('no');
                };
            }
        };
    }
}
