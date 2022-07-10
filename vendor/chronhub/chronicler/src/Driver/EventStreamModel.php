<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver;

interface EventStreamModel
{
    public function realStreamName(): string;

    public function tableName(): string;

    public function category(): ?string;
}
