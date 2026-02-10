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
        $users = User::orderBy('name')->get();
        return view('partner.index', compact('partners', 'users'));
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
        $partner = Partner::with([
            'projects', 'contacts', 'opportunities', 'leads', 'fundings', 'resources',
            'blueprintAssignments.blueprint',
            'blueprintAssignments.enrollments.progress',
            'enablementSessions',
        ])->findOrFail($id);

        // Check if the authenticated user has access to this partner
        if ($partner->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access to this partner.');
        }

        // ── KPI Metrics ──
        $activeProjects = $partner->projects->whereNotIn('status', ['Completed'])->count();
        $totalContacts = $partner->contacts->count();
        $totalLeads = $partner->leads->count();
        $totalAcv = $partner->projects->sum('acv');

        // ── Business Metrics ──
        $totalAmount = $partner->opportunities->sum('amount');
        $sourcedAmount = $partner->opportunities->where('revenue_type', 'sourced')->sum('amount');
        $influencedAmount = $partner->opportunities->where('revenue_type', 'influenced')->sum('amount');
        $sourcedRevenuePct = $totalAmount > 0 ? round($sourcedAmount / $totalAmount * 100, 1) : 0;
        $influencedRevenuePct = $totalAmount > 0 ? round($influencedAmount / $totalAmount * 100, 1) : 0;

        $assignmentsWithEnrollments = $partner->blueprintAssignments->filter(fn($a) => $a->enrollments->count() > 0);
        $avgBlueprintCompletion = $assignmentsWithEnrollments->count() > 0
            ? round($assignmentsWithEnrollments->avg(fn($a) => $a->completionPercentage()), 1) : 0;

        $enrolledContactIds = $partner->blueprintAssignments->flatMap(fn($a) => $a->enrollments->pluck('contact_id'))->unique();
        $contactEngagement = $totalContacts > 0
            ? round($enrolledContactIds->count() / $totalContacts * 100, 1) : 0;

        // Pipeline Growth YoY
        $currentPipeline = $totalAmount;
        $yearAgoMonth = now()->subYear()->format('Y-m');
        $yearAgoPipeline = \DB::table('opportunity_snapshots')
            ->join('imports', 'opportunity_snapshots.import_id', '=', 'imports.id')
            ->join('opportunities', 'opportunity_snapshots.opportunity_id', '=', 'opportunities.id')
            ->where('opportunities.partner_id', $partner->id)
            ->whereRaw('DATE_FORMAT(imports.started_at, "%Y-%m") = ?', [$yearAgoMonth])
            ->sum('opportunity_snapshots.amount');
        $pipelineGrowthYoY = $yearAgoPipeline > 0
            ? round(($currentPipeline - $yearAgoPipeline) / $yearAgoPipeline * 100, 1)
            : ($currentPipeline > 0 ? 100 : 0);

        // ── Chart Data ──
        // Historical opportunity snapshots (sourced/influenced NNACV + count)
        $endDate = now();
        $startDate = now()->subWeeks(52);

        $opportunityHistory = \DB::table('opportunity_snapshots')
            ->join('imports', 'opportunity_snapshots.import_id', '=', 'imports.id')
            ->join('opportunities', 'opportunity_snapshots.opportunity_id', '=', 'opportunities.id')
            ->where('opportunities.partner_id', $partner->id)
            ->whereBetween('imports.started_at', [$startDate, $endDate])
            ->select(
                \DB::raw('DATE_FORMAT(imports.started_at, "%Y-%m") as snapshot_month'),
                \DB::raw('SUM(CASE WHEN opportunity_snapshots.revenue_type = "sourced" THEN opportunity_snapshots.amount ELSE 0 END) as sourced_amount'),
                \DB::raw('SUM(CASE WHEN opportunity_snapshots.revenue_type = "influenced" THEN opportunity_snapshots.amount ELSE 0 END) as influenced_amount'),
                \DB::raw('COUNT(DISTINCT opportunity_snapshots.opportunity_id) as opportunity_count')
            )
            ->groupBy(\DB::raw('DATE_FORMAT(imports.started_at, "%Y-%m")'))
            ->orderBy(\DB::raw('DATE_FORMAT(imports.started_at, "%Y-%m")'))
            ->get();

        // Blueprint completion by product area + role (multi-series radar chart)
        $roleLabels = ['delivery' => 'Delivery', 'gtm' => 'Sales', 'pre_sales' => 'Pre-Sales'];
        $productAreas = \App\Models\Blueprint::distinct()
            ->pluck('product_area')
            ->filter()
            ->sort()
            ->values()
            ->all();

        $blueprintByArea = [];
        foreach ($roleLabels as $roleKey => $roleLabel) {
            $roleAssignments = $partner->blueprintAssignments->where('role', $roleKey);
            if ($roleAssignments->isEmpty()) continue;

            $data = [];
            foreach ($productAreas as $area) {
                $areaAssignments = $roleAssignments->filter(fn($a) => ($a->blueprint->product_area ?? 'Other') === $area);
                $data[] = $areaAssignments->isNotEmpty()
                    ? round($areaAssignments->avg(fn($a) => $a->completionPercentage()), 1)
                    : 0;
            }
            $blueprintByArea[] = ['name' => $roleLabel, 'data' => $data];
        }
        $blueprintAreas = $productAreas;

        // Project Risk per product area
        // Risk = completed delivery blueprint assignments / opportunities in that product area
        $opportunitiesByArea = $partner->opportunities
            ->whereNotNull('product_area')
            ->groupBy('product_area')
            ->map->count();

        $deliveryAssignments = $partner->blueprintAssignments->where('role', 'delivery');
        $completedDeliveryByArea = $deliveryAssignments
            ->filter(fn($a) => $a->status === 'completed')
            ->groupBy(fn($a) => $a->blueprint->product_area ?? 'Other')
            ->map->count();

        $allAreas = $opportunitiesByArea->keys()
            ->merge($completedDeliveryByArea->keys())
            ->unique()
            ->filter()
            ->sort()
            ->values();

        $projectRisk = $allAreas->mapWithKeys(function($area) use ($opportunitiesByArea, $completedDeliveryByArea) {
            $oppCount = $opportunitiesByArea->get($area, 0);
            $deliveryCount = $completedDeliveryByArea->get($area, 0);
            $ratio = $oppCount > 0 ? round($deliveryCount / $oppCount, 2) : ($deliveryCount > 0 ? 2.0 : 0);
            return [$area => $ratio];
        });

        // Pipeline Progression funnel (NNACV by sales stage)
        $stageOrder = [
            'Prospecting', 'Qualification', 'Needs Analysis',
            'Value Proposition', 'Proposal/Price Quote',
            'Negotiation/Review', 'Closed Won',
        ];
        $pipelineFunnel = collect($stageOrder)->mapWithKeys(function($stage) use ($partner) {
            return [$stage => round($partner->opportunities->where('stage', $stage)->sum('amount'), 2)];
        });

        // Enablement Engagement (sessions by status)
        $enablementByStatus = $partner->enablementSessions
            ->groupBy('status')
            ->map->count();

        return view('partner.show', compact(
            'partner',
            'activeProjects', 'totalContacts', 'totalLeads', 'totalAcv',
            'sourcedRevenuePct', 'influencedRevenuePct', 'avgBlueprintCompletion',
            'pipelineGrowthYoY', 'contactEngagement',
            'opportunityHistory', 'enablementByStatus', 'blueprintByArea', 'blueprintAreas',
            'projectRisk', 'pipelineFunnel'
        ));
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
     * Show the blueprint progress summary for the specified partner.
     */
    public function blueprints(string $id)
    {
        $partner = Partner::with([
            'blueprintAssignments.blueprint',
            'blueprintAssignments.enrollments.contact',
            'blueprintAssignments.enrollments.progress',
            'blueprintAssignments.assignedBy',
        ])->findOrFail($id);

        if ($partner->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access to this partner.');
        }

        return view('partner.blueprints', compact('partner'));
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
