<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Illuminate\Http\Request;
use Chronhub\Chronicler\Chronicler;
use Illuminate\Contracts\Validation\Factory;
use Chronhub\Chronicler\Http\Api\Response\ResponseFactory;
use function explode;
use function array_filter;

final class RequestCategoryNames
{
    public function __construct(private Chronicler $chronicler,
                                private Factory $validation,
                                private ResponseFactory $response)
    {
    }

    public function __invoke(Request $request): ResponseFactory
    {
        $validator = $this->validation->make($request->all(), [
            'category_names' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->response
                ->withErrors($validator->errors())
                ->withData(['extra' => 'Require one or many category names separated by comma'])
                ->withStatusCode(400, 'Invalid category names');
        }

        $result = $this->chronicler->fetchCategoryNames(...$this->extractCategoryNames($request));

        return $this->response
            ->withStatusCode(200)
            ->withData($result);
    }

    private function extractCategoryNames(Request $request): array
    {
        $categoryNames = explode(',', $request->get('category_names', []));

        return array_filter($categoryNames);
    }
}
