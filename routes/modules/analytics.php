<?php

use App\Http\Controllers\BenchmarkController;
use App\Http\Controllers\MeasureController;
use App\Http\Controllers\PartnerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Analytics Suite Module Routes
|--------------------------------------------------------------------------
|
| Feature slug: analytics
| Gated by module:analytics middleware via bootstrap/app.php
|
*/

// Analysis & Insights
Route::get('/custom-filters', [PartnerController::class, 'analysis'])->name('custom-filters.index');
Route::get('/wizard', [PartnerController::class, 'guidedAnalysis'])->name('wizard.index');
Route::get('/coach', [PartnerController::class, 'partnerCoach'])->name('coach.index');

// Full Summary & Insights (portfolio-level)
Route::get('/full-summary', [PartnerController::class, 'fullSummary'])->name('partners.full-summary');
Route::get('/full-insights', [PartnerController::class, 'fullInsights'])->name('partners.full-insights');

// Measure Section — Tabbed Analytics
Route::prefix('measure')->name('measure.')->group(function () {
    Route::get('/', [MeasureController::class, 'index'])->name('index');
    Route::get('/at-a-glance', fn () => redirect()->route('measure.index', ['tab' => 'at-a-glance']))->name('at-a-glance');
    Route::get('/pipeline', fn () => redirect()->route('measure.index', ['tab' => 'pipeline']))->name('pipeline');
    Route::get('/lead-flow', fn () => redirect()->route('measure.index', ['tab' => 'leads']))->name('lead-flow');
    Route::get('/funding', fn () => redirect()->route('measure.index', ['tab' => 'funding']))->name('funding');
    Route::get('/revenue', fn () => redirect()->route('measure.index', ['tab' => 'revenue']))->name('revenue');
    Route::get('/enablement', fn () => redirect()->route('measure.index', ['tab' => 'enablement']))->name('enablement');
    Route::get('/delivery', fn () => redirect()->route('measure.index', ['tab' => 'delivery']))->name('delivery');

    // Benchmarks
    Route::get('/benchmarks', [BenchmarkController::class, 'index'])->name('benchmarks.index');
    Route::get('/benchmarks/{partner}', [BenchmarkController::class, 'partner'])->name('benchmarks.partner');
});
