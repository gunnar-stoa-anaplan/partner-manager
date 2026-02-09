<?php

namespace App\Providers;

use App\Models\Partner;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share authenticated user's partner count across all views
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('authUserPartnerCount', auth()->user()->partners()->count());
            }
        });

        // Share selected partner data across relevant views
        View::composer('shared.leftaside', function ($view) {
            if (session('selected_partner_id')) {
                $partner = Partner::find(session('selected_partner_id'));
                $view->with('selectedPartner', $partner);
            }
        });

        // Share breadcrumb defaults
        View::composer('shared.breadcrumb', function ($view) {
            if (!$view->offsetExists('items')) {
                $view->with('items', []);
            }
            if (!$view->offsetExists('current')) {
                $view->with('current', null);
            }
        });
    }
}
