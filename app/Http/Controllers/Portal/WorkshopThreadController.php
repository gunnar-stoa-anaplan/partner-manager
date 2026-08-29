<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\WorkshopSession;
use App\Models\WorkshopThread;
use App\Models\WorkshopUpload;
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
            'workshop_page_id' => ['nullable', 'exists:workshop_pages,id'],
        ]);

        $thread = $session->threads()->create([
            'workshop_page_id' => $validated['workshop_page_id'] ?? null,
            'author_contact_id' => $contact->id,
            'anonymous' => $request->boolean('anonymous'),
            'kind' => 'question',
            'body' => $validated['body'],
            'status' => 'open',
        ]);

        $this->storeUpload($request, $contact->id, threadId: $thread->id);

        return redirect()->back()->with('success', 'Question posted.');
    }

    public function reply(Request $request, WorkshopSession $session, WorkshopThread $thread): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();
        $this->authorizeParticipant($session, $contact->id);

        abort_unless($thread->workshop_session_id === $session->id, 404);

        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $reply = $thread->replies()->create([
            'author_contact_id' => $contact->id,
            'body' => $validated['body'],
        ]);

        $this->storeUpload($request, $contact->id, replyId: $reply->id);

        return redirect()->back()->with('success', 'Reply posted.');
    }

    public function react(WorkshopSession $session, WorkshopThread $thread): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();
        $this->authorizeParticipant($session, $contact->id);
        abort_unless($thread->workshop_session_id === $session->id, 404);

        $existing = $thread->reactions()->where('author_contact_id', $contact->id)->where('kind', 'me_too')->first();

        if ($existing) {
            $existing->delete();
        } else {
            $thread->reactions()->create(['author_contact_id' => $contact->id, 'kind' => 'me_too']);
        }

        return back();
    }

    private function storeUpload(Request $request, int $contactId, ?int $threadId = null, ?int $replyId = null): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        $file = $request->file('attachment');
        $path = $file->store('workshop-uploads', 'local');

        WorkshopUpload::create([
            'workshop_thread_id' => $threadId,
            'workshop_reply_id' => $replyId,
            'filename' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_contact_id' => $contactId,
        ]);
    }

    private function authorizeParticipant(WorkshopSession $session, int $contactId): void
    {
        abort_unless(
            $session->participants()->where('contact_id', $contactId)->where('status', '!=', 'removed')->exists(),
            403,
        );
    }
}
