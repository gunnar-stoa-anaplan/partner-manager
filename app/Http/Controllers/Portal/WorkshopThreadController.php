<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\WorkshopSession;
use App\Models\WorkshopThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkshopThreadController extends Controller
{
    public function store(Request $request, WorkshopSession $session): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();
        $this->authorizeParticipant($session, $contact->id);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'anonymous' => ['nullable', 'boolean'],
        ]);

        $session->threads()->create([
            'author_contact_id' => $contact->id,
            'anonymous' => $request->boolean('anonymous'),
            'kind' => 'question',
            'body' => $validated['body'],
            'status' => 'open',
        ]);

        return redirect()->route('portal.my-workshops.show', $session)->with('success', 'Question posted.');
    }

    public function reply(Request $request, WorkshopSession $session, WorkshopThread $thread): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();
        $this->authorizeParticipant($session, $contact->id);

        abort_unless($thread->workshop_session_id === $session->id, 404);

        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $thread->replies()->create([
            'author_contact_id' => $contact->id,
            'body' => $validated['body'],
        ]);

        return redirect()->route('portal.my-workshops.show', $session)->with('success', 'Reply posted.');
    }

    private function authorizeParticipant(WorkshopSession $session, int $contactId): void
    {
        abort_unless(
            $session->participants()->where('contact_id', $contactId)->where('status', '!=', 'removed')->exists(),
            403,
        );
    }
}
