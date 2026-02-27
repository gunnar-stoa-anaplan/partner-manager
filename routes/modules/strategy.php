<?php

use App\Http\Controllers\Strategy\ActualsController;
use App\Http\Controllers\Strategy\BenchmarkController;
use App\Http\Controllers\Strategy\DashboardController;
use App\Http\Controllers\Strategy\PlanController;
use App\Http\Controllers\Strategy\ReviewController;
use App\Http\Controllers\Strategy\ScenarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Strategy Workbench Module Routes
|--------------------------------------------------------------------------
|
| Feature slug: strategy
| Gated by module:strategy middleware via bootstrap/app.php
|
*/

Route::get('/', DashboardController::class)->name('dashboard');

// Plans
Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
Route::get('/plans/create', [PlanController::class, 'create'])->name('plans.create');
Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
Route::get('/plans/{plan}', [PlanController::class, 'show'])->name('plans.show');
Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
Route::post('/plans/{plan}/commit', [PlanController::class, 'commit'])->name('plans.commit');
Route::get('/plans/{plan}/variance', [PlanController::class, 'variance'])->name('plans.variance');
Route::post('/plans/{plan}/version', [PlanController::class, 'createVersion'])->name('plans.version');
Route::post('/plans/{plan}/targets', [PlanController::class, 'storeTarget'])->name('plans.targets.store');
Route::post('/plans/{plan}/assumptions', [PlanController::class, 'storeAssumption'])->name('plans.assumptions.store');
Route::post('/plans/{plan}/budgets', [PlanController::class, 'storeBudget'])->name('plans.budgets.store');
Route::post('/plans/{plan}/initiatives', [PlanController::class, 'storeInitiative'])->name('plans.initiatives.store');
Route::put('/initiatives/{initiative}', [PlanController::class, 'updateInitiative'])->name('initiatives.update');

// Actuals refresh
Route::post('/plans/{plan}/refresh-actuals', [ActualsController::class, 'refresh'])->name('plans.refresh-actuals');

// Scenarios
Route::get('/scenarios', [ScenarioController::class, 'index'])->name('scenarios.index');
Route::post('/scenarios', [ScenarioController::class, 'store'])->name('scenarios.store');
Route::get('/scenarios/compare', [ScenarioController::class, 'compare'])->name('scenarios.compare');
Route::get('/scenarios/{scenario}', [ScenarioController::class, 'show'])->name('scenarios.show');
Route::post('/scenarios/{scenario}/overrides', [ScenarioController::class, 'storeOverride'])->name('scenarios.overrides.store');
Route::post('/scenarios/{scenario}/finalize', [ScenarioController::class, 'finalize'])->name('scenarios.finalize');

// Peer Benchmarks
Route::get('/benchmarks', BenchmarkController::class)->name('benchmarks');

// Reviews
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
Route::get('/reviews/{review}', [ReviewController::class, 'show'])->name('reviews.show');
Route::post('/reviews/{review}/complete', [ReviewController::class, 'complete'])->name('reviews.complete');
