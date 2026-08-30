<?php

namespace App\Http\Controllers;

use App\Ai\Agents\WorkshopReplySuggester;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopThread;
use App\Models\WorkshopUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WorkshopThreadController extends Controller
{
    public function store(Request $request, Workshop $workshop, WorkshopSession $session): RedirectResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $thread = $session->threads()->create([
            'author_user_id' => $request->user()->id,
            'kind' => 'comment',
            'body' => $validated['body'],
            'status' => 'open',
        ]);

        $this->storeUpload($request, threadId: $thread->id);

        return redirect()->route('workshops.sessions.show', [$workshop, $session])->with('success', 'Posted.');
    }

    public function reply(Request $request, Workshop $workshop, WorkshopSession $session, WorkshopThread $thread): RedirectResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $reply = $thread->replies()->create([
            'author_user_id' => $request->user()->id,
            'is_monitor_reply' => true,
            'body' => $validated['body'],
        ]);

        $this->storeUpload($request, replyId: $reply->id);

        $thread->update([
            'status' => 'answered',
            'answered_at' => Carbon::now(),
            'answered_by' => $request->user()->id,
        ]);

        return redirect()->route('workshops.sessions.show', [$workshop, $session])->with('success', 'Reply posted.');
    }

    public function markAnswered(Request $request, Workshop $workshop, WorkshopSession $session, WorkshopThread $thread): RedirectResponse
    {
        $thread->update([
            'status' => 'answered',
            'answered_at' => Carbon::now(),
            'answered_by' => $request->user()->id,
        ]);

        return redirect()->route('workshops.sessions.show', [$workshop, $session])->with('success', 'Marked answered.');
    }

    public function hide(Request $request, Workshop $workshop, WorkshopSession $session, WorkshopThread $thread): RedirectResponse
    {
        $thread->update([
            'status' => 'hidden',
            'hidden_at' => Carbon::now(),
            'hidden_by' => $request->user()->id,
        ]);

        return redirect()->route('workshops.sessions.show', [$workshop, $session])->with('success', 'Thread hidden.');
    }

    public function react(Request $request, Workshop $workshop, WorkshopSession $session, WorkshopThread $thread): RedirectResponse
    {
        $userId = $request->user()->id;
        $existing = $thread->reactions()->where('author_user_id', $userId)->where('kind', 'me_too')->first();

        if ($existing) {
            $existing->delete();
        } else {
            $thread->reactions()->create(['author_user_id' => $userId, 'kind' => 'me_too']);
        }

        return back();
    }

    public function suggestReply(Workshop $workshop, WorkshopSession $session, WorkshopThread $thread): JsonResponse
    {
        $response = WorkshopReplySuggester::make(workshopName: $thread->session->workshop->name)
            ->prompt($thread->body);

        return response()->json(['suggestion' => $response->text]);
    }

    private function storeUpload(Request $request, ?int $threadId = null, ?int $replyId = null): void
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
            'uploaded_by_user_id' => $request->user()->id,
        ]);
    }
}
