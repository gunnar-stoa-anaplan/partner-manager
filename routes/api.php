<?php

use App\Http\Controllers\Api\EnablementDesignerController;
use App\Http\Controllers\Api\InternalActivationAggregateController;
use App\Http\Controllers\Api\InternalMetricsController;
use App\Http\Controllers\Api\InternalSignalAggregateController;
use App\Http\Controllers\Api\LabDesignerController;
use App\Http\Controllers\Api\PartnerAdvisorController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EnablementController;
use App\Http\Controllers\Api\V1\ImportController;
use App\Http\Controllers\Api\V1\PartnerController;
use App\Http\Controllers\Api\V1\TokenController;
use Illuminate\Support\Facades\Route;

// --- Authentication (no token required) ---
Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');

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

    // --- Imports (write, gated by integration_hub)
    Route::middleware(['abilities:imports:write', 'module:integration_hub.hub'])->group(function () {
        Route::post('/imports/opportunities', [ImportController::class, 'opportunities']);
        Route::post('/imports/projects', [ImportController::class, 'projects']);
        Route::post('/imports/contacts', [ImportController::class, 'contacts']);
    });
    Route::middleware(['abilities:imports:read', 'module:integration_hub.hub'])->group(function () {
        Route::get('/imports/{id}', [ImportController::class, 'show']);
    });

    // --- Partners (read, gated by partner_management) ---
    Route::middleware(['abilities:partners:read', 'module:partner_management'])->group(function () {
        Route::get('/partners', [PartnerController::class, 'index']);
        Route::get('/partners/{id}', [PartnerController::class, 'show']);
        Route::get('/partners/{id}/pipeline', [PartnerController::class, 'pipeline']);
        Route::get('/partners/{id}/projects', [PartnerController::class, 'projects']);
        Route::get('/partners/{partner}/advisor', PartnerAdvisorController::class)->name('api.partners.advisor');
    });

    // --- Enablement (read, gated by enablement) ---
    Route::middleware(['abilities:enablement:read', 'module:enablement'])->group(function () {
        Route::get('/enablement/calendar', [EnablementController::class, 'calendar']);
        Route::get('/enablement/events', [EnablementController::class, 'events']);
        Route::get('/enablement/events/{id}', [EnablementController::class, 'show']);
        Route::post('/enablement/designer', EnablementDesignerController::class)->name('api.enablement.designer');
        Route::post('/enablement/lab-designer', LabDesignerController::class)->name('api.enablement.lab-designer');
    });
});
