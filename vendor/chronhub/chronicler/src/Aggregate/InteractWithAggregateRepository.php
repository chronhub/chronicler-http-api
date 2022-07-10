<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Aggregate;

use Chronhub\Messager\Message\Header;
use Chronhub\Messager\Message\Message;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Chronicler\Exception\ConcurrencyException;
use function count;

trait InteractWithAggregateRepository
{
    public function retrieve(AggregateId $aggregateId): ?AggregateRoot
    {
        if ($this->aggregateCache->has($aggregateId)) {
            return $this->aggregateCache->get($aggregateId);
        }

        $aggregateRoot = $this->reconstituteAggregateRoot($aggregateId);

        if ($aggregateRoot) {
            $this->aggregateCache->put($aggregateRoot);
        }

        return $aggregateRoot;
    }

    public function persist(AggregateRoot $aggregateRoot): void
    {
        $this->aggregateType->assertAggregateRootIsSupported($aggregateRoot::class);

        $events = $this->releaseEvents($aggregateRoot);

        if (! $firstEvent = reset($events)) {
            return;
        }

        $stream = $this->streamProducer->produceStream($aggregateRoot->aggregateId(), $events);

        $concurrencyException = null;

        try {
            $this->streamProducer->isFirstCommit($firstEvent)
                ? $this->chronicler->persistFirstCommit($stream)
                : $this->chronicler->persist($stream);
        } catch (ConcurrencyException $exception) {
            $concurrencyException = $exception;
        }

        $this->aggregateCache->forget($aggregateRoot->aggregateId());

        if ($concurrencyException) {
            throw $concurrencyException;
        }
    }

    /**
     * @return array<DomainEvent>
     */
    private function releaseEvents(AggregateRoot $aggregateRoot): array
    {
        $events = $aggregateRoot->releaseEvents();

        if (0 === count($events)) {
            return [];
        }

        $version = $aggregateRoot->version() - count($events);
        $aggregateId = $aggregateRoot->aggregateId();

        $headers = [
            Header::AGGREGATE_ID->value => $aggregateId->toString(),
            Header::AGGREGATE_ID_TYPE->value => $aggregateId::class,
            Header::AGGREGATE_TYPE->value => $aggregateRoot::class,
        ];

        return array_map(
            function (DomainEvent $event) use ($headers, &$version) {
                return $this->messageDecorator->decorate(
                    new Message($event, $headers + [Header::AGGREGATE_VERSION->value => ++$version])
                )->event();
            }, $events);
    }
}
