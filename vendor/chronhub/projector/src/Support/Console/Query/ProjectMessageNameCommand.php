<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Console\Query;

use Closure;
use Chronhub\Messager\Message\Header;
use Chronhub\Messager\Support\Facade\AliasMessage;
use Chronhub\Projector\Context\ContextualProjection;
use Chronhub\Messager\Support\Aggregate\AggregateChanged;
use Chronhub\Projector\Support\Console\PersistentProjectionCommand;

final class ProjectMessageNameCommand extends PersistentProjectionCommand
{
    protected $signature = 'project:message_name {--projector=default} {--signal=1} {--alias=1}';

    protected $description = 'optimize queries by projecting events per message name';

    public function handle(): void
    {
        $this->withProjection('$by_message_name');

        $this->projector
            ->fromAll()
            ->whenAny($this->eventHandler())
            ->run(true);
    }

    private function eventHandler(): Closure
    {
        $asAlias = $this->isMessageNameMustBeAliased();

        return function (AggregateChanged $event) use ($asAlias): void {

            /** @var ContextualProjection $this */
            $messageName = $event->header(Header::EVENT_TYPE->value);

            if ($asAlias) {
                $messageName = AliasMessage::classToAlias($messageName);
            }

            $this->linkTo('$mn-'.$messageName, $event);
        };
    }

    private function isMessageNameMustBeAliased(): bool
    {
        if ($this->hasOption('alias')) {
            return 1 === (int) $this->option('alias');
        }

        return false;
    }
}
