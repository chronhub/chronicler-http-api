<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Console\Query;

use Closure;
use Chronhub\Projector\Context\ContextualProjection;
use Chronhub\Messager\Support\Aggregate\AggregateChanged;
use Chronhub\Projector\Support\Console\PersistentProjectionCommand;

final class ProjectAllStreamCommand extends PersistentProjectionCommand
{
    protected $signature = 'project:all_stream {--projector=default} {--signal=1}';

    protected $description = 'optimize queries by projecting all events in one table';

    public function handle(): void
    {
        $this->withProjection('$all');

        $this->projector
            ->fromAll()
            ->whenAny($this->eventHandler())
            ->run(true);
    }

    private function eventHandler(): Closure
    {
        return function (AggregateChanged $event): void {

            /** @var ContextualProjection $this */
            $this->emit($event);
        };
    }
}
