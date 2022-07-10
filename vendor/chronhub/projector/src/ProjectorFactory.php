<?php

declare(strict_types=1);

namespace Chronhub\Projector;

use Closure;
use Chronhub\Chronicler\Driver\QueryFilter;

interface ProjectorFactory extends Projector
{
    public function initialize(Closure $initCallback): ProjectorFactory;

    public function fromStreams(string ...$streams): ProjectorFactory;

    public function fromCategories(string ...$categories): ProjectorFactory;

    public function fromAll(): ProjectorFactory;

    public function when(array $eventHandlers): ProjectorFactory;

    public function whenAny(Closure $eventsHandler): ProjectorFactory;

    /**
     * Run the projection until time is reached
     * delay can be in seconds or a string interval
     * to produce timestamp comparison.
     *
     * note that projection should stop gracefully and
     * could not exactly stop at the delay requested
     */
    public function until(int|string $delay): ProjectorFactory;

    public function withQueryFilter(QueryFilter $queryFilter): ProjectorFactory;
}
