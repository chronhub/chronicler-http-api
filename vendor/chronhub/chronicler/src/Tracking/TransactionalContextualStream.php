<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Tracking;

interface TransactionalContextualStream extends ContextualStream
{
    public function hasTransactionNotStarted(): bool;

    public function hasTransactionAlreadyStarted(): bool;
}
