<?php

declare(strict_types=1);

namespace Chronhub\Projector\Concerns;

use Chronhub\Projector\Pipes\HandleGap;
use Chronhub\Projector\ProjectorRunner;
use Chronhub\Projector\Pipes\HandleTimer;
use Chronhub\Projector\PersistentProjector;
use Chronhub\Projector\Pipes\DispatchSignal;
use Chronhub\Projector\Pipes\HandleStreamEvent;
use Chronhub\Projector\Pipes\ResetEventCounter;
use Chronhub\Projector\Pipes\PersistOrUpdateLock;
use Chronhub\Projector\Pipes\StopWhenRunningOnce;
use Chronhub\Projector\Pipes\PreparePersistentRunner;
use Chronhub\Projector\Pipes\UpdateStatusAndPositions;

trait InteractWithPersistentProjector
{
    public function run(bool $inBackground): void
    {
        $this->prepareContext($inBackground);

        $run = new ProjectorRunner($this->pipes(), $this->repository);

        $run($this->context);
    }

    public function stop(): void
    {
        $this->repository->stop();
    }

    public function reset(): void
    {
        $this->repository->reset();
    }

    public function delete(bool $withEmittedEvents): void
    {
        $this->repository->delete($withEmittedEvents);
    }

    public function getState(): array
    {
        return $this->context->state()->getState();
    }

    public function getStreamName(): string
    {
        return $this->streamName;
    }

    protected function pipes(): array
    {
        /* @var PersistentProjector $this */

        return [
            new HandleTimer($this),
            new PreparePersistentRunner($this->repository),
            new HandleStreamEvent($this->chronicler, $this->repository),
            new PersistOrUpdateLock($this->repository),
            new HandleGap($this->repository),
            new ResetEventCounter(),
            new DispatchSignal(),
            new UpdateStatusAndPositions($this->repository),
            new StopWhenRunningOnce($this),
        ];
    }
}
