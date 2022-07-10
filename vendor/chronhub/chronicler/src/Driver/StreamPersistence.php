<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver;

use Chronhub\Chronicler\StreamName;
use Chronhub\Messager\Message\DomainEvent;

interface StreamPersistence
{
    public function tableName(StreamName $streamName): string;

    public function up(string $tableName): ?callable;

    public function serializeMessage(DomainEvent $event): array;

    public function isOneStreamPerAggregate(): bool;
}
