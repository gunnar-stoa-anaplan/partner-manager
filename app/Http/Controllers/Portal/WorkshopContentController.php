<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\WorkshopCheckAttempt;
use App\Models\WorkshopCredentialAssignment;
use App\Models\WorkshopPage;
use App\Models\WorkshopPresence;
use App\Models\WorkshopSession;
use App\Models\WorkshopThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WorkshopContentController extends Controller
{
    public function show(WorkshopSession $session, WorkshopPage $page): View
    {
        $contact = Auth::guard('portal')->user();
        $this->authorizeParticipant($session, $contact->id);
        abort_unless($page->workshop_id === $session->workshop_id, 404);

        $pages = $session->workshop->pages;
        $completion = $page->completionFor($contact->id, $session->id);

        $currentIndex = $pages->search(fn (WorkshopPage $p) => $p->id === $page->id);
        $previousPage = $currentIndex > 0 ? $pages->get($currentIndex - 1) : null;
        $nextPage = $pages->get($currentIndex + 1);

        WorkshopPresence::heartbeat($session->id, $page->id, null, $contact->id);

        $pageThreads = WorkshopThread::where('workshop_session_id', $session->id)
            ->where('workshop_page_id', $page->id)
            ->where('status', '!=', 'hidden')
            ->with('replies')
            ->latest()
            ->get();

        return view('portal.my-workshops.page', compact('session', 'page', 'pages', 'completion', 'previousPage', 'nextPage', 'pageThreads'));
    }

    public function submitCheck(Request $request, WorkshopSession $session, WorkshopPage $page): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();
        $this->authorizeParticipant($session, $contact->id);
        abort_unless($page->workshop_id === $session->workshop_id, 404);

        $validated = $request->validate([
            'check_id' => ['required', 'string'],
            'passed' => ['required', 'boolean'],
        ]);

        WorkshopCheckAttempt::create([
            'contact_id' => $contact->id,
            'workshop_session_id' => $session->id,
            'workshop_page_id' => $page->id,
            'check_id' => $validated['check_id'],
            'passed' => $validated['passed'],
            'attempted_at' => Carbon::now(),
        ]);

        return redirect()->route('portal.my-workshops.pages.show', [$session, $page])
            ->with('success', $validated['passed'] ? 'Nice work!' : 'Not quite - give it another try.');
    }

    public function progress(WorkshopSession $session): View
    {
        $contact = Auth::guard('portal')->user();
        $this->authorizeParticipant($session, $contact->id);

        $pages = $session->workshop->pages;
        $pageProgress = $pages->map(fn (WorkshopPage $page) => [
            'page' => $page,
            ...$page->completionFor($contact->id, $session->id),
        ]);

        $totalChecks = $pageProgress->sum('total');
        $passedChecks = $pageProgress->sum('passed');

        // Surface the participant's own assigned sandbox credential here so
        // they can log in to their workshop environment without an
        // out-of-band email chase - only ever their own assignment, scoped
        // by contact_id + session_id.
        $credentialAssignment = WorkshopCredentialAssignment::where('workshop_session_id', $session->id)
            ->where('contact_id', $contact->id)
            ->whereNull('released_at')
            ->with('credential')
            ->first();

        return view('portal.my-workshops.progress', compact('session', 'pageProgress', 'totalChecks', 'passedChecks', 'credentialAssignment'));
    }

    public function progressJson(WorkshopSession $session): JsonResponse
    {
        $contact = Auth::guard('portal')->user();
        $this->authorizeParticipant($session, $contact->id);

        $pages = $session->workshop->pages;
        $pageProgress = $pages->map(fn (WorkshopPage $page) => [
            'page_slug' => $page->slug,
            'title' => $page->title,
            ...$page->completionFor($contact->id, $session->id),
        ]);

        return response()->json([
            'session' => $session->slug,
            'pages' => $pageProgress,
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
