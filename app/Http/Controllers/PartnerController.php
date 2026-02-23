<?php

namespace App\Http\Controllers;

use App\Enums\StepType;
use App\Helpers\FiscalYearHelper;
use App\Models\Blueprint;
use App\Models\Certification;
use App\Models\Contact;
use App\Models\ContactInteraction;
use App\Models\LaunchProgram;
use App\Models\LaunchProgramParticipant;
use App\Models\LaunchProgramPartner;
use App\Models\Partner;
use App\Models\PartnerAlert;
use App\Models\PartnerGoal;
use App\Models\PartnerGoalDimension;
use App\Models\PartnerPathEnrollment;
use App\Models\PartnerScorecard;
use App\Models\Product;
use App\Models\User;
use App\Services\ActivationTracker;
use App\Services\GateEvaluationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $query = auth()->user()->role === 'admin'
            ? Partner::query()
            : Partner::where('user_id', auth()->id());

        $partners = $query
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
     * Display the specified resource (tabbed partner detail shell).
     */
    public function show(string $id)
    {
        $partner = Partner::with(['projects', 'contacts', 'opportunities', 'leads', 'certifications', 'anaplanWorkspaces'])
            ->findOrFail($id);
        $this->authorizePartnerAccess($partner);

        app(ActivationTracker::class)->track(auth()->user(), 'partner_management_user', StepType::UsageThreshold);

        // KPI metrics (always visible above tabs)
        $activeProjects = $partner->projects->whereNotIn('status', ['Completed'])->count();
        $activeCerts = $partner->certifications->where('status', 'active');
        $certifiedModelBuilders = $activeCerts->whereIn('type', ['model_builder', 'model_builder_pro'])->count();
        $certifiedSolutionArchitects = $activeCerts->where('type', 'solution_architect')->count();
        $totalAcv = $partner->projects->sum('acv');

        // Overview tab data (server-rendered with the page)
        $overviewData = $this->overviewTabData($partner);

        return view('partner.show', array_merge(
            compact('partner', 'activeProjects', 'certifiedModelBuilders', 'certifiedSolutionArchitects', 'totalAcv'),
            $overviewData
        ));
    }

    /**
     * Return AJAX-loaded tab partial HTML.
     */
    public function partnerTab(string $id, string $tab)
    {
        $partner = Partner::findOrFail($id);
        $this->authorizePartnerAccess($partner);

        $data = match ($tab) {
            'overview' => $this->overviewTabData($partner),
            'pipeline' => $this->pipelineTabData($partner),
            'enablement' => $this->enablementTabData($partner),
            'projects' => $this->projectsTabData($partner),
            'calendar' => $this->calendarTabData($partner),
            'influencers' => $this->influencersTabData($partner),
            'resources' => $this->resourcesTabData($partner),
            'app-enablement' => $this->appEnablementTabData($partner),
            'launch-programs' => $this->launchProgramsTabData($partner),
            'workspaces' => $this->workspacesTabData($partner),
            'portal-users' => $this->portalUsersTabData($partner),
            default => abort(404),
        };

        return view("partner.tabs.{$tab}", array_merge(['partner' => $partner], $data));
    }

    /**
     * Authorize access to a partner.
     */
    private function authorizePartnerAccess(Partner $partner): void
    {
        if ($partner->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access to this partner.');
        }
    }

    /**
     * Overview tab: business metrics + health score + partner path.
     */
    private function overviewTabData(Partner $partner): array
    {
        // Ensure relationships are loaded
        if (! $partner->relationLoaded('opportunities')) {
            $partner->load('opportunities');
        }
        if (! $partner->relationLoaded('blueprintAssignments')) {
            $partner->load(['blueprintAssignments.blueprint', 'blueprintAssignments.enrollments.progress']);
        }
        if (! $partner->relationLoaded('contacts')) {
            $partner->load('contacts');
        }

        // Fiscal context
        $fiscal = FiscalYearHelper::getCurrentFiscalPeriod();
        $currentFY = $fiscal['fiscal_year'];
        $currentFQ = $fiscal['fiscal_quarter'];

        // Business Metrics — quarter vs annual (YTD)
        $qtrOpps = $partner->opportunities->where('fiscal_quarter', $currentFQ);
        $qtrTotal = $qtrOpps->sum('amount');
        $qtrSourced = $qtrOpps->where('revenue_type', 'sourced')->sum('amount');
        $qtrInfluenced = $qtrOpps->where('revenue_type', 'influenced')->sum('amount');

        $ytdOpps = $partner->opportunities->where('fiscal_year', $currentFY);
        $ytdTotal = $ytdOpps->sum('amount');
        $ytdSourced = $ytdOpps->where('revenue_type', 'sourced')->sum('amount');
        $ytdInfluenced = $ytdOpps->where('revenue_type', 'influenced')->sum('amount');

        $businessMetrics = [
            'quarter' => [
                'label' => $currentFQ,
                'sourced_pct' => $qtrTotal > 0 ? round($qtrSourced / $qtrTotal * 100, 1) : 0,
                'influenced_pct' => $qtrTotal > 0 ? round($qtrInfluenced / $qtrTotal * 100, 1) : 0,
                'sourced_amount' => round($qtrSourced, 0),
                'influenced_amount' => round($qtrInfluenced, 0),
                'total_amount' => round($qtrTotal, 0),
            ],
            'annual' => [
                'label' => 'FY'.$currentFY,
                'sourced_pct' => $ytdTotal > 0 ? round($ytdSourced / $ytdTotal * 100, 1) : 0,
                'influenced_pct' => $ytdTotal > 0 ? round($ytdInfluenced / $ytdTotal * 100, 1) : 0,
                'sourced_amount' => round($ytdSourced, 0),
                'influenced_amount' => round($ytdInfluenced, 0),
                'total_amount' => round($ytdTotal, 0),
            ],
        ];

        // Shared metrics (not period-dependent)
        $assignmentsWithEnrollments = $partner->blueprintAssignments->filter(fn ($a) => $a->enrollments->count() > 0);
        $avgBlueprintCompletion = $assignmentsWithEnrollments->count() > 0
            ? round($assignmentsWithEnrollments->avg(fn ($a) => $a->completionPercentage()), 1) : 0;

        $totalContacts = $partner->contacts->count();
        $enrolledContactIds = $partner->blueprintAssignments->flatMap(fn ($a) => $a->enrollments->pluck('contact_id'))->unique();
        $contactEngagement = $totalContacts > 0
            ? round($enrolledContactIds->count() / $totalContacts * 100, 1) : 0;

        // Pipeline Growth YoY
        $currentPipeline = $partner->opportunities->sum('amount');
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

        // Partner Path Enrollments
        $pathEnrollments = PartnerPathEnrollment::with(['path.stages', 'path.gates', 'currentStage'])
            ->where('partner_id', $partner->id)
            ->where('status', 'active')
            ->get();

        $gateService = app(GateEvaluationService::class);
        $pathData = $pathEnrollments->map(function ($enrollment) use ($partner, $gateService) {
            $stages = $enrollment->path->stages->sortBy('stage_number');
            $nextStage = $stages
                ->where('stage_number', '>', $enrollment->currentStage->stage_number)
                ->first();

            $gateResults = $nextStage
                ? $gateService->evaluateGates($partner, $enrollment->current_stage_id, $nextStage->id)
                : collect();

            $metCount = $gateResults->where('met', true)->count();
            $totalGates = $gateResults->count();
            $stageProgress = $stages->count() > 1
                ? round(($enrollment->currentStage->stage_number - 1) / ($stages->count() - 1) * 100)
                : 100;

            return [
                'enrollment' => $enrollment,
                'stages' => $stages,
                'nextStage' => $nextStage,
                'gateResults' => $gateResults,
                'metCount' => $metCount,
                'totalGates' => $totalGates,
                'stageProgress' => $stageProgress,
                'daysInStage' => (int) $enrollment->stage_entered_at->diffInDays(now()),
            ];
        });

        $latestScorecard = PartnerScorecard::where('partner_id', $partner->id)
            ->orderByDesc('scored_at')
            ->first();

        $scorecardHistory = PartnerScorecard::where('partner_id', $partner->id)
            ->orderByDesc('scored_at')
            ->take(6)
            ->get();

        return compact(
            'businessMetrics', 'avgBlueprintCompletion',
            'pipelineGrowthYoY', 'contactEngagement',
            'pathData', 'latestScorecard', 'scorecardHistory'
        );
    }

    /**
     * Pipeline tab: comprehensive pipeline analytics dashboard.
     */
    private function pipelineTabData(Partner $partner): array
    {
        if (! $partner->relationLoaded('opportunities')) {
            $partner->load('opportunities');
        }

        // ── Fiscal Context ──
        $fiscal = FiscalYearHelper::getCurrentFiscalPeriod();
        $currentFY = $fiscal['fiscal_year'];
        $currentFQ = $fiscal['fiscal_quarter'];

        // ── Open pipeline (reusable collection) ──
        $openOpps = $partner->opportunities->whereNotIn('stage', ['Closed Won', 'Closed Lost']);
        $currentPipelineValue = round($openOpps->sum('amount'), 2);

        // ── Goal Attainment & Shortfall ──
        $goal = PartnerGoal::where('partner_id', $partner->id)
            ->where('fiscal_year', $currentFY)
            ->first();

        $closedWonSourced = round($partner->opportunities
            ->where('stage', 'Closed Won')
            ->where('fiscal_year', $currentFY)
            ->where('revenue_type', 'sourced')
            ->sum('amount'), 2);

        $closedWonInfluenced = round($partner->opportunities
            ->where('stage', 'Closed Won')
            ->where('fiscal_year', $currentFY)
            ->where('revenue_type', 'influenced')
            ->sum('amount'), 2);

        $sourcedTarget = $goal ? (float) $goal->sourced_nnacv_target : 0;
        $influencedTarget = $goal ? (float) $goal->influenced_nnacv_target : 0;
        $sourcedShortfall = max(0, $sourcedTarget - $closedWonSourced);
        $influencedShortfall = max(0, $influencedTarget - $closedWonInfluenced);
        $sourcedAttainmentPct = $sourcedTarget > 0 ? round($closedWonSourced / $sourcedTarget * 100, 1) : 0;
        $influencedAttainmentPct = $influencedTarget > 0 ? round($closedWonInfluenced / $influencedTarget * 100, 1) : 0;

        $weightedPipeline = round($openOpps->sum(fn ($o) => $o->amount * (float) ($o->probability ?? 0) / 100), 2);
        $totalTarget = $sourcedTarget + $influencedTarget;
        $totalClosed = $closedWonSourced + $closedWonInfluenced;
        $coverageRatio = $totalTarget > 0 ? round(($totalClosed + $weightedPipeline) / $totalTarget, 2) : null;

        // ── Win Rate (12-month rolling) ──
        $won12m = $partner->opportunities
            ->where('stage', 'Closed Won')
            ->filter(fn ($o) => $o->close_date && $o->close_date->gte(now()->subMonths(12)))
            ->count();
        $lost12m = $partner->opportunities
            ->where('stage', 'Closed Lost')
            ->filter(fn ($o) => $o->close_date && $o->close_date->gte(now()->subMonths(12)))
            ->count();
        $winRate = ($won12m + $lost12m) > 0 ? round($won12m / ($won12m + $lost12m) * 100, 1) : 0;
        $wonAmount12m = round($partner->opportunities
            ->where('stage', 'Closed Won')
            ->filter(fn ($o) => $o->close_date && $o->close_date->gte(now()->subMonths(12)))
            ->sum('amount'), 2);

        // ── New Business (Last 30 Days) ──
        $newBiz30 = $openOpps->filter(fn ($o) => $o->created_at->gte(now()->subDays(30)));
        $newBiz30Count = $newBiz30->count();
        $newBiz30Amount = round($newBiz30->sum('amount'), 2);

        // ── Stale Deals (not updated in 30+ days) ──
        $staleDeals = $openOpps->filter(fn ($o) => $o->updated_at->lt(now()->subDays(30)));
        $staleDealCount = $staleDeals->count();
        $staleDealAmount = round($staleDeals->sum('amount'), 2);

        // ── Closing This Quarter ──
        $closingThisQtrCount = 0;
        $closingThisQtrAmount = 0;
        $lowProbClosingCount = 0;
        try {
            $fyQuarterDates = FiscalYearHelper::getFiscalQuarterDates($currentFQ);
            $closingThisQtr = $openOpps->filter(fn ($o) => $o->close_date && $o->close_date->between($fyQuarterDates['start_date'], $fyQuarterDates['end_date'])
            );
            $closingThisQtrCount = $closingThisQtr->count();
            $closingThisQtrAmount = round($closingThisQtr->sum('amount'), 2);
            $lowProbClosingCount = $closingThisQtr->filter(fn ($o) => ($o->probability ?? 0) < 50)->count();
        } catch (\Exception $e) {
            // Gracefully handle if fiscal quarter format is invalid
        }

        // ── Pipeline Velocity by Stage ──
        $stageOrder = [
            'Prospecting', 'Qualification', 'Needs Analysis',
            'Value Proposition', 'Proposal/Price Quote',
            'Negotiation/Review',
        ];
        $stageVelocity = collect($stageOrder)->map(function ($stage) use ($partner) {
            $stageOpps = $partner->opportunities->where('stage', $stage);
            $count = $stageOpps->count();

            return [
                'stage' => $stage,
                'count' => $count,
                'amount' => round($stageOpps->sum('amount'), 2),
                'avg_days' => $count > 0
                    ? (int) round($stageOpps->avg(fn ($o) => abs(now()->diffInDays($o->updated_at))), 0)
                    : 0,
            ];
        })->filter(fn ($s) => $s['count'] > 0)->values();

        // ── Historical Pipeline Comparison (90 days ago) ──
        $historicalPipelineValue = 0;
        $nearestSnapshotDate = \DB::table('opportunity_snapshots')
            ->join('imports', 'opportunity_snapshots.import_id', '=', 'imports.id')
            ->join('opportunities', 'opportunity_snapshots.opportunity_id', '=', 'opportunities.id')
            ->where('opportunities.partner_id', $partner->id)
            ->where('imports.started_at', '<=', now()->subDays(90))
            ->orderByDesc('imports.started_at')
            ->value(\DB::raw('DATE(imports.started_at)'));

        if ($nearestSnapshotDate) {
            $historicalPipelineValue = (float) \DB::table('opportunity_snapshots')
                ->join('imports', 'opportunity_snapshots.import_id', '=', 'imports.id')
                ->join('opportunities', 'opportunity_snapshots.opportunity_id', '=', 'opportunities.id')
                ->where('opportunities.partner_id', $partner->id)
                ->whereNotIn('opportunity_snapshots.stage', ['Closed Won', 'Closed Lost'])
                ->whereRaw('DATE(imports.started_at) = ?', [$nearestSnapshotDate])
                ->sum('opportunity_snapshots.amount');
        }

        $pipelineChangeAmt = $currentPipelineValue - $historicalPipelineValue;
        $pipelineChangePct = $historicalPipelineValue > 0
            ? round($pipelineChangeAmt / $historicalPipelineValue * 100, 1)
            : ($currentPipelineValue > 0 ? 100 : 0);

        // ── Pipeline by Product Area ──
        $pipelineByProductArea = $openOpps
            ->groupBy(fn ($o) => $o->product_area ?? 'Unassigned')
            ->map(fn ($group) => round($group->sum('amount'), 2))
            ->sortDesc();

        // ── Historical opportunity snapshots (52 weeks) ── (existing)
        $opportunityHistory = \DB::table('opportunity_snapshots')
            ->join('imports', 'opportunity_snapshots.import_id', '=', 'imports.id')
            ->join('opportunities', 'opportunity_snapshots.opportunity_id', '=', 'opportunities.id')
            ->where('opportunities.partner_id', $partner->id)
            ->whereBetween('imports.started_at', [now()->subWeeks(52), now()])
            ->select(
                \DB::raw('DATE_FORMAT(imports.started_at, "%Y-%m") as snapshot_month'),
                \DB::raw('SUM(CASE WHEN opportunity_snapshots.revenue_type = "sourced" THEN opportunity_snapshots.amount ELSE 0 END) as sourced_amount'),
                \DB::raw('SUM(CASE WHEN opportunity_snapshots.revenue_type = "influenced" THEN opportunity_snapshots.amount ELSE 0 END) as influenced_amount'),
                \DB::raw('COUNT(DISTINCT opportunity_snapshots.opportunity_id) as opportunity_count')
            )
            ->groupBy(\DB::raw('DATE_FORMAT(imports.started_at, "%Y-%m")'))
            ->orderBy(\DB::raw('DATE_FORMAT(imports.started_at, "%Y-%m")'))
            ->get();

        // ── Pipeline Funnel ── (existing)
        $funnelStages = [
            'Prospecting', 'Qualification', 'Needs Analysis',
            'Value Proposition', 'Proposal/Price Quote',
            'Negotiation/Review', 'Closed Won',
        ];
        $pipelineFunnel = collect($funnelStages)->mapWithKeys(function ($stage) use ($partner) {
            return [$stage => round($partner->opportunities->where('stage', $stage)->sum('amount'), 2)];
        });

        $opportunities = $partner->opportunities->sortByDesc('amount');

        // ── Product Whitespace Analysis ──
        $allProductAreas = Blueprint::PRODUCT_AREAS;
        $goalRecord = $goal ? PartnerGoal::with('dimensions')->find($goal->id) : null;

        $whitespaceSummary = collect($allProductAreas)->map(function ($area) use ($openOpps, $goalRecord) {
            $areaOpps = $openOpps->where('product_area', $area);
            $pipelineValue = round($areaOpps->sum('amount'), 2);
            $dimKey = 'pa_'.Str::snake(strtolower($area));

            $goalDim = null;
            if ($goalRecord) {
                $goalDim = PartnerGoalDimension::where('partner_goal_id', $goalRecord->id)
                    ->where('dimension', $dimKey)
                    ->first();
            }

            $target = $goalDim ? (float) $goalDim->target_value : 0;
            $gap = max(0, $target - $pipelineValue);

            return [
                'area' => $area,
                'in_pipeline' => $areaOpps->count() > 0,
                'pipeline_value' => $pipelineValue,
                'opp_count' => $areaOpps->count(),
                'target' => $target,
                'gap' => $gap,
                'attainment_pct' => $target > 0 ? round($pipelineValue / $target * 100, 1) : null,
            ];
        });

        // ── Computed Pipeline Alerts ──
        $pipelineAlerts = $this->computePipelineAlerts(
            $partner, $goal, $currentFY, $currentFQ,
            $sourcedTarget, $influencedTarget,
            $sourcedShortfall, $influencedShortfall,
            $coverageRatio, $winRate, $won12m, $lost12m, $wonAmount12m,
            $staleDealCount, $staleDealAmount,
            $newBiz30Count, $currentPipelineValue,
            $lowProbClosingCount, $closingThisQtrAmount,
            $openOpps
        );

        // ── Server-side PartnerAlerts (change detection + pipeline risk) ──
        $serverAlerts = PartnerAlert::where('partner_id', $partner->id)
            ->whereIn('type', ['new_opportunity', 'stage_change', 'close_date_slip', 'pipeline_risk'])
            ->where('status', 'active')
            ->orderByRaw("FIELD(severity, 'critical', 'warning', 'info', 'positive')")
            ->take(10)
            ->get();

        return compact(
            'opportunityHistory', 'pipelineFunnel', 'opportunities',
            'currentFY', 'currentFQ',
            'goal', 'sourcedTarget', 'influencedTarget',
            'closedWonSourced', 'closedWonInfluenced',
            'sourcedShortfall', 'influencedShortfall',
            'sourcedAttainmentPct', 'influencedAttainmentPct',
            'weightedPipeline', 'coverageRatio',
            'winRate', 'won12m', 'lost12m', 'wonAmount12m',
            'newBiz30Count', 'newBiz30Amount',
            'staleDealCount', 'staleDealAmount',
            'closingThisQtrCount', 'closingThisQtrAmount', 'lowProbClosingCount',
            'stageVelocity',
            'currentPipelineValue', 'historicalPipelineValue', 'pipelineChangeAmt', 'pipelineChangePct',
            'pipelineByProductArea',
            'whitespaceSummary', 'pipelineAlerts', 'serverAlerts'
        );
    }

    /**
     * Build structured, actionable pipeline alerts with specific metrics.
     */
    private function computePipelineAlerts(
        Partner $partner, $goal, $currentFY, $currentFQ,
        $sourcedTarget, $influencedTarget,
        $sourcedShortfall, $influencedShortfall,
        $coverageRatio, $winRate, $won12m, $lost12m, $wonAmount12m,
        $staleDealCount, $staleDealAmount,
        $newBiz30Count, $currentPipelineValue,
        $lowProbClosingCount, $closingThisQtrAmount,
        $openOpps
    ): array {
        $alerts = [];
        $totalTarget = $sourcedTarget + $influencedTarget;
        $avgDealSize = $won12m > 0 ? round($wonAmount12m / $won12m, 0) : ($openOpps->count() > 0 ? round($openOpps->avg('amount'), 0) : 0);
        $fmtAvg = '$'.number_format($avgDealSize, 0);

        // 1. Coverage ratio alert
        if ($coverageRatio !== null && $coverageRatio < 1.5 && $totalTarget > 0) {
            $desiredCoverage = 3.0;
            $neededPipeline = max(0, ($totalTarget * $desiredCoverage) - ($currentPipelineValue + ($goal ? ($sourcedTarget - $sourcedShortfall) + ($influencedTarget - $influencedShortfall) : 0)));
            $dealsNeeded = $avgDealSize > 0 ? ceil($neededPipeline / $avgDealSize) : 0;
            $fmtNeeded = '$'.number_format($neededPipeline, 0);

            $alerts[] = [
                'level' => 'danger',
                'icon' => 'fa-shield',
                'title' => 'Pipeline Coverage Below Target',
                'message' => "Your pipeline needs an additional {$fmtNeeded}".($dealsNeeded > 0 ? " or ~{$dealsNeeded} opportunities (at avg deal size of {$fmtAvg})" : '')." to meet the desired 3x coverage ratio. Current weighted coverage is {$coverageRatio}x against $".number_format($totalTarget, 0)." FY{$currentFY} target.",
                'action_url' => '#pipeline-opps-table',
                'action_label' => 'Review Pipeline',
            ];
        }

        // 2. Stale deals alert
        if ($staleDealCount > 0) {
            $staleSorted = $openOpps->filter(fn ($o) => $o->updated_at->lt(now()->subDays(30)))->sortByDesc('amount');
            $largest = $staleSorted->first();
            $largestName = $largest ? $largest->opportunity_name : '';
            $largestAmt = $largest ? '$'.number_format($largest->amount, 0) : '';
            $largestDays = $largest ? (int) abs(now()->diffInDays($largest->updated_at)) : 0;
            $fmtStaleAmt = '$'.number_format($staleDealAmount, 0);

            $alerts[] = [
                'level' => 'danger',
                'icon' => 'fa-clock',
                'title' => 'Stale Deals Need Attention',
                'message' => "{$staleDealCount} ".Str::plural('deal', $staleDealCount)." worth {$fmtStaleAmt} ".($staleDealCount === 1 ? 'has' : 'have').' not been updated in 30+ days.'.($largest ? " The largest is \"{$largestName}\" ({$largestAmt}) last updated {$largestDays}d ago." : ''),
                'action_url' => '#pipeline-opps-table',
                'action_label' => 'Review Stale Deals',
            ];
        }

        // 3. Win rate alert
        if ($winRate > 0 && $winRate < 25 && ($won12m + $lost12m) >= 3) {
            $alerts[] = [
                'level' => 'warning',
                'icon' => 'fa-trophy',
                'title' => 'Low Win Rate',
                'message' => "Win rate is {$winRate}% over the last 12 months ({$won12m}W / {$lost12m}L). Review lost deal patterns to identify areas for improvement.",
                'action_url' => null,
                'action_label' => null,
            ];
        }

        // 4. No new pipeline alert
        if ($newBiz30Count === 0 && $currentPipelineValue > 0) {
            $alerts[] = [
                'level' => 'warning',
                'icon' => 'fa-plus-circle',
                'title' => 'No New Pipeline Activity',
                'message' => 'No new pipeline has been added in the last 30 days. Consider scheduling joint prospecting sessions or reviewing account plans for whitespace opportunities.',
                'action_url' => null,
                'action_label' => null,
            ];
        }

        // 5. Low-probability deals closing this quarter
        if ($lowProbClosingCount > 0) {
            $lowProbDeals = $openOpps->filter(fn ($o) => ($o->probability ?? 0) < 50 && $o->close_date)
                ->sortByDesc('amount');
            $topDeal = $lowProbDeals->first();
            $topName = $topDeal ? $topDeal->opportunity_name : '';
            $topAmt = $topDeal ? '$'.number_format($topDeal->amount, 0) : '';
            $topProb = $topDeal ? round($topDeal->probability, 0) : 0;
            $fmtQtrAmt = '$'.number_format($closingThisQtrAmount, 0);

            $alerts[] = [
                'level' => 'warning',
                'icon' => 'fa-calendar-exclamation',
                'title' => 'At-Risk Quarter Deals',
                'message' => "{$lowProbClosingCount} ".Str::plural('deal', $lowProbClosingCount)." worth {$fmtQtrAmt} closing this quarter with probability below 50%.".($topDeal ? " Highest value: \"{$topName}\" ({$topAmt}, {$topProb}%)." : ''),
                'action_url' => '#pipeline-opps-table',
                'action_label' => 'Review Deals',
            ];
        }

        // 6. Goal shortfall alert
        if ($goal && ($sourcedShortfall + $influencedShortfall) > 0) {
            $totalShortfall = $sourcedShortfall + $influencedShortfall;
            $fmtTotal = '$'.number_format($totalShortfall, 0);
            $fmtSourced = '$'.number_format($sourcedShortfall, 0);
            $fmtInfluenced = '$'.number_format($influencedShortfall, 0);
            $dealsNeeded = $avgDealSize > 0 ? ceil($totalShortfall / $avgDealSize) : 0;

            $alerts[] = [
                'level' => 'info',
                'icon' => 'fa-bullseye',
                'title' => 'FY'.$currentFY.' Target Shortfall',
                'message' => "FY{$currentFY} target shortfall: {$fmtTotal} remaining ({$fmtSourced} sourced, {$fmtInfluenced} influenced).".($dealsNeeded > 0 ? " At avg deal size of {$fmtAvg}, you need ~{$dealsNeeded} more wins to close the gap." : ''),
                'action_url' => null,
                'action_label' => null,
            ];
        }

        return $alerts;
    }

    /**
     * Influencers tab: key influencer profiles and interaction timeline.
     */
    private function influencersTabData(Partner $partner): array
    {
        $influencers = $partner->contacts()
            ->where('contact_type', 'influencer')
            ->withCount('interactions')
            ->with('interactions')
            ->get();

        $recentInteractions = ContactInteraction::where('partner_id', $partner->id)
            ->with('contact', 'loggedBy')
            ->orderByDesc('interaction_date')
            ->take(15)
            ->get();

        $byRole = $influencers->groupBy('influencer_role')
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $interactionsThisMonth = ContactInteraction::where('partner_id', $partner->id)
            ->where('interaction_date', '>=', now()->startOfMonth())
            ->count();

        $lastInteractionDate = ContactInteraction::where('partner_id', $partner->id)
            ->max('interaction_date');

        // ── Breadth: Influencer network growth ──
        $breadthTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $breadthTrend[] = [
                'month' => $monthStart->format('M Y'),
                'count' => $partner->contacts()
                    ->where('contact_type', 'influencer')
                    ->where('created_at', '<=', $monthEnd)
                    ->count(),
                'new' => $partner->contacts()
                    ->where('contact_type', 'influencer')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count(),
            ];
        }
        $currentCount = $influencers->count();
        $countThreeMonthsAgo = $breadthTrend[2]['count'] ?? 0;
        $breadthGrowthPct = $countThreeMonthsAgo > 0
            ? round(($currentCount - $countThreeMonthsAgo) / $countThreeMonthsAgo * 100, 1)
            : ($currentCount > 0 ? 100 : 0);
        $newThisMonth = $breadthTrend[5]['new'] ?? 0;
        $rolesUsed = $influencers->pluck('influencer_role')->filter()->unique()->count();
        $totalRoles = count(Contact::INFLUENCER_ROLES);
        $roleCoveragePct = $totalRoles > 0 ? round($rolesUsed / $totalRoles * 100, 0) : 0;

        // Role coverage for radar chart (count per role)
        $roleCoverage = collect(Contact::INFLUENCER_ROLES)->mapWithKeys(function ($label, $key) use ($influencers) {
            return [$key => $influencers->where('influencer_role', $key)->count()];
        });

        // ── Depth: Interaction engagement analysis ──
        $depthMetrics = $influencers->map(function ($inf) {
            $uniqueTypes = $inf->interactions->pluck('interaction_type')->unique()->count();
            $last30 = $inf->interactions->filter(fn ($i) => $i->interaction_date->gte(now()->subDays(30)))->count();
            $prev30 = $inf->interactions->filter(fn ($i) => $i->interaction_date->between(now()->subDays(60), now()->subDays(30)))->count();

            return [
                'id' => $inf->id,
                'name' => $inf->full_name,
                'role' => $inf->influencer_role,
                'total' => $inf->interactions_count,
                'unique_types' => $uniqueTypes,
                'last_30' => $last30,
                'prev_30' => $prev30,
                'trend' => $last30 - $prev30,
            ];
        });

        $deepInfluencers = $depthMetrics->filter(fn ($d) => $d['total'] >= 3)->count();
        $avgInteractions = $influencers->count() > 0 ? round($depthMetrics->avg('total'), 1) : 0;
        $avgTypeDiversity = $influencers->count() > 0 ? round($depthMetrics->avg('unique_types'), 1) : 0;

        // Interaction type distribution
        $interactionTypeDist = ContactInteraction::where('partner_id', $partner->id)
            ->selectRaw('interaction_type, count(*) as cnt')
            ->groupBy('interaction_type')
            ->pluck('cnt', 'interaction_type');

        // Per-influencer interaction breakdown by type (for stacked bar)
        $depthByType = $influencers->map(function ($inf) {
            $typeCounts = $inf->interactions->groupBy('interaction_type')
                ->map(fn ($group) => $group->count());

            return [
                'name' => $inf->full_name,
                'types' => $typeCounts,
            ];
        });

        // Monthly interaction trend (6 months)
        $depthTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $depthTrend[] = [
                'month' => $monthStart->format('M Y'),
                'count' => ContactInteraction::where('partner_id', $partner->id)
                    ->whereBetween('interaction_date', [$monthStart, $monthEnd])
                    ->count(),
            ];
        }

        return compact(
            'influencers', 'recentInteractions', 'byRole', 'interactionsThisMonth', 'lastInteractionDate',
            'breadthTrend', 'breadthGrowthPct', 'newThisMonth', 'rolesUsed', 'totalRoles', 'roleCoveragePct', 'roleCoverage',
            'depthMetrics', 'deepInfluencers', 'avgInteractions', 'avgTypeDiversity',
            'interactionTypeDist', 'depthByType', 'depthTrend'
        );
    }

    /**
     * Resources tab: partner resources — specialists who staff projects, earn certifications, etc.
     */
    private function resourcesTabData(Partner $partner): array
    {
        $resources = $partner->contacts()
            ->where('contact_type', 'resource')
            ->withCount(['projects', 'certifications', 'blueprintEnrollments', 'enablementRegistrations'])
            ->with(['certifications', 'blueprintEnrollments.progress', 'projects'])
            ->get();

        $byRole = $resources->groupBy('resource_role')
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        // Certification stats
        $certifications = Certification::where('partner_id', $partner->id)->get();
        $activeCerts = $certifications->where('status', 'active')->count();
        $totalCerts = $certifications->count();
        $certsByType = $certifications->groupBy('type')->map->count();
        $certsByLevel = $certifications->groupBy('level')->map->count();
        $expiringSoon = $certifications->filter(fn ($c) => $c->isExpiringSoon(90))->count();

        // Blueprint enrollment stats
        $enrollments = $partner->blueprintAssignments()
            ->with(['enrollments.contact', 'enrollments.progress', 'blueprint'])
            ->get()
            ->flatMap(fn ($a) => $a->enrollments);

        $completedEnrollments = $enrollments->where('status', 'completed')->count();
        $inProgressEnrollments = $enrollments->where('status', 'in_progress')->count();
        $totalEnrollments = $enrollments->count();
        $avgCompletion = $totalEnrollments > 0
            ? round($enrollments->avg(fn ($e) => $e->completionPercentage()), 1)
            : 0;

        // Enablement session registrations
        $registrations = $partner->enablementRegistrations()
            ->with('session', 'contact')
            ->get();
        $attendedSessions = $registrations->where('status', 'attended')->count();
        $upcomingRegistrations = $registrations->filter(
            fn ($r) => $r->session && $r->session->session_date && $r->session->session_date->gte(now())
        )->count();

        // Projects staffed by resources
        $projectsStaffed = $resources->flatMap(fn ($r) => $r->projects)->unique('id')->count();

        // Resource growth trend (6 months)
        $growthTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $growthTrend[] = [
                'month' => now()->subMonths($i)->startOfMonth()->format('M Y'),
                'count' => $partner->contacts()
                    ->where('contact_type', 'resource')
                    ->where('created_at', '<=', $monthEnd)
                    ->count(),
            ];
        }

        // Certification trend (6 months)
        $certTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $certTrend[] = [
                'month' => now()->subMonths($i)->startOfMonth()->format('M Y'),
                'active' => Certification::where('partner_id', $partner->id)
                    ->where('status', 'active')
                    ->where('issued_date', '<=', $monthEnd)
                    ->count(),
            ];
        }

        // Per-resource capability matrix
        $resourceMetrics = $resources->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->full_name,
                'role' => $r->resource_role,
                'projects' => $r->projects_count,
                'certs' => $r->certifications_count,
                'blueprints' => $r->blueprint_enrollments_count,
                'sessions' => $r->enablement_registrations_count,
            ];
        });

        return compact(
            'resources', 'byRole', 'certifications', 'activeCerts', 'totalCerts',
            'certsByType', 'certsByLevel', 'expiringSoon',
            'completedEnrollments', 'inProgressEnrollments', 'totalEnrollments', 'avgCompletion',
            'attendedSessions', 'upcomingRegistrations', 'projectsStaffed',
            'growthTrend', 'certTrend', 'resourceMetrics'
        );
    }

    /**
     * App Enablement tab: product readiness matrix crossing products with pipeline progression.
     */
    private function appEnablementTabData(Partner $partner): array
    {
        $products = Product::published()
            ->orderBy('product_line')
            ->orderBy('sort_order')
            ->get();

        // Resource contacts for this partner with their product training
        $resources = $partner->contacts()
            ->where('contact_type', 'resource')
            ->with('products')
            ->get();

        // Opportunities with product relationships
        $opportunities = $partner->opportunities()
            ->whereNotNull('product_id')
            ->with('product')
            ->get();

        // Projects with opportunity+product
        $projects = $partner->projects()
            ->with('opportunity.product')
            ->get();

        // Build the matrix: for each product, compute each column
        $matrix = $products->map(function (Product $product) use ($resources, $opportunities, $projects) {
            // Trained resources
            $trainedResources = $resources->filter(
                fn ($r) => $r->products->contains(fn ($p) => $p->id === $product->id && $p->pivot->status === 'trained')
            );
            $inProgressResources = $resources->filter(
                fn ($r) => $r->products->contains(fn ($p) => $p->id === $product->id && $p->pivot->status === 'in_progress')
            );
            $workshopResources = $resources->filter(
                fn ($r) => $r->products->contains(fn ($p) => $p->id === $product->id && $p->pivot->status === 'workshop')
            );

            // Pipeline: Stage 3+ (Needs Analysis, Value Proposition, Proposal)
            $pipelineStages = ['Needs Analysis', 'Value Proposition', 'Proposal/Price Quote', 'Negotiation/Review'];
            $pipelineOpps = $opportunities->filter(
                fn ($o) => $o->product_id === $product->id && in_array($o->stage, $pipelineStages)
            );

            // Won / Kick-off projects
            $kickoffProjects = $projects->filter(
                fn ($p) => $p->opportunity?->product_id === $product->id && $p->phase === 'kickoff'
            );

            // Implementation (IHS) projects
            $implementationProjects = $projects->filter(
                fn ($p) => $p->opportunity?->product_id === $product->id && $p->phase === 'implementation'
            );

            // Customer Live (CS) projects
            $liveProjects = $projects->filter(
                fn ($p) => $p->opportunity?->product_id === $product->id && $p->phase === 'live'
            );

            return [
                'product' => $product,
                'trained_count' => $trainedResources->count(),
                'in_progress_count' => $inProgressResources->count(),
                'workshop_count' => $workshopResources->count(),
                'total_resources' => $trainedResources->count() + $inProgressResources->count() + $workshopResources->count(),
                'pipeline_opps' => $pipelineOpps->map(fn ($o) => $o->account_name)->unique()->values(),
                'pipeline_amount' => round($pipelineOpps->sum('amount'), 2),
                'kickoff_projects' => $kickoffProjects->map(fn ($p) => $p->opportunity?->account_name ?? $p->name)->unique()->values(),
                'implementation_projects' => $implementationProjects->map(fn ($p) => $p->opportunity?->account_name ?? $p->name)->unique()->values(),
                'live_projects' => $liveProjects->map(fn ($p) => $p->opportunity?->account_name ?? $p->name)->unique()->values(),
            ];
        });

        // Group by product line
        $productLines = $matrix->groupBy(fn ($row) => $row['product']->product_line);

        // KPI totals
        $totalTrained = $matrix->sum('trained_count');
        $totalInProgress = $matrix->sum('in_progress_count');
        $totalPipeline = $matrix->sum(fn ($row) => $row['pipeline_opps']->count());
        $totalLive = $matrix->sum(fn ($row) => $row['live_projects']->count());

        return compact('productLines', 'matrix', 'totalTrained', 'totalInProgress', 'totalPipeline', 'totalLive');
    }

    /**
     * Enablement tab: blueprint radar + enablement donut + assignments + sessions.
     */
    private function enablementTabData(Partner $partner): array
    {
        $partner->load([
            'blueprintAssignments.blueprint',
            'blueprintAssignments.enrollments.contact',
            'blueprintAssignments.enrollments.progress',
            'blueprintAssignments.assignedBy',
            'enablementSessions',
            'contacts:id,partner_id,first_name,last_name,email',
        ]);

        // Blueprint completion by product area + role (radar chart)
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
            if ($roleAssignments->isEmpty()) {
                continue;
            }

            $data = [];
            foreach ($productAreas as $area) {
                $areaAssignments = $roleAssignments->filter(fn ($a) => ($a->blueprint->product_area ?? 'Other') === $area);
                $data[] = $areaAssignments->isNotEmpty()
                    ? round($areaAssignments->avg(fn ($a) => $a->completionPercentage()), 1)
                    : 0;
            }
            $blueprintByArea[] = ['name' => $roleLabel, 'data' => $data];
        }
        $blueprintAreas = $productAreas;

        // Enablement sessions by status (donut)
        $enablementByStatus = $partner->enablementSessions
            ->groupBy('status')
            ->map->count();

        $blueprintAssignments = $partner->blueprintAssignments;

        // ── KPI metrics (moved from blade @php block) ──
        $enrolledContactIds = $blueprintAssignments->flatMap(fn ($a) => $a->enrollments->pluck('contact_id'))->unique();
        $assignmentsWithEnrollments = $blueprintAssignments->filter(fn ($a) => $a->enrollments->count() > 0);
        $avgCompletion = $assignmentsWithEnrollments->count() > 0
            ? round($assignmentsWithEnrollments->avg(fn ($a) => $a->completionPercentage()), 1) : 0;
        $completedAssignments = $assignmentsWithEnrollments->filter(fn ($a) => $a->completionPercentage() >= 100)->count();

        // ── Enrollment Depth per blueprint ──
        $enrollmentDepth = $assignmentsWithEnrollments
            ->map(fn ($a) => [
                'name' => Str::limit($a->blueprint->name, 30),
                'enrolled' => $a->enrollments->count(),
                'completion' => $a->completionPercentage(),
                'role' => $a->role,
            ])
            ->sortByDesc('enrolled')
            ->values();

        $avgDepth = $assignmentsWithEnrollments->count() > 0
            ? round($assignmentsWithEnrollments->avg(fn ($a) => $a->enrollments->count()), 1)
            : 0;

        // ── Stalled Blueprints (in-progress but no recent activity) ──
        $stalledAssignments = $blueprintAssignments
            ->filter(fn ($a) => $a->completionPercentage() > 0 && $a->completionPercentage() < 100)
            ->map(function ($a) {
                $lastActivity = $a->enrollments
                    ->flatMap(fn ($e) => $e->progress)
                    ->where('completed', true)
                    ->max('completed_at');

                return [
                    'name' => Str::limit($a->blueprint->name, 30),
                    'days_since_activity' => $lastActivity
                        ? (int) Carbon::parse($lastActivity)->diffInDays(now())
                        : (int) $a->created_at->diffInDays(now()),
                    'completion' => $a->completionPercentage(),
                    'role' => $a->role,
                ];
            })
            ->sortByDesc('days_since_activity')
            ->values();

        $stalledCount = $stalledAssignments->filter(fn ($a) => $a['days_since_activity'] > 30)->count();

        // ── Time to Complete distribution ──
        $completedEnrollments = $blueprintAssignments
            ->flatMap(fn ($a) => $a->enrollments)
            ->where('status', 'completed')
            ->filter(fn ($e) => $e->completed_at !== null);

        $bucketDefs = [
            ['bucket' => '< 2 weeks', 'max_days' => 14],
            ['bucket' => '2-4 weeks', 'max_days' => 28],
            ['bucket' => '1-2 months', 'max_days' => 60],
            ['bucket' => '2-3 months', 'max_days' => 90],
            ['bucket' => '3+ months', 'max_days' => PHP_INT_MAX],
        ];

        $timeToCompleteBuckets = collect($bucketDefs)->map(fn ($b) => array_merge($b, ['count' => 0]))->all();
        foreach ($completedEnrollments as $enrollment) {
            $days = (int) $enrollment->created_at->diffInDays($enrollment->completed_at);
            foreach ($timeToCompleteBuckets as $idx => &$bucket) {
                if ($days <= $bucket['max_days']) {
                    $bucket['count']++;
                    break;
                }
            }
        }
        unset($bucket);

        $avgTimeToComplete = $completedEnrollments->count() > 0
            ? (int) round($completedEnrollments->avg(fn ($e) => $e->created_at->diffInDays($e->completed_at)))
            : null;

        // Upcoming sessions
        $upcomingSessions = $partner->enablementSessions
            ->where('session_date', '>=', now())
            ->sortBy('session_date')
            ->take(10);

        // Contacts for embedded calendar
        $contacts = $partner->contacts->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->first_name.' '.$c->last_name,
            'email' => $c->email,
        ]);

        return compact(
            'blueprintByArea', 'blueprintAreas', 'enablementByStatus',
            'blueprintAssignments', 'upcomingSessions', 'contacts',
            'enrolledContactIds', 'avgCompletion', 'completedAssignments',
            'enrollmentDepth', 'avgDepth',
            'stalledAssignments', 'stalledCount',
            'timeToCompleteBuckets', 'avgTimeToComplete',
        );
    }

    /**
     * Projects tab: project risk chart + project table.
     */
    private function projectsTabData(Partner $partner): array
    {
        $partner->load(['projects.opportunity', 'opportunities', 'blueprintAssignments.blueprint', 'blueprintAssignments.enrollments']);

        $projects = $partner->projects->sortByDesc('created_at');

        // Delivery readiness: count people (enrollments) who completed delivery blueprints per area
        $deliveryAssignments = $partner->blueprintAssignments->where('role', 'delivery');
        $completedDeliveryByArea = collect();
        foreach ($deliveryAssignments as $assignment) {
            $area = $assignment->blueprint->product_area ?? 'Other';
            $completedPeople = $assignment->enrollments->where('status', 'completed')->count();
            $completedDeliveryByArea[$area] = ($completedDeliveryByArea->get($area, 0)) + $completedPeople;
        }

        // ── Active Project Risk: active projects vs delivery readiness ──
        $activeProjects = $projects->whereNotIn('status', ['Completed', 'Draft']);
        $activeByArea = $activeProjects
            ->groupBy(fn ($p) => $p->product_area ?? 'Other')
            ->filter(fn ($group, $key) => $key !== 'Other')
            ->map->count();

        $activeRisk = $this->computeRiskRatios($activeByArea, $completedDeliveryByArea);

        // ── Upcoming Pipeline Risk: opps at stage 3+ (Needs Analysis+), excluding Closed Won/Lost ──
        $upcomingStages = ['Needs Analysis', 'Value Proposition', 'Proposal/Price Quote', 'Negotiation/Review'];
        $upcomingByArea = $partner->opportunities
            ->whereIn('stage', $upcomingStages)
            ->whereNotNull('product_area')
            ->groupBy('product_area')
            ->map->count();

        $upcomingRisk = $this->computeRiskRatios($upcomingByArea, $completedDeliveryByArea);

        return compact('projects', 'activeRisk', 'upcomingRisk');
    }

    /**
     * Compute risk ratios: delivery readiness / demand count per area.
     */
    private function computeRiskRatios($demandByArea, $deliveryByArea): \Illuminate\Support\Collection
    {
        $allAreas = $demandByArea->keys()
            ->merge($deliveryByArea->keys())
            ->unique()
            ->filter()
            ->sort()
            ->values();

        return $allAreas->mapWithKeys(function ($area) use ($demandByArea, $deliveryByArea) {
            $demandCount = $demandByArea->get($area, 0);
            $deliveryCount = $deliveryByArea->get($area, 0);
            $ratio = $demandCount > 0 ? round($deliveryCount / $demandCount, 2) : ($deliveryCount > 0 ? 2.0 : 0);

            return [$area => $ratio];
        });
    }

    /**
     * Path & Health tab: partner path detail + scorecard radar + history.
     */
    private function pathHealthTabData(Partner $partner): array
    {
        // Partner Path Enrollments (full detail)
        $pathEnrollments = PartnerPathEnrollment::with(['path.stages', 'path.gates', 'currentStage'])
            ->where('partner_id', $partner->id)
            ->where('status', 'active')
            ->get();

        $gateService = app(GateEvaluationService::class);
        $pathData = $pathEnrollments->map(function ($enrollment) use ($partner, $gateService) {
            $stages = $enrollment->path->stages->sortBy('stage_number');
            $nextStage = $stages
                ->where('stage_number', '>', $enrollment->currentStage->stage_number)
                ->first();

            $gateResults = $nextStage
                ? $gateService->evaluateGates($partner, $enrollment->current_stage_id, $nextStage->id)
                : collect();

            $metCount = $gateResults->where('met', true)->count();
            $totalGates = $gateResults->count();
            $stageProgress = $stages->count() > 1
                ? round(($enrollment->currentStage->stage_number - 1) / ($stages->count() - 1) * 100)
                : 100;

            return [
                'enrollment' => $enrollment,
                'stages' => $stages,
                'nextStage' => $nextStage,
                'gateResults' => $gateResults,
                'metCount' => $metCount,
                'totalGates' => $totalGates,
                'stageProgress' => $stageProgress,
                'daysInStage' => (int) $enrollment->stage_entered_at->diffInDays(now()),
            ];
        });

        $latestScorecard = PartnerScorecard::where('partner_id', $partner->id)
            ->orderByDesc('scored_at')
            ->first();

        $scorecardHistory = PartnerScorecard::where('partner_id', $partner->id)
            ->orderByDesc('scored_at')
            ->take(6)
            ->get();

        return compact('pathData', 'latestScorecard', 'scorecardHistory');
    }

    /**
     * Calendar tab: upcoming partner events + event stats.
     */
    private function calendarTabData(Partner $partner): array
    {
        $events = \App\Models\PartnerEvent::where('partner_id', $partner->id)
            ->orderBy('start_at', 'asc')
            ->get();

        $upcomingEvents = $events->where('start_at', '>=', now());
        $pastEvents = $events->where('start_at', '<', now());
        $eventsByType = $events->groupBy('type')->map->count();

        return compact('events', 'upcomingEvents', 'pastEvents', 'eventsByType');
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
        $this->authorizePartnerAccess($partner);

        return view('partner.blueprints', compact('partner'));
    }

    /**
     * Show the full summary view aggregating all partners for the authenticated user.
     */
    public function fullSummary()
    {
        // Get all partners assigned to the authenticated user (admins see all)
        $query = auth()->user()->role === 'admin'
            ? Partner::query()
            : Partner::where('user_id', auth()->id());

        $partners = $query
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
        $totalProjects = $partners->sum(fn ($p) => $p->projects->count());
        $totalOpportunities = $partners->sum(fn ($p) => $p->opportunities->count());
        $totalResources = $partners->sum(fn ($p) => $p->resources->count());
        $totalContacts = $partners->sum(fn ($p) => $p->contacts->count());
        $totalPipelineValue = $partners->sum(fn ($p) => $p->opportunities->sum('amount'));

        // Get stage distribution across all partners
        $allOpportunities = $partners->flatMap(fn ($p) => $p->opportunities);
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
        // Get all partners (admins see all, PSMs see their own)
        $query = auth()->user()->role === 'admin'
            ? Partner::query()
            : Partner::where('user_id', auth()->id());

        $partners = $query
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
        $totalProjects = $partners->sum(fn ($p) => $p->projects->count());
        $totalOpportunities = $partners->sum(fn ($p) => $p->opportunities->count());
        $totalResources = $partners->sum(fn ($p) => $p->resources->count());
        $totalContacts = $partners->sum(fn ($p) => $p->contacts->count());
        $totalPipelineValue = $partners->sum(fn ($p) => $p->opportunities->sum('amount'));

        // Get all opportunities for the feed
        $allOpportunities = $partners->flatMap(fn ($p) => $p->opportunities);

        // Get partners sorted by pipeline value for quota cards
        $topPartners = $partners->sortByDesc(function ($partner) {
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
            'user_id' => $request->user_id,
        ]);

        app(ActivationTracker::class)->track(auth()->user(), 'partner_management_user', StepType::FeatureUsage);

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
            ->map(fn ($items) => $items->pluck('region')->unique()->values());

        // Build PSM → partners mapping for dependent filters
        $psmPartnerMap = Partner::whereNotNull('user_id')
            ->select('id', 'name', 'user_id')
            ->orderBy('name')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($items) => $items->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])->values());

        return view('analysis.index', compact('partners', 'psms', 'geographies', 'regions', 'geoRegionMap', 'psmPartnerMap'));
    }

    /**
     * Show the guided analysis wizard.
     */
    public function guidedAnalysis()
    {
        $query = auth()->user()->role === 'admin'
            ? Partner::query()
            : Partner::where('user_id', auth()->id());

        $partners = $query
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
            ->map(fn ($items) => $items->pluck('region')->unique()->values());

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
        $query = auth()->user()->role === 'admin'
            ? Partner::query()
            : Partner::where('user_id', auth()->id());

        $partners = $query
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

    /**
     * Launch Programs tab: enrolled programs + available programs for nomination.
     */
    private function launchProgramsTabData(Partner $partner): array
    {
        $enrolledPrograms = LaunchProgramPartner::where('partner_id', $partner->id)
            ->with([
                'program.product',
                'program.milestones',
                'participants.contact',
                'participants.milestoneCompletions',
            ])
            ->get();

        $enrolledProgramIds = $enrolledPrograms->pluck('launch_program_id');

        $availablePrograms = LaunchProgram::where('status', 'accepting_nominations')
            ->where('partner_strategy', 'include')
            ->whereNotIn('id', $enrolledProgramIds)
            ->with('product')
            ->get();

        $contacts = $partner->contacts()->orderBy('first_name')->get();

        return compact('enrolledPrograms', 'availablePrograms', 'contacts');
    }

    private function workspacesTabData(Partner $partner): array
    {
        $workspaces = $partner->anaplanWorkspaces()
            ->with(['models.modules', 'models.lists'])
            ->orderBy('name')
            ->get();

        $totalModels = $workspaces->flatMap(fn ($ws) => $ws->models)->count();
        $activeModels = $workspaces->flatMap(fn ($ws) => $ws->models)->where('active_state', '!=', 'ARCHIVED')->count();
        $totalMemory = $workspaces->flatMap(fn ($ws) => $ws->models)->sum('memory_usage');
        $lastSync = $workspaces->max('last_synced_at');
        $isConfigured = app(\App\Services\AnaplanApiService::class)->isConfigured();
        $versionConfigs = \App\Models\AnaplanVersionConfig::all()->keyBy('product_area');

        $now = now();
        $allActiveModels = $workspaces->flatMap(fn ($ws) => $ws->models)->where('active_state', '!=', 'ARCHIVED');
        $modelActivity = [
            'under_1_month' => $allActiveModels->filter(fn ($m) => $m->last_modified_in_anaplan_at && $m->last_modified_in_anaplan_at->gte($now->copy()->subMonth()))->count(),
            '1_to_6_months' => $allActiveModels->filter(fn ($m) => $m->last_modified_in_anaplan_at && $m->last_modified_in_anaplan_at->lt($now->copy()->subMonth()) && $m->last_modified_in_anaplan_at->gte($now->copy()->subMonths(6)))->count(),
            '6_to_12_months' => $allActiveModels->filter(fn ($m) => $m->last_modified_in_anaplan_at && $m->last_modified_in_anaplan_at->lt($now->copy()->subMonths(6)) && $m->last_modified_in_anaplan_at->gte($now->copy()->subYear()))->count(),
            'over_12_months' => $allActiveModels->filter(fn ($m) => $m->last_modified_in_anaplan_at && $m->last_modified_in_anaplan_at->lt($now->copy()->subYear()))->count(),
            'never' => $allActiveModels->filter(fn ($m) => ! $m->last_modified_in_anaplan_at)->count(),
        ];

        return compact('workspaces', 'totalModels', 'activeModels', 'totalMemory', 'lastSync', 'isConfigured', 'versionConfigs', 'modelActivity');
    }

    private function portalUsersTabData(Partner $partner): array
    {
        $contacts = $partner->contacts()
            ->where('contact_type', '!=', 'influencer')
            ->orderByDesc('portal_access')
            ->orderBy('first_name')
            ->get();

        return compact('contacts');
    }

    /**
     * Nominate a partner for a launch program.
     */
    public function nominateForProgram(Partner $partner, LaunchProgram $launchProgram): \Illuminate\Http\RedirectResponse
    {
        $this->authorizePartnerAccess($partner);

        $redirectUrl = route('partners.show', $partner).'#launch-programs';

        if (! $launchProgram->isAcceptingNominations()) {
            return redirect($redirectUrl)->with('error', 'This program is not currently accepting nominations.');
        }

        if (LaunchProgramPartner::where('launch_program_id', $launchProgram->id)->where('partner_id', $partner->id)->exists()) {
            return redirect($redirectUrl)->with('error', 'This partner is already enrolled in this program.');
        }

        LaunchProgramPartner::create([
            'launch_program_id' => $launchProgram->id,
            'partner_id' => $partner->id,
            'status' => 'nominated',
            'nominated_by' => auth()->id(),
        ]);

        return redirect($redirectUrl)->with('success', "Partner nominated for {$launchProgram->name}.");
    }

    /**
     * Add a participant to a launch program partner enrollment.
     */
    public function addParticipant(Request $request, LaunchProgramPartner $programPartner): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'contact_id' => ['required', 'exists:contacts,id'],
            'role' => ['required', 'string', 'in:'.implode(',', array_keys(LaunchProgramParticipant::ROLES))],
        ]);

        $programPartner->participants()->create([
            'contact_id' => $validated['contact_id'],
            'role' => $validated['role'],
            'assigned_by' => auth()->id(),
        ]);

        $partnerId = $programPartner->partner_id;

        return redirect(route('partners.show', $partnerId).'#launch-programs')
            ->with('success', 'Participant added.');
    }

    /**
     * Remove a participant from a launch program.
     */
    public function removeParticipant(LaunchProgramParticipant $participant): \Illuminate\Http\RedirectResponse
    {
        $partnerId = $participant->programPartner->partner_id;
        $participant->delete();

        return redirect(route('partners.show', $partnerId).'#launch-programs')
            ->with('success', 'Participant removed.');
    }
}
