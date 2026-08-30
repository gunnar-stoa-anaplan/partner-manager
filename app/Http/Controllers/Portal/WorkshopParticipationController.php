<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\WorkshopPresence;
use App\Models\WorkshopReadState;
use App\Models\WorkshopSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WorkshopParticipationController extends Controller
{
    /**
     * Sessions this contact has personally been invited to.
     */
    public function index(): View
    {
        $contact = Auth::guard('portal')->user();

        $sessions = $contact->workshopSessions()
            ->wherePivot('status', '!=', 'removed')
            ->with('workshop')
            ->orderBy('start_date')
            ->get();

        return view('portal.my-workshops.index', compact('sessions'));
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
            'workshop.pages',
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
