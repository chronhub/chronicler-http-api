<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Illuminate\Http\Request;
use Chronhub\Chronicler\Chronicler;
use Chronhub\Chronicler\StreamName;
use Illuminate\Contracts\Validation\Factory;
use Chronhub\Chronicler\Http\Api\Response\ResponseFactory;

final class RequestStreamNames
{
    public function __construct(private Chronicler $chronicler,
                                private Factory $validation,
                                private ResponseFactory $response)
    {
    }

    public function __invoke(Request $request): ResponseFactory
    {
        $validator = $this->validation->make($request->all(), [
            'stream_names' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->response
                ->withErrors($validator->errors())
                ->withData(['extra' => 'Require one or many stream names separated by comma'])
                ->withStatusCode(400, 'Invalid stream names');
        }

        $streamNames = $this->chronicler->fetchStreamNames(
            ...$this->convertStreamNamesFromRequest($request)
        );

        $streamNames = array_map(fn (StreamName $streamName): string => $streamName->toString(), $streamNames);

        return $this->response
            ->withData($streamNames)
            ->withStatusCode(200);
    }

    private function convertStreamNamesFromRequest(Request $request): array
    {
        $streamNames = array_filter(explode(',', $request->get('stream_names')));

        return array_map(function (string $streamName): StreamName {
            return new StreamName($streamName);
        }, $streamNames);
    }
}
