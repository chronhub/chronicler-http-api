<?php

declare(strict_types=1);

namespace Chronhub\Projector;

interface PersistentProjector extends Projector
{
    public function delete(bool $withEmittedEvents): void;

    public function getStreamName(): string;
}
