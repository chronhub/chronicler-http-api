<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Aggregate;

use Chronhub\Messager\Message\Header;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Chronicler\Exception\InvalidArgumentException;
use function in_array;

final class GenericAggregateType implements AggregateType
{
    public function __construct(protected string $aggregateRootClassName,
                                protected array $map = [])
    {
        if (! class_exists($aggregateRootClassName)) {
            throw new InvalidArgumentException('Aggregate root must be a FQCN');
        }

        foreach ($map as $className) {
            if (! is_subclass_of($className, $this->aggregateRootClassName)) {
                throw new InvalidArgumentException("Class $className must inherit from $aggregateRootClassName");
            }
        }
    }

    public function aggregateRootClassName(): string
    {
        return $this->aggregateRootClassName;
    }

    public function determineFromEvent(DomainEvent $event): string
    {
        $aggregateType = $event->header(Header::AGGREGATE_TYPE->value);

        $this->assertAggregateRootIsSupported($aggregateType);

        return $aggregateType;
    }

    public function determineFromAggregateRoot(AggregateRoot $aggregateRoot): string
    {
        $this->assertAggregateRootIsSupported($aggregateRoot::class);

        return $aggregateRoot::class;
    }

    public function determineFromAggregateRootClass(string $aggregateRootClass): string
    {
        $this->assertAggregateRootIsSupported($aggregateRootClass);

        return $aggregateRootClass;
    }

    public function assertAggregateRootIsSupported(string $aggregateRoot): void
    {
        if (! $this->supportAggregateRoot($aggregateRoot)) {
            throw new InvalidArgumentException("Aggregate root $aggregateRoot class is not supported");
        }
    }

    private function supportAggregateRoot(string $aggregateRoot): bool
    {
        if ($aggregateRoot === $this->aggregateRootClassName) {
            return true;
        }

        return in_array($aggregateRoot, $this->map, true);
    }
}
