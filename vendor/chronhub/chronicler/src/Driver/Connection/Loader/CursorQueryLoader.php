<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver\Connection\Loader;

use Chronhub\Chronicler\StreamName;
use Illuminate\Database\Query\Builder;
use Chronhub\Chronicler\Driver\Connection\EventConverter;

final class CursorQueryLoader extends StreamEventLoader
{
    public function __construct(protected EventConverter $eventConverter)
    {
    }

    protected function generateFrom(Builder $builder, StreamName $StreamName): iterable
    {
        return $builder->cursor();
    }
}
