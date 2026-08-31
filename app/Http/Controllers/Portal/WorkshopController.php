<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteWorkshopParticipantsRequest;
use App\Models\Contact;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionParticipant;
use App\Services\WorkshopInviteService;
use App\Services\WorkshopLeaderboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WorkshopController extends Controller
{
    public function __construct(private WorkshopLeaderboardService $leaderboardService) {}

    /**
     * "Our sessions" (the firm already has people enrolled) plus "open
     * sessions" (upcoming, any partner can discover and self-invite the
     * first person into) - open discovery, since nothing in this app scopes
     * workshops to specific partners.
     */
    public function index(): View
    {
        $contact = Auth::guard('portal')->user();

        $ourSessions = WorkshopSession::query()
            ->whereHas('participants', fn ($query) => $query
                ->where('status', '!=', 'removed')
                ->whereHas('contact', fn ($q) => $q->where('partner_id', $contact->partner_id)))
            ->with(['workshop', 'participants' => fn ($query) => $query
                ->where('status', '!=', 'removed')
                ->whereHas('contact', fn ($q) => $q->where('partner_id', $contact->partner_id))
                ->with('contact:id,first_name,last_name,email')])
            ->orderBy('start_date')
            ->get();

        $openSessions = WorkshopSession::query()
            ->where('status', 'upcoming')
            ->whereHas('workshop', fn ($query) => $query->where('status', 'active'))
            ->whereDoesntHave('participants', fn ($query) => $query
                ->where('status', '!=', 'removed')
                ->whereHas('contact', fn ($q) => $q->where('partner_id', $contact->partner_id)))
            ->with('workshop')
            ->orderBy('start_date')
            ->get();

        return view('portal.workshops.index', compact('ourSessions', 'openSessions'));
    }

    public function show(WorkshopSession $session): View
    {
        $contact = Auth::guard('portal')->user();

        $this->authorizeSessionAccess($session, $contact->partner_id);

        $session->load(['workshop', 'participants' => fn ($query) => $query
            ->whereHas('contact', fn ($q) => $q->where('partner_id', $contact->partner_id))
            ->with('contact:id,first_name,last_name,email')]);

        $availableContacts = Contact::where('partner_id', $contact->partner_id)
            ->whereNotIn('id', $session->participants->pluck('contact_id'))
            ->orderBy('first_name')
            ->get();

        return view('portal.workshops.show', compact('session', 'availableContacts'));
    }

    public function invite(
        InviteWorkshopParticipantsRequest $request,
        WorkshopSession $session,
        WorkshopInviteService $invites,
    ): RedirectResponse {
        $contact = Auth::guard('portal')->user();

        abort_unless($contact->role?->hasWritePermission('workshops'), 403);

        $this->authorizeSessionAccess($session, $contact->partner_id);

        $emails = $invites->parseEmailList($request->validated('emails'));

        $result = $invites->inviteToSession(
            $session,
            $emails,
            invitedByContact: $contact,
            restrictToPartnerId: $contact->partner_id,
        );

        $message = sprintf(
            '%d invited, %d already enrolled, %d unmatched (they need a contact record with this organization first).',
            count($result['invited']),
            count($result['already_enrolled']),
            count($result['unmatched']),
        );

        return back()->with('success', $message);
    }

    /**
     * Per-checkpoint progression for the firm's own enrolled people in this
     * session - the exact same status computation the internal leaderboard
     * uses (WorkshopReportController::sessionLeaderboard), just scoped to
     * this partner's participants rather than the full roster. The service
     * already takes participants as an injected Collection, so this
     * composes with zero changes to WorkshopLeaderboardService.
     */
    public function progress(WorkshopSession $session): View
    {
        $contact = Auth::guard('portal')->user();

        $this->authorizeSessionAccess($session, $contact->partner_id);

        $ourParticipants = $session->participants()
            ->notRemoved()
            ->whereHas('contact', fn ($q) => $q->where('partner_id', $contact->partner_id))
            ->with('contact:id,first_name,last_name')
            ->get();

        $checkpoints = $this->leaderboardService->checkpointsFor($session->workshop);
        [$surveyResponses, $checkAttempts] = $this->leaderboardService->loadDataForSession($session->id);
        $checkpointRowsByContact = collect($this->leaderboardService->rowsFor($ourParticipants, $checkpoints, $surveyResponses, $checkAttempts))
            ->keyBy(fn ($row) => $row['contact']->id);

        $progression = $ourParticipants
            ->map(fn ($participant) => [
                'contact' => $participant->contact,
                'statuses' => $checkpointRowsByContact->get($participant->contact_id)['statuses'] ?? [],
            ])
            ->sortBy(fn ($row) => $row['contact']->full_name)
            ->values();

        return view('portal.workshops.progress', compact('session', 'checkpoints', 'progression'));
    }

    /**
     * The firm's own participation trend across every workshop, not just
     * the current session - a partner-scoped version of
     * WorkshopAnalyticsService::partnerParticipation(), which ranks every
     * firm within a workshop rather than showing one firm's history across
     * all of them.
     */
    public function rollup(): View
    {
        $contact = Auth::guard('portal')->user();

        $participants = WorkshopSessionParticipant::query()
            ->notRemoved()
            ->whereHas('contact', fn ($q) => $q->where('partner_id', $contact->partner_id))
            ->with('session.workshop:id,name')
            ->get();

        $byWorkshop = $participants
            ->groupBy(fn ($p) => $p->session->workshop->name)
            ->map(function ($rows, $workshopName) {
                $completed = $rows->whereNotNull('completed_at')->count();

                return [
                    'workshop' => $workshopName,
                    'sessions' => $rows->pluck('workshop_session_id')->unique()->count(),
                    'participants' => $rows->count(),
                    'completed' => $completed,
                    'completion_rate' => $rows->count() > 0 ? round(($completed / $rows->count()) * 100) : 0,
                ];
            })
            ->sortByDesc('participants')
            ->values();

        return view('portal.workshops.rollup', compact('byWorkshop'));
    }

    /**
     * A firm can view/invite into a session it's already represented in, or
     * any open (upcoming, active-workshop) session it isn't part of yet -
     * that's how a firm gets its first person into a session in the first
     * place under open discovery.
     */
    private function authorizeSessionAccess(WorkshopSession $session, int $partnerId): void
    {
        $isRepresented = $session->participants()
            ->whereHas('contact', fn ($q) => $q->where('partner_id', $partnerId))
            ->exists();

        $isOpen = $session->status === 'upcoming' && $session->workshop->status === 'active';

        abort_unless($isRepresented || $isOpen, 403);
    }
}
