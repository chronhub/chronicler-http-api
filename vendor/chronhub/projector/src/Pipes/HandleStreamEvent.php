<?php

declare(strict_types=1);

namespace Chronhub\Projector\Pipes;

use Closure;
use Chronhub\Chronicler\Chronicler;
use Chronhub\Chronicler\StreamName;
use Chronhub\Projector\Context\Context;
use Chronhub\Projector\Repository\Repository;
use Chronhub\Chronicler\Exception\StreamNotFound;
use Chronhub\Projector\Factory\MergeStreamIterator;
use Chronhub\Projector\Factory\StreamEventIterator;
use Chronhub\Projector\Support\ProjectionQueryFilter;

final class HandleStreamEvent
{
    public function __construct(private Chronicler $chronicler,
                                private ?Repository $repository)
    {
    }

    public function __invoke(Context $context, Closure $next): callable|bool
    {
        $streams = $this->retrieveStreams($context);

        $eventHandlers = $context->eventHandlers();

        foreach ($streams as $eventPosition => $event) {
            $context->currentStreamName = $streams->streamName();

            $eventHandled = $eventHandlers($context, $event, $eventPosition, $this->repository);

            if (! $eventHandled || $context->runner()->isStopped()) {
                return $next($context);
            }
        }

        return $next($context);
    }

    private function retrieveStreams(Context $context): MergeStreamIterator
    {
        $iterator = [];

        $queryFilter = $context->queryFilter();

        foreach ($context->streamPosition()->all() as $streamName => $position) {
            if ($queryFilter instanceof ProjectionQueryFilter) {
                $queryFilter->setCurrentPosition($position + 1);
            }

            try {
                $events = $this->chronicler->retrieveFiltered(new StreamName($streamName), $queryFilter);

                $iterator[$streamName] = new StreamEventIterator($events);
            } catch (StreamNotFound) {
                continue;
            }
        }

        return new MergeStreamIterator(array_keys($iterator), ...array_values($iterator));
    }
}
