<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Illuminate\Http\Request;
use Chronhub\Projector\ProjectorManager;
use Illuminate\Contracts\Validation\Factory;
use Chronhub\Chronicler\Http\Api\Response\ResponseFactory;

final class RequestProjectionStatus
{
    public function __construct(private ProjectorManager $projectorManager,
                                private Factory $validation,
                                private ResponseFactory $response)
    {
    }

    public function __invoke(Request $request): ResponseFactory
    {
        $validator = $this->validation->make($request->all(), [
            'projection_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->response
                ->withErrors($validator->errors())
                ->withStatusCode(400, 'Invalid projection name');
        }

        $projectionName = $request->get('projection_name');

        $result = $this->projectorManager->statusOf($projectionName);

        return $this->response
            ->withData([$projectionName => $result])
            ->withStatusCode(200);
    }
}
