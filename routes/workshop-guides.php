<?php

use App\Http\Controllers\WorkshopGuide\WorkshopGuideController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Workshop Guide Routes
|--------------------------------------------------------------------------
| Public workshop guide routes. No authentication required by default
| (individual guides may enforce auth via requires_auth flag).
*/

Route::get('/', [WorkshopGuideController::class, 'index'])->name('index');
Route::get('/{guide:slug}/images/{path}', [WorkshopGuideController::class, 'image'])
    ->where('path', '.*')
    ->name('image');
Route::get('/{guide:slug}/search-index', [WorkshopGuideController::class, 'searchIndex'])
    ->name('search-index');
Route::get('/{guide:slug}/{page?}', [WorkshopGuideController::class, 'show'])
    ->name('show');
