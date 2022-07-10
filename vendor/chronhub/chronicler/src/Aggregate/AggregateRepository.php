<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Aggregate;

use Chronhub\Chronicler\ReadOnlyChronicler;
use Chronhub\Chronicler\Strategy\StreamProducer;

interface AggregateRepository
{
    public function retrieve(AggregateId $aggregateId): ?AggregateRoot;

    public function persist(AggregateRoot $aggregateRoot): void;

    public function chronicler(): ReadOnlyChronicler;

    public function streamProducer(): StreamProducer;

    public function aggregateCache(): AggregateCache;
}
