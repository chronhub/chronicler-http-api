<?php

declare(strict_types=1);

namespace Chronhub\Chronicler;

use Chronhub\Messager\Message\DomainEvent;

interface InMemoryChronicler extends Chronicler
{
    /**
     * Pull recorded streams.
     *
     * it must only been called for standalone in memory event store
     *
     * for standalone non-transactional, dev should call it manually
     * for standalone transactional, dev would use a message subscriber
     *
     * when decorated in memory with event not/in transaction,
     * dev would use a stream subscriber
     *
     * @return array<DomainEvent>
     *
     * @see PublishTransactionalInMemoryEvents
     * @see PublishEvents
     */
    public function pullCachedRecordedEvents(): array;
}
