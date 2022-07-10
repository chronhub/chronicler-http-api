<?php

declare(strict_types=1);

namespace Chronhub\Projector;

use Chronhub\Chronicler\Chronicler;
use Chronhub\Projector\Context\Context;
use Chronhub\Projector\Repository\Repository;
use Chronhub\Projector\Context\ContextFactory;
use Chronhub\Projector\Context\ContextualReadModel;
use Chronhub\Projector\Concerns\InteractWithContext;
use Chronhub\Projector\Concerns\InteractWithPersistentProjector;

final class ProjectReaModel implements ProjectorFactory, ReadModelProjector
{
    use InteractWithContext;
    use InteractWithPersistentProjector;

    protected ContextFactory $factory;

    public function __construct(protected Context $context,
                                protected Repository $repository,
                                protected Chronicler $chronicler,
                                protected string $streamName,
                                private ReadModel $readModel)
    {
        $this->factory = new ContextFactory();
    }

    public function readModel(): ReadModel
    {
        return $this->readModel;
    }

    protected function contextualEventHandler(): ContextualReadModel
    {
        return new ContextualReadModel(
            $this,
            $this->context->clock(),
            $this->context->currentStreamName
        );
    }
}
