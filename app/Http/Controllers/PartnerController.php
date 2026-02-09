<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partner;
use App\Models\User;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $partners = Partner::with('user')->orderBy('name')->get();
        return view('partner.index', compact('partners'));
    }

    /**
     * Display the authenticated user's assigned partners as cards.
     */
    public function myPartners()
    {
        $partners = Partner::where('user_id', auth()->id())
            ->with(['projects', 'contacts', 'opportunities', 'leads'])
            ->orderBy('name')
            ->get();

        return view('partners', compact('partners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $partner = Partner::with(['projects', 'contacts', 'opportunities', 'leads', 'fundings', 'resources'])
            ->findOrFail($id);

        // Check if the authenticated user has access to this partner
        if ($partner->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access to this partner.');
        }

        return view('partner.show', compact('partner'));
    }

    /**
     * Show the summary view for the specified partner.
     */
    public function summary(string $id)
    {
        $partner = Partner::with(['projects', 'contacts', 'opportunities', 'leads', 'resources'])
            ->findOrFail($id);

        // Check if the authenticated user has access to this partner
        if ($partner->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access to this partner.');
        }

        return view('partner.summary', compact('partner'));
    }

    /**
     * Show the insights view for the specified partner.
     */
    public function insights(string $id)
    {
        $partner = Partner::with(['projects', 'contacts', 'opportunities', 'leads', 'resources'])
            ->findOrFail($id);

        // Check if the authenticated user has access to this partner
        if ($partner->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access to this partner.');
        }

        // Get historical data for the last 12 weeks
        $endDate = now();
        $startDate = now()->subWeeks(12);

        // Query opportunity snapshots for this partner
        $opportunityHistory = \DB::table('opportunity_snapshots')
            ->join('imports', 'opportunity_snapshots.import_id', '=', 'imports.id')
            ->join('opportunities', 'opportunity_snapshots.opportunity_id', '=', 'opportunities.id')
            ->where('opportunities.partner_id', $partner->id)
            ->whereBetween('imports.started_at', [$startDate, $endDate])
            ->select(
                \DB::raw('DATE(imports.started_at) as snapshot_date'),
                \DB::raw('SUM(opportunity_snapshots.amount) as total_amount'),
                \DB::raw('COUNT(DISTINCT opportunity_snapshots.opportunity_id) as opportunity_count')
            )
            ->groupBy(\DB::raw('DATE(imports.started_at)'))
            ->orderBy(\DB::raw('DATE(imports.started_at)'))
            ->get();

        // Get blueprint completion data (resources by role)
        $blueprintCompletion = \DB::table('resources')
            ->where('partner_id', $partner->id)
            ->select(
                'role',
                \DB::raw('COUNT(*) as total'),
                \DB::raw('SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed')
            )
            ->whereIn('role', ['Delivery', 'Sales', 'Pre-Sales'])
            ->groupBy('role')
            ->get()
            ->keyBy('role');

        // Get all partners for quota comparison
        $allPartners = Partner::where('user_id', auth()->id())
            ->with('opportunities')
            ->get()
            ->take(5); // Top 5 partners for quota cards

        return view('partner.insights', compact(
            'partner',
            'opportunityHistory',
            'blueprintCompletion',
            'allPartners'
        ));
    }

    /**
     * Show the full summary view aggregating all partners for the authenticated user.
     */
    public function fullSummary()
    {
        // Get all partners assigned to the authenticated user
        $partners = Partner::where('user_id', auth()->id())
            ->with(['projects', 'contacts', 'opportunities', 'leads', 'resources'])
            ->get();

        // Get historical data for the last 12 weeks
        $endDate = now();
        $startDate = now()->subWeeks(12);

        // Query opportunity snapshots for all user's partners
        $opportunityHistory = \DB::table('opportunity_snapshots')
            ->join('imports', 'opportunity_snapshots.import_id', '=', 'imports.id')
            ->join('opportunities', 'opportunity_snapshots.opportunity_id', '=', 'opportunities.id')
            ->whereIn('opportunities.partner_id', $partners->pluck('id'))
            ->whereBetween('imports.started_at', [$startDate, $endDate])
            ->select(
                \DB::raw('DATE(imports.started_at) as snapshot_date'),
                \DB::raw('SUM(opportunity_snapshots.amount) as total_amount'),
                \DB::raw('COUNT(DISTINCT opportunity_snapshots.opportunity_id) as opportunity_count'),
                \DB::raw('SUM(CASE WHEN opportunity_snapshots.stage = "Closed Won" THEN 1 ELSE 0 END) as closed_won_count')
            )
            ->groupBy(\DB::raw('DATE(imports.started_at)'))
            ->orderBy(\DB::raw('DATE(imports.started_at)'))
            ->get();

        // Query resource snapshots for all user's partners
        $resourceHistory = \DB::table('resource_snapshots')
            ->join('imports', 'resource_snapshots.import_id', '=', 'imports.id')
            ->whereIn('resource_snapshots.partner_id', $partners->pluck('id'))
            ->whereBetween('imports.started_at', [$startDate, $endDate])
            ->select(
                \DB::raw('DATE(imports.started_at) as snapshot_date'),
                \DB::raw('SUM(resource_snapshots.total_count) as total_resources'),
                \DB::raw('SUM(resource_snapshots.completed_count) as certified_resources')
            )
            ->groupBy(\DB::raw('DATE(imports.started_at)'))
            ->orderBy(\DB::raw('DATE(imports.started_at)'))
            ->get();

        // Calculate aggregate metrics
        $totalProjects = $partners->sum(fn($p) => $p->projects->count());
        $totalOpportunities = $partners->sum(fn($p) => $p->opportunities->count());
        $totalResources = $partners->sum(fn($p) => $p->resources->count());
        $totalContacts = $partners->sum(fn($p) => $p->contacts->count());
        $totalPipelineValue = $partners->sum(fn($p) => $p->opportunities->sum('amount'));

        // Get stage distribution across all partners
        $allOpportunities = $partners->flatMap(fn($p) => $p->opportunities);
        $stageDistribution = $allOpportunities->groupBy('stage')->map->count()->sortDesc()->take(5);

        return view('partner.full-summary', compact(
            'partners',
            'opportunityHistory',
            'resourceHistory',
            'totalProjects',
            'totalOpportunities',
            'totalResources',
            'totalContacts',
            'totalPipelineValue',
            'stageDistribution'
        ));
    }

    /**
     * Show the full insights view aggregating all partners for the authenticated user.
     */
    public function fullInsights()
    {
        // Get all partners assigned to the authenticated user
        $partners = Partner::where('user_id', auth()->id())
            ->with(['projects', 'contacts', 'opportunities', 'leads', 'resources'])
            ->get();

        // Get historical data for the last 12 weeks
        $endDate = now();
        $startDate = now()->subWeeks(12);

        // Query opportunity snapshots for all user's partners
        $opportunityHistory = \DB::table('opportunity_snapshots')
            ->join('imports', 'opportunity_snapshots.import_id', '=', 'imports.id')
            ->join('opportunities', 'opportunity_snapshots.opportunity_id', '=', 'opportunities.id')
            ->whereIn('opportunities.partner_id', $partners->pluck('id'))
            ->whereBetween('imports.started_at', [$startDate, $endDate])
            ->select(
                \DB::raw('DATE(imports.started_at) as snapshot_date'),
                \DB::raw('SUM(opportunity_snapshots.amount) as total_amount'),
                \DB::raw('COUNT(DISTINCT opportunity_snapshots.opportunity_id) as opportunity_count')
            )
            ->groupBy(\DB::raw('DATE(imports.started_at)'))
            ->orderBy(\DB::raw('DATE(imports.started_at)'))
            ->get();

        // Get blueprint completion data aggregated across all partners
        $blueprintCompletion = \DB::table('resources')
            ->whereIn('partner_id', $partners->pluck('id'))
            ->select(
                'role',
                \DB::raw('COUNT(*) as total'),
                \DB::raw('SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed')
            )
            ->whereIn('role', ['Delivery', 'Sales', 'Pre-Sales'])
            ->groupBy('role')
            ->get()
            ->keyBy('role');

        // Calculate aggregate metrics
        $totalProjects = $partners->sum(fn($p) => $p->projects->count());
        $totalOpportunities = $partners->sum(fn($p) => $p->opportunities->count());
        $totalResources = $partners->sum(fn($p) => $p->resources->count());
        $totalContacts = $partners->sum(fn($p) => $p->contacts->count());
        $totalPipelineValue = $partners->sum(fn($p) => $p->opportunities->sum('amount'));

        // Get all opportunities for the feed
        $allOpportunities = $partners->flatMap(fn($p) => $p->opportunities);

        // Get partners sorted by pipeline value for quota cards
        $topPartners = $partners->sortByDesc(function($partner) {
            return $partner->opportunities->sum('amount');
        })->take(5);

        return view('partner.full-insights', compact(
            'partners',
            'opportunityHistory',
            'blueprintCompletion',
            'totalProjects',
            'totalOpportunities',
            'totalResources',
            'totalContacts',
            'totalPipelineValue',
            'allOpportunities',
            'topPartners'
        ));
    }

    /**
     * Show the subscription dashboard (template recreation).
     */
    public function subscriptionDashboard()
    {
        // Sample data matching the template
        return view('partner.subscription-dashboard');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Partner $partner)
    {
        $users = \App\Models\User::orderBy('name')->get();
        return view('partner.edit', compact('partner', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partner $partner)
    {
        $partner->update([
            'user_id' => $request->user_id
        ]);
    
        return redirect()->route('partners.index')->with('success', 'Partner assignment updated successfully.');
    }

    /**
     * Show the analysis dashboard.
     */
    public function analysis()
    {
        $isAdmin = auth()->user()->role === 'admin';

        if ($isAdmin) {
            $partners = Partner::with(['projects', 'contacts', 'opportunities', 'leads', 'resources'])
                ->orderBy('name')
                ->get();
        } else {
            $partners = Partner::where('user_id', auth()->id())
                ->with(['projects', 'contacts', 'opportunities', 'leads', 'resources'])
                ->orderBy('name')
                ->get();
        }

        $psms = User::where('role', 'psm')
            ->orderBy('name')
            ->get();

        // Get distinct geographies and regions from all partners
        $geographies = Partner::whereNotNull('geography')
            ->select('geography')
            ->distinct()
            ->orderBy('geography')
            ->pluck('geography');

        $regions = Partner::whereNotNull('region')
            ->select('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        // Build geography → regions mapping for dependent filters
        $geoRegionMap = Partner::whereNotNull('geography')
            ->whereNotNull('region')
            ->select('geography', 'region')
            ->distinct()
            ->orderBy('region')
            ->get()
            ->groupBy('geography')
            ->map(fn($items) => $items->pluck('region')->unique()->values());

        // Build PSM → partners mapping for dependent filters
        $psmPartnerMap = Partner::whereNotNull('user_id')
            ->select('id', 'name', 'user_id')
            ->orderBy('name')
            ->get()
            ->groupBy('user_id')
            ->map(fn($items) => $items->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values());

        return view('analysis.index', compact('partners', 'psms', 'geographies', 'regions', 'geoRegionMap', 'psmPartnerMap'));
    }

    /**
     * Show the guided analysis wizard.
     */
    public function guidedAnalysis()
    {
        $partners = Partner::where('user_id', auth()->id())
            ->with(['projects', 'contacts', 'opportunities', 'leads', 'resources'])
            ->orderBy('name')
            ->get();

        $geographies = Partner::whereNotNull('geography')
            ->select('geography')
            ->distinct()
            ->orderBy('geography')
            ->pluck('geography');

        $regions = Partner::whereNotNull('region')
            ->select('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        $geoRegionMap = Partner::whereNotNull('geography')
            ->whereNotNull('region')
            ->select('geography', 'region')
            ->distinct()
            ->orderBy('region')
            ->get()
            ->groupBy('geography')
            ->map(fn($items) => $items->pluck('region')->unique()->values());

        // Historical opportunity data for trend charts
        $endDate = now();
        $startDate = now()->subWeeks(12);

        $opportunityHistory = \DB::table('opportunity_snapshots')
            ->join('imports', 'opportunity_snapshots.import_id', '=', 'imports.id')
            ->join('opportunities', 'opportunity_snapshots.opportunity_id', '=', 'opportunities.id')
            ->whereIn('opportunities.partner_id', $partners->pluck('id'))
            ->whereBetween('imports.started_at', [$startDate, $endDate])
            ->select(
                \DB::raw('DATE(imports.started_at) as snapshot_date'),
                \DB::raw('SUM(opportunity_snapshots.amount) as total_amount'),
                \DB::raw('COUNT(DISTINCT opportunity_snapshots.opportunity_id) as opportunity_count'),
                \DB::raw('SUM(CASE WHEN opportunity_snapshots.stage = "Closed Won" THEN 1 ELSE 0 END) as closed_won_count')
            )
            ->groupBy(\DB::raw('DATE(imports.started_at)'))
            ->orderBy(\DB::raw('DATE(imports.started_at)'))
            ->get();

        return view('analysis.guided', compact(
            'partners',
            'geographies',
            'regions',
            'geoRegionMap',
            'opportunityHistory'
        ));
    }

    /**
     * Show the Partner Coach dashboard.
     */
    public function partnerCoach()
    {
        $partners = Partner::where('user_id', auth()->id())
            ->with(['projects', 'contacts', 'opportunities', 'leads', 'resources'])
            ->orderBy('name')
            ->get();

        // Historical data for trend sparklines
        $endDate = now();
        $startDate = now()->subWeeks(12);

        $opportunityHistory = \DB::table('opportunity_snapshots')
            ->join('imports', 'opportunity_snapshots.import_id', '=', 'imports.id')
            ->join('opportunities', 'opportunity_snapshots.opportunity_id', '=', 'opportunities.id')
            ->whereIn('opportunities.partner_id', $partners->pluck('id'))
            ->whereBetween('imports.started_at', [$startDate, $endDate])
            ->select(
                \DB::raw('DATE(imports.started_at) as snapshot_date'),
                \DB::raw('SUM(opportunity_snapshots.amount) as total_amount'),
                \DB::raw('COUNT(DISTINCT opportunity_snapshots.opportunity_id) as opportunity_count'),
                \DB::raw('SUM(CASE WHEN opportunity_snapshots.stage = "Closed Won" THEN 1 ELSE 0 END) as closed_won_count')
            )
            ->groupBy(\DB::raw('DATE(imports.started_at)'))
            ->orderBy(\DB::raw('DATE(imports.started_at)'))
            ->get();

        $resourceHistory = \DB::table('resource_snapshots')
            ->join('imports', 'resource_snapshots.import_id', '=', 'imports.id')
            ->whereIn('resource_snapshots.partner_id', $partners->pluck('id'))
            ->whereBetween('imports.started_at', [$startDate, $endDate])
            ->select(
                \DB::raw('DATE(imports.started_at) as snapshot_date'),
                \DB::raw('SUM(resource_snapshots.total_count) as total_resources'),
                \DB::raw('SUM(resource_snapshots.completed_count) as certified_resources')
            )
            ->groupBy(\DB::raw('DATE(imports.started_at)'))
            ->orderBy(\DB::raw('DATE(imports.started_at)'))
            ->get();

        return view('analysis.coach', compact(
            'partners',
            'opportunityHistory',
            'resourceHistory'
        ));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
