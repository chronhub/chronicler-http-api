<?php

declare(strict_types=1);

namespace Chronhub\Projector\Factory;

use Closure;
use Chronhub\Projector\Context\Context;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Projector\Repository\Repository;

final class ClosureEventHandler extends EventHandlers
{
    public function __construct(private Closure $eventHandlers)
    {
    }

    public function __invoke(Context $context, DomainEvent $event, int $key, ?Repository $repository): bool
    {
        if (! $this->preProcess($context, $event, $key, $repository)) {
            return false;
        }

        $state = ($this->eventHandlers)($event, $context->state()->getState());

        return $this->afterProcess($context, $state, $repository);
    }
}
