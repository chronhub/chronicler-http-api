<?php

declare(strict_types=1);

namespace Chronhub\Projector\Pipes;

use Chronhub\Projector\Projector;
use Chronhub\Projector\Context\Context;

final class HandleTimer
{
    public function __construct(private Projector $projector)
    {
    }

    public function __invoke(Context $context, callable $next): callable|bool
    {
        $context->timer()->start();

        $process = $next($context);

        if (! $context->runner()->isStopped() && $context->timer()->isExpired()) {
            $this->projector->stop();
        }

        return $process;
    }
}
