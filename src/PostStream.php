<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Throwable;
use Illuminate\Http\Request;
use Chronhub\Chronicler\Stream;
use Chronhub\Chronicler\Chronicler;
use Chronhub\Chronicler\StreamName;
use Illuminate\Contracts\Validation\Factory;
use Chronhub\Chronicler\TransactionalChronicler;
use Chronhub\Messager\Message\Factory\MessageFactory;
use Chronhub\Chronicler\Http\Api\Response\ResponseFactory;

final class PostStream
{
    public function __construct(private Chronicler $chronicler,
                                private MessageFactory $messageFactory,
                                private Factory $validation,
                                private ResponseFactory $response)
    {
    }

    public function __invoke(Request $request): ResponseFactory
    {
        $validateStream = $this->validation->make($request->all(), [
            'stream_name' => 'required|string',
        ]);

        if ($validateStream->failed()) {
            return $this->response
                ->withErrors($validateStream->errors())
                ->withStatusCode(400);
        }

        $payload = $request->json()->all();

        $validatePayload = $this->validation->make($payload, [
            'headers' => 'array',
            'payload' => 'array',
        ]);

        if ($validatePayload->failed()) {
            return $this->response
                ->withErrors($validateStream->errors())
                ->withStatusCode(400);
        }

        $streamName = new StreamName($request->get('stream_name'));

        $this->persistStream($this->produceStream($streamName, $payload));

        return $this->response->withStatusCode(204);
    }

    private function produceStream(StreamName $streamName, array $payload): Stream
    {
        $messages = [];

        foreach ($payload as $message) {
            $messages[] = $this->messageFactory->createFromMessage($message);
        }

        return new Stream($streamName, $messages);
    }

    private function persistStream(Stream $stream): void
    {
        if ($this->chronicler instanceof TransactionalChronicler) {
            $this->chronicler->beginTransaction();
        }

        try {
            $this->chronicler->hasStream($stream->name())
                ? $this->chronicler->persist($stream)
                : $this->chronicler->persistFirstCommit($stream);
        } catch (Throwable $exception) {
            if ($this->chronicler instanceof TransactionalChronicler) {
                $this->chronicler->rollbackTransaction();
            }

            throw $exception;
        }

        if ($this->chronicler instanceof TransactionalChronicler) {
            $this->chronicler->commitTransaction();
        }
    }
}
