<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertEnablementGoalRequest;
use App\Models\EnablementGoal;
use App\Models\Partner;
use App\Models\PsmObjective;
use App\Models\SavedFilter;
use App\Models\WeeklyFocusItem;
use App\Services\EnablementGoalService;
use App\Services\RiskAnalyzerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StateOfBusinessController extends Controller
{
    public function __construct(
        private RiskAnalyzerService $riskService,
        private EnablementGoalService $goalService,
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $scope = $request->get('scope', $user->isAdmin() ? 'all' : 'mine');
        $selectedPartnerId = $request->get('partner');
        $selectedFilterId = $request->get('filter');

        // Build partner list for dropdown
        $allPartners = Partner::orderBy('name')->get(['id', 'name']);
        $myPartners = $user->isAdmin()
            ? $allPartners
            : Partner::where('user_id', $user->id)->orderBy('name')->get(['id', 'name']);

        $partnerIds = $this->resolvePartnerScope($user, $scope, $selectedPartnerId, $selectedFilterId, $myPartners);

        $selectedPartner = $selectedPartnerId
            ? $allPartners->firstWhere('id', (int) $selectedPartnerId)
            : null;

        $activeFilter = $selectedFilterId
            ? SavedFilter::where('id', $selectedFilterId)->where('user_id', $user->id)->first()
            : null;

        $snapshotDates = $this->riskService->snapshotDates();
        $latestSnapshot = $snapshotDates->last();
        $previousSnapshot = $snapshotDates->count() >= 2 ? $snapshotDates[$snapshotDates->count() - 2] : null;

        // PSMs for filter modal
        $psms = \App\Models\User::whereHas('role', function ($q) {
            $q->where('slug', 'psm');
        })->orderBy('name')->get(['id', 'name']);

        // Saved filters for this user
        $savedFilters = SavedFilter::where('user_id', $user->id)
            ->where('context', 'state-of-business')
            ->orderBy('name')
            ->get();

        // Filter options for the modal
        $filterOptions = $this->riskService->filterOptions();

        $data = [
            'scope' => $scope,
            'allPartners' => $allPartners,
            'myPartners' => $myPartners,
            'selectedPartnerId' => $selectedPartnerId,
            'selectedPartner' => $selectedPartner,
            'activeFilter' => $activeFilter,
            'latestSnapshot' => $latestSnapshot,
            'previousSnapshot' => $previousSnapshot,
            'snapshotDates' => $snapshotDates,
            'psms' => $psms,
            'savedFilters' => $savedFilters,
            'filterOptions' => $filterOptions,
        ];

        if (! $latestSnapshot) {
            return view('state-of-business.index', $data);
        }

        // Historical Trends (all snapshots, all fiscal years)
        $data['trends'] = $this->riskService->historicalTrends($partnerIds);

        // Scope remaining metrics to current fiscal year (Anaplan FY starts in Feb)
        $this->riskService->forFiscalYear(2027);

        // WoW Changes
        $wow = $this->riskService->weekOverWeekDeltas($partnerIds);
        $data['current'] = $wow['current'];
        $data['previous'] = $wow['previous'];
        $data['deltas'] = $wow['deltas'];

        // Portfolio Metrics (from current snapshot)
        $data['portfolio'] = $wow['current'];

        // Opportunity & Resource Movement
        if ($previousSnapshot) {
            $data['oppMovement'] = $this->riskService->opportunityMovement($previousSnapshot, $latestSnapshot, $partnerIds);
            $data['resMovement'] = $this->riskService->resourceMovement($previousSnapshot, $latestSnapshot, $partnerIds);
            $data['reassignments'] = $this->riskService->partnerReassignments($previousSnapshot, $latestSnapshot, $partnerIds);
            $data['lobChanges'] = $this->riskService->lobChanges($previousSnapshot, $latestSnapshot, $partnerIds);
        }

        // LOB breakdown for current snapshot
        $data['lobBreakdown'] = $this->riskService->dimensionBreakdown($latestSnapshot, 'lob', $partnerIds);

        // Resource heatmap (partner x program)
        $data['resourceHeatmap'] = $this->riskService->resourceHeatmap($partnerIds, $latestSnapshot);

        // Stage aging for Sales Velocity
        $data['aging'] = $this->riskService->stageAging($latestSnapshot, $partnerIds);

        // Delivery risk by LOB
        $data['deliveryRisk'] = $this->riskService->deliveryRiskByLob($latestSnapshot, $partnerIds);

        // LOB-level recommendations
        $data['lobRecs'] = $this->riskService->lobRecommendations($latestSnapshot, $partnerIds);

        // Partner Summary with deltas
        $data['partnerSummary'] = $this->riskService->partnerSummaryWithDeltas($latestSnapshot, $previousSnapshot, $partnerIds);

        // Priorities for scoped partners — only active objectives
        $objectivesQuery = PsmObjective::with('partner')
            ->where('user_id', $user->id)
            ->where('status', 'active');
        if ($partnerIds) {
            $objectivesQuery->whereIn('partner_id', $partnerIds);
        }
        $data['sobObjectives'] = $objectivesQuery
            ->orderByRaw('CASE WHEN priority > 0 THEN 0 ELSE 1 END')
            ->orderBy('priority')
            ->orderBy('sort_order')
            ->orderBy('target_date')
            ->get();

        $actionItemsQuery = WeeklyFocusItem::with(['partner', 'weeklyFocus'])
            ->whereHas('weeklyFocus', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('week_start', '>=', now()->subWeek()->startOfWeek());
            });
        if ($partnerIds) {
            $actionItemsQuery->whereIn('partner_id', $partnerIds);
        }
        $data['sobActionItems'] = $actionItemsQuery->orderBy('sort_order')->get();

        // Manager view: direct reports' objectives
        $data['teamObjectives'] = collect();
        if ($user->directReports()->exists()) {
            $reportIds = $user->directReports()->pluck('id');
            $teamQuery = PsmObjective::with(['partner', 'user'])
                ->whereIn('user_id', $reportIds);
            if ($partnerIds) {
                $teamQuery->whereIn('partner_id', $partnerIds);
            }
            $data['teamObjectives'] = $teamQuery
                ->orderByRaw("FIELD(status, 'active', 'completed', 'missed', 'cancelled')")
                ->orderBy('target_date')
                ->get();
        }

        // Partner enablement calendar (single-partner view only)
        if ($selectedPartnerId) {
            $data['calendarPartner'] = Partner::with('contacts:id,partner_id,first_name,last_name,email')
                ->find((int) $selectedPartnerId);
        }

        // Enablement Goals
        $goalPartnerIds = $partnerIds ?? $myPartners->pluck('id');
        $data['enablementGoals'] = $this->goalService->goalsForPartners(
            $goalPartnerIds,
            $user->id,
            $latestSnapshot,
        );
        $data['enablementGoalGroups'] = EnablementGoal::groups();
        $data['goalPartners'] = $allPartners->whereIn('id', $goalPartnerIds)->keyBy('id');
        $data['enablementTrends'] = $this->goalService->historicalActuals($goalPartnerIds);
        $data['enablementWaterfall'] = $this->goalService->waterfallBreakdown($goalPartnerIds, $latestSnapshot);

        return view('state-of-business.index', $data);
    }

    public function storeFilter(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'criteria' => 'required|array',
        ]);

        $filter = SavedFilter::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'context' => 'state-of-business',
            'criteria' => $validated['criteria'],
        ]);

        return response()->json(['id' => $filter->id, 'name' => $filter->name]);
    }

    public function destroyFilter(SavedFilter $savedFilter)
    {
        if ($savedFilter->user_id !== auth()->id()) {
            abort(403);
        }

        $savedFilter->delete();

        return response()->json(['ok' => true]);
    }

    public function upsertGoal(UpsertEnablementGoalRequest $request): JsonResponse
    {
        $goal = EnablementGoal::updateOrCreate(
            [
                'partner_id' => $request->validated('partner_id'),
                'user_id' => auth()->id(),
                'category' => $request->validated('category'),
            ],
            [
                'target' => $request->validated('target'),
                'baseline' => $request->validated('baseline', 0),
            ],
        );

        $actual = $this->goalService->actuals($goal->partner_id)[$goal->category] ?? 0;

        return response()->json([
            'goal_id' => $goal->id,
            'target' => $goal->target,
            'baseline' => $goal->baseline,
            'actual' => $actual,
            'variance' => $goal->target > 0 ? $actual - $goal->target : 0,
        ]);
    }

    public function destroyGoal(EnablementGoal $enablementGoal): JsonResponse
    {
        if ($enablementGoal->user_id !== auth()->id()) {
            abort(403);
        }

        $enablementGoal->delete();

        return response()->json(['ok' => true]);
    }

    private function resolvePartnerScope($user, string $scope, ?string $selectedPartnerId, ?string $selectedFilterId, Collection $myPartners): ?Collection
    {
        // Single partner selected
        if ($selectedPartnerId) {
            return collect([(int) $selectedPartnerId]);
        }

        // Saved filter with partner criteria
        if ($selectedFilterId) {
            $filter = SavedFilter::where('id', $selectedFilterId)
                ->where('user_id', $user->id)
                ->first();

            if ($filter && ! empty($filter->criteria['partner'])) {
                return collect($filter->criteria['partner'])->map(fn ($id) => (int) $id);
            }
        }

        // Scope toggle
        if ($scope === 'all' && $user->isAdmin()) {
            return null;
        }

        if ($scope === 'mine' || ! $user->isAdmin()) {
            return $myPartners->pluck('id');
        }

        return null;
    }
}
