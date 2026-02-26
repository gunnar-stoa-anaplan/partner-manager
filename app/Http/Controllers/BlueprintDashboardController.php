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
        // ── KPI Cards ──
        $totalBlueprints = Blueprint::count();
        $publishedBlueprints = Blueprint::where('status', 'published')->count();
        $totalAssignments = PartnerBlueprintAssignment::count();
        $totalEnrollments = BlueprintEnrollment::count();

        // Avg blueprint completion
        $avgCompletion = 0;
        $enrollmentsWithProgress = BlueprintEnrollment::with(['progress', 'assignment.blueprint.outcomes.contentItems'])->get();
        if ($enrollmentsWithProgress->count() > 0) {
            $avgCompletion = round($enrollmentsWithProgress->avg(fn ($e) => $e->completionPercentage()), 1);
        }

        // ── Blueprint Status Breakdown (donut) ──
        $blueprintByStatus = Blueprint::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── Blueprint Completion by Category (bar) ──
        $categories = array_keys(Blueprint::CATEGORIES);
        $completionByCategory = [];
        foreach ($categories as $cat) {
            $catAssignments = PartnerBlueprintAssignment::whereHas('blueprint', fn ($q) => $q->where('category', $cat))
                ->with(['enrollments.progress', 'blueprint.outcomes.contentItems'])
                ->get();

            $catEnrollments = $catAssignments->flatMap->enrollments;
            $completionByCategory[$cat] = $catEnrollments->count() > 0
                ? round($catEnrollments->avg(fn ($e) => $e->completionPercentage()), 1)
                : 0;
        }

        // ── Top 10 Partners by Blueprint Assignments (horizontal bar) ──
        $topPartners = PartnerBlueprintAssignment::select('partner_id', DB::raw('count(*) as total'))
            ->groupBy('partner_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('partner:id,name')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->partner->name ?? 'Unknown',
                'count' => $row->total,
            ]);

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
}
