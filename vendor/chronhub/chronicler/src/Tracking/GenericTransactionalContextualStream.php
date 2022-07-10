<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Tracking;

use Chronhub\Chronicler\Exception\TransactionNotStarted;
use Chronhub\Chronicler\Exception\TransactionAlreadyStarted;

final class GenericTransactionalContextualStream extends GenericContextualStream implements TransactionalContextualStream
{
    public function hasTransactionNotStarted(): bool
    {
        return $this->exception instanceof TransactionNotStarted;
    }

    public function hasTransactionAlreadyStarted(): bool
    {
        return $this->exception instanceof TransactionAlreadyStarted;
    }
}
