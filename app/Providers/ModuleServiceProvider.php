<?php

namespace App\Providers;

use App\Services\ModuleRegistry;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class);
    }

    public function boot(): void
    {
        Feature::discover();

        $registry = $this->app->make(ModuleRegistry::class);

        // Register each module defined in config
        foreach (config('modules', []) as $slug => $definition) {
            $registry->register($slug, $definition);
        }

        // Share enabled module navigation with the sidebar
        View::composer('layouts.partials.generated-navigation', function ($view) use ($registry) {
            $view->with('moduleNav', $registry->navigationItems());
            $view->with('moduleRegistry', $registry);
        });
    }
}
