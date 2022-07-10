<?php

declare(strict_types=1);

namespace Chronhub\Chronicler;

use Generator;
use Illuminate\Support\Collection;

final class Stream
{
    public function __construct(private StreamName $streamName, private iterable $events = [])
    {
    }

    public function name(): StreamName
    {
        return $this->streamName;
    }

    public function events(): Generator
    {
        return yield from $this->events;
    }

    public function enum(): Collection
    {
        return new Collection($this->events);
    }
}
