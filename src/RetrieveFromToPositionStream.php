<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Illuminate\Http\Request;
use Chronhub\Chronicler\Driver\QueryFilter;
use Illuminate\Contracts\Validation\Validator;
use Chronhub\Chronicler\Http\Api\QueryFilter\FromToPosition;

final class RetrieveFromToPositionStream extends RetrieveWithQueryFilter
{
    protected function makeValidator(Request $request): Validator
    {
        return $this->validation->make($request->all(), [
            'stream_name' => 'required|string',
            'from'        => 'required|integer|min:0|not_in:0',
            'to'          => 'required|integer|gt:from',
            'direction'   => 'required|string|in:asc,desc',
        ]);
    }

    protected function makeQueryFilter(Request $request): QueryFilter
    {
        $from = (int) $request->get('from');
        $to = (int) $request->get('to');
        $direction = $request->get('direction');

        return (new FromToPosition())($from, $to, $direction);
    }
}
