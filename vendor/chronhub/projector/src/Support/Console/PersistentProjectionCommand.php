<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Console;

use Illuminate\Console\Command;
use Chronhub\Projector\ReadModel;
use Chronhub\Projector\ProjectorFactory;
use Chronhub\Projector\Support\Facade\Project;
use Chronhub\Projector\Support\ProjectionQueryFilter;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use function is_string;

abstract class PersistentProjectionCommand extends Command implements SignalableCommandInterface
{
    protected string $projectorDriver = 'default';

    protected bool $dispatchSignal = false;

    protected ?ProjectorFactory $projector = null;

    protected function withProjection(string $streamName,
                                      string|ReadModel $readModel = null,
                                      array $options = [],
                                      ?ProjectionQueryFilter $queryFilter = null): void
    {
        if ($this->dispatchSignal()) {
            pcntl_async_signals(true);
        }

        $this->projector = $readModel
            ? $this->projectReadModel($streamName, $readModel, $options, $queryFilter)
            : $this->projectPersistentProjection($streamName, $options, $queryFilter);
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT];
    }

    public function handleSignal(int $signal): void
    {
        if ($this->dispatchSignal()) {
            $this->line('Stopping projection ...');

            $this->projector->stop();
        }
    }

    protected function projectorName(): string
    {
        if ($this->hasOption('projector')) {
            return $this->option('projector');
        }

        return $this->projectorDriver;
    }

    protected function dispatchSignal(): bool
    {
        if ($this->hasOption('signal')) {
            return 1 === (int) $this->option('signal');
        }

        return $this->dispatchSignal;
    }

    private function projectReadModel(string $streamName,
                                      string|ReadModel $readModel,
                                      array $options = [],
                                      ?ProjectionQueryFilter $queryFilter = null): ProjectorFactory
    {
        $projector = Project::create($this->projectorName());

        if (is_string($readModel)) {
            $readModel = $this->laravel[$readModel];
        }

        $queryFilter = $queryFilter ?? $projector->queryScope()->fromIncludedPosition();

        return $projector
            ->createReadModelProjection($streamName, $readModel, $options)
            ->withQueryFilter($queryFilter);
    }

    private function projectPersistentProjection(string $streamName,
                                                 array $options = [],
                                                 ?ProjectionQueryFilter $queryFilter = null): ProjectorFactory
    {
        $projector = Project::create($this->projectorName());

        $queryFilter = $queryFilter ?? $projector->queryScope()->fromIncludedPosition();

        return $projector
            ->createProjection($streamName, $options)
            ->withQueryFilter($queryFilter);
    }
}
