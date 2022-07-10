<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Events;

use Throwable;

final class ProjectionFailed
{
    public function __construct(private string $streamName,
                                private Throwable $exception,
                                private string $operation)
    {
    }

    public function streamName(): string
    {
        return $this->streamName;
    }

    public function exception(): Throwable
    {
        return $this->exception;
    }

    public function operation(): string
    {
        return $this->operation;
    }
}
