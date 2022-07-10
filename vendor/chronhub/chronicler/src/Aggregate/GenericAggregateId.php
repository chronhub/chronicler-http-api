<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Aggregate;

final class GenericAggregateId implements AggregateId
{
    use HasAggregateIdentity;
}
