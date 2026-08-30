<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionParticipant;
use App\Services\WorkshopLeaderboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkshopController extends Controller
{
    public function __construct(private WorkshopLeaderboardService $leaderboardService) {}

    public function dashboard(): View
    {
        $workshops = Workshop::withCount('sessions')->get();

        $sessions = WorkshopSession::with('workshop:id,name,slug')
            ->withCount(['activeParticipants'])
            ->get();

        $totalWorkshops = $workshops->count();
        $totalSessions = $sessions->count();
        $totalParticipants = WorkshopSessionParticipant::notRemoved()->count();
        $totalCompleted = WorkshopSessionParticipant::notRemoved()->completed()->count();
        $completionRate = $totalParticipants > 0 ? round(($totalCompleted / $totalParticipants) * 100) : 0;

        $upcomingSessions = $sessions->filter(fn (WorkshopSession $session) => $session->end_date->isFuture())
            ->sortBy('start_date')
            ->take(5);

        $workshopSummaries = $workshops->map(function (Workshop $workshop) {
            $participants = WorkshopSessionParticipant::notRemoved()
                ->whereHas('session', fn ($query) => $query->where('workshop_id', $workshop->id))
                ->get();

            $total = $participants->count();
            $completed = $participants->whereNotNull('completed_at')->count();

            return [
                'workshop' => $workshop,
                'sessions' => $workshop->sessions_count,
                'participants' => $total,
                'completed' => $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        });

        return view('workshops.dashboard', compact(
            'totalWorkshops', 'totalSessions', 'totalParticipants', 'totalCompleted',
            'completionRate', 'upcomingSessions', 'workshopSummaries',
        ));
    }

    public function index(): View
    {
        $workshops = Workshop::withCount('sessions')
            ->with('creator:id,name')
            ->orderByDesc('updated_at')
            ->get();

        return view('workshops.index', compact('workshops'));
    }

    public function create(): View
    {
        return view('workshops.create');
    }

    public function store(StoreWorkshopRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();

        $workshop = Workshop::create($validated);

        return redirect()->route('workshops.show', $workshop)
            ->with('success', 'Workshop created successfully.');
    }

    public function show(Workshop $workshop): View
    {
        $workshop->load(['sessions' => fn ($query) => $query->withCount('activeParticipants')->orderBy('start_date')]);

        return view('workshops.show', compact('workshop'));
    }

    public function edit(Workshop $workshop): View
    {
        return view('workshops.edit', compact('workshop'));
    }

    public function rollup(Workshop $workshop): View
    {
        $workshop->load([
            'sessions' => fn ($query) => $query->orderBy('start_date'),
            'sessions.participants' => fn ($query) => $query->notRemoved()->with('contact.partner:id,name'),
            'sessions.surveyResponses.contact:id,first_name,last_name,partner_id',
            'sessions.surveyResponses.contact.partner:id,name',
            'sessions.threads',
        ]);

        $allThreads = $workshop->sessions->flatMap(fn (WorkshopSession $session) => $session->threads);
        $answeredThreads = $allThreads->whereNotNull('answered_at');
        $avgResponseMinutes = $answeredThreads->isNotEmpty()
            ? round($answeredThreads->avg(fn ($thread) => $thread->created_at->diffInMinutes($thread->answered_at)))
            : null;

        $discussionMetrics = [
            'total_threads' => $allThreads->count(),
            'open_threads' => $allThreads->where('status', 'open')->count(),
            'answered_threads' => $answeredThreads->count(),
            'avg_response_minutes' => $avgResponseMinutes,
        ];

        $sessionStats = $workshop->sessions->map(function (WorkshopSession $session) {
            $total = $session->participants->count();
            $completed = $session->participants->whereNotNull('completed_at')->count();

            return [
                'session' => $session,
                'total' => $total,
                'completed' => $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        });

        $partnerStats = $workshop->sessions
            ->flatMap(fn (WorkshopSession $session) => $session->participants)
            ->groupBy(fn (WorkshopSessionParticipant $participant) => $participant->contact->partner?->name ?? 'Unknown')
            ->map(function ($participants, $partnerName) {
                $total = $participants->count();
                $completed = $participants->whereNotNull('completed_at')->count();

                return [
                    'partner' => $partnerName,
                    'total' => $total,
                    'completed' => $completed,
                    'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $totalParticipants = $sessionStats->sum('total');
        $totalCompleted = $sessionStats->sum('completed');

        $checkpoints = $this->leaderboardService->checkpointsFor($workshop);
        [$surveyResponses, $checkAttempts] = $this->leaderboardService->loadDataForSessions($workshop->sessions->pluck('id')->all());

        $participantsByContact = $workshop->sessions
            ->flatMap(fn (WorkshopSession $session) => $session->participants)
            ->groupBy('contact_id');

        $checkpointRowsByContact = collect($this->leaderboardService->rowsFor($participantsByContact->map->first(), $checkpoints, $surveyResponses, $checkAttempts))
            ->keyBy(fn ($row) => $row['contact']->id);

        $leaderboard = $participantsByContact
            ->map(function ($participantRows) use ($workshop, $checkpointRowsByContact) {
                $contact = $participantRows->first()->contact;
                $completedCount = $participantRows->whereNotNull('completed_at')->count();
                $surveyScores = $workshop->sessions
                    ->flatMap(fn (WorkshopSession $session) => $session->surveyResponses)
                    ->where('contact_id', $contact->id)
                    ->pluck('score');

                $avgScore = $surveyScores->isNotEmpty() ? round($surveyScores->avg()) : null;
                $points = ($completedCount * 100) + ($avgScore ?? 0);

                return [
                    'contact' => $contact,
                    'sessions_completed' => $completedCount,
                    'average_survey_score' => $avgScore,
                    'points' => $points,
                    'statuses' => $checkpointRowsByContact->get($contact->id)['statuses'] ?? [],
                ];
            })
            ->sortByDesc('points')
            ->values()
            ->take(10);

        return view('workshops.rollup', compact('workshop', 'sessionStats', 'partnerStats', 'totalParticipants', 'totalCompleted', 'leaderboard', 'discussionMetrics', 'checkpoints'));
    }

    public function update(UpdateWorkshopRequest $request, Workshop $workshop): RedirectResponse
    {
        $workshop->update($request->validated());

        return redirect()->route('workshops.show', $workshop)
            ->with('success', 'Workshop updated successfully.');
    }

    public function destroy(Workshop $workshop): RedirectResponse
    {
        $workshop->delete();

        return redirect()->route('workshops.index')
            ->with('success', 'Workshop deleted successfully.');
    }

    public function archive(Workshop $workshop): RedirectResponse
    {
        if ($workshop->status !== 'archived') {
            $workshop->update(['status' => 'archived', 'archived_at' => now()]);
        }

        return redirect()->route('workshops.index')
            ->with('success', 'Workshop archived.');
    }
}
