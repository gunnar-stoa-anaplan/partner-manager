<?php

use App\Http\Controllers\Portal\PortalBlueprintsController;
use App\Http\Controllers\Portal\PortalCalendarController;
use App\Http\Controllers\Portal\PortalCasesController;
use App\Http\Controllers\Portal\PortalCertificationsController;
use App\Http\Controllers\Portal\PortalContactsController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalDealRegistrationController;
use App\Http\Controllers\Portal\PortalEnablementController;
use App\Http\Controllers\Portal\PortalGoalsController;
use App\Http\Controllers\Portal\PortalLabController;
use App\Http\Controllers\Portal\PortalLaunchProgramController;
use App\Http\Controllers\Portal\PortalLoginController;
use App\Http\Controllers\Portal\PortalProjectsController;
use App\Http\Controllers\Portal\PortalWorkspaceController;
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

    // All roles: enablement, blueprints & workspaces
    Route::get('/enablement', [PortalEnablementController::class, 'index'])->name('enablement.index');
    Route::get('/blueprints', [PortalBlueprintsController::class, 'index'])->name('blueprints.index');
    Route::get('/blueprints/{enrollment}', [PortalBlueprintsController::class, 'show'])->name('blueprints.show');
    Route::get('/workspaces', [PortalWorkspaceController::class, 'index'])->name('workspaces.index');

    // Labs (all roles)
    Route::get('/labs', [PortalLabController::class, 'index'])->name('labs.index');
    Route::get('/labs/{lab}', [PortalLabController::class, 'show'])->name('labs.show');
    Route::post('/labs/checkpoints/{checkpoint}/validate', [PortalLabController::class, 'validateCheckpoint'])->name('labs.validate-checkpoint');

    // Admin only: goals, certifications, calendar, team management
    Route::middleware('portal.role:admin')->group(function () {
        Route::get('/goals', [PortalGoalsController::class, 'index'])->name('goals.index');
        Route::get('/certifications', [PortalCertificationsController::class, 'index'])->name('certifications.index');
        Route::get('/calendar', [PortalCalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [PortalCalendarController::class, 'events'])->name('calendar.events');
        Route::get('/contacts', [PortalContactsController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/create', [PortalContactsController::class, 'create'])->name('contacts.create');
        Route::post('/contacts', [PortalContactsController::class, 'store'])->name('contacts.store');
        Route::get('/contacts/{contact}/edit', [PortalContactsController::class, 'edit'])->name('contacts.edit');
        Route::put('/contacts/{contact}', [PortalContactsController::class, 'update'])->name('contacts.update');
        Route::delete('/contacts/{contact}', [PortalContactsController::class, 'destroy'])->name('contacts.destroy');

        // Launch Programs
        Route::get('/launch-programs', [PortalLaunchProgramController::class, 'index'])->name('launch-programs.index');
        Route::get('/launch-programs/{programPartner}', [PortalLaunchProgramController::class, 'show'])->name('launch-programs.show');
        Route::post('/launch-programs/{programPartner}/milestones/{milestone}/complete', [PortalLaunchProgramController::class, 'completeMilestone'])->name('launch-programs.complete-milestone');
        Route::post('/launch-programs/{programPartner}/feedback', [PortalLaunchProgramController::class, 'submitFeedback'])->name('launch-programs.submit-feedback');
        Route::post('/launch-programs/{programPartner}/participants', [PortalLaunchProgramController::class, 'addParticipant'])->name('launch-programs.add-participant');
        Route::delete('/launch-programs/{programPartner}/participants/{participant}', [PortalLaunchProgramController::class, 'removeParticipant'])->name('launch-programs.remove-participant');
    });

    // Admin + Sales: deal registrations
    Route::middleware('portal.role:admin,sales')->group(function () {
        Route::get('/deal-registrations', [PortalDealRegistrationController::class, 'index'])->name('deal-registrations.index');
        Route::get('/deal-registrations/create', [PortalDealRegistrationController::class, 'create'])->name('deal-registrations.create');
        Route::post('/deal-registrations', [PortalDealRegistrationController::class, 'store'])->name('deal-registrations.store');
    });

    // Admin + Delivery: projects & cases
    Route::middleware('portal.role:admin,delivery')->group(function () {
        Route::get('/projects', [PortalProjectsController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project}', [PortalProjectsController::class, 'show'])->name('projects.show');
        Route::get('/cases', [PortalCasesController::class, 'index'])->name('cases.index');
        Route::post('/cases', [PortalCasesController::class, 'store'])->name('cases.store');
    });
});
