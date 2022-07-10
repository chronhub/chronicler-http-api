<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api\Support;

use Chronhub\Chronicler\Aggregate\AggregateId;
use Chronhub\Chronicler\Aggregate\HasAggregateIdentity;

final class GenericAggregateId implements AggregateId
{
    use HasAggregateIdentity;
}
