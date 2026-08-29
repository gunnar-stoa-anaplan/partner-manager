<?php

use App\Http\Controllers\WorkshopMonitorDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/{session}', [WorkshopMonitorDashboardController::class, 'show'])->name('show');
