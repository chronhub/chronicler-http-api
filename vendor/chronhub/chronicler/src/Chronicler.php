<?php

declare(strict_types=1);

namespace Chronhub\Chronicler;

use Throwable;

interface Chronicler extends ReadOnlyChronicler
{
    /**
     * @throws Throwable
     */
    public function persistFirstCommit(Stream $stream): void;

    /**
     * @throws Throwable
     */
    public function persist(Stream $stream): void;

    /**
     * @throws Throwable
     */
    public function delete(StreamName $streamName): void;
}
