<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', function ($user) {
            return $user->role === 'admin';
        });

        Pulse::user(fn ($user) => [
            'name' => $user->name,
            'extra' => $user->email,
            'avatar' => $user->avatar_url,
        ]);
    }
}
