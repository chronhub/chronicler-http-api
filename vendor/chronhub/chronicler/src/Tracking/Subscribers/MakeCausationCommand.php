<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Tracking\Subscribers;

use Chronhub\Messager\Reporter;
use Chronhub\Chronicler\Chronicler;
use Chronhub\Messager\Message\Header;
use Chronhub\Messager\Message\Message;
use Chronhub\Chronicler\EventableChronicler;
use Chronhub\Messager\Message\DomainCommand;
use Chronhub\Messager\Tracker\MessageTracker;
use Chronhub\Messager\Tracker\OneTimeListener;
use Chronhub\Messager\Tracker\ContextualMessage;
use Chronhub\Chronicler\Tracking\ContextualStream;
use Chronhub\Messager\Message\Decorator\MessageDecorator;
use Chronhub\Messager\Subscribers\AbstractMessageSubscriber;

final class MakeCausationCommand extends AbstractMessageSubscriber
{
    private array $oneTimeListeners = [];

    public function __construct(private Chronicler $chronicler)
    {
    }

    public function attachToTracker(MessageTracker $tracker): void
    {
        if (! $this->chronicler instanceof EventableChronicler) {
            return;
        }

        $this->listeners[] = $tracker->listen(Reporter::DISPATCH_EVENT,
            function (ContextualMessage $context): void {
                $command = $this->determineCommand($context->message());

                if ($command) {
                    $callback = function (ContextualStream $stream) use ($command): void {
                        $messageDecorator = $this->correlationMessageDecorator($command);

                        $stream->decorateStreamEvents($messageDecorator);
                    };

                    $this->oneTimeListeners[] = $this->subscribeOnFirstCommitEvent($callback);

                    $this->oneTimeListeners[] = $this->subscribeOnPersistStreamEvent($callback);
                }
            }, 1000);

        $this->listeners[] = $tracker->listen(Reporter::FINALIZE_EVENT,
            function (): void {
                $this->chronicler->unsubscribe(...$this->oneTimeListeners);
                $this->oneTimeListeners = [];
            }, 1000);
    }

    private function subscribeOnPersistStreamEvent(callable $callback): OneTimeListener
    {
        return $this->chronicler->subscribeOnce(
            EventableChronicler::PERSIST_STREAM_EVENT, $callback, 1000
        );
    }

    private function subscribeOnFirstCommitEvent(callable $callback): OneTimeListener
    {
        return $this->chronicler->subscribeOnce(
            EventableChronicler::FIRST_COMMIT_EVENT, $callback, 1000
        );
    }

    private function determineCommand(Message $message): ?DomainCommand
    {
        $event = $message->event();

        return $event instanceof DomainCommand ? $event : null;
    }

    private function correlationMessageDecorator(DomainCommand $command): MessageDecorator
    {
        $eventId = (string) $command->header(Header::EVENT_ID->value);
        $eventType = $command->header(Header::EVENT_TYPE->value);

        return new class($eventId, $eventType) implements MessageDecorator
        {
            private string $eventId;

            private string $eventType;

            public function __construct(string $eventId, string $eventType)
            {
                $this->eventId = $eventId;
                $this->eventType = $eventType;
            }

            public function decorate(Message $message): Message
            {
                if ($message->has(Header::EVENT_CAUSATION_ID->value)
                    && $message->has(Header::EVENT_CAUSATION_TYPE->value)) {
                    return $message;
                }

                return $message
                    ->withHeader(Header::EVENT_CAUSATION_ID->value, $this->eventId)
                    ->withHeader(Header::EVENT_CAUSATION_TYPE->value, $this->eventType);
            }
        };
    }
}
