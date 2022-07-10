<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('aggregate')->group(function (): void {
    Route::get('/', \Chronhub\Chronicler\Http\Api\RetrieveAllAggregate::class);
    Route::get('/from_to', \Chronhub\Chronicler\Http\Api\RetrieveFromToVersionAggregate::class);
});

Route::prefix('stream')->group(function (): void {
    Route::post('/', \Chronhub\Chronicler\Http\Api\PostStream::class);
    Route::delete('/', \Chronhub\Chronicler\Http\Api\DeleteStream::class);
    Route::get('/', \Chronhub\Chronicler\Http\Api\RetrieveFromIncludedPositionStream::class);
    Route::get('/from_to', \Chronhub\Chronicler\Http\Api\RetrieveFromToPositionStream::class);
    Route::get('/paginated', \Chronhub\Chronicler\Http\Api\RetrieveAllPaginatedStream::class);
    Route::get('/names', \Chronhub\Chronicler\Http\Api\RequestStreamNames::class);
    Route::get('/categories', \Chronhub\Chronicler\Http\Api\RequestCategoryNames::class);
    Route::get('/exists', \Chronhub\Chronicler\Http\Api\RequestStreamExists::class);
});

Route::prefix('projection')->group(function (): void {
    Route::delete('/', \Chronhub\Chronicler\Http\Api\DeleteProjection::class);
    Route::get('/reset', \Chronhub\Chronicler\Http\Api\ResetProjection::class);
    Route::get('/stop', \Chronhub\Chronicler\Http\Api\StopProjection::class);
    Route::get('/state', \Chronhub\Chronicler\Http\Api\RequestProjectionState::class);
    Route::get('/status', \Chronhub\Chronicler\Http\Api\RequestProjectionStatus::class);
    Route::get('/position', \Chronhub\Chronicler\Http\Api\RequestProjectionStreamPositions::class);
});
