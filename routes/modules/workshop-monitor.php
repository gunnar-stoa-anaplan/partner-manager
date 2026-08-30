<?php

use App\Http\Controllers\WorkshopMonitorDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/{session}', [WorkshopMonitorDashboardController::class, 'show'])->name('show');
Route::post('/{session}/threads/{thread}/answer', [WorkshopMonitorDashboardController::class, 'markAnswered'])->name('threads.answer');
