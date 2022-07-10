<?php

declare(strict_types=1);

namespace Chronhub\Projector\Context;

use Chronhub\Projector\QueryProjector;
use Chronhub\Messager\Support\Clock\Clock;

final class ContextualQuery
{
    private ?string $currentStreamName = null;

    public function __construct(private QueryProjector $projector,
                                private Clock $clock,
                                ?string &$currentStreamName)
    {
        $this->currentStreamName = &$currentStreamName;
    }

    public function stop(): void
    {
        $this->projector->stop();
    }

    public function streamName(): ?string
    {
        return $this->currentStreamName;
    }

    public function clock(): Clock
    {
        return $this->clock;
    }
}
