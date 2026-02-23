<?php

use App\Http\Controllers\Api\EnablementController;
use App\Http\Controllers\Api\PartnerEventController;
use App\Http\Controllers\BlueprintAssignmentController;
use App\Http\Controllers\BlueprintController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EnablementDashboardController;
use App\Http\Controllers\EnablementEventController;
use App\Http\Controllers\FieldEnablementController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\LabFacilitatorController;
use App\Http\Controllers\LaunchProgramController;
use App\Http\Controllers\LaunchProgramDashboardController;
use App\Http\Controllers\PartnerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Enablement Suite Module Routes
|--------------------------------------------------------------------------
|
| Feature slug: enablement
| Gated by module:enablement middleware via bootstrap/app.php
|
*/

// Influence Map
Route::get('/influence-map', [FieldEnablementController::class, 'influenceMap'])->name('influence_map');

// Calendar Views
Route::get('/calendar', function () {
    $query = auth()->user()->role === 'admin'
        ? \App\Models\Partner::query()
        : \App\Models\Partner::where('user_id', auth()->id());

    $partners = $query->select('id', 'name')->orderBy('name')->get();

    return view('calendar.portfolio', compact('partners'));
})->name('calendar.portfolio');
Route::get('/partners/{id}/calendar', function ($id) {
    $partner = \App\Models\Partner::with([
        'contacts:id,partner_id,first_name,last_name',
        'opportunities:id,partner_id,opportunity_name',
        'projects:id,partner_id,name',
    ])->findOrFail($id);
    if ($partner->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
        abort(403);
    }

    return view('calendar.partner', compact('partner'));
})->name('partners.calendar');
Route::view('full_calendar', 'fullcalendar')->name('full_calendar');

// Field Enablement
Route::resource('influencers', ContactController::class);
Route::post('/influencers/{influencer}/interactions', [ContactController::class, 'storeInteraction'])
    ->name('influencers.interactions.store');
Route::delete('/interactions/{interaction}', [ContactController::class, 'destroyInteraction'])
    ->name('interactions.destroy');
Route::post('/influencers/{influencer}/linkedin', [ContactController::class, 'storeLinkedIn'])
    ->name('influencers.linkedin.store');
Route::delete('/influencers/{influencer}/linkedin', [ContactController::class, 'removeLinkedIn'])
    ->name('influencers.linkedin.remove');
Route::prefix('field')->name('field.')->group(function () {
    Route::get('/key-influencers', [FieldEnablementController::class, 'keyInfluencers'])->name('key-influencers');
    Route::get('/breadth', [FieldEnablementController::class, 'breadth'])->name('breadth');
    Route::get('/depth', [FieldEnablementController::class, 'depth'])->name('depth');
});

// API Routes
Route::prefix('api')->name('api.')->group(function () {
    // Partner Events API
    Route::get('/events', [PartnerEventController::class, 'index'])->name('events.index');
    Route::post('/events', [PartnerEventController::class, 'store'])->name('events.store');
    Route::get('/events/{id}', [PartnerEventController::class, 'show'])->name('events.show');
    Route::put('/events/{id}', [PartnerEventController::class, 'update'])->name('events.update');
    Route::delete('/events/{id}', [PartnerEventController::class, 'destroy'])->name('events.destroy');
    Route::get('/partners/{partner_id}/contacts', [PartnerEventController::class, 'partnerContacts'])->name('partners.contacts');

    // Enablement Events API
    Route::get('/enablement/sessions', [EnablementController::class, 'sessions'])->name('enablement.sessions');
    Route::post('/enablement/sessions', [EnablementController::class, 'storeSession'])->name('enablement.sessions.store');
    Route::get('/enablement/sessions/{id}', [EnablementController::class, 'sessionDetail'])->name('enablement.sessions.show');
    Route::put('/enablement/sessions/{id}', [EnablementController::class, 'updateSessionApi'])->name('enablement.sessions.update');
    Route::delete('/enablement/sessions/{id}', [EnablementController::class, 'deleteSessionApi'])->name('enablement.sessions.delete');
    Route::post('/enablement/sessions/{id}/nominate', [EnablementController::class, 'nominate'])->name('enablement.nominate');
    Route::post('/enablement/sessions/{id}/register-partner', [EnablementController::class, 'registerPartner'])->name('enablement.register-partner');
    Route::delete('/enablement/sessions/{id}/unregister-partner', [EnablementController::class, 'unregisterPartner'])->name('enablement.unregister-partner');
    Route::get('/enablement/partners/{id}/sessions', [EnablementController::class, 'partnerSessions'])->name('enablement.partner-sessions');
    Route::delete('/enablement/registrations/{id}', [EnablementController::class, 'removeNomination'])->name('enablement.remove-nomination');
    Route::get('/enablement/my-nominations', [EnablementController::class, 'myNominations'])->name('enablement.my-nominations');

    // Blueprint Progress API
    Route::post('/blueprint-progress/toggle', [BlueprintAssignmentController::class, 'toggleProgress'])->name('blueprint-progress.toggle');
});

// Launch Program: PSM nomination + participant management
Route::post('/partners/{partner}/launch-programs/{launchProgram}/nominate', [PartnerController::class, 'nominateForProgram'])->name('partners.launch-programs.nominate');
Route::post('/launch-program-partners/{programPartner}/participants', [PartnerController::class, 'addParticipant'])->name('launch-program-partners.participants.store');
Route::delete('/launch-program-participants/{participant}', [PartnerController::class, 'removeParticipant'])->name('launch-program-participants.destroy');
Route::put('/launch-program-partners/{programPartner}', [LaunchProgramController::class, 'updatePartnerStatus'])->name('admin.launch-program-partners.update');

// Enablement Calendar
Route::get('/enablement', function () {
    return view('enablement.calendar');
})->name('enablement.calendar');
Route::get('/enablement/my-nominations', function () {
    return view('enablement.my-nominations');
})->name('enablement.my-nominations');
Route::get('/partners/{id}/enablement', function ($id) {
    $partner = \App\Models\Partner::with('contacts:id,partner_id,first_name,last_name,email')
        ->findOrFail($id);
    if ($partner->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
        abort(403);
    }

    return view('enablement.partner', compact('partner'));
})->name('partners.enablement');

// Lab Facilitator Dashboard
Route::get('/enablement-sessions/{session}/lab-dashboard', [LabFacilitatorController::class, 'dashboard'])->name('sessions.lab-dashboard');
Route::post('/enablement-sessions/{session}/validate-all', [LabFacilitatorController::class, 'validateAll'])->name('sessions.validate-all');
Route::post('/lab-participants/{participant}/checkpoints/{checkpoint}/validate', [LabFacilitatorController::class, 'validateParticipant'])->name('lab-participants.validate');

// Blueprint Assignments & Enrollments (PSM)
Route::get('/blueprints-catalog', [BlueprintAssignmentController::class, 'catalog'])->name('blueprints.catalog');
Route::get('/blueprints-catalog/{blueprint}', [BlueprintAssignmentController::class, 'blueprintDetail'])->name('blueprints.detail');
Route::post('/blueprints-catalog/{blueprint}/assign', [BlueprintAssignmentController::class, 'assign'])->name('blueprints.assign');
Route::delete('/blueprint-assignments/{assignment}', [BlueprintAssignmentController::class, 'removeAssignment'])->name('blueprint-assignments.destroy');
Route::get('/blueprint-assignments/{assignment}', [BlueprintAssignmentController::class, 'partnerProgress'])->name('blueprint-assignments.show');
Route::post('/blueprint-assignments/{assignment}/enroll', [BlueprintAssignmentController::class, 'enrollContact'])->name('blueprint-assignments.enroll');
Route::delete('/blueprint-enrollments/{enrollment}', [BlueprintAssignmentController::class, 'removeEnrollment'])->name('blueprint-enrollments.destroy');
Route::get('/blueprint-enrollments/{enrollment}', [BlueprintAssignmentController::class, 'contactProgress'])->name('blueprint-enrollments.show');

// Blueprint Owner Access (non-admin users who own a blueprint)
Route::get('/my-blueprints/{blueprint}/edit', [BlueprintController::class, 'ownerEdit'])->name('blueprints.owner.edit');
Route::put('/my-blueprints/{blueprint}', [BlueprintController::class, 'ownerUpdate'])->name('blueprints.owner.update');
Route::post('/my-blueprints/{blueprint}/submit', [BlueprintController::class, 'submit'])->name('blueprints.owner.submit');

/*
|--------------------------------------------------------------------------
| Admin-Only Enablement Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['admin'])->group(function () {
    // Blueprint CRUD
    Route::resource('blueprints', BlueprintController::class);
    Route::post('/blueprints/{blueprint}/duplicate', [BlueprintController::class, 'duplicate'])->name('blueprints.duplicate');
    Route::post('/blueprints/{blueprint}/outcomes', [BlueprintController::class, 'addOutcome'])->name('blueprints.outcomes.store');
    Route::put('/blueprint-outcomes/{outcome}', [BlueprintController::class, 'updateOutcome'])->name('blueprint-outcomes.update');
    Route::delete('/blueprint-outcomes/{outcome}', [BlueprintController::class, 'destroyOutcome'])->name('blueprint-outcomes.destroy');
    Route::post('/blueprint-outcomes/{outcome}/items', [BlueprintController::class, 'addContentItem'])->name('blueprint-outcomes.items.store');
    Route::put('/blueprint-content-items/{item}', [BlueprintController::class, 'updateContentItem'])->name('blueprint-content-items.update');
    Route::delete('/blueprint-content-items/{item}', [BlueprintController::class, 'destroyContentItem'])->name('blueprint-content-items.destroy');

    // Enablement Events CRUD
    Route::resource('enablement-events', EnablementEventController::class);
    Route::get('/enablement-events/{id}/sessions/create', [EnablementEventController::class, 'createSession'])->name('enablement-events.sessions.create');
    Route::post('/enablement-events/{id}/sessions', [EnablementEventController::class, 'storeSession'])->name('enablement-events.sessions.store');
    Route::get('/enablement-sessions/{id}/edit', [EnablementEventController::class, 'editSession'])->name('enablement-sessions.edit');
    Route::put('/enablement-sessions/{id}', [EnablementEventController::class, 'updateSession'])->name('enablement-sessions.update');
    Route::delete('/enablement-sessions/{id}', [EnablementEventController::class, 'destroySession'])->name('enablement-sessions.destroy');
    Route::get('/enablement-sessions/{id}/registrations', [EnablementEventController::class, 'sessionRegistrations'])->name('enablement-sessions.registrations');
    Route::put('/enablement-registrations/{id}', [EnablementEventController::class, 'updateRegistration'])->name('enablement-registrations.update');
    Route::get('/enablement-calendar', [EnablementEventController::class, 'calendar'])->name('enablement-calendar');

    // Enablement Dashboard
    Route::get('/enablement-dashboard', [EnablementDashboardController::class, 'index'])->name('admin.enablement-dashboard');

    // Launch Programs Dashboard (must be before resource to avoid {launchProgram} conflict)
    Route::get('/launch-programs/dashboard', [LaunchProgramDashboardController::class, 'index'])->name('admin.launch-programs.dashboard');

    // Launch Programs CRUD
    Route::resource('launch-programs', LaunchProgramController::class)->names('admin.launch-programs')->parameters(['launch-programs' => 'launchProgram']);
    Route::post('/launch-programs/{launchProgram}/milestones', [LaunchProgramController::class, 'addMilestone'])->name('admin.launch-programs.milestones.store');
    Route::post('/launch-programs/{launchProgram}/admin-nominate', [LaunchProgramController::class, 'adminNominate'])->name('admin.launch-programs.admin-nominate');
    Route::put('/launch-program-milestones/{milestone}', [LaunchProgramController::class, 'updateMilestone'])->name('admin.launch-program-milestones.update');
    Route::delete('/launch-program-milestones/{milestone}', [LaunchProgramController::class, 'destroyMilestone'])->name('admin.launch-program-milestones.destroy');

    // Workshop Labs
    Route::resource('labs', LabController::class);
    Route::post('/labs/{lab}/publish', [LabController::class, 'publish'])->name('labs.publish');
    Route::post('/labs/{lab}/checkpoints', [LabController::class, 'addCheckpoint'])->name('labs.checkpoints.store');
    Route::put('/lab-checkpoints/{checkpoint}', [LabController::class, 'updateCheckpoint'])->name('lab-checkpoints.update');
    Route::delete('/lab-checkpoints/{checkpoint}', [LabController::class, 'destroyCheckpoint'])->name('lab-checkpoints.destroy');
    Route::get('/labs/{lab}/browse-model', [LabController::class, 'browseModel'])->name('labs.browse-model');
    Route::get('/labs/{lab}/browse-module/{moduleId}', [LabController::class, 'browseModule'])->name('labs.browse-module');
    Route::get('/labs/{lab}/browse-lists', [LabController::class, 'browseLists'])->name('labs.browse-lists');
    Route::post('/labs/{lab}/participants', [LabController::class, 'addParticipant'])->name('labs.participants.store');
    Route::delete('/lab-participants/{participant}', [LabController::class, 'removeParticipant'])->name('lab-participants.destroy');
});
