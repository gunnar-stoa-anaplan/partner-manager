<?php

namespace App\Http\Controllers;

use App\Services\PartnerSuccess\MetricService;
use App\Support\VisibilityContext;
use Illuminate\Http\Request;

/**
 * Partner Success dashboard — audited, warehouse-backed. Every number drills to
 * its source rows + SQL + weekly history; GEO / PARTNER filters re-run all of it.
 * Row-level visibility: non-admin actors only ever see their own partners, so
 * "all partners" means "all of MY partners".
 */
class PartnerSuccessController extends Controller
{
    public function __construct(
        private readonly MetricService $metrics,
        VisibilityContext $visibility,
    ) {
        // Constrain every metric/drill to the partners this actor may see.
        $this->metrics->restrictTo($visibility->visiblePartnerNames());
        $this->visiblePartnerNames = $visibility->visiblePartnerNames();
    }

    /** @var array<string>|null */
    private ?array $visiblePartnerNames;

    /**
     * PSM Engagement — the app-native dashboard built on the Insight Kit. All
     * five sections (Pipeline / Delivery / Capability / Enablement / Catalog),
     * every KPI drillable to source, visibility-scoped. Uses the shared
     * SmartAdmin design language so it matches Workshops and the rest of the app.
     */
    public function index()
    {
        // One pass computes all metrics (restrictTo already applied in ctor).
        $order = ['Pipeline', 'Delivery', 'Capability', 'Enablement', 'Catalog'];
        $sections = [];
        foreach ($this->metrics->metrics() as $m) {
            $sections[$m['section'] ?: 'Other'][] = $m;
        }
        uksort($sections, fn ($a, $b) => (array_search($a, $order) === false ? 99 : array_search($a, $order))
            <=> (array_search($b, $order) === false ? 99 : array_search($b, $order)));

        return view('partner-success.overview', compact('sections'));
    }

    /** Legacy imported dashboard (custom design), kept for reference only. */
    public function legacy()
    {
        return view('partner-success.dashboard');
    }

    public function apiMetrics(Request $request)
    {
        return response()->json($this->metrics->metrics($request->query('geo'), $request->query('partner')));
    }

    public function apiPartners()
    {
        $partners = $this->metrics->partners();

        // Restrict the picker to partners this actor may see.
        if ($this->visiblePartnerNames !== null) {
            $allowed = array_flip($this->visiblePartnerNames);
            $partners = array_values(array_filter($partners, fn ($p) => isset($allowed[$p])));
        }

        return response()->json($partners);
    }

    public function apiDrill(Request $request, string $name)
    {
        return response()->json($this->metrics->drill($name, $request->query('geo'), $request->query('partner')));
    }

    public function apiTrend(Request $request, string $name)
    {
        return response()->json($this->metrics->trend($name, $request->query('geo'), $request->query('partner')));
    }

    public function apiBreakdown(Request $request, string $key)
    {
        return response()->json($this->metrics->breakdown($key, $request->query('geo'), $request->query('partner')));
    }

    public function apiDrillBreakdown(Request $request, string $key)
    {
        return response()->json($this->metrics->drillBreakdown(
            $key, (string) $request->query('value', ''), $request->query('geo'), $request->query('partner')
        ));
    }
}
