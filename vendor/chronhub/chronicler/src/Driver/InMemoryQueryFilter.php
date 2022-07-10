<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver;

interface InMemoryQueryFilter extends QueryFilter
{
    public function orderBy(): string;
}
