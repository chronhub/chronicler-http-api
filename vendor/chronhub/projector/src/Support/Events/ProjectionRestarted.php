<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Events;

final class ProjectionRestarted
{
    public function __construct(private string $streamName)
    {
    }

    public function streamName(): string
    {
        return $this->streamName;
    }
}
