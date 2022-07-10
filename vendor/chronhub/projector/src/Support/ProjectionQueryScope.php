<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support;

use Chronhub\Chronicler\Driver\QueryScope;

interface ProjectionQueryScope extends QueryScope
{
    public function fromIncludedPosition(): ProjectionQueryFilter;
}
