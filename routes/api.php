<?php

use App\Http\Controllers\Api\ContentExtractionRunController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('websites', WebsiteController::class)->only(['index', 'show']);
    Route::apiResource('websites.pages', PageController::class)->only(['index', 'show']);
    Route::apiResource('websites.content-extraction-runs', ContentExtractionRunController::class)->only(['index', 'show']);
});
