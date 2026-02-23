<?php

use App\Http\Controllers\Api\InternalActivationAggregateController;
use App\Http\Controllers\Api\InternalMetricsController;
use App\Http\Controllers\Api\InternalSignalAggregateController;
use App\Http\Controllers\Api\V1\EnablementController;
use App\Http\Controllers\Api\V1\ImportController;
use App\Http\Controllers\Api\V1\PartnerController;
use App\Http\Controllers\Api\V1\TokenController;
use Illuminate\Support\Facades\Route;

// --- Internal (agent-only, no auth) ---
Route::get('/internal/metrics', InternalMetricsController::class);
Route::get('/internal/signal-aggregate', InternalSignalAggregateController::class);
Route::post('/internal/signal-benchmarks', [InternalSignalAggregateController::class, 'storeBenchmarks']);
Route::get('/internal/activation-aggregate', InternalActivationAggregateController::class);
Route::post('/internal/activation-benchmarks', [InternalActivationAggregateController::class, 'storeBenchmarks']);

Route::middleware('auth:sanctum')->group(function () {

    // --- Token Management ---
    Route::get('/tokens', [TokenController::class, 'index']);
    Route::post('/tokens', [TokenController::class, 'store']);
    Route::delete('/tokens/{id}', [TokenController::class, 'destroy']);

    // --- Imports (write) ---
    Route::middleware('abilities:imports:write')->group(function () {
        Route::post('/imports/opportunities', [ImportController::class, 'opportunities']);
        Route::post('/imports/projects', [ImportController::class, 'projects']);
        Route::post('/imports/contacts', [ImportController::class, 'contacts']);
    });
    Route::middleware('abilities:imports:read')->group(function () {
        Route::get('/imports/{id}', [ImportController::class, 'show']);
    });

    // --- Partners (read) ---
    Route::middleware('abilities:partners:read')->group(function () {
        Route::get('/partners', [PartnerController::class, 'index']);
        Route::get('/partners/{id}', [PartnerController::class, 'show']);
        Route::get('/partners/{id}/pipeline', [PartnerController::class, 'pipeline']);
        Route::get('/partners/{id}/projects', [PartnerController::class, 'projects']);
    });

    // --- Enablement (read) ---
    Route::middleware('abilities:enablement:read')->group(function () {
        Route::get('/enablement/calendar', [EnablementController::class, 'calendar']);
        Route::get('/enablement/events', [EnablementController::class, 'events']);
        Route::get('/enablement/events/{id}', [EnablementController::class, 'show']);
    });
});
