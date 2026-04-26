<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use App\Services\JourneyDatabase;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    /**
     * Index: all journeys visible to the logged-in user.
     * Admins see all; PSMs see only assigned journeys.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $user = auth()->user();

        $journeys = Journey::query()
            ->visibleTo($user)
            ->orderBy('name')
            ->get()
            ->map(function (Journey $journey) {
                $db = new JourneyDatabase($journey);
                $journey->participant_count = $db->participants()->count();

                return $journey;
            });

        return view('journeys.index', compact('journeys'));
    }

    /**
     * Detail dashboard for a single journey.
     */
    public function show(Journey $journey): \Illuminate\View\View
    {
        $this->authorizeJourneyAccess($journey);

        $db = new JourneyDatabase($journey);

        $participants = $db->participants();
        $pendingActions = $db->pendingActions();
        $recentActivity = $db->agentLog(10);

        $totalParticipants = $participants->count();

        // Active = stage < 5 OR just anyone with data
        $activeCount = $participants->filter(fn ($p) => isset($p->current_stage) && $p->current_stage < 5)->count();

        // Stalled = >14 days in stage and stage < 5
        $stalledCount = $participants->filter(function ($p) {
            if (! isset($p->current_stage) || $p->current_stage >= 5) {
                return false;
            }

            $lastActive = isset($p->last_active_at) ? Carbon::parse($p->last_active_at) : null;

            return $lastActive && $lastActive->diffInDays(now()) > 14;
        })->count();

        $pendingCount = $pendingActions->count();

        // Stage pipeline: participants per stage
        $stages = collect(JourneyDatabase::STAGES)->map(function ($stage, $stageNum) use ($participants) {
            $count = $participants->filter(fn ($p) => isset($p->current_stage) && (int) $p->current_stage === $stageNum)->count();

            return array_merge($stage, ['num' => $stageNum, 'count' => $count]);
        });

        return view('journeys.show', compact(
            'journey',
            'totalParticipants',
            'activeCount',
            'stalledCount',
            'pendingCount',
            'stages',
            'recentActivity',
        ));
    }

    /**
     * Full participant list for a journey.
     */
    public function participants(Journey $journey): \Illuminate\View\View
    {
        $this->authorizeJourneyAccess($journey);

        $db = new JourneyDatabase($journey);
        $participants = $db->participants()->map(function ($p) {
            $stageNum = (int) ($p->current_stage ?? 0);
            $stageDef = JourneyDatabase::STAGES[$stageNum] ?? null;
            $p->stage_name = $stageDef ? $stageDef['name'] : 'Unknown';
            $p->stage_tasks = $stageDef ? $stageDef['tasks'] : 0;

            // Days in current stage
            $lastActive = isset($p->last_active_at) ? Carbon::parse($p->last_active_at) : null;
            $p->days_in_stage = $lastActive ? $lastActive->diffInDays(now()) : null;
            $p->is_stalled = $stageNum < 5 && $p->days_in_stage !== null && $p->days_in_stage > 14;

            return $p;
        });

        return view('journeys.participants', compact('journey', 'participants'));
    }

    /**
     * Pending actions approval queue for a journey.
     */
    public function pending(Journey $journey): \Illuminate\View\View
    {
        $this->authorizeJourneyAccess($journey);

        $db = new JourneyDatabase($journey);
        $pendingActions = $db->pendingActions();

        return view('journeys.pending', compact('journey', 'pendingActions'));
    }

    /**
     * Agent activity log for a journey.
     */
    public function activity(Journey $journey): \Illuminate\View\View
    {
        $this->authorizeJourneyAccess($journey);

        $db = new JourneyDatabase($journey);

        // Paginate in-memory (SQLite, expected small dataset)
        $perPage = 25;
        $page = (int) request('page', 1);
        $allLog = $db->agentLog(500);
        $total = $allLog->count();
        $log = $allLog->forPage($page, $perPage);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            items: $log,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: ['path' => request()->url(), 'query' => request()->query()],
        );

        return view('journeys.activity', compact('journey', 'paginator'));
    }

    /**
     * Enforce that the current user can view this journey.
     * Admins can view all; PSMs only their assigned journeys.
     */
    private function authorizeJourneyAccess(Journey $journey): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        $assigned = $journey->assignments()->where('user_id', $user->id)->exists();

        if (! $assigned) {
            abort(403, 'You are not assigned to this journey.');
        }
    }
}
