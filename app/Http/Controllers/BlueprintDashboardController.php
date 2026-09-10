<?php

namespace App\Http\Controllers;

use App\Models\Blueprint;
use App\Models\BlueprintEnrollment;
use App\Models\PartnerBlueprintAssignment;
use Illuminate\Support\Facades\DB;

class BlueprintDashboardController extends Controller
{
    public function index()
    {
        // ── KPI Cards (simple counts) ──
        $totalBlueprints = Blueprint::count();
        $publishedBlueprints = Blueprint::where('status', 'published')->count();
        $totalAssignments = PartnerBlueprintAssignment::count();
        $totalEnrollments = BlueprintEnrollment::count();

        // ── Pre-compute completion data via aggregate queries ──
        [$avgCompletion, $completionByCategory] = $this->computeCompletionMetrics();

        // ── Blueprint Status Breakdown (donut) ──
        $blueprintByStatus = Blueprint::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── Top 10 Partners by Blueprint Assignments (horizontal bar) ──
        $topPartners = DB::table('partner_blueprint_assignments')
            ->join('partners', 'partners.id', '=', 'partner_blueprint_assignments.partner_id')
            ->select('partners.name', DB::raw('count(*) as count'))
            ->groupBy('partners.id', 'partners.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // ── Recent Assignments Table ──
        $recentAssignments = PartnerBlueprintAssignment::with(['partner:id,name', 'blueprint:id,name,category', 'assignedBy:id,name'])
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.blueprint-dashboard', compact(
            'totalBlueprints',
            'publishedBlueprints',
            'totalAssignments',
            'totalEnrollments',
            'avgCompletion',
            'blueprintByStatus',
            'completionByCategory',
            'topPartners',
            'recentAssignments',
        ));
    }

    /**
     * Compute avg completion and per-category completion using aggregate queries
     * instead of loading the full enrollment/progress/content-item object graph.
     *
     * @return array{float, array<string, float>}
     */
    private function computeCompletionMetrics(): array
    {
        // Completed items per enrollment (single query)
        $completedCounts = DB::table('blueprint_progress')
            ->select('blueprint_enrollment_id', DB::raw('SUM(CASE WHEN completed THEN 1 ELSE 0 END) as completed_count'))
            ->where('completed', true)
            ->groupBy('blueprint_enrollment_id')
            ->pluck('completed_count', 'blueprint_enrollment_id');

        // Applicable items per (blueprint_id, role) — single query
        $applicableItems = DB::table('blueprint_content_items as ci')
            ->join('blueprint_outcomes as bo', 'ci.blueprint_outcome_id', '=', 'bo.id')
            ->select(
                'bo.blueprint_id',
                DB::raw("SUM(CASE WHEN ci.gtm_applicable != 'na' THEN 1 ELSE 0 END) as gtm_count"),
                DB::raw("SUM(CASE WHEN ci.pre_sales_applicable != 'na' THEN 1 ELSE 0 END) as pre_sales_count"),
                DB::raw("SUM(CASE WHEN ci.delivery_applicable != 'na' THEN 1 ELSE 0 END) as delivery_count"),
            )
            ->groupBy('bo.blueprint_id')
            ->get()
            ->keyBy('blueprint_id');

        // All enrollments with assignment role + blueprint category (single query)
        $enrollments = DB::table('blueprint_enrollments as e')
            ->join('partner_blueprint_assignments as a', 'e.partner_blueprint_assignment_id', '=', 'a.id')
            ->join('blueprints as b', 'a.blueprint_id', '=', 'b.id')
            ->select('e.id', 'a.blueprint_id', 'a.role', 'b.category')
            ->get();

        // Calculate percentages in PHP from pre-aggregated data
        $allPcts = [];
        $catPcts = [];

        foreach ($enrollments as $e) {
            $applicable = $applicableItems[$e->blueprint_id] ?? null;
            if (! $applicable) {
                continue;
            }

            $totalItems = match ($e->role) {
                'gtm' => $applicable->gtm_count,
                'pre_sales' => $applicable->pre_sales_count,
                'delivery' => $applicable->delivery_count,
                default => 0,
            };

            if ($totalItems == 0) {
                continue;
            }

            $completed = $completedCounts[$e->id] ?? 0;
            $pct = round($completed / $totalItems * 100, 1);
            $allPcts[] = $pct;
            $catPcts[$e->category][] = $pct;
        }

        $avgCompletion = count($allPcts) > 0
            ? round(array_sum($allPcts) / count($allPcts), 1)
            : 0;

        $completionByCategory = [];
        foreach (array_keys(Blueprint::CATEGORIES) as $cat) {
            $pcts = $catPcts[$cat] ?? [];
            $completionByCategory[$cat] = count($pcts) > 0
                ? round(array_sum($pcts) / count($pcts), 1)
                : 0;
        }

        return [$avgCompletion, $completionByCategory];
    }
}
