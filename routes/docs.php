<?php

use App\Http\Controllers\Docs\DocsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Documentation Routes
|--------------------------------------------------------------------------
| Public documentation routes. No authentication required.
*/

Route::get('/', [DocsController::class, 'index'])->name('index');
Route::get('/{version}/images/{path}', [DocsController::class, 'image'])
    ->where('path', '.*')
    ->name('image');
Route::get('/{version}/search-index', [DocsController::class, 'searchIndex'])->name('search-index');
Route::get('/{version}/{page?}', [DocsController::class, 'show'])->name('show');
