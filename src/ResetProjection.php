<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Illuminate\Http\Request;
use Chronhub\Chronicler\StreamName;
use Chronhub\Projector\ProjectorManager;
use Illuminate\Contracts\Validation\Factory;
use Chronhub\Chronicler\Http\Api\Response\ResponseFactory;

final class ResetProjection
{
    public function __construct(private ProjectorManager $projectorManager,
                                private Factory $validation,
                                private ResponseFactory $responseFactory)
    {
    }

    public function __invoke(Request $request): ResponseFactory
    {
        $validator = $this->validation->make($request->all(), [
            'projection_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->responseFactory
                ->withErrors($validator->errors())
                ->withStatusCode(400);
        }

        $projectionName = new StreamName($request->get('projection_name'));

        $this->projectorManager->reset($projectionName->toString());

        return $this->responseFactory->withStatusCode(204);
    }
}
