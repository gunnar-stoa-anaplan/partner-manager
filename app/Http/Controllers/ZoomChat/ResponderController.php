<?php

namespace App\Http\Controllers\ZoomChat;

use App\Http\Controllers\Controller;
use App\Http\Requests\ZoomChat\StoreResponderRequest;
use App\Http\Requests\ZoomChat\UpdateResponderRequest;
use App\Models\EnablementSession;
use App\Models\ZoomChatResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResponderController extends Controller
{
    public function index(): View
    {
        $responders = ZoomChatResponder::with(['enablementSession', 'creator'])
            ->withCount('messages')
            ->latest()
            ->get();

        return view('zoom-chat.responders.index', compact('responders'));
    }

    public function create(): View
    {
        $sessions = EnablementSession::with('event')
            ->whereNotNull('meeting_url')
            ->latest('start_at')
            ->get();

        $agents = ZoomChatResponder::allowedAgents();

        return view('zoom-chat.responders.create', compact('sessions', 'agents'));
    }

    public function store(StoreResponderRequest $request): RedirectResponse
    {
        ZoomChatResponder::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('zoom_chat.responders.index')
            ->with('success', 'Chat responder created.');
    }

    public function show(ZoomChatResponder $responder): View
    {
        $responder->load(['enablementSession.event', 'creator']);

        $messages = $responder->messages()
            ->latest()
            ->limit(20)
            ->get();

        $stats = [
            'total' => $responder->messages()->count(),
            'responded' => $responder->messages()->where('status', 'responded')->count(),
            'failed' => $responder->messages()->where('status', 'failed')->count(),
            'avg_time' => (int) $responder->messages()->where('status', 'responded')->avg('processing_time_ms'),
        ];

        return view('zoom-chat.responders.show', compact('responder', 'messages', 'stats'));
    }

    public function edit(ZoomChatResponder $responder): View
    {
        $sessions = EnablementSession::with('event')
            ->whereNotNull('meeting_url')
            ->latest('start_at')
            ->get();

        $agents = ZoomChatResponder::allowedAgents();

        return view('zoom-chat.responders.edit', compact('responder', 'sessions', 'agents'));
    }

    public function update(UpdateResponderRequest $request, ZoomChatResponder $responder): RedirectResponse
    {
        $responder->update($request->validated());

        return redirect()->route('zoom_chat.responders.show', $responder)
            ->with('success', 'Chat responder updated.');
    }

    public function destroy(ZoomChatResponder $responder): RedirectResponse
    {
        $responder->delete();

        return redirect()->route('zoom_chat.responders.index')
            ->with('success', 'Chat responder deleted.');
    }

    public function toggle(ZoomChatResponder $responder): JsonResponse
    {
        $responder->update(['is_active' => ! $responder->is_active]);

        return response()->json([
            'is_active' => $responder->is_active,
            'message' => $responder->is_active ? 'Responder activated.' : 'Responder deactivated.',
        ]);
    }
}
