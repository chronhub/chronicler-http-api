<?php

declare(strict_types=1);

namespace Chronhub\Projector\Concerns;

use Chronhub\Projector\ReadModel;
use Chronhub\Projector\Factory\Option;
use Chronhub\Projector\Context\Context;
use Chronhub\Projector\Factory\DetectGap;
use Illuminate\Contracts\Events\Dispatcher;
use Chronhub\Projector\Factory\EventCounter;
use Chronhub\Projector\Factory\DefaultOption;
use Chronhub\Projector\Repository\Repository;
use Chronhub\Projector\Factory\StreamPosition;
use Chronhub\Projector\Repository\RepositoryLock;
use Chronhub\Projector\Repository\ReadModelRepository;
use Chronhub\Projector\Repository\ProjectionRepository;
use Chronhub\Projector\Repository\EventableProjectorRepository;

trait InteractWithProjectorManager
{
    protected function createProjectorContext(array $options, ?EventCounter $eventCounter): Context
    {
        $options = $this->createProjectorOption($options);

        $streamPositions = new StreamPosition($this->eventStreamProvider);

        $gapDetector = $eventCounter ? $this->createGapDetector($streamPositions, $options) : null;

        return new Context($options, $this->clock, $streamPositions, $eventCounter, $gapDetector);
    }

    protected function createProjectorOption(array $mergeOptions): Option
    {
        if ($this->options instanceof Option) {
            return $this->options;
        }

        return new DefaultOption(...array_merge($this->options, $mergeOptions));
    }

    protected function createProjectorRepository(Context $context,
                                                 string $streamName,
                                                 ?ReadModel $readModel): Repository
    {
        $repositoryClass = $readModel instanceof ReadModel
            ? ReadModelRepository::class : ProjectionRepository::class;

        $repository = new $repositoryClass(
            $context,
            $this->projectionProvider,
            $this->createRepositoryLock($context->option()),
            $streamName,
            $readModel ?? $this->chronicler
        );

        if ($this->events instanceof Dispatcher) {
            $repository = new EventableProjectorRepository($repository, $this->events);
        }

        return $repository;
    }

    protected function createGapDetector(StreamPosition $streamPositions, Option $option): DetectGap
    {
        return new DetectGap(
            $streamPositions,
            $this->clock,
            $option->retriesMs,
            $option->detectionWindows
        );
    }

    protected function createRepositoryLock(Option $option): RepositoryLock
    {
        return new RepositoryLock(
            $this->clock,
            $option->lockTimeoutMs,
            $option->updateLockThreshold
        );
    }
}
