<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\GoalTemplate;
use App\Models\Partner;
use App\Models\PartnerAssignedGoal;
use App\Models\PartnerGoal;
use App\Services\GoalComputationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoalsController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $user = auth()->user();
        $partnersQuery = $user->isAdmin()
            ? Partner::query()
            : Partner::where('user_id', $user->id);

        $partners = $partnersQuery->select('id', 'name')->orderBy('name')->get();
        $partnerIds = $partners->pluck('id');
        $fy = $request->query('fy', $this->currentFiscalYear());
        $fyInt = $this->fyToInt($fy); // partner_goals + opportunities store fiscal_year as an int

        // Legacy goals (sourced/influenced NNACV)
        $goals = PartnerGoal::whereIn('partner_id', $partnerIds)
            ->where('fiscal_year', $fyInt)
            ->with('dimensions')
            ->get()
            ->keyBy('partner_id');

        // New assigned goals
        $assignedGoals = PartnerAssignedGoal::whereIn('partner_id', $partnerIds)
            ->where('fiscal_year', $fy)
            ->with(['partner', 'goalTemplate', 'assignedByUser'])
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $goalsByCategory = $assignedGoals->groupBy('category');

        // Category summary stats
        $categorySummary = [];
        foreach (GoalTemplate::CATEGORIES as $catKey => $catLabel) {
            $catGoals = $goalsByCategory->get($catKey, collect());
            $totalTarget = $catGoals->sum('target_value');
            $totalActual = $catGoals->sum('actual_value');
            $attainment = $totalTarget > 0 ? round(($totalActual / $totalTarget) * 100, 1) : 0;
            $categorySummary[$catKey] = [
                'label' => $catLabel,
                'count' => $catGoals->count(),
                'target' => $totalTarget,
                'actual' => $totalActual,
                'attainment' => $attainment,
            ];
        }

        // Revenue planning data
        $pipelineData = DB::table('opportunities')
            ->whereIn('partner_id', $partnerIds)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->selectRaw('partner_id, SUM(amount) as pipeline, COUNT(*) as opp_count')
            ->groupBy('partner_id')
            ->pluck('pipeline', 'partner_id');

        $wonData = DB::table('opportunities')
            ->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')
            ->where('fiscal_year', $fyInt)
            ->selectRaw('partner_id, SUM(amount) as won, COUNT(*) as won_count, AVG(amount) as asp')
            ->groupBy('partner_id')
            ->get()
            ->keyBy('partner_id');

        $historicalAsp = DB::table('opportunities')
            ->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subMonths(24))
            ->avg('amount') ?? 200000;

        $totalWon = DB::table('opportunities')
            ->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subMonths(12))
            ->count();
        $totalLost = DB::table('opportunities')
            ->whereIn('partner_id', $partnerIds)
            ->where('stage', 'Closed Lost')
            ->where('close_date', '>=', now()->subMonths(12))
            ->count();
        $historicalWinRate = ($totalWon + $totalLost) > 0 ? $totalWon / ($totalWon + $totalLost) : 0.25;

        // Available templates for assigning new goals
        $availableTemplates = GoalTemplate::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        // Year-over-year comparison
        $prevFy = $this->previousFiscalYear($fy);
        $prevGoals = PartnerAssignedGoal::whereIn('partner_id', $partnerIds)
            ->where('fiscal_year', $prevFy)
            ->get();
        $prevByCategory = [];
        foreach (GoalTemplate::CATEGORIES as $catKey => $catLabel) {
            $catGoals = $prevGoals->where('category', $catKey);
            $prevByCategory[$catKey] = [
                'target' => $catGoals->sum('target_value'),
                'actual' => $catGoals->sum('actual_value'),
            ];
        }

        return view('plan.goals.index', compact(
            'partners', 'goals', 'fy', 'pipelineData', 'wonData',
            'historicalAsp', 'historicalWinRate', 'assignedGoals', 'goalsByCategory',
            'categorySummary', 'availableTemplates', 'prevFy', 'prevByCategory'
        ));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'goal_template_id' => 'nullable|exists:goal_templates,id',
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:'.implode(',', array_keys(GoalTemplate::CATEGORIES)),
            'metric_key' => 'required|string|in:'.implode(',', array_keys(GoalTemplate::METRIC_KEYS)),
            'unit' => 'required|string|in:'.implode(',', array_keys(GoalTemplate::UNITS)),
            'cadence' => 'required|string|in:'.implode(',', array_keys(GoalTemplate::CADENCES)),
            'fiscal_year' => 'required|string|max:10',
            'target_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['assigned_by'] = auth()->id();

        PartnerAssignedGoal::create($validated);

        return redirect()->back()->with('success', 'Goal assigned to partner.');
    }

    public function update(Request $request, PartnerAssignedGoal $goal): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'target_value' => 'required|numeric|min:0',
            'actual_value' => 'nullable|numeric|min:0',
            'q1_target' => 'nullable|numeric|min:0',
            'q2_target' => 'nullable|numeric|min:0',
            'q3_target' => 'nullable|numeric|min:0',
            'q4_target' => 'nullable|numeric|min:0',
            'q1_actual' => 'nullable|numeric|min:0',
            'q2_actual' => 'nullable|numeric|min:0',
            'q3_actual' => 'nullable|numeric|min:0',
            'q4_actual' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:'.implode(',', array_keys(PartnerAssignedGoal::STATUSES)),
            'notes' => 'nullable|string',
        ]);

        $goal->update($validated);

        return redirect()->back()->with('success', 'Goal updated.');
    }

    public function destroy(PartnerAssignedGoal $goal): \Illuminate\Http\RedirectResponse
    {
        $goal->delete();

        return redirect()->back()->with('success', 'Goal removed.');
    }

    public function refreshActuals(Request $request, GoalComputationService $service): \Illuminate\Http\RedirectResponse
    {
        $fy = $request->query('fy', $this->currentFiscalYear());
        $count = $service->computeAll($fy);

        return redirect()->back()->with('success', "Refreshed actuals for {$count} goal(s).");
    }

    protected function currentFiscalYear(): string
    {
        $fyStart = (int) AppSetting::get('fiscal_year_start_month', 2);
        $now = now();
        $year = $now->month >= $fyStart ? $now->year : $now->year - 1;

        return 'FY'.substr($year + 1, 2);
    }

    protected function previousFiscalYear(string $fy): string
    {
        $num = (int) substr($fy, 2);

        return 'FY'.str_pad($num - 1, 2, '0', STR_PAD_LEFT);
    }

    /** "FY27" → 2027, for tables whose fiscal_year column is an integer. */
    protected function fyToInt(string $fy): int
    {
        $n = (int) preg_replace('/\D/', '', $fy);

        return $n < 100 ? 2000 + $n : $n;
    }
}
