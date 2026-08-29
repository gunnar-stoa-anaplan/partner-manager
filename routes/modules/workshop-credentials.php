<?php

use App\Http\Controllers\WorkshopCredentialController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkshopCredentialController::class, 'index'])->name('index');
Route::get('/create', [WorkshopCredentialController::class, 'create'])->name('create');
Route::post('/', [WorkshopCredentialController::class, 'store'])->name('store');
Route::get('/{credential}/edit', [WorkshopCredentialController::class, 'edit'])->name('edit');
Route::put('/{credential}', [WorkshopCredentialController::class, 'update'])->name('update');
Route::delete('/{credential}', [WorkshopCredentialController::class, 'destroy'])->name('destroy');
Route::post('/{credential}/assign', [WorkshopCredentialController::class, 'assign'])->name('assign');
Route::post('/assignments/{assignment}/release', [WorkshopCredentialController::class, 'release'])->name('assignments.release');
