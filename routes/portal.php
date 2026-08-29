<?php

use App\Http\Controllers\Portal\PortalBlueprintsController;
use App\Http\Controllers\Portal\PortalCalendarController;
use App\Http\Controllers\Portal\PortalCasesController;
use App\Http\Controllers\Portal\PortalCertificationsController;
use App\Http\Controllers\Portal\PortalCompanyProfileController;
use App\Http\Controllers\Portal\PortalContactsController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalDealRegistrationController;
use App\Http\Controllers\Portal\PortalEnablementController;
use App\Http\Controllers\Portal\PortalGoalsController;
use App\Http\Controllers\Portal\PortalLabController;
use App\Http\Controllers\Portal\PortalLaunchProgramController;
use App\Http\Controllers\Portal\PortalLoginController;
use App\Http\Controllers\Portal\PortalPathController;
use App\Http\Controllers\Portal\PortalProjectsController;
use App\Http\Controllers\Portal\PortalWorkspaceController;
use App\Http\Controllers\Portal\WorkshopController as PortalWorkshopController;
use App\Http\Controllers\Portal\WorkshopParticipationController as PortalWorkshopParticipationController;
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
    Route::get('/blueprints/assignments/{assignment}', [PortalBlueprintsController::class, 'showAssignment'])->name('blueprints.assignment');
    Route::get('/blueprints/{enrollment}', [PortalBlueprintsController::class, 'show'])->name('blueprints.show');
    Route::get('/workspaces', [PortalWorkspaceController::class, 'index'])->name('workspaces.index');

    // Labs (all roles)
    Route::get('/labs', [PortalLabController::class, 'index'])->name('labs.index');
    Route::get('/labs/{lab}', [PortalLabController::class, 'show'])->name('labs.show');
    Route::post('/labs/checkpoints/{checkpoint}/validate', [PortalLabController::class, 'validateCheckpoint'])->name('labs.validate-checkpoint');

    // Capability-gated routes
    Route::middleware('portal.can:goals')->group(function () {
        Route::get('/goals', [PortalGoalsController::class, 'index'])->name('goals.index');
    });

    Route::middleware('portal.can:certifications')->group(function () {
        Route::get('/certifications', [PortalCertificationsController::class, 'index'])->name('certifications.index');
    });

    Route::middleware('portal.can:calendar')->group(function () {
        Route::get('/calendar', [PortalCalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [PortalCalendarController::class, 'events'])->name('calendar.events');
    });

    // Company profile — partner_admin only
    Route::middleware('portal.can:company_profile')->group(function () {
        Route::get('/company-profile', [PortalCompanyProfileController::class, 'edit'])->name('company-profile.edit');
        Route::put('/company-profile', [PortalCompanyProfileController::class, 'update'])->name('company-profile.update');
        Route::post('/company-profile/entries', [PortalCompanyProfileController::class, 'storeEntry'])->name('company-profile.entries.store');
        Route::put('/company-profile/entries/{entry}', [PortalCompanyProfileController::class, 'updateEntry'])->name('company-profile.entries.update');
        Route::delete('/company-profile/entries/{entry}', [PortalCompanyProfileController::class, 'destroyEntry'])->name('company-profile.entries.destroy');
    });

    // Team management — partner_admin only (has contacts via wildcard)
    Route::middleware('portal.can:contacts')->group(function () {
        Route::get('/contacts', [PortalContactsController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/create', [PortalContactsController::class, 'create'])->name('contacts.create');
        Route::post('/contacts', [PortalContactsController::class, 'store'])->name('contacts.store');
        Route::get('/contacts/{contact}/edit', [PortalContactsController::class, 'edit'])->name('contacts.edit');
        Route::put('/contacts/{contact}', [PortalContactsController::class, 'update'])->name('contacts.update');
        Route::patch('/contacts/{contact}/toggle-access', [PortalContactsController::class, 'toggleAccess'])->name('contacts.toggle-access');
        Route::patch('/contacts/{contact}/update-role', [PortalContactsController::class, 'updateRole'])->name('contacts.update-role');
        Route::delete('/contacts/{contact}', [PortalContactsController::class, 'destroy'])->name('contacts.destroy');
    });

    Route::middleware('portal.can:path')->group(function () {
        Route::get('/path', [PortalPathController::class, 'index'])->name('path.index');
    });

    Route::middleware('portal.can:launch_programs')->group(function () {
        Route::get('/launch-programs', [PortalLaunchProgramController::class, 'index'])->name('launch-programs.index');
        Route::get('/launch-programs/{programPartner}', [PortalLaunchProgramController::class, 'show'])->name('launch-programs.show');
        Route::post('/launch-programs/{programPartner}/milestones/{milestone}/complete', [PortalLaunchProgramController::class, 'completeMilestone'])->name('launch-programs.complete-milestone');
        Route::post('/launch-programs/{programPartner}/feedback', [PortalLaunchProgramController::class, 'submitFeedback'])->name('launch-programs.submit-feedback');
        Route::post('/launch-programs/{programPartner}/participants', [PortalLaunchProgramController::class, 'addParticipant'])->name('launch-programs.add-participant');
        Route::delete('/launch-programs/{programPartner}/participants/{participant}', [PortalLaunchProgramController::class, 'removeParticipant'])->name('launch-programs.remove-participant');
    });

    Route::middleware('portal.can:workshops')->group(function () {
        Route::get('/workshops', [PortalWorkshopController::class, 'index'])->name('workshops.index');
        Route::get('/workshops/{session}', [PortalWorkshopController::class, 'show'])->name('workshops.show');
        Route::post('/workshops/{session}/invite', [PortalWorkshopController::class, 'invite'])->name('workshops.invite');

        Route::get('/my-workshops', [PortalWorkshopParticipationController::class, 'index'])->name('my-workshops.index');
        Route::get('/my-workshops/{session}', [PortalWorkshopParticipationController::class, 'show'])->name('my-workshops.show');
    });

    Route::middleware('portal.can:deal_registrations')->group(function () {
        Route::get('/deal-registrations', [PortalDealRegistrationController::class, 'index'])->name('deal-registrations.index');
        Route::get('/deal-registrations/create', [PortalDealRegistrationController::class, 'create'])->name('deal-registrations.create');
        Route::post('/deal-registrations', [PortalDealRegistrationController::class, 'store'])->name('deal-registrations.store');
    });

    Route::middleware('portal.can:projects')->group(function () {
        Route::get('/projects', [PortalProjectsController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project}', [PortalProjectsController::class, 'show'])->name('projects.show');
    });

    Route::middleware('portal.can:cases')->group(function () {
        Route::get('/cases', [PortalCasesController::class, 'index'])->name('cases.index');
        Route::post('/cases', [PortalCasesController::class, 'store'])->name('cases.store');
    });
});
