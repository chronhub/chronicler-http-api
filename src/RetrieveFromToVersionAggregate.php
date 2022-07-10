<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api;

use Illuminate\Http\Request;
use Chronhub\Chronicler\Driver\QueryFilter;
use Illuminate\Contracts\Validation\Validator;
use Chronhub\Chronicler\Http\Api\QueryFilter\FromToVersion;

final class RetrieveFromToVersionAggregate extends RetrieveWithQueryFilter
{
    protected function makeValidator(Request $request): Validator
    {
        return $this->validation->make($request->all(), [
            'stream_name' => 'required|string',
            'aggregate_id' => 'required|string|uuid',
            'from' => 'required|integer|min:0|not_in:0',
            'to' => 'required|integer|gt:from',
            'direction' => 'required|string|in:asc,desc',
        ]);
    }

    protected function makeQueryFilter(Request $request): QueryFilter
    {
        $aggregateId = $request->get('aggregate_id');
        $limit = (int) $request->get('from');
        $offset = (int) $request->get('to');
        $direction = $request->get('direction');

        return (new FromToVersion())($aggregateId, $limit, $offset, $direction);
    }
}
