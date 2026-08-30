<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkshopPresence;
use App\Models\WorkshopSession;
use App\Models\WorkshopThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class WorkshopMonitorDashboardController extends Controller
{
    /**
     * A lightweight session dashboard for facilitators who are assigned as
     * a monitor for this specific session but may not hold the broader
     * workshops.management permission - mirrors the prototype's dedicated
     * /m/<workshop>/<session> monitor view, separate from the full admin UI.
     */
    public function show(Request $request, WorkshopSession $session): View
    {
        $this->authorizeMonitor($request->user(), $session);

        $session->load([
            'workshop:id,name',
            'participants' => fn ($query) => $query->notRemoved()->with('contact:id,first_name,last_name,partner_id', 'contact.partner:id,name'),
            'threads' => fn ($query) => $query->where('status', '!=', 'hidden')->latest(),
            'threads.replies',
        ]);

        $openThreadsCount = $session->threads->where('status', 'open')->count();
        $totalParticipants = $session->participants->count();
        $completedParticipants = $session->participants->whereNotNull('completed_at')->count();
        $activeNow = WorkshopPresence::activeCountForSession($session->id);

        return view('workshops.monitor.show', compact(
            'session', 'openThreadsCount', 'totalParticipants', 'completedParticipants', 'activeNow',
        ));
    }

    public function markAnswered(Request $request, WorkshopSession $session, WorkshopThread $thread): RedirectResponse
    {
        $this->authorizeMonitor($request->user(), $session);
        abort_unless($thread->workshop_session_id === $session->id, 404);

        $thread->update([
            'status' => 'answered',
            'answered_at' => Carbon::now(),
            'answered_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Marked answered.');
    }

    private function authorizeMonitor(User $user, WorkshopSession $session): void
    {
        abort_unless(
            $user->bypassesPermissions()
                || $user->hasModulePermission('workshops.management')
                || $session->monitors()->where('user_id', $user->id)->whereNull('removed_at')->exists(),
            403,
        );
    }
}
