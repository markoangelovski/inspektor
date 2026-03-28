<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContentExtractionRunController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::prefix('content-extraction')->group(function () {
    Route::get('/runs/{run}/progress', [ContentExtractionRunController::class, 'progress']);
    Route::get('/runs/{run}/events', [ContentExtractionRunController::class, 'events']);
});

Route::get(
    '/websites/{website}/content-extraction-runs',
    [ContentExtractionRunController::class, 'history']
);
