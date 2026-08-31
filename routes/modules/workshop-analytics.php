<?php

use App\Http\Controllers\WorkshopAnalyticsController;
use Illuminate\Support\Facades\Route;

Route::get('/workshops', [WorkshopAnalyticsController::class, 'workshops'])->name('workshops');
Route::get('/sessions', [WorkshopAnalyticsController::class, 'sessions'])->name('sessions');
Route::get('/participation', [WorkshopAnalyticsController::class, 'participation'])->name('participation');
Route::get('/gaps', [WorkshopAnalyticsController::class, 'gaps'])->name('gaps');
