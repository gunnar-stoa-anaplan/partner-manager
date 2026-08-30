<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkshopSessionRequest;
use App\Http\Requests\UpdateWorkshopSessionRequest;
use App\Models\Workshop;
use App\Models\WorkshopPresence;
use App\Models\WorkshopReadState;
use App\Models\WorkshopSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WorkshopSessionController extends Controller
{
    public function store(StoreWorkshopSessionRequest $request, Workshop $workshop): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();

        $session = $workshop->sessions()->create($validated);

        return redirect()->route('workshops.sessions.show', [$workshop, $session])
            ->with('success', 'Session created successfully.');
    }

    public function show(Workshop $workshop, WorkshopSession $session): View
    {
        $session->load([
            'monitors.user:id,name',
            'threads' => fn ($query) => $query->where('status', '!=', 'hidden')->latest(),
            'threads.replies',
        ]);
        $session->loadCount(['activeParticipants']);

        $userId = Auth::id();
        WorkshopPresence::heartbeat($session->id, null, $userId, null);
        $session->threads->each(fn ($thread) => WorkshopReadState::markRead($thread->id, $userId, null));
        $activeViewers = WorkshopPresence::activeCountForSession($session->id);

        return view('workshops.sessions.show', compact('workshop', 'session', 'activeViewers'));
    }

    public function feed(Workshop $workshop, WorkshopSession $session): View
    {
        $session->load([
            'threads' => fn ($query) => $query->where('status', '!=', 'hidden')->oldest(),
            'threads.replies',
            'threads.page:id,title',
        ]);

        $items = $session->threads->flatMap(function ($thread) {
            $entries = collect([['type' => 'thread', 'model' => $thread, 'at' => $thread->created_at]]);
            $entries = $entries->concat($thread->replies->map(fn ($reply) => ['type' => 'reply', 'model' => $reply, 'thread' => $thread, 'at' => $reply->created_at]));

            return $entries;
        })->sortBy('at')->values();

        return view('workshops.sessions.feed', compact('workshop', 'session', 'items'));
    }

    public function edit(Workshop $workshop, WorkshopSession $session): View
    {
        return view('workshops.sessions.edit', compact('workshop', 'session'));
    }

    public function update(UpdateWorkshopSessionRequest $request, Workshop $workshop, WorkshopSession $session): RedirectResponse
    {
        $session->update($request->validated());

        return redirect()->route('workshops.sessions.show', [$workshop, $session])
            ->with('success', 'Session updated successfully.');
    }

    public function destroy(Workshop $workshop, WorkshopSession $session): RedirectResponse
    {
        $session->delete();

        return redirect()->route('workshops.show', $workshop)
            ->with('success', 'Session deleted successfully.');
    }

    public function archive(Workshop $workshop, WorkshopSession $session): RedirectResponse
    {
        if ($session->status !== 'archived') {
            $session->update(['status' => 'archived', 'archived_at' => now()]);
        }

        return redirect()->route('workshops.sessions.show', [$workshop, $session])
            ->with('success', 'Session archived. Discussion is now read-only.');
    }
}
