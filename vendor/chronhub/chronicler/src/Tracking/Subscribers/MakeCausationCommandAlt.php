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
use Chronhub\Messager\Tracker\ContextualMessage;
use Chronhub\Chronicler\Tracking\ContextualStream;
use Chronhub\Chronicler\Tracking\StreamSubscriber;
use Chronhub\Messager\Message\Decorator\MessageDecorator;
use Chronhub\Messager\Subscribers\AbstractMessageSubscriber;

/**
 * need to be a singleton and register in both config
 */
final class MakeCausationCommandAlt extends AbstractMessageSubscriber implements StreamSubscriber
{
    private ?DomainCommand $command = null;

    public function attachToTracker(MessageTracker $tracker): void
    {
        $this->listeners[] = $tracker->listen(Reporter::DISPATCH_EVENT,
            function (ContextualMessage $context): void {
                $event = $context->message()->event();

                $this->command = $event instanceof DomainCommand ? $event : null;
            }, 1000);

        $this->listeners[] = $tracker->listen(Reporter::FINALIZE_EVENT,
            function (): void {
                $this->command = null;
            }, 10000);
    }

    public function attachToChronicler(Chronicler $chronicler): void
    {
        if (! $chronicler instanceof EventableChronicler) {
            return;
        }

        foreach ([EventableChronicler::PERSIST_STREAM_EVENT, EventableChronicler::FIRST_COMMIT_EVENT] as $streamEvent) {
            $chronicler->subscribe($streamEvent,
                function (ContextualStream $event): void {
                    if ($this->command) {
                        $messageDecorator = $this->correlationMessageDecorator();

                        $event->decorateStreamEvents($messageDecorator);
                    }
                }, 1000);
        }
    }

    private function correlationMessageDecorator(): MessageDecorator
    {
        $eventId = (string) $this->command->header(Header::EVENT_ID->value);
        $eventType = $this->command->header(Header::EVENT_TYPE->value);

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
