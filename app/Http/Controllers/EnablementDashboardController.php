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
