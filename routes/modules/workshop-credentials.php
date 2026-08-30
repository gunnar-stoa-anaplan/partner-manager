<?php

use App\Http\Controllers\WorkshopCredentialController;
use Illuminate\Support\Facades\Route;

// Literal/no-param routes registered before the /{workshop} wildcard below,
// so it doesn't swallow them as if they were a workshop id.
Route::get('/refresh-queue', [WorkshopCredentialController::class, 'refreshQueue'])->name('refresh-queue');
Route::post('/{credential}/mark-refreshed', [WorkshopCredentialController::class, 'markRefreshed'])->name('mark-refreshed');
Route::get('/{credential}/edit', [WorkshopCredentialController::class, 'edit'])->name('edit');
Route::put('/{credential}', [WorkshopCredentialController::class, 'update'])->name('update');
Route::delete('/{credential}', [WorkshopCredentialController::class, 'destroy'])->name('destroy');
Route::post('/{credential}/assign', [WorkshopCredentialController::class, 'assign'])->name('assign');
Route::post('/assignments/{assignment}/release', [WorkshopCredentialController::class, 'release'])->name('assignments.release');
Route::post('/assignments/{assignment}/extend', [WorkshopCredentialController::class, 'extend'])->name('assignments.extend');

Route::get('/{workshop}', [WorkshopCredentialController::class, 'index'])->name('index');
Route::get('/{workshop}/create', [WorkshopCredentialController::class, 'create'])->name('create');
Route::post('/{workshop}', [WorkshopCredentialController::class, 'store'])->name('store');
Route::post('/{workshop}/bulk-upload', [WorkshopCredentialController::class, 'bulkUpload'])->name('bulk-upload');
