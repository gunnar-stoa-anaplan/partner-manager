<?php

namespace App\Http\Controllers;

use App\Services\PartnerSuccess\MetricService;
use Illuminate\Http\Request;

/**
 * Partner Success dashboard — audited, warehouse-backed. Every number drills to
 * its source rows + SQL + weekly history; GEO / PARTNER filters re-run all of it.
 */
class PartnerSuccessController extends Controller
{
    public function __construct(private readonly MetricService $metrics)
    {
    }

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
        return response()->json($this->metrics->partners());
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
