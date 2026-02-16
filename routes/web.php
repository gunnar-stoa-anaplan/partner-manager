<?php

use App\Http\Controllers\ActionPlanController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AnaplanOverviewController;
use App\Http\Controllers\AnaplanProvisioningController;
use App\Http\Controllers\Api\EnablementController;
use App\Http\Controllers\Api\FilterDataController;
use App\Http\Controllers\Api\PartnerEventController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\BenchmarkController;
use App\Http\Controllers\BlueprintAssignmentController;
use App\Http\Controllers\BlueprintController;
use App\Http\Controllers\CompetencyMatrixController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DealRegistrationController;
use App\Http\Controllers\EnablementDashboardController;
use App\Http\Controllers\EnablementEventController;
use App\Http\Controllers\FieldEnablementController;
use App\Http\Controllers\FundingController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\GoalTemplateController;
use App\Http\Controllers\HealthDashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\LabFacilitatorController;
use App\Http\Controllers\LaunchProgramController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MeasureController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\ObjectiveController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PartnerGoalController;
use App\Http\Controllers\PartnerPathController;
use App\Http\Controllers\PathConfigurationController;
use App\Http\Controllers\PathReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QbrController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\SalesforceCredentialController;
use App\Http\Controllers\ScorecardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SolutionLifecycleController;
use App\Http\Controllers\TaskBoardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/influence-map', [FieldEnablementController::class, 'influenceMap'])->name('influence_map');
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
    Route::get('/projects/risks', [ProjectController::class, 'risks'])->name('projects.risks');
    Route::resource('projects', ProjectController::class);

    /*
    |--------------------------------------------------------------------------
    | Field Enablement
    |--------------------------------------------------------------------------
    */
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
    // Global Search
    Route::get('/api/search', [SearchController::class, 'search'])->name('api.search');

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
    Route::get('/partners/{id}/tab/{tab}', [PartnerController::class, 'partnerTab'])
        ->name('partners.tab')
        ->where('tab', 'overview|pipeline|enablement|projects|calendar|influencers|resources|app-enablement|launch-programs|workspaces');

    // Launch Program: PSM nomination + participant management
    Route::post('/partners/{partner}/launch-programs/{launchProgram}/nominate', [PartnerController::class, 'nominateForProgram'])->name('partners.launch-programs.nominate');
    Route::post('/launch-program-partners/{programPartner}/participants', [PartnerController::class, 'addParticipant'])->name('launch-program-partners.participants.store');
    Route::delete('/launch-program-participants/{participant}', [PartnerController::class, 'removeParticipant'])->name('launch-program-participants.destroy');
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

    // Lab Facilitator Dashboard
    Route::get('/enablement-sessions/{session}/lab-dashboard', [LabFacilitatorController::class, 'dashboard'])->name('sessions.lab-dashboard');
    Route::post('/enablement-sessions/{session}/validate-all', [LabFacilitatorController::class, 'validateAll'])->name('sessions.validate-all');
    Route::post('/lab-participants/{participant}/checkpoints/{checkpoint}/validate', [LabFacilitatorController::class, 'validateParticipant'])->name('lab-participants.validate');

    /*
    |--------------------------------------------------------------------------
    | Blueprint Assignments & Enrollments (PSM)
    |--------------------------------------------------------------------------
    */
    Route::get('/blueprints-catalog', [BlueprintAssignmentController::class, 'catalog'])->name('blueprints.catalog');
    Route::get('/blueprints-catalog/{blueprint}', [BlueprintAssignmentController::class, 'blueprintDetail'])->name('blueprints.detail');
    Route::post('/blueprints-catalog/{blueprint}/assign', [BlueprintAssignmentController::class, 'assign'])->name('blueprints.assign');
    Route::delete('/blueprint-assignments/{assignment}', [BlueprintAssignmentController::class, 'removeAssignment'])->name('blueprint-assignments.destroy');
    Route::get('/blueprint-assignments/{assignment}', [BlueprintAssignmentController::class, 'partnerProgress'])->name('blueprint-assignments.show');
    Route::post('/blueprint-assignments/{assignment}/enroll', [BlueprintAssignmentController::class, 'enrollContact'])->name('blueprint-assignments.enroll');
    Route::delete('/blueprint-enrollments/{enrollment}', [BlueprintAssignmentController::class, 'removeEnrollment'])->name('blueprint-enrollments.destroy');
    Route::get('/blueprint-enrollments/{enrollment}', [BlueprintAssignmentController::class, 'contactProgress'])->name('blueprint-enrollments.show');

    /*
    |--------------------------------------------------------------------------
    | Blueprint Owner Access (non-admin users who own a blueprint)
    |--------------------------------------------------------------------------
    */
    Route::get('/my-blueprints/{blueprint}/edit', [BlueprintController::class, 'ownerEdit'])->name('blueprints.owner.edit');
    Route::put('/my-blueprints/{blueprint}', [BlueprintController::class, 'ownerUpdate'])->name('blueprints.owner.update');
    Route::post('/my-blueprints/{blueprint}/submit', [BlueprintController::class, 'submit'])->name('blueprints.owner.submit');

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
    | Partner Goals (PSM + Admin)
    |--------------------------------------------------------------------------
    */
    Route::get('/partner-goals', [PartnerGoalController::class, 'index'])->name('partner-goals.index');
    Route::put('/partner-goals', [PartnerGoalController::class, 'update'])->name('partner-goals.update');

    /*
    |--------------------------------------------------------------------------
    | Admin-Only Routes (Configuration & Setup)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('partner-assignments', [PartnerController::class, 'index'])->name('partners.index');
        Route::resource('partners', PartnerController::class);
        Route::resource('objectives', ObjectiveController::class);
        Route::resource('salesforce-credentials', SalesforceCredentialController::class);
        Route::post('/salesforce-credentials/test', [SalesforceCredentialController::class, 'testConnection'])->name('salesforce-credentials.test');
        Route::resource('imports', ImportController::class)->except(['edit', 'update']);
        Route::resource('users', UserController::class);

        // API Token Management
        Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('admin.api-tokens');
        Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('admin.api-tokens.store');
        Route::delete('/api-tokens/{id}', [ApiTokenController::class, 'destroy'])->name('admin.api-tokens.destroy');

        Route::get('/configuration', [ConfigurationController::class, 'index'])->name('configuration.index');
        Route::put('/configuration', [ConfigurationController::class, 'update'])->name('configuration.update');
        Route::put('/configuration/navigation', [ConfigurationController::class, 'updateNavigation'])->name('configuration.update-navigation');
        Route::put('/configuration/dev-focus', [ConfigurationController::class, 'updateDevFocus'])->name('configuration.update-dev-focus');

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

        // Launch Programs CRUD
        Route::resource('launch-programs', LaunchProgramController::class)->names('admin.launch-programs')->parameters(['launch-programs' => 'launchProgram']);
        Route::post('/launch-programs/{launchProgram}/milestones', [LaunchProgramController::class, 'addMilestone'])->name('admin.launch-programs.milestones.store');
        Route::put('/launch-program-milestones/{milestone}', [LaunchProgramController::class, 'updateMilestone'])->name('admin.launch-program-milestones.update');
        Route::delete('/launch-program-milestones/{milestone}', [LaunchProgramController::class, 'destroyMilestone'])->name('admin.launch-program-milestones.destroy');
        Route::put('/launch-program-partners/{programPartner}', [LaunchProgramController::class, 'updatePartnerStatus'])->name('admin.launch-program-partners.update');

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

        // Anaplan Provisioning
        Route::get('/anaplan-provisioning', [AnaplanProvisioningController::class, 'index'])->name('admin.anaplan-provisioning.index');
        Route::put('/anaplan-provisioning/workspaces/{workspace}/assign', [AnaplanProvisioningController::class, 'assignPartner'])->name('admin.anaplan-provisioning.assign');
        Route::post('/anaplan-provisioning/sync', [AnaplanProvisioningController::class, 'sync'])->name('admin.anaplan-provisioning.sync');

        // Anaplan Version Overview
        Route::get('/anaplan-overview', [AnaplanOverviewController::class, 'index'])->name('admin.anaplan-overview');
        Route::put('/anaplan-overview/version-config', [AnaplanOverviewController::class, 'updateVersionConfig'])->name('admin.anaplan-overview.update-version');
    });

    /*
    |--------------------------------------------------------------------------
    | Plan Section — Strategic Planning & Readiness
    |--------------------------------------------------------------------------
    */
    Route::prefix('plan')->name('plan.')->group(function () {
        // Partner Paths
        Route::get('/paths', [PartnerPathController::class, 'index'])->name('paths.index');
        Route::post('/paths/enroll', [PartnerPathController::class, 'enroll'])->name('paths.enroll');
        Route::get('/paths/{enrollment}', [PartnerPathController::class, 'show'])->name('paths.show');
        Route::post('/paths/{enrollment}/advance', [PartnerPathController::class, 'advanceStage'])->name('paths.advance');

        // Scorecard
        Route::get('/scorecard', [ScorecardController::class, 'index'])->name('scorecard.index');
        Route::get('/scorecard/{partner}', [ScorecardController::class, 'show'])->name('scorecard.show');

        // Competency Matrix
        Route::get('/competency-matrix', [CompetencyMatrixController::class, 'index'])->name('competency-matrix.index');
        Route::get('/competency-matrix/{partner}', [CompetencyMatrixController::class, 'show'])->name('competency-matrix.show');

        // Task Board
        Route::get('/tasks', [TaskBoardController::class, 'index'])->name('tasks.index');
        Route::post('/tasks', [TaskBoardController::class, 'store'])->name('tasks.store');
        Route::put('/tasks/{task}/status', [TaskBoardController::class, 'updateStatus'])->name('tasks.update-status');
        Route::post('/tasks/{task}/comments', [TaskBoardController::class, 'addComment'])->name('tasks.comments.store');

        // Goals & Targets
        Route::get('/goals', [GoalsController::class, 'index'])->name('goals.index');
        Route::post('/goals', [GoalsController::class, 'store'])->name('goals.store');
        Route::put('/goals/{goal}', [GoalsController::class, 'update'])->name('goals.update');
        Route::delete('/goals/{goal}', [GoalsController::class, 'destroy'])->name('goals.destroy');
        Route::post('/goals/refresh-actuals', [GoalsController::class, 'refreshActuals'])->name('goals.refresh-actuals');

        // Action Plans
        Route::get('/action-plans', [ActionPlanController::class, 'index'])->name('action-plans.index');
        Route::post('/action-plans', [ActionPlanController::class, 'store'])->name('action-plans.store');
        Route::put('/action-plans/{plan}', [ActionPlanController::class, 'update'])->name('action-plans.update');
        Route::post('/action-plans/{plan}/items', [ActionPlanController::class, 'addItem'])->name('action-plans.items.store');
        Route::put('/action-plan-items/{item}', [ActionPlanController::class, 'updateItem'])->name('action-plan-items.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Engage Section — Solution Lifecycle
    |--------------------------------------------------------------------------
    */
    Route::prefix('engage')->name('engage.')->group(function () {
        Route::get('/solutions', [SolutionLifecycleController::class, 'index'])->name('solutions.index');
        Route::post('/solutions', [SolutionLifecycleController::class, 'store'])->name('solutions.store');
        Route::put('/solutions/{solution}', [SolutionLifecycleController::class, 'update'])->name('solutions.update');
        Route::post('/solutions/{solution}/advance', [SolutionLifecycleController::class, 'advanceStage'])->name('solutions.advance');

        // Deal Registrations
        Route::get('/deal-registrations', [DealRegistrationController::class, 'index'])->name('deal-registrations.index');
        Route::get('/deal-registrations/create', [DealRegistrationController::class, 'create'])->name('deal-registrations.create');
        Route::post('/deal-registrations', [DealRegistrationController::class, 'store'])->name('deal-registrations.store');
        Route::get('/deal-registrations/{dealRegistration}', [DealRegistrationController::class, 'show'])->name('deal-registrations.show');
        Route::put('/deal-registrations/{dealRegistration}/review', [DealRegistrationController::class, 'review'])->name('deal-registrations.review');
    });

    /*
    |--------------------------------------------------------------------------
    | Measure Section — Tabbed Analytics
    |--------------------------------------------------------------------------
    */
    Route::prefix('measure')->name('measure.')->group(function () {
        Route::get('/', [MeasureController::class, 'index'])->name('index');
        // Individual tab routes redirect to index with tab query parameter
        Route::get('/at-a-glance', fn () => redirect()->route('measure.index', ['tab' => 'at-a-glance']))->name('at-a-glance');
        Route::get('/pipeline', fn () => redirect()->route('measure.index', ['tab' => 'pipeline']))->name('pipeline');
        Route::get('/lead-flow', fn () => redirect()->route('measure.index', ['tab' => 'leads']))->name('lead-flow');
        Route::get('/funding', fn () => redirect()->route('measure.index', ['tab' => 'funding']))->name('funding');
        Route::get('/revenue', fn () => redirect()->route('measure.index', ['tab' => 'revenue']))->name('revenue');
        Route::get('/enablement', fn () => redirect()->route('measure.index', ['tab' => 'enablement']))->name('enablement');
        Route::get('/delivery', fn () => redirect()->route('measure.index', ['tab' => 'delivery']))->name('delivery');

        // Benchmarks
        Route::get('/benchmarks', [BenchmarkController::class, 'index'])->name('benchmarks.index');
        Route::get('/benchmarks/{partner}', [BenchmarkController::class, 'partner'])->name('benchmarks.partner');
    });

    /*
    |--------------------------------------------------------------------------
    | Act Section — Actions & Decisions
    |--------------------------------------------------------------------------
    */
    Route::prefix('act')->name('act.')->group(function () {
        // Partner Health
        Route::get('/health', [HealthDashboardController::class, 'index'])->name('health.index');
        Route::get('/health/{partner}', [HealthDashboardController::class, 'show'])->name('health.show');

        // Alerts & Recommendations
        Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
        Route::put('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
        Route::put('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
        Route::put('/alerts/{alert}/dismiss', [AlertController::class, 'dismiss'])->name('alerts.dismiss');

        // QBR Workbench
        Route::get('/qbr', [QbrController::class, 'index'])->name('qbr.index');
        Route::post('/qbr', [QbrController::class, 'store'])->name('qbr.store');
        Route::get('/qbr/{session}', [QbrController::class, 'show'])->name('qbr.show');
        Route::put('/qbr/{session}', [QbrController::class, 'update'])->name('qbr.update');
        Route::put('/qbr/{session}/sections/{section}', [QbrController::class, 'updateSection'])->name('qbr.sections.update');
        Route::post('/qbr/{session}/complete', [QbrController::class, 'complete'])->name('qbr.complete');

        // Path Reviews
        Route::get('/path-reviews', [PathReviewController::class, 'index'])->name('path-reviews.index');
        Route::get('/path-reviews/{enrollment}', [PathReviewController::class, 'show'])->name('path-reviews.show');
        Route::post('/path-reviews/{enrollment}/advance', [PathReviewController::class, 'advance'])->name('path-reviews.advance');
        Route::post('/path-reviews/{enrollment}/hold', [PathReviewController::class, 'hold'])->name('path-reviews.hold');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin-Only: Phase 1-3 Configuration Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        // Path Configuration
        Route::get('/paths', [PathConfigurationController::class, 'index'])->name('paths.index');
        Route::post('/paths', [PathConfigurationController::class, 'store'])->name('paths.store');
        Route::get('/paths/{path}', [PathConfigurationController::class, 'show'])->name('paths.show');
        Route::put('/paths/{path}', [PathConfigurationController::class, 'update'])->name('paths.update');
        Route::post('/paths/{path}/stages', [PathConfigurationController::class, 'addStage'])->name('paths.stages.store');
        Route::put('/path-stages/{stage}', [PathConfigurationController::class, 'updateStage'])->name('path-stages.update');
        Route::delete('/path-stages/{stage}', [PathConfigurationController::class, 'destroyStage'])->name('path-stages.destroy');
        Route::post('/paths/{path}/gates', [PathConfigurationController::class, 'addGate'])->name('paths.gates.store');
        Route::put('/path-gates/{gate}', [PathConfigurationController::class, 'updateGate'])->name('path-gates.update');
        Route::delete('/path-gates/{gate}', [PathConfigurationController::class, 'destroyGate'])->name('path-gates.destroy');

        // Task Template Management
        Route::get('/task-templates', [PathConfigurationController::class, 'taskTemplates'])->name('task-templates.index');
        Route::post('/task-templates', [PathConfigurationController::class, 'storeTaskTemplate'])->name('task-templates.store');
        Route::put('/task-templates/{template}', [PathConfigurationController::class, 'updateTaskTemplate'])->name('task-templates.update');
        Route::delete('/task-templates/{template}', [PathConfigurationController::class, 'destroyTaskTemplate'])->name('task-templates.destroy');

        // Scorecard Weight Configuration
        Route::get('/scorecard-weights', [ScorecardController::class, 'weights'])->name('scorecard-weights.index');
        Route::put('/scorecard-weights', [ScorecardController::class, 'updateWeights'])->name('scorecard-weights.update');

        // Goal Templates
        Route::get('/goal-templates', [GoalTemplateController::class, 'index'])->name('goal-templates.index');
        Route::post('/goal-templates', [GoalTemplateController::class, 'store'])->name('goal-templates.store');
        Route::put('/goal-templates/{template}', [GoalTemplateController::class, 'update'])->name('goal-templates.update');
        Route::delete('/goal-templates/{template}', [GoalTemplateController::class, 'destroy'])->name('goal-templates.destroy');
        Route::post('/goal-templates/{template}/assign', [GoalTemplateController::class, 'assign'])->name('goal-templates.assign');

        // Notification Templates
        Route::get('/notification-templates', [NotificationTemplateController::class, 'index'])->name('notification-templates.index');
        Route::post('/notification-templates', [NotificationTemplateController::class, 'store'])->name('notification-templates.store');
        Route::put('/notification-templates/{template}', [NotificationTemplateController::class, 'update'])->name('notification-templates.update');
        Route::delete('/notification-templates/{template}', [NotificationTemplateController::class, 'destroy'])->name('notification-templates.destroy');
        Route::post('/notification-templates/{template}/test', [NotificationTemplateController::class, 'preview'])->name('notification-templates.test');
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
