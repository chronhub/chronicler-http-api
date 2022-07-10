<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver\Connection\Loader;

use Chronhub\Chronicler\StreamName;
use Illuminate\Database\Query\Builder;
use Chronhub\Chronicler\Driver\Connection\EventConverter;
use function is_int;

final class LazyQueryLoader extends StreamEventLoader
{
    public function __construct(protected EventConverter $eventConverter,
                                protected int $chunkSize = 5000)
    {
    }

    protected function generateFrom(Builder $builder, StreamName $StreamName): iterable
    {
        $limit = is_int($builder->limit) ? $builder->limit : null;

        $query = $builder->lazy(min($limit ?? PHP_INT_MAX, $this->chunkSize));

        if ($limit) {
            $query = $query->take($limit);
        }

        return $query;
    }
}
