<?php

use App\Http\Controllers\WorkshopCredentialController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkshopCredentialController::class, 'index'])->name('index');
Route::get('/create', [WorkshopCredentialController::class, 'create'])->name('create');
Route::post('/', [WorkshopCredentialController::class, 'store'])->name('store');
Route::post('/bulk-upload', [WorkshopCredentialController::class, 'bulkUpload'])->name('bulk-upload');
Route::get('/refresh-queue', [WorkshopCredentialController::class, 'refreshQueue'])->name('refresh-queue');
Route::post('/{credential}/mark-refreshed', [WorkshopCredentialController::class, 'markRefreshed'])->name('mark-refreshed');
Route::get('/{credential}/edit', [WorkshopCredentialController::class, 'edit'])->name('edit');
Route::put('/{credential}', [WorkshopCredentialController::class, 'update'])->name('update');
Route::delete('/{credential}', [WorkshopCredentialController::class, 'destroy'])->name('destroy');
Route::post('/{credential}/assign', [WorkshopCredentialController::class, 'assign'])->name('assign');
Route::post('/assignments/{assignment}/release', [WorkshopCredentialController::class, 'release'])->name('assignments.release');
Route::post('/assignments/{assignment}/extend', [WorkshopCredentialController::class, 'extend'])->name('assignments.extend');
