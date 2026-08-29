<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkshopSessionRequest;
use App\Http\Requests\UpdateWorkshopSessionRequest;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use Illuminate\Http\RedirectResponse;
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
            'participants.contact:id,first_name,last_name,email,partner_id',
            'participants.contact.partner:id,name',
            'monitors.user:id,name',
        ]);

        return view('workshops.sessions.show', compact('workshop', 'session'));
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
}
