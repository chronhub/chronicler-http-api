<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Aggregate;

use Chronhub\Messager\Message\DomainEvent;

interface AggregateType
{
    public function determineFromEvent(DomainEvent $event): string;

    public function determineFromAggregateRoot(AggregateRoot $aggregateRoot): string;

    public function determineFromAggregateRootClass(string $aggregateRootClass): string;

    public function assertAggregateRootIsSupported(string $aggregateRoot): void;

    public function aggregateRootClassName(): string;
}
