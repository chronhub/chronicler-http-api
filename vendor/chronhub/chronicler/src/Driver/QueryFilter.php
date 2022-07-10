<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver;

interface QueryFilter
{
    public function filter(): callable;
}
