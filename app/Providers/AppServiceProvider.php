<?php

namespace App\Providers;

use App\Contracts\ChatServiceInterface;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\SimulatedChatService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use Laravel\Pulse\Facades\Pulse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ChatServiceInterface::class, SimulatedChatService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('manage-activation', function (User $user) {
            if ($user->role === 'admin') {
                return true;
            }

            if ($user->role === 'coe') {
                return Feature::for(null)->active('feature_activation_coe');
            }

            return false;
        });

        Pulse::user(fn ($user) => [
            'name' => $user->name,
            'extra' => $user->email,
            'avatar' => $user->avatar_url,
        ]);

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
