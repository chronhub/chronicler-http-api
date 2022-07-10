<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Console;

use Illuminate\Console\Command;
use Chronhub\Chronicler\StreamName;
use Chronhub\Projector\ProjectorManager;
use Chronhub\Projector\Support\Facade\Project;

abstract class ReadProjectionCommand extends Command
{
    protected string $projectorDriver = 'default';

    public function handle(): void
    {
        $stream = new StreamName($this->argument('stream'));

        $result = $this->processProjection($stream);

        $result = empty($result) ? 'No result' : json_encode($result);

        $this->info("{$this->field()} $stream projection is: ");
        $this->info($result);
    }

    protected function projector(): ProjectorManager
    {
        $projectorDriver = $this->hasOption('projector')
            ? $this->option('projector') : $this->projectorDriver;

        return Project::create($projectorDriver);
    }

    abstract protected function processProjection(StreamName $streamName): array;

    abstract protected function field(): string;
}
