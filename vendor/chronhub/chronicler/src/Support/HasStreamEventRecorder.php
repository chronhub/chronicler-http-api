<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Support;

use Generator;
use Chronhub\Messager\ReportEvent;
use Illuminate\Support\Collection;

trait HasStreamEventRecorder
{
    protected Collection $recorder;

    public function __construct(protected ReportEvent $reporter)
    {
        $this->recorder = new Collection();
    }

    protected function record(iterable $events): void
    {
        if ($events instanceof Generator) {
            $events = iterator_to_array($events);
        }

        $this->recorder->push($events);
    }

    protected function publish(iterable $events): void
    {
        if ($events instanceof Generator) {
            $events = iterator_to_array($events);
        }

        if (empty($events)) {
            return;
        }

        foreach ($events as $event) {
            $this->reporter->publish($event);
        }
    }

    protected function pull(): array
    {
        $recordedStreams = $this->recorder->flatten();

        $this->clear();

        return $recordedStreams->toArray();
    }

    protected function clear(): void
    {
        $this->recorder = new Collection();
    }
}
