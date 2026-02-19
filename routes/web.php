<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AnaplanOverviewController;
use App\Http\Controllers\AnaplanProvisioningController;
use App\Http\Controllers\Api\FilterDataController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\BrandingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\DealRegistrationController;
use App\Http\Controllers\FundingController;
use App\Http\Controllers\HealthDashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ObjectiveController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PortalImpersonationController;
use App\Http\Controllers\PortalUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\SalesforceCredentialController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SignalDashboardController;
use App\Http\Controllers\SolutionLifecycleController;
use App\Http\Controllers\SystemStatusController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserImpersonationController;
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
    | User Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

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
        ->where('tab', 'overview|pipeline|enablement|projects|calendar|influencers|resources|app-enablement|launch-programs|workspaces|portal-users');

    // Portal User Management (PSM/Admin)
    Route::patch('/partners/{partner}/portal-users/{contact}/toggle-access', [PortalUserController::class, 'toggleAccess'])->name('partners.portal-users.toggle-access');
    Route::patch('/partners/{partner}/portal-users/{contact}/update-role', [PortalUserController::class, 'updateRole'])->name('partners.portal-users.update-role');
    Route::patch('/partners/{partner}/portal-users/{contact}/set-password', [PortalUserController::class, 'setPassword'])->name('partners.portal-users.set-password');
    Route::post('/partners/{partner}/portal-users/import', [PortalUserController::class, 'import'])->name('partners.portal-users.import');
    Route::get('/partners/portal-users/template', [PortalUserController::class, 'downloadTemplate'])->name('partners.portal-users.template');

    // Portal Impersonation (PSM/Admin) — stop must be before start to avoid {contact} matching "stop"
    Route::post('/portal/impersonate/stop', [PortalImpersonationController::class, 'stop'])->name('portal.impersonate.stop');
    Route::post('/portal/impersonate/{contact}', [PortalImpersonationController::class, 'start'])->name('portal.impersonate.start');

    // User Impersonation — stop route outside admin group so impersonated non-admins can access it
    Route::post('/users/impersonate/stop', [UserImpersonationController::class, 'stop'])->name('impersonate.stop');

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
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('partner-assignments', [PartnerController::class, 'index'])->name('partners.index');
        Route::resource('partners', PartnerController::class);
        Route::resource('objectives', ObjectiveController::class);
        Route::resource('salesforce-credentials', SalesforceCredentialController::class);
        Route::post('/salesforce-credentials/test', [SalesforceCredentialController::class, 'testConnection'])->name('salesforce-credentials.test');
        Route::resource('imports', ImportController::class)->except(['edit', 'update']);
        Route::resource('users', UserController::class);
        Route::post('/users/impersonate/{user}', [UserImpersonationController::class, 'start'])->name('impersonate.start');

        // API Token Management
        Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('admin.api-tokens');
        Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('admin.api-tokens.store');
        Route::delete('/api-tokens/{id}', [ApiTokenController::class, 'destroy'])->name('admin.api-tokens.destroy');

        Route::get('/configuration', [ConfigurationController::class, 'index'])->name('configuration.index');
        Route::put('/configuration', [ConfigurationController::class, 'update'])->name('configuration.update');
        Route::put('/configuration/navigation', [ConfigurationController::class, 'updateNavigation'])->name('configuration.update-navigation');
        Route::put('/configuration/dev-focus', [ConfigurationController::class, 'updateDevFocus'])->name('configuration.update-dev-focus');
        Route::put('/configuration/ai', [ConfigurationController::class, 'updateAi'])->name('configuration.update-ai');

        // Provisioning
        Route::get('/provisioning-workspaces', [AnaplanProvisioningController::class, 'index'])->name('admin.provisioning-workspaces.index');
        Route::put('/provisioning-workspaces/{workspace}/assign', [AnaplanProvisioningController::class, 'assignPartner'])->name('admin.provisioning-workspaces.assign');
        Route::post('/provisioning-workspaces/sync', [AnaplanProvisioningController::class, 'sync'])->name('admin.provisioning-workspaces.sync');

        // Provisioning Summary
        Route::get('/provisioning-summary', [AnaplanOverviewController::class, 'index'])->name('admin.provisioning-summary');
        Route::put('/provisioning-summary/version-config', [AnaplanOverviewController::class, 'updateVersionConfig'])->name('admin.provisioning-summary.update-version');

        // System Status & Updates
        Route::get('/system-status', [SystemStatusController::class, 'index'])->name('admin.system-status');
        Route::get('/system-update', [UpdateController::class, 'show'])->name('admin.update');
        Route::post('/system-update/accept', [UpdateController::class, 'accept'])->name('admin.update.accept');

        // Branding
        Route::get('/branding', [BrandingController::class, 'index'])->name('admin.branding');
        Route::put('/branding', [BrandingController::class, 'update'])->name('admin.branding.update');
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
    | Act Section — Partner Health & Alerts (Core)
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

        // Signal Engine
        Route::get('/signals', [SignalDashboardController::class, 'index'])->name('signals.index');
        Route::get('/signals/{signal}', [SignalDashboardController::class, 'show'])->name('signals.show');
        Route::put('/signals/{signal}/acknowledge', [SignalDashboardController::class, 'acknowledge'])->name('signals.acknowledge');
        Route::put('/signals/{signal}/resolve', [SignalDashboardController::class, 'resolve'])->name('signals.resolve');
        Route::put('/signals/{signal}/dismiss', [SignalDashboardController::class, 'dismiss'])->name('signals.dismiss');
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
