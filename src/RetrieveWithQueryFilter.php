<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Generator;
use Illuminate\Http\Request;
use Chronhub\Chronicler\Chronicler;
use Chronhub\Chronicler\StreamName;
use Chronhub\Messager\Message\Message;
use Chronhub\Chronicler\Driver\QueryFilter;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use Chronhub\Chronicler\Http\Api\Response\ResponseFactory;
use Chronhub\Messager\Message\Serializer\MessageSerializer;

abstract class RetrieveWithQueryFilter
{
    public function __construct(protected Chronicler $chronicler,
                                protected MessageSerializer $messageSerializer,
                                protected Factory $validation,
                                protected ResponseFactory $response)
    {
    }

    public function __invoke(Request $request): ResponseFactory
    {
        $validator = $this->makeValidator($request);

        if ($validator->fails()) {
            return $this->response
                ->withErrors($validator->errors())
                ->withStatusCode(400);
        }

        $streamName = new StreamName($request->get('stream_name'));

        $streamEvents = $this->chronicler->retrieveFiltered(
            $streamName,
            $this->makeQueryFilter($request)
        );

        return $this->response
            ->withStatusCode(200)
            ->withData($this->convertStreamEvents($streamEvents));
    }

    private function convertStreamEvents(Generator $streamEvents): array
    {
        $events = [];

        foreach ($streamEvents as $message) {
            $events[] = $this->messageSerializer->serializeMessage(new Message($message));
        }

        return $events;
    }

    abstract protected function makeValidator(Request $request): Validator;

    abstract protected function makeQueryFilter(Request $request): QueryFilter;
}
