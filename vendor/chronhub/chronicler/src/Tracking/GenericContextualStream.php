<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Tracking;

use Chronhub\Chronicler\Stream;
use Chronhub\Chronicler\StreamName;
use Chronhub\Messager\Message\Message;
use Chronhub\Messager\Tracker\HasEnvelop;
use Chronhub\Chronicler\Driver\QueryFilter;
use Chronhub\Chronicler\Aggregate\AggregateId;
use Chronhub\Chronicler\Exception\StreamNotFound;
use Chronhub\Chronicler\Exception\StreamAlreadyExists;
use Chronhub\Chronicler\Exception\ConcurrencyException;
use Chronhub\Messager\Message\Decorator\MessageDecorator;
use Chronhub\Chronicler\Exception\InvalidArgumentException;
use function in_array;

class GenericContextualStream implements ContextualStream
{
    use HasEnvelop;

    protected ?Stream $stream = null;

    protected ?StreamName $streamName = null;

    protected ?AggregateId $aggregateId = null;

    protected ?string $direction = null;

    protected ?QueryFilter $queryFilter = null;

    protected array $streamNames = [];

    protected array $categoryNames = [];

    protected bool $isStreamExists = false;

    public function withStream(Stream $stream): void
    {
        $this->stream = $stream;
    }

    public function withStreamName(StreamName $streamName): void
    {
        $this->streamName = $streamName;
    }

    public function withStreamNames(StreamName ...$streamNames): void
    {
        $this->streamNames = $streamNames;
    }

    public function withCategoryNames(string ...$categoryNames): void
    {
        $this->categoryNames = $categoryNames;
    }

    public function withAggregateId(AggregateId $aggregateId): void
    {
        $this->aggregateId = $aggregateId;
    }

    public function withQueryFilter(QueryFilter $queryFilter): void
    {
        $this->queryFilter = $queryFilter;
    }

    public function withDirection(string $direction): void
    {
        if (! in_array($direction, ['asc', 'desc'])) {
            throw new InvalidArgumentException("Invalid Order by direction, allowed asc/desc, current is $direction");
        }

        $this->direction = $direction;
    }

    public function setStreamExists(bool $isStreamExists): void
    {
        $this->isStreamExists = $isStreamExists;
    }

    public function decorateStreamEvents(MessageDecorator $messageDecorator): void
    {
        if ($this->stream instanceof Stream) {
            $events = [];

            foreach ($this->stream->enum() as $event) {
                $events[] = $messageDecorator->decorate(new Message($event))->event();
            }

            $this->stream = new Stream($this->stream->name(), $events);
        }
    }

    public function stream(): ?Stream
    {
        return $this->stream;
    }

    public function streamName(): ?StreamName
    {
        return $this->streamName;
    }

    public function streamNames(): array
    {
        return $this->streamNames;
    }

    public function categoryNames(): array
    {
        return $this->categoryNames;
    }

    public function aggregateId(): ?AggregateId
    {
        return $this->aggregateId;
    }

    public function direction(): ?string
    {
        return $this->direction;
    }

    public function queryFilter(): ?QueryFilter
    {
        return $this->queryFilter;
    }

    public function isStreamExists(): bool
    {
        return $this->isStreamExists;
    }

    public function hasStreamNotFound(): bool
    {
        return $this->exception instanceof StreamNotFound;
    }

    public function hasStreamAlreadyExits(): bool
    {
        return $this->exception instanceof StreamAlreadyExists;
    }

    public function hasRaceCondition(): bool
    {
        return $this->exception instanceof ConcurrencyException;
    }
}
