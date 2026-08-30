<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Role;
use App\Models\WorkshopCheckAttempt;
use App\Models\WorkshopPresence;
use App\Models\WorkshopReply;
use App\Models\WorkshopSurveyResponse;
use App\Models\WorkshopSurveyTemplate;
use App\Models\WorkshopThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WorkshopUserController extends Controller
{
    /**
     * Workshop "users" are portal Contacts, not a separate identity system -
     * this lists everyone who's ever been enrolled as a session participant,
     * whether or not they've been granted portal access yet.
     */
    public function index(): View
    {
        $contacts = Contact::whereHas('workshopSessions')
            ->with('role:id,slug,display_name')
            ->orderByDesc('created_at')
            ->get();

        $grantableContacts = Contact::whereHas('workshopSessions')
            ->where(fn ($q) => $q->where('portal_access', false)->orWhereNull('portal_access'))
            ->orderBy('first_name')
            ->get();

        $roles = Role::whereIn('slug', ['workshop_practice_lead', 'workshop_delivery_resource'])->get();

        return view('workshops.users.index', compact('contacts', 'grantableContacts', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'contact_id' => ['required', 'exists:contacts,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ])->validate();

        $contact = Contact::findOrFail($validated['contact_id']);
        $password = Str::password(16);

        $contact->update([
            'portal_access' => true,
            'portal_role_id' => $validated['role_id'],
            'password' => $password,
        ]);

        return redirect()->route('workshops.users.index')
            ->with('success', "Access granted for {$contact->full_name}. Initial password: {$password} (shown once - share it securely).");
    }

    public function resetPassword(Contact $contact): RedirectResponse
    {
        $password = Str::password(16);
        $contact->update(['password' => $password]);

        return redirect()->route('workshops.users.index')
            ->with('success', "Password reset for {$contact->full_name}. New password: {$password} (shown once - share it securely).");
    }

    public function disable(Contact $contact): RedirectResponse
    {
        $contact->update(['portal_access' => false]);

        return redirect()->route('workshops.users.index')
            ->with('success', "{$contact->full_name} disabled - portal access revoked.");
    }

    public function activity(Contact $contact): View
    {
        $views = WorkshopPresence::where('author_contact_id', $contact->id)
            ->with('page:id,title', 'session:id,name,workshop_id', 'session.workshop:id,name')
            ->get()
            ->map(fn (WorkshopPresence $presence) => [
                'at' => $presence->last_seen_at,
                'label' => $presence->page ? "Viewed \"{$presence->page->title}\"" : 'Viewed a page',
                'context' => $presence->session ? "{$presence->session->workshop->name} · {$presence->session->name}" : null,
                'status' => null,
            ]);

        $checks = WorkshopCheckAttempt::where('contact_id', $contact->id)
            ->with('page:id,title', 'session:id,name,workshop_id', 'session.workshop:id,name')
            ->get()
            ->map(fn (WorkshopCheckAttempt $attempt) => [
                'at' => $attempt->attempted_at,
                'label' => 'Check: '.($attempt->page->title ?? 'Unknown page'),
                'context' => $attempt->session ? "{$attempt->session->workshop->name} · {$attempt->session->name}" : null,
                'status' => $attempt->passed ? 'Passed' : 'Not yet',
            ]);

        $surveys = WorkshopSurveyResponse::where('contact_id', $contact->id)
            ->with('template', 'session:id,name,workshop_id', 'session.workshop:id,name')
            ->get()
            ->map(fn (WorkshopSurveyResponse $response) => [
                'at' => $response->submitted_at,
                'label' => 'Submitted '.(WorkshopSurveyTemplate::KINDS[$response->template->kind ?? ''] ?? 'a').' survey',
                'context' => $response->session ? "{$response->session->workshop->name} · {$response->session->name}" : null,
                'status' => $response->template?->isScored() ? ($response->passed ? 'Passed' : 'Failed') : null,
            ]);

        $threads = WorkshopThread::where('author_contact_id', $contact->id)
            ->with('session:id,name,workshop_id', 'session.workshop:id,name')
            ->get()
            ->map(fn (WorkshopThread $thread) => [
                'at' => $thread->created_at,
                'label' => 'Posted a question',
                'context' => $thread->session ? "{$thread->session->workshop->name} · {$thread->session->name}" : null,
                'status' => null,
            ]);

        $replies = WorkshopReply::where('author_contact_id', $contact->id)
            ->with('thread.session:id,name,workshop_id', 'thread.session.workshop:id,name')
            ->get()
            ->map(fn (WorkshopReply $reply) => [
                'at' => $reply->created_at,
                'label' => 'Replied in discussion',
                'context' => $reply->thread?->session ? "{$reply->thread->session->workshop->name} · {$reply->thread->session->name}" : null,
                'status' => null,
            ]);

        $events = $views->concat($checks)->concat($surveys)->concat($threads)->concat($replies)
            ->filter(fn (array $event) => $event['at'] !== null)
            ->sortByDesc('at')
            ->values();

        return view('workshops.users.activity', compact('contact', 'events'));
    }
}
