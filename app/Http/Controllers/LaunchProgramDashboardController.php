<?php

namespace App\Http\Controllers;

use App\Models\LaunchProgram;
use App\Models\LaunchProgramDateChange;
use App\Models\LaunchProgramFeedback;
use App\Models\LaunchProgramMilestone;
use App\Models\LaunchProgramMilestoneCompletion;
use App\Models\LaunchProgramPartner;
use App\Models\Product;
use App\Support\DatabasePortability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaunchProgramDashboardController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        // Filters
        $filterProduct = $request->query('product_id');
        $filterStatus = $request->query('status');
        $products = Product::orderBy('name')->get();

        $baseQuery = LaunchProgram::query()
            ->when($filterProduct, fn ($q) => $q->where('product_id', $filterProduct))
            ->when($filterStatus, fn ($q) => $q->where('status', $filterStatus));

        // KPI Cards
        $totalPrograms = (clone $baseQuery)->count();
        $activePrograms = (clone $baseQuery)->where('status', 'active')->count();
        $enrolledPartners = LaunchProgramPartner::whereNotIn('status', ['withdrawn'])
            ->when($filterProduct || $filterStatus, function ($q) use ($baseQuery) {
                $q->whereIn('launch_program_id', (clone $baseQuery)->select('id'));
            })
            ->count();
        $totalFeedback = LaunchProgramFeedback::query()
            ->when($filterProduct || $filterStatus, function ($q) use ($baseQuery) {
                $q->whereHas('programPartner', fn ($q2) => $q2->whereIn('launch_program_id', (clone $baseQuery)->select('id')));
            })
            ->count();

        $activeWithPartners = (clone $baseQuery)->where('status', 'active')
            ->with('programPartners.participants.milestoneCompletions', 'milestones')
            ->get();
        $avgCompletion = $activeWithPartners->isEmpty()
            ? 0
            : round($activeWithPartners->avg(fn ($p) => $p->completionPercentage()), 1);

        // Programs at Risk: active programs with incomplete milestones, sorted by urgency
        $programsAtRisk = (clone $baseQuery)->where('status', 'active')
            ->whereNotNull('target_ga_date')
            ->has('milestones')
            ->with('programPartners.participants.milestoneCompletions', 'milestones', 'product', 'owner')
            ->get()
            ->map(function ($p) {
                $p->computed_completion = $p->completionPercentage();
                $p->days_to_ga = (int) now()->diffInDays($p->target_ga_date, false);

                // Build per-milestone completion status across all partners
                $allCompletedMilestoneIds = $p->programPartners
                    ->flatMap(fn ($pp) => $pp->participants->flatMap(fn ($pt) => $pt->milestoneCompletions->pluck('launch_program_milestone_id')))
                    ->unique();
                $p->incomplete_milestones = $p->milestones->reject(fn ($ms) => $allCompletedMilestoneIds->contains($ms->id))->values();
                $p->completed_milestone_count = $p->milestones->count() - $p->incomplete_milestones->count();

                return $p;
            })
            ->filter(fn ($p) => $p->computed_completion < 100)
            ->sortBy('target_ga_date')
            ->values();

        // Charts: Programs by Status (donut)
        $programsByStatus = (clone $baseQuery)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Charts: Partner Nomination Funnel (ordered stages)
        $funnelOrder = ['nominated', 'accepted', 'active', 'completed', 'withdrawn'];
        $partnersByStatus = LaunchProgramPartner::query()
            ->when($filterProduct || $filterStatus, function ($q) use ($baseQuery) {
                $q->whereIn('launch_program_id', (clone $baseQuery)->select('id'));
            })
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Charts: Milestone Types (donut)
        $milestonesByType = LaunchProgramMilestone::query()
            ->when($filterProduct || $filterStatus, function ($q) use ($baseQuery) {
                $q->whereIn('launch_program_id', (clone $baseQuery)->select('id'));
            })
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        // Timeline rangeBar data with GA slippage history
        $gaChanges = LaunchProgramDateChange::where('field', 'target_ga_date')
            ->orderBy('created_at')
            ->get()
            ->groupBy('launch_program_id');

        $timelinePrograms = (clone $baseQuery)->whereNotNull('starts_at')
            ->whereNotNull('ends_at')
            ->with('product')
            ->withCount('programPartners')
            ->orderBy('starts_at')
            ->get()
            ->map(function ($p) use ($gaChanges) {
                $history = $gaChanges->get($p->id, collect());
                $previousGaDates = $history->pluck('old_date')
                    ->map(fn ($d) => $d->toISOString())
                    ->values()
                    ->all();

                return [
                    'name' => $p->name,
                    'start' => $p->starts_at->toISOString(),
                    'end' => $p->ends_at->toISOString(),
                    'ga' => $p->target_ga_date?->toISOString(),
                    'status' => $p->status,
                    'partnerStrategy' => $p->partner_strategy,
                    'partnerCount' => $p->program_partners_count,
                    'previousGaDates' => $previousGaDates,
                ];
            });

        // Owner Workload: active/accepting programs per owner
        $ownerWorkload = (clone $baseQuery)->whereIn('status', ['active', 'accepting_nominations'])
            ->whereNotNull('owner_id')
            ->with('owner')
            ->get()
            ->groupBy('owner_id')
            ->map(fn ($programs) => [
                'name' => $programs->first()->owner->name ?? 'Unknown',
                'count' => $programs->count(),
                'programs' => $programs->pluck('name')->values()->all(),
            ])
            ->sortByDesc('count')
            ->values();

        // Milestone Completion Trend (by month)
        $milestoneCompletionTrend = LaunchProgramMilestoneCompletion::query()
            ->when($filterProduct || $filterStatus, function ($q) use ($baseQuery) {
                $q->whereHas('participant.programPartner', fn ($q2) => $q2->whereIn('launch_program_id', (clone $baseQuery)->select('id')));
            })
            ->select(
                DB::raw(DatabasePortability::dateFormat('completed_at', '%Y-%m').' as month'),
                DB::raw('count(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Recent feedback (10)
        $recentFeedback = LaunchProgramFeedback::with(['contact', 'programPartner.partner', 'programPartner.program'])
            ->latest()
            ->limit(10)
            ->get();

        // Recent date changes (10)
        $recentDateChanges = LaunchProgramDateChange::with(['launchProgram', 'changedBy'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.launch-programs.dashboard', compact(
            'filterProduct',
            'filterStatus',
            'products',
            'totalPrograms',
            'activePrograms',
            'enrolledPartners',
            'avgCompletion',
            'totalFeedback',
            'programsAtRisk',
            'programsByStatus',
            'partnersByStatus',
            'funnelOrder',
            'milestonesByType',
            'timelinePrograms',
            'ownerWorkload',
            'milestoneCompletionTrend',
            'recentFeedback',
            'recentDateChanges',
        ));
    }
}
