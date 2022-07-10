<?php

declare(strict_types=1);

namespace Chronhub\Chronicler;

use Chronhub\Chronicler\Aggregate\AggregateRepository;

interface RepositoryManager
{
    public function create(string $streamName): AggregateRepository;

    public function extends(string $streamName, callable $repository): void;
}
