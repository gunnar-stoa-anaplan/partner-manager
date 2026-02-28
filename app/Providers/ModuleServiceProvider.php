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

        // Share module registry and navigation sections with the sidebar
        View::composer('layouts.partials.generated-navigation', function ($view) use ($registry) {
            $view->with('navSections', $registry->getAccessibleSections());
            $view->with('moduleRegistry', $registry);
        });
    }
}
