<?php

declare(strict_types=1);

namespace Chronhub\Projector;

interface Projector
{
    public function run(bool $inBackground): void;

    public function stop(): void;

    public function reset(): void;

    public function getState(): array;
}