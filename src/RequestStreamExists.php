<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Illuminate\Http\Request;
use Chronhub\Chronicler\Chronicler;
use Chronhub\Chronicler\StreamName;
use Illuminate\Contracts\Validation\Factory;
use Chronhub\Chronicler\Http\Api\Response\ResponseFactory;

final class RequestStreamExists
{
    public function __construct(private Chronicler $chronicler,
                                private Factory $validation,
                                private ResponseFactory $response)
    {
    }

    public function __invoke(Request $request): ResponseFactory
    {
        $validator = $this->validation->make($request->all(), [
            'stream_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->response
                ->withErrors($validator->errors())
                ->withStatusCode(400);
        }

        $streamName = new StreamName($request->get('stream_name'));

        $hasStream = $this->chronicler->hasStream($streamName);

        $result = [$streamName->toString() => $hasStream];

        return $this->response
            ->withStatusCode($hasStream ? 200 : 404)
            ->withData($result);
    }
}
