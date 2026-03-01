<?php

namespace App\Providers;

use App\Models\Module;
use App\Services\ModuleRegistry;
use Illuminate\Support\Facades\Schema;
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

        // Load module definitions from the database, falling back to config
        // when the table is empty, missing, or the DB is unreachable
        // (e.g. during tests, before initial seeding, or first deploy).
        $loaded = false;

        try {
            if (Schema::hasTable('modules')) {
                $modules = Module::orderBy('sort_order')->get();

                if ($modules->isNotEmpty()) {
                    $modules->each(function (Module $module) use ($registry) {
                        $registry->register($module->slug, $module->toRegistryArray());
                    });
                    $loaded = true;
                }
            }
        } catch (\Throwable) {
            // DB not available — fall through to config
        }

        if (! $loaded) {
            foreach (config('modules', []) as $slug => $definition) {
                $registry->register($slug, $definition);
            }
        }

        // Share module registry and navigation sections with the sidebar
        View::composer('layouts.partials.generated-navigation', function ($view) use ($registry) {
            $view->with('navSections', $registry->getNavigationSections());
            $view->with('moduleRegistry', $registry);
        });
    }
}
