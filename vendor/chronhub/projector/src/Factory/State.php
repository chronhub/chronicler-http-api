<?php

declare(strict_types=1);

namespace Chronhub\Projector\Factory;

interface State
{
    public function setState(array $state): void;

    public function getState(): array;

    public function resetState(): void;

    public function toJson(): string;
}
