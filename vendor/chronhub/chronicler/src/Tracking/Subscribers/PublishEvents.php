<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Tracking\Subscribers;

use Chronhub\Chronicler\Chronicler;
use Chronhub\Chronicler\EventableChronicler;
use Chronhub\Chronicler\TransactionalChronicler;
use Chronhub\Chronicler\Tracking\ContextualStream;
use Chronhub\Chronicler\Tracking\StreamSubscriber;
use Chronhub\Chronicler\Support\HasStreamEventRecorder;

final class PublishEvents implements StreamSubscriber
{
    use HasStreamEventRecorder;

    public function attachToChronicler(Chronicler $chronicler): void
    {
        if ($chronicler instanceof EventableChronicler) {
            $this->subscribeToEventableChronicler($chronicler);

            if ($chronicler instanceof TransactionalChronicler) {
                $this->subscribeToTransactionalChronicler($chronicler);
            }
        }
    }

    private function subscribeToEventableChronicler(EventableChronicler $chronicler): void
    {
        $chronicler->subscribe($chronicler::FIRST_COMMIT_EVENT,
            function (ContextualStream $context) use ($chronicler): void {
                $streamEvents = $context->stream()->events();

                if (! $this->inTransaction($chronicler)) {
                    if (! $context->hasStreamAlreadyExits()) {
                        $this->publish($streamEvents);
                    }
                } else {
                    $this->record($streamEvents);
                }
            });

        $chronicler->subscribe($chronicler::PERSIST_STREAM_EVENT,
            function (ContextualStream $context) use ($chronicler): void {
                $streamEvents = $context->stream()->events();

                if (! $this->inTransaction($chronicler)) {
                    if (! $context->hasStreamNotFound() && ! $context->hasRaceCondition()) {
                        $this->publish($streamEvents);
                    }
                } else {
                    $this->record($streamEvents);
                }
            });
    }

    private function subscribeToTransactionalChronicler(EventableChronicler & TransactionalChronicler $chronicler): void
    {
        $chronicler->subscribe($chronicler::COMMIT_TRANSACTION_EVENT,
            function (): void {
                $this->publish($this->pull());
            });

        $chronicler->subscribe($chronicler::ROLLBACK_TRANSACTION_EVENT,
            function (): void {
                $this->clear();
            });
    }

    private function inTransaction(Chronicler $chronicler): bool
    {
        return $chronicler instanceof TransactionalChronicler && $chronicler->inTransaction();
    }
}
