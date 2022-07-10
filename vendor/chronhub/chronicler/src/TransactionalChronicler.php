<?php

declare(strict_types=1);

namespace Chronhub\Chronicler;

use Throwable;

interface TransactionalChronicler extends Chronicler
{
    public const BEGIN_TRANSACTION_EVENT = 'begin_transaction';

    public const COMMIT_TRANSACTION_EVENT = 'commit_transaction';

    public const ROLLBACK_TRANSACTION_EVENT = 'rollback_transaction';

    /**
     * @throws Throwable
     */
    public function beginTransaction(): void;

    /**
     * @throws Throwable
     */
    public function commitTransaction(): void;

    /**
     * @throws Throwable
     */
    public function rollbackTransaction(): void;

    /**
     * @throws Throwable
     */
    public function transactional(callable $callback): mixed;

    public function inTransaction(): bool;
}
