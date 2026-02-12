<?php
namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerGoal;
use App\Models\PartnerGoalDimension;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoalsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $partnersQuery = $user->role === 'admin'
            ? Partner::query()
            : Partner::where('user_id', $user->id);

        $partners = $partnersQuery->select('id', 'name')->orderBy('name')->get();
        $fy = $this->currentFiscalYear();

        $goals = PartnerGoal::whereIn('partner_id', $partners->pluck('id'))
            ->where('fiscal_year', $fy)
            ->with('dimensions')
            ->get()
            ->keyBy('partner_id');

        // Revenue planning data
        $pipelineData = DB::table('opportunities')
            ->whereIn('partner_id', $partners->pluck('id'))
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->selectRaw('partner_id, SUM(amount) as pipeline, COUNT(*) as opp_count')
            ->groupBy('partner_id')
            ->pluck('pipeline', 'partner_id');

        $wonData = DB::table('opportunities')
            ->whereIn('partner_id', $partners->pluck('id'))
            ->where('stage', 'Closed Won')
            ->where('fiscal_year', $fy)
            ->selectRaw('partner_id, SUM(amount) as won, COUNT(*) as won_count, AVG(amount) as asp')
            ->groupBy('partner_id')
            ->get()
            ->keyBy('partner_id');

        // Historical ASP and win rate for revenue planning calculator
        $historicalAsp = DB::table('opportunities')
            ->whereIn('partner_id', $partners->pluck('id'))
            ->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subMonths(24))
            ->avg('amount') ?? 200000;

        $totalWon = DB::table('opportunities')
            ->whereIn('partner_id', $partners->pluck('id'))
            ->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subMonths(12))
            ->count();
        $totalLost = DB::table('opportunities')
            ->whereIn('partner_id', $partners->pluck('id'))
            ->where('stage', 'Closed Lost')
            ->where('close_date', '>=', now()->subMonths(12))
            ->count();
        $historicalWinRate = ($totalWon + $totalLost) > 0 ? $totalWon / ($totalWon + $totalLost) : 0.25;

        return view('plan.goals.index', compact(
            'partners', 'goals', 'fy', 'pipelineData', 'wonData',
            'historicalAsp', 'historicalWinRate'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'fiscal_year' => 'required|string',
            'sourced_nnacv_target' => 'required|numeric|min:0',
            'influenced_nnacv_target' => 'required|numeric|min:0',
        ]);

        $goal = PartnerGoal::updateOrCreate(
            ['partner_id' => $request->partner_id, 'fiscal_year' => $request->fiscal_year],
            ['sourced_nnacv_target' => $request->sourced_nnacv_target, 'influenced_nnacv_target' => $request->influenced_nnacv_target]
        );

        // Update dimensions if provided
        if ($request->has('dimensions')) {
            foreach ($request->dimensions as $dim) {
                PartnerGoalDimension::updateOrCreate(
                    ['partner_goal_id' => $goal->id, 'dimension' => $dim['dimension']],
                    ['target_value' => $dim['target_value'], 'unit' => $dim['unit'] ?? 'count',
                     'q1_target' => $dim['q1_target'] ?? null, 'q2_target' => $dim['q2_target'] ?? null,
                     'q3_target' => $dim['q3_target'] ?? null, 'q4_target' => $dim['q4_target'] ?? null]
                );
            }
        }

        return redirect()->back()->with('success', 'Goals updated successfully.');
    }

    protected function currentFiscalYear(): string
    {
        $fyStart = (int) AppSetting::get('fiscal_year_start_month', 2);
        $now = now();
        $year = $now->month >= $fyStart ? $now->year : $now->year - 1;
        return 'FY' . substr($year + 1, 2);
    }
}
