<?php

use App\Http\Controllers\ActionPlanController;
use App\Http\Controllers\Admin\ActivationBenchmarkController;
use App\Http\Controllers\Admin\FeatureActivationController;
use App\Http\Controllers\Admin\ProductBundleController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AiAgentsController;
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
use App\Http\Controllers\BlueprintDashboardController;
use App\Http\Controllers\BrandingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CompetencyMatrixController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DealRegistrationController;
use App\Http\Controllers\DirectorySyncController;
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
use App\Http\Controllers\LaunchProgramDashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MeasureController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\ObjectiveController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PartnerGoalController;
use App\Http\Controllers\PartnerPathController;
use App\Http\Controllers\PathConfigurationController;
use App\Http\Controllers\PathReviewController;
use App\Http\Controllers\PortalImpersonationController;
use App\Http\Controllers\PortalUserController;
use App\Http\Controllers\PrioritiesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QbrController;
use App\Http\Controllers\ReportingHierarchyController;
use App\Http\Controllers\RiskAnalyzerController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\SalesforceCredentialController;
use App\Http\Controllers\ScorecardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SolutionLifecycleController;
use App\Http\Controllers\StateOfBusinessController;
use App\Http\Controllers\Strategy\CoeProgramDashboardController;
use App\Http\Controllers\SystemStatusController;
use App\Http\Controllers\TaskBoardController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserImpersonationController;
use App\Http\Controllers\WorkshopGuideAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Auth::routes();

// GET /logout convenience — always redirects to login, logging out if needed
Route::get('/logout', function (\Illuminate\Http\Request $request) {
    if (Auth::check()) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    return redirect('/login');
})->name('logout.get');

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

    Route::get('/program-dashboard', CoeProgramDashboardController::class)->name('program-dashboard');
    Route::get('/state-of-business', [StateOfBusinessController::class, 'index'])->name('state-of-business');
    Route::post('/state-of-business/filters', [StateOfBusinessController::class, 'storeFilter'])->name('state-of-business.filters.store');
    Route::delete('/state-of-business/filters/{savedFilter}', [StateOfBusinessController::class, 'destroyFilter'])->name('state-of-business.filters.destroy');
    Route::post('/state-of-business/goals', [StateOfBusinessController::class, 'upsertGoal'])->name('state-of-business.goals.upsert');
    Route::delete('/state-of-business/goals/{enablementGoal}', [StateOfBusinessController::class, 'destroyGoal'])->name('state-of-business.goals.destroy');

    /*
    |--------------------------------------------------------------------------
    | My Training (Tell / Show / Do)
    |--------------------------------------------------------------------------
    */
    Route::prefix('training')->name('training.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TrainingController::class, 'index'])->name('index');
        Route::get('/paths/{path}', [\App\Http\Controllers\TrainingController::class, 'show'])->name('path');
        Route::get('/steps/{step}', [\App\Http\Controllers\TrainingController::class, 'step'])->name('step');
        Route::post('/steps/{step}/complete', [\App\Http\Controllers\TrainingController::class, 'completeStep'])->name('step.complete');
        Route::get('/steps/{step}/tour-data', [\App\Http\Controllers\TrainingController::class, 'tourData'])->name('step.tour-data');
    });

    /*
    |--------------------------------------------------------------------------
    | Feature Preview (locked features landing page)
    |--------------------------------------------------------------------------
    */
    Route::get('/features/{module:slug}/preview', \App\Http\Controllers\FeaturePreviewController::class)->name('features.preview');

    Route::get('/dashboard', function () {
        return view('index');
    })->name('dashboard')->middleware('module:partner_management.management');

    /*
    |--------------------------------------------------------------------------
    | Key Metrics
    |--------------------------------------------------------------------------
    */
    Route::view('partner_profile', 'partner_profile')->name('partner_profile');
    Route::view('relationship_overview', 'relationship_overview')->name('relationship_overview');
    Route::get('/influence-map', [FieldEnablementController::class, 'influenceMap'])->name('influence_map')->middleware('module:enablement.field');
    Route::middleware('module:partner_management.management')->group(function () {
        Route::get('/calendar', function () {
            $query = auth()->user()->isAdmin()
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
            if ($partner->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
                abort(403);
            }

            return view('calendar.partner', compact('partner'));
        })->name('partners.calendar');
        Route::view('full_calendar', 'fullcalendar')->name('full_calendar');
    }); // end partner_management.management calendar group

    /*
    |--------------------------------------------------------------------------
    | Business Development (gated by deal_registration.business_development)
    |--------------------------------------------------------------------------
    */
    Route::middleware('module:deal_registration.business_development')->group(function () {
        Route::get('/leads/sourced', [LeadController::class, 'sourced'])->name('leads.sourced');
        Route::get('/leads/influenced', [LeadController::class, 'influenced'])->name('leads.influenced');
        Route::get('/funding/development', [FundingController::class, 'development'])->name('funding.development');
    });

    /*
    |--------------------------------------------------------------------------
    | Project Delivery (gated by project_health feature flag)
    |--------------------------------------------------------------------------
    */
    Route::middleware('module:launch_programs.projects')->group(function () {
        Route::get('/projects/risks', [ProjectController::class, 'risks'])->name('projects.risks');
        Route::resource('projects', ProjectController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Field Enablement (gated by enablement feature flag)
    |--------------------------------------------------------------------------
    */
    Route::middleware('module:enablement.field')->group(function () {
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
    Route::middleware('module:partner_management.analytics')->group(function () {
        Route::get('/custom-filters', [PartnerController::class, 'analysis'])->name('custom-filters.index');
        Route::get('/wizard', [PartnerController::class, 'guidedAnalysis'])->name('wizard.index');
        Route::get('/coach', [PartnerController::class, 'partnerCoach'])->name('coach.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Priorities & Progress
    |--------------------------------------------------------------------------
    */
    Route::middleware('module:partner_management.priorities')->prefix('priorities')->name('priorities.')->group(function () {
        Route::get('/', [PrioritiesController::class, 'index'])->name('index');
        Route::get('/weekly', [PrioritiesController::class, 'weeklyFocus'])->name('weekly');
        Route::get('/timeline', [PrioritiesController::class, 'timeline'])->name('timeline');
        Route::get('/team', [PrioritiesController::class, 'team'])->name('team');
        Route::get('/team/{user}', [PrioritiesController::class, 'teamMember'])->name('team.member');

        Route::post('/objectives', [PrioritiesController::class, 'storeObjective'])->name('objectives.store');
        Route::patch('/objectives/{objective}', [PrioritiesController::class, 'updateObjective'])->name('objectives.update');

        Route::post('/weekly/items', [PrioritiesController::class, 'storeActionItem'])->name('items.store');
        Route::patch('/weekly/items/{item}', [PrioritiesController::class, 'updateActionItem'])->name('items.update');
        Route::delete('/weekly/items/{item}', [PrioritiesController::class, 'destroyActionItem'])->name('items.destroy');

        Route::post('/weekly/carry-over', [PrioritiesController::class, 'carryOver'])->name('carry-over');
        Route::post('/weekly/summary', [PrioritiesController::class, 'updateSummary'])->name('summary.update');
        Route::post('/weekly/{focus}/review', [PrioritiesController::class, 'review'])->name('review');
        Route::post('/team/{user}/suggest', [PrioritiesController::class, 'suggestObjective'])->name('suggest');
    });

    /*
    |--------------------------------------------------------------------------
    | API Routes for Filters
    |--------------------------------------------------------------------------
    */
    // Global Search
    Route::get('/api/search', [SearchController::class, 'search'])->name('api.search');

    // AI Chat
    Route::post('/api/chat', [ChatController::class, 'stream'])->name('api.chat');

    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/filter-data/psm-partner-mapping', [FilterDataController::class, 'psmPartnerMapping'])->name('filter-data.psm-partner-mapping');
        Route::get('/filter-data/geo-region-mapping', [FilterDataController::class, 'geoRegionMapping'])->name('filter-data.geo-region-mapping');
        Route::get('/filter-data/all', [FilterDataController::class, 'allFilterOptions'])->name('filter-data.all');

        // Partner Events API (gated by partner_management.management)
        Route::middleware('module:partner_management.management')->group(function () {
            Route::get('/events', [PartnerEventController::class, 'index'])->name('events.index');
            Route::post('/events', [PartnerEventController::class, 'store'])->name('events.store');
            Route::get('/events/{id}', [PartnerEventController::class, 'show'])->name('events.show');
            Route::put('/events/{id}', [PartnerEventController::class, 'update'])->name('events.update');
            Route::delete('/events/{id}', [PartnerEventController::class, 'destroy'])->name('events.destroy');
            Route::get('/partners/{partner_id}/contacts', [PartnerEventController::class, 'partnerContacts'])->name('partners.contacts');
        });

        // Enablement Events API (gated by enablement.events)
        Route::middleware('module:enablement.events')->group(function () {
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
        });

        // Blueprint Progress API (gated by blueprints.catalog)
        Route::post('/blueprint-progress/toggle', [BlueprintAssignmentController::class, 'toggleProgress'])->name('blueprint-progress.toggle')->middleware('module:enablement.blueprints');
    });

    /*
    |--------------------------------------------------------------------------
    | Partner Assignments and Selection
    |--------------------------------------------------------------------------
    */
    // Partner detail (gated by partner_management.management)
    Route::middleware('module:partner_management.management')->group(function () {
        Route::get('/my-partners', [PartnerController::class, 'myPartners'])->name('partners.my');
        Route::get('/partners/{id}', [PartnerController::class, 'show'])->name('partners.show');
        Route::get('/partners/{id}/summary', [PartnerController::class, 'summary'])->name('partners.summary');
        Route::get('/partners/{id}/insights', [PartnerController::class, 'insights'])->name('partners.insights');
        Route::get('/partners/{id}/blueprints', [PartnerController::class, 'blueprints'])->name('partners.blueprints');
        Route::post('/partners/{partner}/advisor', [PartnerController::class, 'advisor'])->name('partners.advisor');
        Route::get('/partners/{id}/tab/{tab}', [PartnerController::class, 'partnerTab'])
            ->name('partners.tab')
            ->where('tab', 'overview|pipeline|deal-registrations|enablement|projects|calendar|influencers|resources|blueprints|app-enablement|launch-programs|workspaces|portal-users|marketplace|psm-priorities');
    });

    // Marketplace Profile Review (gated by deal_registration.marketplace)
    Route::put('/partners/{partner}/marketplace-review', [PartnerController::class, 'reviewMarketplace'])->name('partners.marketplace-review')->middleware('module:deal_registration.marketplace');

    // Portal User Management (gated by portal.management)
    Route::middleware('module:portal.management')->group(function () {
        Route::patch('/partners/{partner}/portal-users/{contact}/toggle-access', [PortalUserController::class, 'toggleAccess'])->name('partners.portal-users.toggle-access');
        Route::patch('/partners/{partner}/portal-users/{contact}/update-role', [PortalUserController::class, 'updateRole'])->name('partners.portal-users.update-role');
        Route::patch('/partners/{partner}/portal-users/{contact}/set-password', [PortalUserController::class, 'setPassword'])->name('partners.portal-users.set-password');
        Route::post('/partners/{partner}/portal-users/import', [PortalUserController::class, 'import'])->name('partners.portal-users.import');
        Route::get('/partners/portal-users/template', [PortalUserController::class, 'downloadTemplate'])->name('partners.portal-users.template');
    });

    // User Impersonation — switch between user roles during development
    Route::post('/impersonate/{user}', [UserImpersonationController::class, 'start'])->name('impersonate.start');
    Route::post('/impersonate-stop', [UserImpersonationController::class, 'stop'])->name('impersonate.stop');

    // Portal Impersonation (PSM/Admin) — stop must be before start to avoid {contact} matching "stop"
    Route::post('/portal/impersonate/stop', [PortalImpersonationController::class, 'stop'])->name('portal.impersonate.stop');
    Route::post('/portal/impersonate/{contact}', [PortalImpersonationController::class, 'start'])->name('portal.impersonate.start');

    // Launch Program: PSM nomination + participant management (gated by launch_programs feature flag)
    Route::middleware('module:launch_programs.programs')->group(function () {
        Route::post('/partners/{partner}/launch-programs/{launchProgram}/nominate', [PartnerController::class, 'nominateForProgram'])->name('partners.launch-programs.nominate');
        Route::post('/launch-program-partners/{programPartner}/participants', [PartnerController::class, 'addParticipant'])->name('launch-program-partners.participants.store');
        Route::delete('/launch-program-participants/{participant}', [PartnerController::class, 'removeParticipant'])->name('launch-program-participants.destroy');
        Route::put('/launch-program-partners/{programPartner}', [LaunchProgramController::class, 'updatePartnerStatus'])->name('admin.launch-program-partners.update');
    });
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
    | Enablement Calendar (gated by enablement feature flag)
    |--------------------------------------------------------------------------
    */
    Route::middleware('module:enablement.calendar')->group(function () {
        Route::get('/enablement', function () {
            return view('enablement.calendar');
        })->name('enablement.calendar');
        Route::get('/enablement/my-nominations', function () {
            return view('enablement.my-nominations');
        })->name('enablement.my-nominations');
        Route::get('/partners/{id}/enablement', function ($id) {
            $partner = \App\Models\Partner::with('contacts:id,partner_id,first_name,last_name,email')
                ->findOrFail($id);
            if ($partner->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
                abort(403);
            }

            return view('enablement.partner', compact('partner'));
        })->name('partners.enablement');
    });

    // Lab Facilitator Dashboard (gated by workshops.labs)
    Route::middleware('module:enablement.workshops')->group(function () {
        Route::get('/enablement-sessions/{session}/lab-dashboard', [LabFacilitatorController::class, 'dashboard'])->name('sessions.lab-dashboard');
        Route::post('/enablement-sessions/{session}/validate-all', [LabFacilitatorController::class, 'validateAll'])->name('sessions.validate-all');
        Route::post('/lab-participants/{participant}/checkpoints/{checkpoint}/validate', [LabFacilitatorController::class, 'validateParticipant'])->name('lab-participants.validate');
    });

    /*
    |--------------------------------------------------------------------------
    | Blueprint Assignments & Enrollments (gated by blueprints module)
    |--------------------------------------------------------------------------
    */
    Route::middleware('module:enablement.blueprints')->group(function () {
        Route::get('/blueprints-catalog', [BlueprintAssignmentController::class, 'catalog'])->name('blueprints.catalog');
        Route::get('/blueprints-catalog/{blueprint}', [BlueprintAssignmentController::class, 'blueprintDetail'])->name('blueprints.detail');
        Route::post('/blueprints-catalog/{blueprint}/assign', [BlueprintAssignmentController::class, 'assign'])->name('blueprints.assign');
        Route::delete('/blueprint-assignments/{assignment}', [BlueprintAssignmentController::class, 'removeAssignment'])->name('blueprint-assignments.destroy');
        Route::get('/blueprint-assignments/{assignment}', [BlueprintAssignmentController::class, 'partnerProgress'])->name('blueprint-assignments.show');
        Route::post('/blueprint-assignments/{assignment}/enroll', [BlueprintAssignmentController::class, 'enrollContact'])->name('blueprint-assignments.enroll');
        Route::delete('/blueprint-enrollments/{enrollment}', [BlueprintAssignmentController::class, 'removeEnrollment'])->name('blueprint-enrollments.destroy');
        Route::get('/blueprint-enrollments/{enrollment}', [BlueprintAssignmentController::class, 'contactProgress'])->name('blueprint-enrollments.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Blueprint Owner Access (non-admin users who own a blueprint)
    |--------------------------------------------------------------------------
    */
    Route::middleware('module:enablement.blueprints')->group(function () {
        Route::get('/my-blueprints/{blueprint}/edit', [BlueprintController::class, 'ownerEdit'])->name('blueprints.owner.edit');
        Route::put('/my-blueprints/{blueprint}', [BlueprintController::class, 'ownerUpdate'])->name('blueprints.owner.update');
        Route::post('/my-blueprints/{blueprint}/submit', [BlueprintController::class, 'submit'])->name('blueprints.owner.submit');
    });

    /*
    |--------------------------------------------------------------------------
    | Lead Assignment (gated by deal_registration.lead_assignment)
    |--------------------------------------------------------------------------
    */
    Route::middleware('module:deal_registration.lead_assignment')->prefix('leads')->name('leads.')->group(function () {
        Route::get('/unassigned', [LeadController::class, 'unassigned'])->name('unassigned');
        Route::get('/assigned', [LeadController::class, 'assigned'])->name('assigned');
        Route::get('/rejected', [LeadController::class, 'rejected'])->name('rejected');
    });

    /*
    |--------------------------------------------------------------------------
    | Partner Goals (PSM + Admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware('module:goals_targets.goals')->group(function () {
        Route::get('/partner-goals', [PartnerGoalController::class, 'index'])->name('partner-goals.index');
        Route::put('/partner-goals', [PartnerGoalController::class, 'update'])->name('partner-goals.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin + Integration Routes (Data Imports)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin_or_integration'])->group(function () {
        Route::resource('imports', ImportController::class)->except(['edit', 'update']);
    });

    /*
    |--------------------------------------------------------------------------
    | Reporting Hierarchy (admin, super_admin, coe, manager)
    |--------------------------------------------------------------------------
    */
    Route::middleware('module:partner_management.administration')->group(function () {
        Route::get('reporting-hierarchy', [ReportingHierarchyController::class, 'index'])->name('reporting-hierarchy.index');
        Route::get('reporting-hierarchy/data', [ReportingHierarchyController::class, 'data'])->name('reporting-hierarchy.data');
        Route::put('reporting-hierarchy/{user}', [ReportingHierarchyController::class, 'update'])->name('reporting-hierarchy.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin-Only Routes (Configuration & Setup)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin'])->group(function () {
        // System Administration
        Route::middleware('module:system_administration.admin')->group(function () {
            Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
            Route::resource('users', UserController::class);
            Route::get('/configuration', [ConfigurationController::class, 'index'])->name('configuration.index');
            Route::put('/configuration', [ConfigurationController::class, 'update'])->name('configuration.update');
            Route::put('/configuration/navigation', [ConfigurationController::class, 'updateNavigation'])->name('configuration.update-navigation');
            Route::put('/configuration/dev-focus', [ConfigurationController::class, 'updateDevFocus'])->name('configuration.update-dev-focus');
            Route::put('/configuration/ai', [ConfigurationController::class, 'updateAi'])->name('configuration.update-ai');
        });

        // Partner Administration
        Route::middleware('module:partner_management.administration')->group(function () {
            Route::get('partner-assignments', [PartnerController::class, 'index'])->name('partners.index');
            Route::resource('partners', PartnerController::class);
            Route::resource('objectives', ObjectiveController::class);
        });

        Route::resource('salesforce-credentials', SalesforceCredentialController::class);
        Route::post('/salesforce-credentials/test', [SalesforceCredentialController::class, 'testConnection'])->name('salesforce-credentials.test');

        // API Token Management (gated by integration_hub.api_tokens)
        Route::middleware('module:integration_hub.api_tokens')->group(function () {
            Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('admin.api-tokens');
            Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('admin.api-tokens.store');
            Route::delete('/api-tokens/{id}', [ApiTokenController::class, 'destroy'])->name('admin.api-tokens.destroy');
        });

        // Launch Programs Admin (gated by launch_programs.administration)
        Route::middleware('module:launch_programs.administration')->group(function () {
            // Launch Programs Dashboard (must be before resource to avoid {launchProgram} conflict)
            Route::get('/launch-programs/dashboard', [LaunchProgramDashboardController::class, 'index'])->name('admin.launch-programs.dashboard');

            // Launch Programs CRUD
            Route::resource('launch-programs', LaunchProgramController::class)->names('admin.launch-programs')->parameters(['launch-programs' => 'launchProgram']);
            Route::post('/launch-programs/{launchProgram}/milestones', [LaunchProgramController::class, 'addMilestone'])->name('admin.launch-programs.milestones.store');
            Route::post('/launch-programs/{launchProgram}/admin-nominate', [LaunchProgramController::class, 'adminNominate'])->name('admin.launch-programs.admin-nominate');
            Route::put('/launch-program-milestones/{milestone}', [LaunchProgramController::class, 'updateMilestone'])->name('admin.launch-program-milestones.update');
            Route::delete('/launch-program-milestones/{milestone}', [LaunchProgramController::class, 'destroyMilestone'])->name('admin.launch-program-milestones.destroy');
        });

        // Workshop Labs (gated by workshops.administration)
        Route::middleware('module:enablement.workshop_admin')->group(function () {
            Route::post('/labs/generate', [LabController::class, 'generate'])->name('labs.generate');
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

        // Workshop Guides Admin (gated by enablement.administration)
        Route::middleware('module:enablement.administration')->group(function () {
            Route::resource('workshop-guides', WorkshopGuideAdminController::class)->names('admin.workshop-guides');
            Route::post('/workshop-guides/{workshopGuide}/publish', [WorkshopGuideAdminController::class, 'publish'])->name('admin.workshop-guides.publish');
            Route::post('/workshop-guides/{workshopGuide}/pages', [WorkshopGuideAdminController::class, 'addPage'])->name('admin.workshop-guides.pages.store');
            Route::post('/workshop-guides/{workshopGuide}/images', [WorkshopGuideAdminController::class, 'uploadImage'])->name('admin.workshop-guides.images.store');
        });

        // Provisioning Admin (gated by provisioning.provisioning)
        Route::middleware('module:partner_management.provisioning')->group(function () {
            Route::get('/provisioning-workspaces', [AnaplanProvisioningController::class, 'index'])->name('admin.provisioning-workspaces.index');
            Route::put('/provisioning-workspaces/{workspace}/assign', [AnaplanProvisioningController::class, 'assignPartner'])->name('admin.provisioning-workspaces.assign');
            Route::post('/provisioning-workspaces/sync', [AnaplanProvisioningController::class, 'sync'])->name('admin.provisioning-workspaces.sync');

            // Provisioning Summary
            Route::get('/provisioning-summary', [AnaplanOverviewController::class, 'index'])->name('admin.provisioning-summary');
            Route::put('/provisioning-summary/version-config', [AnaplanOverviewController::class, 'updateVersionConfig'])->name('admin.provisioning-summary.update-version');
        });

        // AI Agents Admin (gated by ai_knowledge.agents)
        Route::middleware('module:ai_knowledge.agents')->group(function () {
            Route::get('/ai-agents', [AiAgentsController::class, 'index'])->name('admin.ai-agents.index');
        });

        // System Status & Updates (gated by system_administration.admin)
        Route::middleware('module:system_administration.admin')->group(function () {
            Route::get('/system-status', [SystemStatusController::class, 'index'])->name('admin.system-status');
            Route::get('/system-update', [UpdateController::class, 'show'])->name('admin.update');
            Route::post('/system-update/accept', [UpdateController::class, 'accept'])->name('admin.update.accept');
        });

        // Branding (gated by portal.management)
        Route::middleware('module:portal.management')->group(function () {
            Route::get('/branding', [BrandingController::class, 'index'])->name('admin.branding');
            Route::put('/branding', [BrandingController::class, 'update'])->name('admin.branding.update');
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Blueprint Admin (admin + CoE, gated by blueprints module)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin:coe', 'module:enablement.blueprints'])->group(function () {
        // Blueprint CRUD
        Route::resource('blueprints', BlueprintController::class);
        Route::post('/blueprints/{blueprint}/duplicate', [BlueprintController::class, 'duplicate'])->name('blueprints.duplicate');
        Route::get('/blueprints/{blueprint}/export', [BlueprintController::class, 'export'])->name('blueprints.export');
        Route::post('/blueprints/{blueprint}/outcomes', [BlueprintController::class, 'addOutcome'])->name('blueprints.outcomes.store');
        Route::put('/blueprint-outcomes/{outcome}', [BlueprintController::class, 'updateOutcome'])->name('blueprint-outcomes.update');
        Route::delete('/blueprint-outcomes/{outcome}', [BlueprintController::class, 'destroyOutcome'])->name('blueprint-outcomes.destroy');
        Route::post('/blueprint-outcomes/{outcome}/items', [BlueprintController::class, 'addContentItem'])->name('blueprint-outcomes.items.store');
        Route::put('/blueprint-content-items/{item}', [BlueprintController::class, 'updateContentItem'])->name('blueprint-content-items.update');
        Route::delete('/blueprint-content-items/{item}', [BlueprintController::class, 'destroyContentItem'])->name('blueprint-content-items.destroy');

        // Blueprint Dashboard
        Route::get('/blueprint-dashboard', [BlueprintDashboardController::class, 'index'])->name('admin.blueprint-dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Enablement Events — Admin (event/session CRUD, gated by enablement.administration)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin:coe', 'module:enablement.administration'])->group(function () {
        Route::post('/enablement-events/generate', [EnablementEventController::class, 'generate'])->name('enablement-events.generate');
        Route::resource('enablement-events', EnablementEventController::class);
        Route::get('/enablement-events/{id}/sessions/create', [EnablementEventController::class, 'createSession'])->name('enablement-events.sessions.create');
        Route::post('/enablement-events/{id}/sessions', [EnablementEventController::class, 'storeSession'])->name('enablement-events.sessions.store');
        Route::get('/enablement-sessions/{id}/edit', [EnablementEventController::class, 'editSession'])->name('enablement-sessions.edit');
        Route::put('/enablement-sessions/{id}', [EnablementEventController::class, 'updateSession'])->name('enablement-sessions.update');
        Route::delete('/enablement-sessions/{id}', [EnablementEventController::class, 'destroySession'])->name('enablement-sessions.destroy');

        // Enablement Dashboard
        Route::get('/enablement-dashboard', [EnablementDashboardController::class, 'index'])->name('admin.enablement-dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Enablement Events — Operations (calendar + registrations, gated by enablement.events)
    |--------------------------------------------------------------------------
    */
    Route::middleware('module:enablement.events')->group(function () {
        Route::get('/enablement-sessions/{id}/registrations', [EnablementEventController::class, 'sessionRegistrations'])->name('enablement-sessions.registrations');
        Route::put('/enablement-registrations/{id}', [EnablementEventController::class, 'updateRegistration'])->name('enablement-registrations.update');
        Route::get('/enablement-calendar', [EnablementEventController::class, 'calendar'])->name('enablement-calendar');
    });

    /*
    |--------------------------------------------------------------------------
    | Directory Sync (gated by directory_sync_admin module permission)
    |--------------------------------------------------------------------------
    */
    Route::middleware('module:integration_hub.directory_sync')->group(function () {
        Route::get('/directory-sync', [DirectorySyncController::class, 'index'])->name('directory-sync.index');
        Route::post('/directory-sync/upload', [DirectorySyncController::class, 'upload'])->name('directory-sync.upload');
    });

    /*
    |--------------------------------------------------------------------------
    | Feature Activation (admin + authorized CoE)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['can:manage-activation'])->group(function () {
        Route::get('/activation/flags', [FeatureActivationController::class, 'flags'])->name('admin.activation.flags');
        Route::post('/activation/toggle', [FeatureActivationController::class, 'toggle'])->name('admin.activation.toggle');
        Route::put('/activation/training-mode', [FeatureActivationController::class, 'updateTrainingMode'])->name('admin.activation.training-mode');
        Route::get('/activation/progression', [FeatureActivationController::class, 'progression'])->name('admin.activation.progression');
        Route::get('/activation/log', [FeatureActivationController::class, 'log'])->name('admin.activation.log');

        Route::get('/activation/paths', [FeatureActivationController::class, 'paths'])->name('admin.activation.paths');
        Route::post('/activation/paths', [FeatureActivationController::class, 'storePath'])->name('admin.activation.paths.store');
        Route::get('/activation/paths/{path}', [FeatureActivationController::class, 'showPath'])->name('admin.activation.paths.show');
        Route::put('/activation/paths/{path}', [FeatureActivationController::class, 'updatePath'])->name('admin.activation.paths.update');
        Route::delete('/activation/paths/{path}', [FeatureActivationController::class, 'destroyPath'])->name('admin.activation.paths.destroy');
        Route::post('/activation/paths/{path}/steps', [FeatureActivationController::class, 'storeStep'])->name('admin.activation.steps.store');
        Route::put('/activation/steps/{step}', [FeatureActivationController::class, 'updateStep'])->name('admin.activation.steps.update');
        Route::delete('/activation/steps/{step}', [FeatureActivationController::class, 'destroyStep'])->name('admin.activation.steps.destroy');
        Route::post('/activation/steps/{step}/toggle-progress', [FeatureActivationController::class, 'toggleStepProgress'])->name('admin.activation.steps.toggle-progress');

        Route::get('/activation/paths/{path}/assignments', [FeatureActivationController::class, 'assignments'])->name('admin.activation.paths.assignments');
        Route::post('/activation/paths/{path}/assignments', [FeatureActivationController::class, 'assignUsers'])->name('admin.activation.paths.assign-users');
        Route::post('/activation/paths/{path}/assignments/role', [FeatureActivationController::class, 'assignRole'])->name('admin.activation.paths.assign-role');
        Route::delete('/activation/paths/{path}/assignments/{user}', [FeatureActivationController::class, 'unassignUser'])->name('admin.activation.paths.unassign-user');
        Route::post('/activation/paths/{path}/assignments/{user}/bypass', [FeatureActivationController::class, 'bypassTraining'])->name('admin.activation.paths.bypass-training');
        Route::delete('/activation/paths/{path}/assignments/{user}/bypass', [FeatureActivationController::class, 'revokeBypass'])->name('admin.activation.paths.revoke-bypass');
        Route::get('/activation/users/{user}/progress', [FeatureActivationController::class, 'userProgress'])->name('admin.activation.user-progress');
        Route::get('/activation/benchmarks', ActivationBenchmarkController::class)->name('admin.activation.benchmarks');

        // Product Bundles
        Route::get('/activation/products', [ProductBundleController::class, 'index'])->name('admin.products.index');
        Route::get('/activation/products/{module}', [ProductBundleController::class, 'show'])->name('admin.products.show');
        Route::put('/activation/products/{module}/sections/{sectionKey}', [ProductBundleController::class, 'updateSection'])->name('admin.products.sections.update');
        Route::post('/activation/products/{module}/sections', [ProductBundleController::class, 'addSection'])->name('admin.products.sections.store');
        Route::delete('/activation/products/{module}/sections/{sectionKey}', [ProductBundleController::class, 'removeSection'])->name('admin.products.sections.destroy');
        Route::post('/activation/products/{module}/sections/{sectionKey}/items', [ProductBundleController::class, 'addItem'])->name('admin.products.items.store');
        Route::put('/activation/products/{module}/sections/{sectionKey}/items/{itemIndex}', [ProductBundleController::class, 'updateItem'])->name('admin.products.items.update');
        Route::delete('/activation/products/{module}/sections/{sectionKey}/items/{itemIndex}', [ProductBundleController::class, 'removeItem'])->name('admin.products.items.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Plan Section — Strategic Planning & Readiness
    |--------------------------------------------------------------------------
    */
    Route::prefix('plan')->name('plan.')->group(function () {
        // Partner Paths (gated by competency.paths)
        Route::middleware('module:competency.paths')->group(function () {
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
        });

        // Task Board (gated by action_plans.plans)
        Route::middleware('module:launch_programs.action_plans')->group(function () {
            Route::get('/tasks', [TaskBoardController::class, 'index'])->name('tasks.index');
            Route::post('/tasks', [TaskBoardController::class, 'store'])->name('tasks.store');
            Route::put('/tasks/{task}/status', [TaskBoardController::class, 'updateStatus'])->name('tasks.update-status');
            Route::post('/tasks/{task}/comments', [TaskBoardController::class, 'addComment'])->name('tasks.comments.store');
        });

        // Goals & Targets (gated by goals_targets.goals)
        Route::middleware('module:goals_targets.goals')->group(function () {
            Route::get('/goals', [GoalsController::class, 'index'])->name('goals.index');
            Route::post('/goals', [GoalsController::class, 'store'])->name('goals.store');
            Route::put('/goals/{goal}', [GoalsController::class, 'update'])->name('goals.update');
            Route::delete('/goals/{goal}', [GoalsController::class, 'destroy'])->name('goals.destroy');
            Route::post('/goals/refresh-actuals', [GoalsController::class, 'refreshActuals'])->name('goals.refresh-actuals');
        });

        // Action Plans (gated by action_plans.plans)
        Route::middleware('module:launch_programs.action_plans')->group(function () {
            Route::get('/action-plans', [ActionPlanController::class, 'index'])->name('action-plans.index');
            Route::post('/action-plans', [ActionPlanController::class, 'store'])->name('action-plans.store');
            Route::put('/action-plans/{plan}', [ActionPlanController::class, 'update'])->name('action-plans.update');
            Route::post('/action-plans/{plan}/items', [ActionPlanController::class, 'addItem'])->name('action-plans.items.store');
            Route::put('/action-plan-items/{item}', [ActionPlanController::class, 'updateItem'])->name('action-plan-items.update');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Engage Section — Solution Lifecycle
    |--------------------------------------------------------------------------
    */
    Route::prefix('engage')->name('engage.')->group(function () {
        Route::middleware('module:deal_registration.deals')->group(function () {
            Route::get('/solutions', [SolutionLifecycleController::class, 'index'])->name('solutions.index');
            Route::post('/solutions', [SolutionLifecycleController::class, 'store'])->name('solutions.store');
            Route::put('/solutions/{solution}', [SolutionLifecycleController::class, 'update'])->name('solutions.update');
            Route::post('/solutions/{solution}/advance', [SolutionLifecycleController::class, 'advanceStage'])->name('solutions.advance');
        });

        // Deal Registrations (gated by deal_registration.deals)
        Route::middleware('module:deal_registration.deals')->group(function () {
            Route::get('/deal-registrations/dashboard', [DealRegistrationController::class, 'dashboard'])->name('deal-registrations.dashboard');
            Route::get('/deal-registrations', [DealRegistrationController::class, 'index'])->name('deal-registrations.index');
            Route::get('/deal-registrations/create', [DealRegistrationController::class, 'create'])->name('deal-registrations.create');
            Route::post('/deal-registrations', [DealRegistrationController::class, 'store'])->name('deal-registrations.store');
            Route::get('/deal-registrations/{dealRegistration}', [DealRegistrationController::class, 'show'])->name('deal-registrations.show');
            Route::put('/deal-registrations/{dealRegistration}/review', [DealRegistrationController::class, 'review'])->name('deal-registrations.review');
        });

        // Marketplace Profile Submissions (gated by deal_registration.marketplace)
        Route::middleware('module:deal_registration.marketplace')->group(function () {
            Route::get('/marketplace-submissions', [PartnerController::class, 'marketplaceSubmissions'])->name('marketplace-submissions.index');
            Route::get('/marketplace-submissions/{partner}', [PartnerController::class, 'marketplaceSubmissionShow'])->name('marketplace-submissions.show');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Measure Section — Tabbed Analytics
    |--------------------------------------------------------------------------
    */
    Route::prefix('measure')->name('measure.')->group(function () {
        // Portfolio Analytics (gated by measurement.analytics)
        Route::middleware('module:measurement.analytics')->group(function () {
            Route::get('/', [MeasureController::class, 'index'])->name('index');
            Route::get('/at-a-glance', fn () => redirect()->route('measure.index', ['tab' => 'at-a-glance']))->name('at-a-glance');
            Route::get('/pipeline', fn () => redirect()->route('measure.index', ['tab' => 'pipeline']))->name('pipeline');
            Route::get('/lead-flow', fn () => redirect()->route('measure.index', ['tab' => 'leads']))->name('lead-flow');
            Route::get('/funding', fn () => redirect()->route('measure.index', ['tab' => 'funding']))->name('funding');
            Route::get('/revenue', fn () => redirect()->route('measure.index', ['tab' => 'revenue']))->name('revenue');
            Route::get('/enablement', fn () => redirect()->route('measure.index', ['tab' => 'enablement']))->name('enablement');
            Route::get('/delivery', fn () => redirect()->route('measure.index', ['tab' => 'delivery']))->name('delivery');
        });

        // Benchmarks (gated by measurement.benchmarks)
        Route::middleware('module:measurement.benchmarks')->group(function () {
            Route::get('/benchmarks', [BenchmarkController::class, 'index'])->name('benchmarks.index');
            Route::get('/benchmarks/{partner}', [BenchmarkController::class, 'partner'])->name('benchmarks.partner');
        });

        // Risk Analyzer (gated by measurement.risk_analyzer)
        Route::middleware('module:measurement.risk_analyzer')->prefix('risk-analyzer')->name('risk-analyzer.')->group(function () {
            Route::get('/', [RiskAnalyzerController::class, 'index'])->name('index');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Act Section — Actions & Decisions
    |--------------------------------------------------------------------------
    */
    Route::prefix('act')->name('act.')->group(function () {
        // Partner Health (gated by measurement.health)
        Route::middleware('module:measurement.health')->group(function () {
            Route::get('/health', [HealthDashboardController::class, 'index'])->name('health.index');
            Route::get('/health/{partner}', [HealthDashboardController::class, 'show'])->name('health.show');

            // Alerts & Recommendations
            Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
            Route::put('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
            Route::put('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
            Route::put('/alerts/{alert}/dismiss', [AlertController::class, 'dismiss'])->name('alerts.dismiss');
        });

        // QBR Workbench (gated by measurement.qbr)
        Route::middleware('module:measurement.qbr')->group(function () {
            Route::get('/qbr', [QbrController::class, 'index'])->name('qbr.index');
            Route::post('/qbr', [QbrController::class, 'store'])->name('qbr.store');
            Route::get('/qbr/{session}', [QbrController::class, 'show'])->name('qbr.show');
            Route::put('/qbr/{session}', [QbrController::class, 'update'])->name('qbr.update');
            Route::put('/qbr/{session}/sections/{section}', [QbrController::class, 'updateSection'])->name('qbr.sections.update');
            Route::post('/qbr/{session}/complete', [QbrController::class, 'complete'])->name('qbr.complete');
        });

        // Path Reviews (gated by competency.paths — operational, not admin config)
        Route::middleware('module:competency.paths')->group(function () {
            Route::get('/path-reviews', [PathReviewController::class, 'index'])->name('path-reviews.index');
            Route::get('/path-reviews/{enrollment}', [PathReviewController::class, 'show'])->name('path-reviews.show');
            Route::post('/path-reviews/{enrollment}/advance', [PathReviewController::class, 'advance'])->name('path-reviews.advance');
            Route::post('/path-reviews/{enrollment}/hold', [PathReviewController::class, 'hold'])->name('path-reviews.hold');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Admin-Only: Phase 1-3 Configuration Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        // Role Management (gated by system_administration.admin)
        Route::middleware('module:system_administration.admin')->group(function () {
            Route::resource('roles', RoleController::class)->except(['show']);
        });

        // Path Configuration (gated by competency.administration)
        Route::middleware('module:competency.administration')->group(function () {
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
        });

        // Goal Templates (gated by goals_targets.administration)
        Route::middleware('module:goals_targets.administration')->group(function () {
            Route::get('/goal-templates', [GoalTemplateController::class, 'index'])->name('goal-templates.index');
            Route::post('/goal-templates', [GoalTemplateController::class, 'store'])->name('goal-templates.store');
            Route::put('/goal-templates/{template}', [GoalTemplateController::class, 'update'])->name('goal-templates.update');
            Route::delete('/goal-templates/{template}', [GoalTemplateController::class, 'destroy'])->name('goal-templates.destroy');
            Route::post('/goal-templates/{template}/assign', [GoalTemplateController::class, 'assign'])->name('goal-templates.assign');
        });

        // Notification Templates (gated by system_administration.admin)
        Route::middleware('module:system_administration.admin')->group(function () {
            Route::get('/notification-templates', [NotificationTemplateController::class, 'index'])->name('notification-templates.index');
            Route::post('/notification-templates', [NotificationTemplateController::class, 'store'])->name('notification-templates.store');
            Route::put('/notification-templates/{template}', [NotificationTemplateController::class, 'update'])->name('notification-templates.update');
            Route::delete('/notification-templates/{template}', [NotificationTemplateController::class, 'destroy'])->name('notification-templates.destroy');
            Route::post('/notification-templates/{template}/test', [NotificationTemplateController::class, 'preview'])->name('notification-templates.test');
        });
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
