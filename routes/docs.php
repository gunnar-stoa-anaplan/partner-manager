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
    ->where(['version' => '[\d.]+', 'path' => '.*'])
    ->name('image');
Route::get('/{version}/search-index', [DocsController::class, 'searchIndex'])
    ->where('version', '[\d.]+')
    ->name('search-index');
Route::get('/{version}/{page?}', [DocsController::class, 'show'])
    ->where('version', '[\d.]+')
    ->name('show');
