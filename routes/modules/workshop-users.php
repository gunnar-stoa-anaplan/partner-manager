<?php

use App\Http\Controllers\WorkshopUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkshopUserController::class, 'index'])->name('index');
Route::post('/', [WorkshopUserController::class, 'store'])->name('store');
Route::get('/{contact}/activity', [WorkshopUserController::class, 'activity'])->name('activity');
Route::post('/{contact}/reset-password', [WorkshopUserController::class, 'resetPassword'])->name('reset-password');
Route::post('/{contact}/disable', [WorkshopUserController::class, 'disable'])->name('disable');
