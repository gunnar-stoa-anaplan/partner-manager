<?php

namespace App\Providers;

use App\Insights\MetricRegistry;
use App\Insights\Sources\WarehouseMetricSource;
use App\Services\PartnerSuccess\MetricService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

/**
 * Registers metric sources into the MetricRegistry. Today the audited warehouse
 * metrics are the first (and only) domain; app-DB metric sources implement the
 * same MetricSource contract and register here later.
 */
class InsightServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MetricRegistry::class);
    }

    public function boot(): void
    {
        $registry = $this->app->make(MetricRegistry::class);
        $metrics = $this->app->make(MetricService::class);

        try {
            $defs = Cache::remember('insights.warehouse_metric_defs', 3600, function () {
                return DB::connection('warehouse')
                    ->select('SELECT name, title, unit, section FROM wh.metric_definition ORDER BY sort');
            });

            foreach ($defs as $d) {
                $registry->register(new WarehouseMetricSource(
                    $metrics, $d->name, $d->title, $d->unit ?? 'count', $d->section
                ));
            }
        } catch (\Throwable) {
            // Warehouse unavailable (tests before seed, first deploy) — skip.
        }
    }
}
