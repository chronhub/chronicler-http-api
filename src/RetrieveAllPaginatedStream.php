<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Illuminate\Http\Request;
use Chronhub\Chronicler\Driver\QueryFilter;
use Illuminate\Contracts\Validation\Validator;
use Chronhub\Chronicler\Http\Api\QueryFilter\AllPaginated;

final class RetrieveAllPaginatedStream extends RetrieveWithQueryFilter
{
    protected function makeValidator(Request $request): Validator
    {
        return $this->validation->make($request->all(), [
            'stream_name' => 'required|string',
            'limit' => 'required|integer',
            'direction' => 'required|string|in:asc,desc',
            'offset' => 'integer|min:0|not_in:0',
        ]);
    }

    protected function makeQueryFilter(Request $request): QueryFilter
    {
        $limit = (int) $request->get('limit');
        $offset = (int) $request->get('offset') ?? 0;
        $direction = $request->get('direction');

        return (new AllPaginated())($limit, $offset, $direction);
    }
}
