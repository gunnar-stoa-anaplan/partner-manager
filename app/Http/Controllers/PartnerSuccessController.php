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

    public function index()
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
