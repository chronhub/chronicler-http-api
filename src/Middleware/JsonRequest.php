<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Http\Api\Middleware;

use Closure;
use Illuminate\Http\Request;
use Chronhub\Chronicler\Http\Api\Response\ResponseFactory;

final class JsonRequest
{
    public function __construct(private ResponseFactory $responseFactory)
    {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->expectsJson()) {
            return $this->responseFactory->withStatusCode(415, 'Invalid request');
        }

        return $next($request);
    }
}
