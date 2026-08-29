<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\WorkshopCheckAttempt;
use App\Models\WorkshopPage;
use App\Models\WorkshopSession;
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

        return view('portal.my-workshops.page', compact('session', 'page', 'pages', 'completion', 'previousPage', 'nextPage'));
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

        return view('portal.my-workshops.progress', compact('session', 'pageProgress', 'totalChecks', 'passedChecks'));
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
