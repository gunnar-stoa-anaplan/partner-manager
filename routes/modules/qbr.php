<?php

use App\Http\Controllers\PathReviewController;
use App\Http\Controllers\QbrController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| QBR Workbench Module Routes
|--------------------------------------------------------------------------
|
| Feature slug: qbr
| Gated by module:qbr middleware via bootstrap/app.php
|
*/

Route::prefix('act')->name('act.')->group(function () {
    // QBR Workbench
    Route::get('/qbr', [QbrController::class, 'index'])->name('qbr.index');
    Route::post('/qbr', [QbrController::class, 'store'])->name('qbr.store');
    Route::get('/qbr/{session}', [QbrController::class, 'show'])->name('qbr.show');
    Route::put('/qbr/{session}', [QbrController::class, 'update'])->name('qbr.update');
    Route::put('/qbr/{session}/sections/{section}', [QbrController::class, 'updateSection'])->name('qbr.sections.update');
    Route::post('/qbr/{session}/complete', [QbrController::class, 'complete'])->name('qbr.complete');

    // Path Reviews
    Route::get('/path-reviews', [PathReviewController::class, 'index'])->name('path-reviews.index');
    Route::get('/path-reviews/{enrollment}', [PathReviewController::class, 'show'])->name('path-reviews.show');
    Route::post('/path-reviews/{enrollment}/advance', [PathReviewController::class, 'advance'])->name('path-reviews.advance');
    Route::post('/path-reviews/{enrollment}/hold', [PathReviewController::class, 'hold'])->name('path-reviews.hold');
});
