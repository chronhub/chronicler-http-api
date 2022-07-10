<?php

declare(strict_types=1);

namespace Chronhub\Projector\Pipes;

use Chronhub\Projector\Context\Context;
use Chronhub\Projector\Repository\Repository;

final class PersistOrUpdateLock
{
    public function __construct(private Repository $repository)
    {
    }

    public function __invoke(Context $context, callable $next): callable|bool
    {
        if (! $context->gap()->hasGap()) {
            $context->eventCounter()->isReset()
                ? $this->sleepBeforeUpdateLock($context->option()->sleepBeforeUpdateLock)
                : $this->repository->persist();
        }

        return $next($context);
    }

    private function sleepBeforeUpdateLock(int $sleep): void
    {
        usleep(microseconds: $sleep);

        $this->repository->updateLock();
    }
}
