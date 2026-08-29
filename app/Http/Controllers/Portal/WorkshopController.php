<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteWorkshopParticipantsRequest;
use App\Models\Contact;
use App\Models\WorkshopSession;
use App\Services\WorkshopInviteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WorkshopController extends Controller
{
    /**
     * Sessions the practice_lead's partner organization has been invited to.
     */
    public function index(): View
    {
        $contact = Auth::guard('portal')->user();

        $sessions = WorkshopSession::query()
            ->whereHas('participants', fn ($query) => $query
                ->where('status', '!=', 'removed')
                ->whereHas('contact', fn ($q) => $q->where('partner_id', $contact->partner_id)))
            ->with(['workshop', 'participants' => fn ($query) => $query
                ->where('status', '!=', 'removed')
                ->whereHas('contact', fn ($q) => $q->where('partner_id', $contact->partner_id))
                ->with('contact:id,first_name,last_name,email')])
            ->orderBy('start_date')
            ->get();

        return view('portal.workshops.index', compact('sessions'));
    }

    public function show(WorkshopSession $session): View
    {
        $contact = Auth::guard('portal')->user();

        $this->authorizeSessionForPartner($session, $contact->partner_id);

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

        $this->authorizeSessionForPartner($session, $contact->partner_id);

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

    private function authorizeSessionForPartner(WorkshopSession $session, int $partnerId): void
    {
        $isRepresented = $session->participants()
            ->whereHas('contact', fn ($q) => $q->where('partner_id', $partnerId))
            ->exists();

        abort_unless($isRepresented, 403);
    }
}
