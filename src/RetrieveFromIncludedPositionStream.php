<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Illuminate\Http\Request;
use Chronhub\Chronicler\Driver\QueryFilter;
use Illuminate\Contracts\Validation\Validator;
use Chronhub\Chronicler\Http\Api\QueryFilter\FromIncludedPosition;

final class RetrieveFromIncludedPositionStream extends RetrieveWithQueryFilter
{
    protected function makeValidator(Request $request): Validator
    {
        return $this->validation->make($request->all(), [
            'stream_name' => 'required|string',
            'position' => 'required|integer|min:0|not_in:0',
        ]);
    }

    protected function makeQueryFilter(Request $request): QueryFilter
    {
        $position = (int) $request->get('position');

        return (new FromIncludedPosition())($position);
    }
}
