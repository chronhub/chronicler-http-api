<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver\Connection;

use Illuminate\Database\Query\Builder;
use Chronhub\Chronicler\Driver\QueryScope;
use Chronhub\Chronicler\Driver\QueryFilter;
use Chronhub\Chronicler\Exception\InvalidArgumentException;

abstract class ConnectionQueryScope implements QueryScope
{
    public function fromToPosition(int $from, int $to, string $direction = 'asc'): QueryFilter
    {
        if ($from < 1) {
            throw new InvalidArgumentException('From position must be greater than 0');
        }

        if ($to <= $from) {
            throw new InvalidArgumentException('To position must be greater than from position');
        }

        $callback = function (Builder $builder) use ($from, $to, $direction): void {
            $builder->whereBetween('no', [$from, $to]);
            $builder->orderBy('no', $direction);
        };

        return $this->wrap($callback);
    }

    protected function wrap(callable $query): QueryFilter
    {
        return new class($query) implements QueryFilter
        {
            /**
             * @var callable
             */
            private $query;

            public function __construct($query)
            {
                $this->query = $query;
            }

            public function filter(): callable
            {
                return $this->query;
            }
        };
    }
}
