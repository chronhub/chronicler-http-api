<?php

declare(strict_types=1);

namespace Chronhub\Projector\Concerns;

use Closure;
use Chronhub\Projector\ProjectorFactory;
use Chronhub\Chronicler\Driver\QueryFilter;

trait InteractWithContext
{
    public function initialize(Closure $initCallback): ProjectorFactory
    {
        $this->factory->initialize($initCallback);

        return $this;
    }

    public function fromStreams(string ...$streams): ProjectorFactory
    {
        $this->factory->fromStreams(...$streams);

        return $this;
    }

    public function fromCategories(string ...$categories): ProjectorFactory
    {
        $this->factory->fromCategories(...$categories);

        return $this;
    }

    public function fromAll(): ProjectorFactory
    {
        $this->factory->fromAll();

        return $this;
    }

    public function when(array $eventHandlers): ProjectorFactory
    {
        $this->factory->when($eventHandlers);

        return $this;
    }

    public function whenAny(Closure $eventsHandler): ProjectorFactory
    {
        $this->factory->whenAny($eventsHandler);

        return $this;
    }

    public function until(int|string $delay): ProjectorFactory
    {
        $this->factory->withTimer($delay);

        return $this;
    }

    public function withQueryFilter(QueryFilter $queryFilter): ProjectorFactory
    {
        $this->factory->withQueryFilter($queryFilter);

        return $this;
    }

    protected function prepareContext(bool $inBackground): void
    {
        $this->context->withFactory($this->factory);

        $this->context->runner()->runInBackground($inBackground);

        $this->context->cast($this->contextualEventHandler());
    }
}
