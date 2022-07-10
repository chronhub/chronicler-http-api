<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver\Connection;

use stdClass;
use Chronhub\Messager\Message\Header;
use Chronhub\Messager\Message\Message;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Chronicler\Support\JsonEncoder;
use Chronhub\Messager\Message\Serializer\MessageSerializer;

class EventConverter
{
    public function __construct(private MessageSerializer $eventSerializer,
                                private JsonEncoder $jsonEncoder)
    {
    }

    public function toArray(DomainEvent $event, bool $autoIncrement): array
    {
        $data = $this->eventSerializer->serializeMessage(new Message($event));

        $serializedEvent = [
            'event_id' => (string) $data['headers'][Header::EVENT_ID->value],
            'event_type' => $data['headers'][Header::EVENT_TYPE->value],
            'aggregate_id' => (string) $data['headers'][Header::AGGREGATE_ID->value],
            'aggregate_type' => (string) $data['headers'][Header::AGGREGATE_TYPE->value],
            'aggregate_version' => $data['headers'][Header::AGGREGATE_VERSION->value],
            'content' => $this->jsonEncoder->encode($data['content']),
            'headers' => $this->jsonEncoder->encode($data['headers']),
            'created_at' => (string) $data['headers'][Header::EVENT_TIME->value],
        ];

        if (! $autoIncrement) {
            $serializedEvent['no'] = $data['headers'][Header::AGGREGATE_VERSION->value];
        }

        return $serializedEvent;
    }

    public function toDomainEvent(stdClass $event): DomainEvent
    {
        $data = [
            'content' => $this->jsonEncoder->decode($event->content),
            'headers' => $this->jsonEncoder->decode($event->headers),
            'no' => $event->no,
        ];

        return $this->eventSerializer->unserializeContent($data)->current();
    }
}
