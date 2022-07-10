<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Console\Query;

use Closure;
use Chronhub\Projector\Context\ContextualProjection;
use Chronhub\Messager\Support\Aggregate\AggregateChanged;
use Chronhub\Projector\Support\Console\PersistentProjectionCommand;

final class ProjectCategoryStreamCommand extends PersistentProjectionCommand
{
    protected $signature = 'project:category {--projector=default} {--signal=1}';

    protected $description = 'optimize queries by projecting events per categories';

    public function handle(): void
    {
        $this->withProjection('$by_category');

        $this->projector
            ->fromAll()
            ->whenAny($this->eventHandler())
            ->run(true);
    }

    private function eventHandler(): Closure
    {
        return function (AggregateChanged $event): void {

            /** @var ContextualProjection $this */
            $streamName = $this->streamName();

            $pos = strpos($streamName, '-');

            if (false === $pos) {
                return;
            }

            $category = substr($streamName, 0, $pos);

            $this->linkTo('$ct-'.$category, $event);
        };
    }
}
