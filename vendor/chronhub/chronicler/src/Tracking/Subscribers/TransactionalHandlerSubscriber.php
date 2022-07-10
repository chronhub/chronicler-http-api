<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Tracking\Subscribers;

use Chronhub\Messager\Reporter;
use Chronhub\Chronicler\Chronicler;
use Chronhub\Messager\OnDispatchPriority;
use Chronhub\Messager\Tracker\MessageTracker;
use Chronhub\Chronicler\TransactionalChronicler;
use Chronhub\Messager\Tracker\ContextualMessage;
use Chronhub\Messager\Subscribers\AbstractMessageSubscriber;

final class TransactionalHandlerSubscriber extends AbstractMessageSubscriber
{
    public function __construct(private Chronicler $chronicler)
    {
    }

    public function attachToTracker(MessageTracker $tracker): void
    {
        $this->listeners[] = $tracker->listen(Reporter::DISPATCH_EVENT, function (ContextualMessage $context): void {
            if ($this->chronicler instanceof TransactionalChronicler) {
                $this->chronicler->beginTransaction();
            }
        }, OnDispatchPriority::INVOKE_HANDLER->value + 1000);

        $this->listeners[] = $tracker->listen(Reporter::FINALIZE_EVENT, function (ContextualMessage $context): void {
            if ($this->chronicler instanceof TransactionalChronicler
                && $this->chronicler->inTransaction()) {
                $context->hasException()
                    ? $this->chronicler->rollbackTransaction()
                    : $this->chronicler->commitTransaction();
            }
        }, 1000);
    }
}
