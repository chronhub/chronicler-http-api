<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Illuminate\Http\Request;
use Chronhub\Projector\ProjectorManager;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use Chronhub\Chronicler\Http\Api\Response\ResponseFactory;

final class DeleteProjection
{
    public function __construct(private ProjectorManager $projectorManager,
                                private Factory $validation,
                                private ResponseFactory $response)
    {
    }

    public function __invoke(Request $request): ResponseFactory
    {
        $validator = $this->makeValidatorFor($request);

        if ($validator->fails()) {
            return $this->response
                ->withErrors($validator->errors())
                ->withStatusCode(400);
        }

        $projectionName = $request->get('projection_name');
        $includeEvents = (bool) $request->get('include_events');

        $this->projectorManager->delete($projectionName, $includeEvents);

        return $this->response->withStatusCode(204);
    }

    private function makeValidatorFor(Request $request): Validator
    {
        return $this->validation->make($request->all(), [
            'projection_name' => 'required|string',
            'include_events' => 'required|boolean',
        ]);
    }
}
