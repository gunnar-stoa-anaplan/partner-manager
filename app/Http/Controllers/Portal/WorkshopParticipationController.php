<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\WorkshopPresence;
use App\Models\WorkshopReadState;
use App\Models\WorkshopSession;
use App\Services\WorkshopLeaderboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WorkshopParticipationController extends Controller
{
    public function __construct(private WorkshopLeaderboardService $leaderboardService) {}

    /**
     * Sessions this contact has personally been invited to (or registered
     * for), each with its own completion outcome - reusing
     * WorkshopLeaderboardService's per-checkpoint status computation scoped
     * to just this contact's own participant row, rather than inventing new
     * completion logic for the history list.
     */
    public function index(): View
    {
        $contact = Auth::guard('portal')->user();

        $sessions = $contact->workshopSessions()
            ->wherePivot('status', '!=', 'removed')
            ->with('workshop')
            ->orderByDesc('start_date')
            ->get();

        $outcomesBySessionId = $sessions->mapWithKeys(function (WorkshopSession $session) use ($contact) {
            $participant = $session->participants()->where('contact_id', $contact->id)->first();

            if (! $participant) {
                return [$session->id => []];
            }

            $checkpoints = $this->leaderboardService->checkpointsFor($session->workshop);
            [$surveyResponses, $checkAttempts] = $this->leaderboardService->loadDataForSession($session->id);
            $rows = $this->leaderboardService->rowsFor(collect([$participant]), $checkpoints, $surveyResponses, $checkAttempts);

            return [$session->id => $rows[0]['statuses'] ?? []];
        });

        return view('portal.my-workshops.index', compact('sessions', 'outcomesBySessionId'));
    }

    /**
     * Upcoming sessions on active workshops this contact isn't already
     * enrolled in - the same open-discovery shape as the practice_lead
     * portal, but for the contact registering themselves rather than
     * inviting someone else.
     */
    public function browse(): View
    {
        $contact = Auth::guard('portal')->user();

        $openSessions = WorkshopSession::query()
            ->where('status', 'upcoming')
            ->whereHas('workshop', fn ($query) => $query->where('status', 'active'))
            ->whereDoesntHave('participants', fn ($query) => $query
                ->where('status', '!=', 'removed')
                ->where('contact_id', $contact->id))
            ->with('workshop')
            ->orderBy('start_date')
            ->get();

        return view('portal.my-workshops.browse', compact('openSessions'));
    }

    /**
     * Self-registers the contact into an open session, landing straight on
     * "active" - unlike an invite from someone else, there's no separate
     * accept step needed since registering is itself the acceptance. The
     * unique (workshop_session_id, contact_id) constraint already guards
     * against double-registration.
     */
    public function selfRegister(WorkshopSession $session): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();

        abort_unless($session->status === 'upcoming' && $session->workshop->status === 'active', 403);

        $session->participants()->firstOrCreate(
            ['contact_id' => $contact->id],
            ['status' => 'active', 'enrolled_at' => now(), 'enrolled_by_contact_id' => $contact->id],
        );

        return redirect()->route('portal.my-workshops.show', $session)->with('success', "You're registered for {$session->name}.");
    }

    /**
     * Confirms an invitation someone else sent - flips the participant row
     * from "invited" to "active". Genuinely new: nothing else in the app
     * ever transitions a participant to "active" except self-registration
     * above.
     */
    public function register(WorkshopSession $session): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();

        $participant = $session->participants()
            ->where('contact_id', $contact->id)
            ->where('status', '!=', 'removed')
            ->firstOrFail();

        $participant->update(['status' => 'active']);

        return redirect()->route('portal.my-workshops.show', $session)->with('success', "You're registered for {$session->name}.");
    }

    public function show(WorkshopSession $session): View
    {
        $contact = Auth::guard('portal')->user();

        abort_unless(
            $session->participants()->where('contact_id', $contact->id)->where('status', '!=', 'removed')->exists(),
            403,
        );

        $session->load([
            'workshop',
            'workshop.surveyTemplates',
            'workshop.participantPages',
            'workshop.toolkitAssets',
            'threads' => fn ($query) => $query->where('status', '!=', 'hidden')->latest(),
            'threads.replies',
        ]);

        $myCompletedSurveyKinds = $session->surveyResponses()
            ->where('contact_id', $contact->id)
            ->pluck('workshop_survey_template_id');

        WorkshopPresence::heartbeat($session->id, null, null, $contact->id);
        $session->threads->each(fn ($thread) => WorkshopReadState::markRead($thread->id, null, $contact->id));

        return view('portal.my-workshops.show', compact('session', 'myCompletedSurveyKinds'));
    }

    public function feed(WorkshopSession $session): View
    {
        $contact = Auth::guard('portal')->user();

        abort_unless(
            $session->participants()->where('contact_id', $contact->id)->where('status', '!=', 'removed')->exists(),
            403,
        );

        $session->load([
            'workshop',
            'threads' => fn ($query) => $query->where('status', '!=', 'hidden')->oldest(),
            'threads.replies',
            'threads.page:id,title',
        ]);

        $items = $session->threads->flatMap(function ($thread) {
            $entries = collect([['type' => 'thread', 'model' => $thread, 'at' => $thread->created_at]]);
            $entries = $entries->concat($thread->replies->map(fn ($reply) => ['type' => 'reply', 'model' => $reply, 'thread' => $thread, 'at' => $reply->created_at]));

            return $entries;
        })->sortBy('at')->values();

        return view('portal.my-workshops.feed', compact('session', 'items'));
    }
}
