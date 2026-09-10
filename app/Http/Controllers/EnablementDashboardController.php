<?php

namespace App\Http\Controllers;

use App\Models\Blueprint;
use App\Models\BlueprintEnrollment;
use App\Models\EnablementEvent;
use App\Models\EnablementRegistration;
use App\Models\EnablementSession;
use App\Models\Lab;
use App\Models\PartnerBlueprintAssignment;
use App\Support\DatabasePortability;
use Illuminate\Support\Facades\DB;

class EnablementDashboardController extends Controller
{
    public function index()
    {
        // ── KPI Cards ──
        $totalBlueprints = Blueprint::count();
        $publishedBlueprints = Blueprint::where('status', 'published')->count();
        $totalAssignments = PartnerBlueprintAssignment::count();
        $totalEnrollments = BlueprintEnrollment::count();
        $totalEvents = EnablementEvent::count();
        $totalSessions = EnablementSession::count();
        $totalRegistrations = EnablementRegistration::count();
        $totalLabs = Lab::count();

        // Completion — load enrollments once and reuse for both the overall
        // average and the per-category breakdown (no per-category re-query).
        $enrollmentsWithProgress = BlueprintEnrollment::with(['progress', 'assignment.blueprint'])->get();
        $completions = $enrollmentsWithProgress->map(fn ($e) => [
            'category' => $e->assignment?->blueprint?->category,
            'pct' => $e->completionPercentage(),
        ]);
        $avgCompletion = $completions->count() > 0 ? round($completions->avg('pct'), 1) : 0;

        // ── Blueprint Status Breakdown (donut) ──
        $blueprintByStatus = Blueprint::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── Blueprint Completion by Category (bar) ──
        $completionByCategory = [];
        foreach (array_keys(Blueprint::CATEGORIES) as $cat) {
            $vals = $completions->where('category', $cat);
            $completionByCategory[$cat] = $vals->count() > 0 ? round($vals->avg('pct'), 1) : 0;
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

        // ── Sessions by Month (area chart) ──
        $sessionsByMonth = EnablementSession::select(
            DB::raw(DatabasePortability::dateFormat('start_at', '%Y-%m').' as month'),
            DB::raw('count(*) as total')
        )
            ->whereNotNull('start_at')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // ── Registrations by Status (donut) ──
        $registrationsByStatus = EnablementRegistration::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── Events by Type (donut) ──
        $eventsByType = EnablementEvent::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        // ── Recent Assignments Table ──
        $recentAssignments = PartnerBlueprintAssignment::with(['partner:id,name', 'blueprint:id,name,category', 'assignedBy:id,name'])
            ->latest()
            ->limit(20)
            ->get();

        // ── Upcoming Sessions Table ──
        $upcomingSessions = EnablementSession::with('event:id,title,type')
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->limit(20)
            ->get();

        return view('admin.enablement-dashboard', compact(
            'totalBlueprints',
            'publishedBlueprints',
            'totalAssignments',
            'totalEnrollments',
            'totalEvents',
            'totalSessions',
            'totalRegistrations',
            'totalLabs',
            'avgCompletion',
            'blueprintByStatus',
            'completionByCategory',
            'topPartners',
            'sessionsByMonth',
            'registrationsByStatus',
            'eventsByType',
            'recentAssignments',
            'upcomingSessions',
        ));
    }
}
