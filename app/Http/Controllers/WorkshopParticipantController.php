<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteWorkshopParticipantsRequest;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionParticipant;
use App\Services\WorkshopInviteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WorkshopParticipantController extends Controller
{
    public function invite(
        InviteWorkshopParticipantsRequest $request,
        Workshop $workshop,
        WorkshopSession $session,
        WorkshopInviteService $invites,
    ): RedirectResponse {
        $emails = $invites->parseEmailList($request->validated('emails'));

        $result = $invites->inviteToSession($session, $emails, invitedByUser: $request->user());

        $message = sprintf(
            '%d invited, %d already enrolled, %d unmatched (no contact found for that email).',
            count($result['invited']),
            count($result['already_enrolled']),
            count($result['unmatched']),
        );

        return redirect()->route('workshops.sessions.show', [$workshop, $session])
            ->with('success', $message);
    }

    public function complete(Request $request, Workshop $workshop, WorkshopSession $session, WorkshopSessionParticipant $participant): RedirectResponse
    {
        $participant->update([
            'completed_at' => Carbon::now(),
            'completed_by' => $request->user()->id,
        ]);

        return redirect()->route('workshops.sessions.show', [$workshop, $session])
            ->with('success', $participant->contact->full_name.' marked complete.');
    }

    public function uncomplete(Workshop $workshop, WorkshopSession $session, WorkshopSessionParticipant $participant): RedirectResponse
    {
        $participant->update([
            'completed_at' => null,
            'completed_by' => null,
        ]);

        return redirect()->route('workshops.sessions.show', [$workshop, $session])
            ->with('success', $participant->contact->full_name.' marked incomplete.');
    }

    public function remove(Workshop $workshop, WorkshopSession $session, WorkshopSessionParticipant $participant): RedirectResponse
    {
        $participant->update([
            'status' => 'removed',
            'removed_at' => Carbon::now(),
        ]);

        return redirect()->route('workshops.sessions.show', [$workshop, $session])
            ->with('success', 'Participant removed.');
    }

    public function assignMonitor(Request $request, Workshop $workshop, WorkshopSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $session->monitors()->updateOrCreate(
            ['user_id' => $validated['user_id']],
            [
                'assigned_at' => Carbon::now(),
                'assigned_by' => $request->user()->id,
                'removed_at' => null,
            ],
        );

        return redirect()->route('workshops.sessions.show', [$workshop, $session])
            ->with('success', 'Monitor assigned.');
    }

    public function removeMonitor(Workshop $workshop, WorkshopSession $session, User $monitorUser): RedirectResponse
    {
        $session->monitors()
            ->where('user_id', $monitorUser->id)
            ->update(['removed_at' => Carbon::now()]);

        return redirect()->route('workshops.sessions.show', [$workshop, $session])
            ->with('success', 'Monitor removed.');
    }
}
