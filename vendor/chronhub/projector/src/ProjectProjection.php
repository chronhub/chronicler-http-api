<?php

declare(strict_types=1);

namespace Chronhub\Projector;

use Chronhub\Chronicler\Stream;
use Chronhub\Chronicler\Chronicler;
use Chronhub\Chronicler\StreamName;
use Chronhub\Projector\Context\Context;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Projector\Factory\StreamCache;
use Chronhub\Projector\Repository\Repository;
use Chronhub\Projector\Context\ContextFactory;
use Chronhub\Projector\Concerns\InteractWithContext;
use Chronhub\Projector\Context\ContextualProjection;
use Chronhub\Projector\Concerns\InteractWithPersistentProjector;

final class ProjectProjection implements ProjectorFactory, ProjectionProjector
{
    use InteractWithContext;
    use InteractWithPersistentProjector;

    private StreamCache $streamCache;

    protected ContextFactory $factory;

    public function __construct(protected Context $context,
                                protected Repository $repository,
                                protected Chronicler $chronicler,
                                protected string $streamName)
    {
        $this->streamCache = new StreamCache($context->option()->streamCacheSize);
        $this->factory = new ContextFactory();
    }

    public function emit(DomainEvent $event): void
    {
        $streamName = new StreamName($this->streamName);

        $this->persistIfStreamIsFirstCommit($streamName);

        $this->linkTo($this->streamName, $event);
    }

    public function linkTo(string $streamName, DomainEvent $event): void
    {
        $streamName = new StreamName($streamName);

        $stream = new Stream($streamName, [$event]);

        $this->determineIfStreamAlreadyExists($streamName)
            ? $this->chronicler->persist($stream)
            : $this->chronicler->persistFirstCommit($stream);
    }

    protected function contextualEventHandler(): ContextualProjection
    {
        return new ContextualProjection(
            $this,
            $this->context->clock(),
            $this->context->currentStreamName
        );
    }

    private function persistIfStreamIsFirstCommit(StreamName $streamName): void
    {
        if (! $this->context->isStreamCreated() && ! $this->chronicler->hasStream($streamName)) {
            $this->chronicler->persistFirstCommit(new Stream($streamName));

            $this->context->setStreamCreated(true);
        }
    }

    private function determineIfStreamAlreadyExists(StreamName $streamName): bool
    {
        if ($this->streamCache->has($streamName->toString())) {
            return true;
        }

        $this->streamCache->push($streamName->toString());

        return $this->chronicler->hasStream($streamName);
    }
}
