<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WorkshopThreadController extends Controller
{
    public function reply(Request $request, Workshop $workshop, WorkshopSession $session, WorkshopThread $thread): RedirectResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $thread->replies()->create([
            'author_user_id' => $request->user()->id,
            'is_monitor_reply' => true,
            'body' => $validated['body'],
        ]);

        $thread->update([
            'status' => 'answered',
            'answered_at' => Carbon::now(),
            'answered_by' => $request->user()->id,
        ]);

        return redirect()->route('workshops.sessions.show', [$workshop, $session])->with('success', 'Reply posted.');
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
}
