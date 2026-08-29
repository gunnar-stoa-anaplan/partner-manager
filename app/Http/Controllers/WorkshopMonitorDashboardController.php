<?php

namespace App\Http\Controllers;

use App\Models\WorkshopPresence;
use App\Models\WorkshopSession;
use Illuminate\Http\Request;
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
        $user = $request->user();

        abort_unless(
            $user->bypassesPermissions()
                || $user->hasModulePermission('workshops.management')
                || $session->monitors()->where('user_id', $user->id)->whereNull('removed_at')->exists(),
            403,
        );

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
}
