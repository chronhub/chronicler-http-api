<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Generator;
use Illuminate\Http\Request;
use Chronhub\Chronicler\Chronicler;
use Chronhub\Chronicler\StreamName;
use Chronhub\Messager\Message\Message;
use Illuminate\Contracts\Validation\Factory;
use Chronhub\Chronicler\Http\Api\Response\ResponseFactory;
use Chronhub\Messager\Message\Serializer\MessageSerializer;
use Chronhub\Chronicler\Http\Api\Support\GenericAggregateId;

final class RetrieveAllAggregate
{
    public function __construct(private Chronicler $chronicler,
                                private MessageSerializer $messageSerializer,
                                private Factory $validation,
                                private ResponseFactory $response)
    {
    }

    public function __invoke(Request $request): ResponseFactory
    {
        $validator = $this->validation->make($request->all(), [
            'stream_name'  => 'required|string',
            'aggregate_id' => 'required|string|uuid',
        ]);

        if ($validator->fails()) {
            return $this->response
                ->withErrors($validator->errors())
                ->withStatusCode(400);
        }

        $streamName = new StreamName($request->get('stream_name'));

        $aggregateId = GenericAggregateId::fromString($request->get('aggregate_id'));

        $streamEvents = $this->chronicler->retrieveAll($streamName, $aggregateId);

        return $this->response
            ->withData($this->convertStreamEvents($streamEvents))
            ->withStatusCode(200);
    }

    private function convertStreamEvents(Generator $streamEvents): array
    {
        $events = [];

        foreach ($streamEvents as $message) {
            $events[] = $this->messageSerializer->serializeMessage(new Message($message));
        }

        return $events;
    }
}
