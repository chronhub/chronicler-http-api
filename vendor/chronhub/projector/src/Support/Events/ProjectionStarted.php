<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Events;

final class ProjectionStarted
{
    public function __construct(private string $streamName)
    {
    }

    public function streamName(): string
    {
        return $this->streamName;
    }
}
