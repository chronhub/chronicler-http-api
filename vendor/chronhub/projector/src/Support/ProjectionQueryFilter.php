<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support;

use Chronhub\Chronicler\Driver\QueryFilter;

interface ProjectionQueryFilter extends QueryFilter
{
    public function setCurrentPosition(int $position): void;
}
