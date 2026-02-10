<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ObjectiveController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\FieldEnablementController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\FundingController;
use App\Http\Controllers\SalesforceCredentialController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\FilterDataController;
use App\Http\Controllers\Api\PartnerEventController;
use App\Http\Controllers\Api\EnablementController;
use App\Http\Controllers\EnablementEventController;
use App\Http\Controllers\BlueprintController;
use App\Http\Controllers\BlueprintAssignmentController;
use App\Http\Controllers\RoutingController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Auth::routes();

/*
|--------------------------------------------------------------------------
| Root Redirect
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('welcome'));

/*
|--------------------------------------------------------------------------
| Protected Routes - Require Authentication
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard & Static Pages
    |--------------------------------------------------------------------------
    */
    Route::get('/welcome', function () {
        return view('welcome', ['data' => '']);
    })->name('welcome');

    Route::get('/dashboard', function () {
        return view('index');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Key Metrics
    |--------------------------------------------------------------------------
    */
    Route::view('partner_profile', 'partner_profile')->name('partner_profile');
    Route::view('relationship_overview', 'relationship_overview')->name('relationship_overview');
    Route::view('influence_map', 'influence_map')->name('influence_map');
    Route::get('/calendar', function () {
        $partners = \App\Models\Partner::where('user_id', auth()->id())
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
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

    /*
    |--------------------------------------------------------------------------
    | Business Development
    |--------------------------------------------------------------------------
    */
    Route::get('/leads/sourced', [LeadController::class, 'sourced'])->name('leads.sourced');
    Route::get('/leads/influenced', [LeadController::class, 'influenced'])->name('leads.influenced');
    Route::get('/funding/development', [FundingController::class, 'development'])->name('funding.development');

    /*
    |--------------------------------------------------------------------------
    | Project Delivery
    |--------------------------------------------------------------------------
    */
    Route::resource('projects', ProjectController::class);

    /*
    |--------------------------------------------------------------------------
    | Field Enablement
    |--------------------------------------------------------------------------
    */
    Route::resource('influencers', ContactController::class);
    Route::prefix('field')->name('field.')->group(function () {
        Route::get('/key-influencers', [FieldEnablementController::class, 'keyInfluencers'])->name('key-influencers');
        Route::get('/breadth', [FieldEnablementController::class, 'breadth'])->name('breadth');
        Route::get('/depth', [FieldEnablementController::class, 'depth'])->name('depth');
    });

    /*
    |--------------------------------------------------------------------------
    | User Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | Analysis & Insights
    |--------------------------------------------------------------------------
    */
    Route::get('/analysis', [PartnerController::class, 'analysis'])->name('analysis.index');
    Route::get('/guided-analysis', [PartnerController::class, 'guidedAnalysis'])->name('analysis.guided');
    Route::get('/partner-coach', [PartnerController::class, 'partnerCoach'])->name('analysis.coach');

    /*
    |--------------------------------------------------------------------------
    | API Routes for Filters
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/filter-data/psm-partner-mapping', [FilterDataController::class, 'psmPartnerMapping'])->name('filter-data.psm-partner-mapping');
        Route::get('/filter-data/geo-region-mapping', [FilterDataController::class, 'geoRegionMapping'])->name('filter-data.geo-region-mapping');
        Route::get('/filter-data/all', [FilterDataController::class, 'allFilterOptions'])->name('filter-data.all');

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

    /*
    |--------------------------------------------------------------------------
    | Partner Assignments and Selection
    |--------------------------------------------------------------------------
    */
    Route::get('/my-partners', [PartnerController::class, 'myPartners'])->name('partners.my');
    Route::get('/partners/{id}', [PartnerController::class, 'show'])->name('partners.show');
    Route::get('/partners/{id}/summary', [PartnerController::class, 'summary'])->name('partners.summary');
    Route::get('/partners/{id}/insights', [PartnerController::class, 'insights'])->name('partners.insights');
    Route::get('/partners/{id}/blueprints', [PartnerController::class, 'blueprints'])->name('partners.blueprints');
    Route::get('/full-summary', [PartnerController::class, 'fullSummary'])->name('partners.full-summary');
    Route::get('/full-insights', [PartnerController::class, 'fullInsights'])->name('partners.full-insights');

    // Partner Selection
    Route::get('/partners/select/{id}', function ($id) {
        $partner = \App\Models\Partner::findOrFail($id);
        session([
            'selected_partner_id' => $partner->id,
            'selected_partner_name' => $partner->name,
        ]);
        return redirect()->back();
    })->name('partners.select');

    Route::get('/partners/clear', function () {
        session()->forget(['selected_partner_id', 'selected_partner_name']);
        return redirect('/');
    })->name('partners.clear');

    /*
    |--------------------------------------------------------------------------
    | Enablement Calendar (all authenticated users)
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Enablement Blueprints (PSM)
    |--------------------------------------------------------------------------
    */
    Route::get('/enablement-blueprints', [BlueprintAssignmentController::class, 'catalog'])->name('blueprints.catalog');
    Route::get('/enablement-blueprints/{blueprint}', [BlueprintAssignmentController::class, 'blueprintDetail'])->name('blueprints.detail');
    Route::post('/enablement-blueprints/{blueprint}/assign', [BlueprintAssignmentController::class, 'assign'])->name('blueprints.assign');
    Route::delete('/blueprint-assignments/{assignment}', [BlueprintAssignmentController::class, 'removeAssignment'])->name('blueprint-assignments.destroy');
    Route::get('/blueprint-assignments/{assignment}', [BlueprintAssignmentController::class, 'partnerProgress'])->name('blueprint-assignments.show');
    Route::post('/blueprint-assignments/{assignment}/enroll', [BlueprintAssignmentController::class, 'enrollContact'])->name('blueprint-assignments.enroll');
    Route::delete('/blueprint-enrollments/{enrollment}', [BlueprintAssignmentController::class, 'removeEnrollment'])->name('blueprint-enrollments.destroy');
    Route::get('/blueprint-enrollments/{enrollment}', [BlueprintAssignmentController::class, 'contactProgress'])->name('blueprint-enrollments.show');

    /*
    |--------------------------------------------------------------------------
    | Leads
    |--------------------------------------------------------------------------
    */
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/unassigned', [LeadController::class, 'unassigned'])->name('unassigned');
        Route::get('/assigned', [LeadController::class, 'assigned'])->name('assigned');
        Route::get('/rejected', [LeadController::class, 'rejected'])->name('rejected');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin-Only Routes (Configuration & Setup)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin'])->group(function () {
        Route::get('partner-assignments', [PartnerController::class, 'index'])->name('partners.index');
        Route::resource('partners', PartnerController::class);
        Route::resource('objectives', ObjectiveController::class);
        Route::resource('salesforce-credentials', SalesforceCredentialController::class);
        Route::post('/salesforce-credentials/test', [SalesforceCredentialController::class, 'testConnection'])->name('salesforce-credentials.test');
        Route::resource('imports', ImportController::class)->except(['edit', 'update']);
        Route::resource('users', UserController::class);

        Route::get('/configuration', [ConfigurationController::class, 'index'])->name('configuration.index');
        Route::put('/configuration', [ConfigurationController::class, 'update'])->name('configuration.update');

        // Blueprint CRUD
        Route::resource('blueprints', BlueprintController::class);
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
    });

    /*
    |--------------------------------------------------------------------------
    | SmartAdmin Template Pages (browse template demos under /template/ prefix)
    |--------------------------------------------------------------------------
    */
    Route::get('/template/{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    Route::get('/template/{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('/template/{any}', [RoutingController::class, 'root'])->name('any');

}); // End of auth middleware group

/*
|--------------------------------------------------------------------------
| Redirect /home to /welcome
|--------------------------------------------------------------------------
*/
Route::get('/home', fn () => redirect()->route('welcome'))->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
