<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Aggregate;

use Chronhub\Chronicler\Chronicler;
use Chronhub\Chronicler\ReadOnlyChronicler;
use Chronhub\Chronicler\Strategy\StreamProducer;
use Chronhub\Messager\Message\Decorator\MessageDecorator;

final class GenericAggregateRepository implements AggregateRepository
{
    use HasReconstituteAggregate;
    use InteractWithAggregateRepository;

    public function __construct(protected AggregateType $aggregateType,
                                protected Chronicler $chronicler,
                                protected StreamProducer $streamProducer,
                                protected AggregateCache $aggregateCache,
                                protected MessageDecorator $messageDecorator)
    {
    }

    public function chronicler(): ReadOnlyChronicler
    {
        return $this->chronicler;
    }

    public function streamProducer(): StreamProducer
    {
        return $this->streamProducer;
    }

    public function aggregateCache(): AggregateCache
    {
        return $this->aggregateCache;
    }
}
