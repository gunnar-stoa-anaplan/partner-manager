<?php

use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalDealRegistrationController;
use App\Http\Controllers\Portal\PortalLoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Partner Portal Routes
|--------------------------------------------------------------------------
|
| These routes are loaded with the "web" middleware group and prefixed
| with "portal". They use the "portal" auth guard for Contact-based
| authentication, separate from the internal User auth.
|
*/

// Guest routes (login)
Route::middleware('guest:portal')->group(function () {
    Route::get('/login', [PortalLoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [PortalLoginController::class, 'login'])->name('login.submit');
});

// Authenticated portal routes
Route::middleware('portal')->group(function () {
    Route::post('/logout', [PortalLoginController::class, 'logout'])->name('logout');
    Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');

    // Deal Registrations
    Route::get('/deal-registrations', [PortalDealRegistrationController::class, 'index'])->name('deal-registrations.index');
    Route::get('/deal-registrations/create', [PortalDealRegistrationController::class, 'create'])->name('deal-registrations.create');
    Route::post('/deal-registrations', [PortalDealRegistrationController::class, 'store'])->name('deal-registrations.store');
});
