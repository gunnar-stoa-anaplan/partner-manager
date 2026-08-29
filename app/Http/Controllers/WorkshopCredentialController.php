<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Models\WorkshopCredential;
use App\Models\WorkshopCredentialAssignment;
use App\Models\WorkshopSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkshopCredentialController extends Controller
{
    public function index(): View
    {
        $credentials = WorkshopCredential::with(['workshop:id,name', 'assignments' => fn ($query) => $query->whereNull('released_at')->with('contact:id,first_name,last_name,partner_id', 'contact.partner:id,name')])
            ->orderBy('workshop_id')
            ->orderBy('username')
            ->get();

        $workshops = Workshop::orderBy('name')->get();

        return view('workshops.credentials.index', compact('credentials', 'workshops'));
    }

    public function create(): View
    {
        $workshops = Workshop::orderBy('name')->get();

        return view('workshops.credentials.create', compact('workshops'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'workshop_id' => ['required', 'exists:workshops,id'],
            'email' => ['required', 'email', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        WorkshopCredential::create([
            'workshop_id' => $validated['workshop_id'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password_enc' => $validated['password'],
            'status' => 'available',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('workshops.credentials.index')
            ->with('success', 'Credential added to the pool.');
    }

    public function edit(WorkshopCredential $credential): View
    {
        return view('workshops.credentials.edit', compact('credential'));
    }

    public function update(Request $request, WorkshopCredential $credential): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(WorkshopCredential::STATUSES))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $credential->update([
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password_enc' => $validated['password'] ?: $credential->password_enc,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'last_refreshed_at' => $request->filled('password') ? Carbon::now() : $credential->last_refreshed_at,
        ]);

        return redirect()->route('workshops.credentials.index')
            ->with('success', 'Credential updated.');
    }

    public function destroy(WorkshopCredential $credential): RedirectResponse
    {
        $credential->update(['status' => 'retired']);

        return redirect()->route('workshops.credentials.index')
            ->with('success', 'Credential retired.');
    }

    public function assign(Request $request, WorkshopCredential $credential): RedirectResponse
    {
        $validated = $request->validate([
            'workshop_session_id' => ['required', 'exists:workshop_sessions,id'],
            'contact_id' => ['required', 'exists:contacts,id'],
            'hold_until' => ['nullable', 'date'],
        ]);

        if (! $credential->isAvailable()) {
            return back()->with('error', 'This credential is already assigned or unavailable.');
        }

        $session = WorkshopSession::findOrFail($validated['workshop_session_id']);

        $credential->assignments()->create([
            'workshop_session_id' => $session->id,
            'contact_id' => $validated['contact_id'],
            'assigned_at' => Carbon::now(),
            'hold_until' => $validated['hold_until'] ?? $session->end_date->addDay(),
        ]);

        return back()->with('success', 'Credential assigned.');
    }

    public function release(WorkshopCredentialAssignment $assignment): RedirectResponse
    {
        $assignment->update([
            'released_at' => Carbon::now(),
            'released_reason' => 'manual',
        ]);

        return back()->with('success', 'Credential released back to the pool.');
    }
}
