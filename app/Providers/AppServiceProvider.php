<?php

namespace App\Providers;

use App\Contracts\ChatServiceInterface;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\WorkshopCredential;
use App\Models\WorkshopPage;
use App\Models\WorkshopSurveyTemplate;
use App\Observers\WorkshopContentAuditObserver;
use App\Services\SimulatedChatService;
use App\Services\ZoomApiService;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ChatServiceInterface::class, SimulatedChatService::class);

        // Row-level visibility resolver — singleton so partner-scope lookups run
        // once per request and both the Policies and the ScopedToActor global
        // scope share the same resolution.
        $this->app->singleton(\App\Support\VisibilityContext::class);

        $this->app->singleton(ZoomApiService::class, function () {
            return new ZoomApiService(
                accountId: config('services.zoom.account_id') ?? '',
                clientId: config('services.zoom.client_id') ?? '',
                clientSecret: config('services.zoom.client_secret') ?? '',
                webhookSecretToken: config('services.zoom.webhook_secret_token') ?? '',
                baseUrl: config('services.zoom.base_url') ?? 'https://api.zoom.us/v2',
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // In production the app can sit behind an SSM tunnel / proxy where the
        // request root loses its port or scheme, breaking generated URLs (form
        // actions, redirects, assets). Pin them to APP_URL.
        if ($this->app->environment('production') && ($root = config('app.url'))) {
            \Illuminate\Support\Facades\URL::forceRootUrl($root);
            if (str_starts_with($root, 'https://')) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }

        Gate::define('viewPulse', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-activation', function (User $user) {
            if ($user->bypassesPermissions()) {
                return true;
            }

            return $user->hasModulePermission('system_administration.admin');
        });

        Pulse::user(fn ($user) => [
            'name' => $user->name,
            'extra' => $user->email,
            'avatar' => $user->avatar_url,
        ]);

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });

        WorkshopPage::observe(WorkshopContentAuditObserver::class);
        WorkshopSurveyTemplate::observe(WorkshopContentAuditObserver::class);
        WorkshopCredential::observe(WorkshopContentAuditObserver::class);

        View::composer('layouts.partials.generated-navigation', function ($view) {
            $navSettings = AppSetting::where('key', 'like', 'nav_visible_%')
                ->pluck('value', 'key')
                ->toArray();

            $devFocus = AppSetting::get('dev_focus_feature', '');

            $view->with('navSettings', $navSettings);
            $view->with('devFocus', $devFocus);
        });
    }
}
