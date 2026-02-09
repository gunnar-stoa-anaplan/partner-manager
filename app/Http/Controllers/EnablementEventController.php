<?php

namespace App\Http\Controllers;

use App\Models\EnablementEvent;
use App\Models\EnablementSession;
use App\Models\EnablementRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnablementEventController extends Controller
{
    // ─── Event CRUD ────────────────────────────────────────────

    public function index()
    {
        $events = EnablementEvent::withCount(['sessions', 'registrations'])
            ->with('creator:id,name')
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.enablement.index', compact('events'));
    }

    public function create()
    {
        return view('admin.enablement.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(EnablementEvent::TYPES)),
            'category' => 'nullable|string|max:255',
            'product_area' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'prerequisites' => 'nullable|string',
            'target_audience' => 'nullable|string|max:255',
            'duration_hours' => 'nullable|numeric|min:0|max:999',
            'max_participants' => 'nullable|integer|min:1',
            'materials_url' => 'nullable|url|max:255',
            'status' => 'required|in:' . implode(',', array_keys(EnablementEvent::STATUSES)),
        ]);

        $validated['created_by'] = auth()->id();

        EnablementEvent::create($validated);

        return redirect()->route('enablement-events.index')
            ->with('success', 'Enablement event created successfully.');
    }

    public function show($id)
    {
        $event = EnablementEvent::withCount(['sessions', 'registrations'])
            ->findOrFail($id);

        $sessions = $event->sessions()
            ->withCount('registrations')
            ->orderBy('start_at')
            ->get();

        return view('admin.enablement.show', compact('event', 'sessions'));
    }

    public function edit($id)
    {
        $event = EnablementEvent::findOrFail($id);
        return view('admin.enablement.edit', compact('event'));
    }

    public function update(Request $request, $id)
    {
        $event = EnablementEvent::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(EnablementEvent::TYPES)),
            'category' => 'nullable|string|max:255',
            'product_area' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'prerequisites' => 'nullable|string',
            'target_audience' => 'nullable|string|max:255',
            'duration_hours' => 'nullable|numeric|min:0|max:999',
            'max_participants' => 'nullable|integer|min:1',
            'materials_url' => 'nullable|url|max:255',
            'status' => 'required|in:' . implode(',', array_keys(EnablementEvent::STATUSES)),
        ]);

        $event->update($validated);

        return redirect()->route('enablement-events.show', $event->id)
            ->with('success', 'Enablement event updated successfully.');
    }

    public function destroy($id)
    {
        $event = EnablementEvent::findOrFail($id);
        $event->delete();

        return redirect()->route('enablement-events.index')
            ->with('success', 'Enablement event deleted successfully.');
    }

    // ─── Session CRUD ──────────────────────────────────────────

    public function createSession($eventId)
    {
        $event = EnablementEvent::findOrFail($eventId);
        return view('admin.enablement.session-form', [
            'event' => $event,
            'session' => null,
        ]);
    }

    public function storeSession(Request $request, $eventId)
    {
        $event = EnablementEvent::findOrFail($eventId);

        $validated = $request->validate([
            'title_override' => 'nullable|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'all_day' => 'boolean',
            'timezone' => 'nullable|string|max:64',
            'location' => 'nullable|string|max:255',
            'meeting_url' => 'nullable|url|max:255',
            'instructor' => 'nullable|string|max:255',
            'geography' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'nullable|in:' . implode(',', array_keys(EnablementSession::STATUSES)),
            'notes' => 'nullable|string',
            'registration_deadline' => 'nullable|date|before:start_at',
        ]);

        $validated['enablement_event_id'] = $event->id;
        $validated['created_by'] = auth()->id();
        $validated['all_day'] = $request->boolean('all_day');

        EnablementSession::create($validated);

        return redirect()->route('enablement-events.show', $event->id)
            ->with('success', 'Session added successfully.');
    }

    public function editSession($sessionId)
    {
        $session = EnablementSession::with('event')->findOrFail($sessionId);
        return view('admin.enablement.session-form', [
            'event' => $session->event,
            'session' => $session,
        ]);
    }

    public function updateSession(Request $request, $sessionId)
    {
        $session = EnablementSession::with('event')->findOrFail($sessionId);

        $validated = $request->validate([
            'title_override' => 'nullable|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'all_day' => 'boolean',
            'timezone' => 'nullable|string|max:64',
            'location' => 'nullable|string|max:255',
            'meeting_url' => 'nullable|url|max:255',
            'instructor' => 'nullable|string|max:255',
            'geography' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'nullable|in:' . implode(',', array_keys(EnablementSession::STATUSES)),
            'notes' => 'nullable|string',
            'summary' => 'nullable|string',
            'registration_deadline' => 'nullable|date|before:start_at',
        ]);

        $validated['all_day'] = $request->boolean('all_day');

        $session->update($validated);

        return redirect()->route('enablement-events.show', $session->enablement_event_id)
            ->with('success', 'Session updated successfully.');
    }

    public function destroySession($sessionId)
    {
        $session = EnablementSession::findOrFail($sessionId);
        $eventId = $session->enablement_event_id;
        $session->delete();

        return redirect()->route('enablement-events.show', $eventId)
            ->with('success', 'Session deleted successfully.');
    }

    // ─── Registration Management ───────────────────────────────

    public function sessionRegistrations($sessionId)
    {
        $session = EnablementSession::with(['event', 'registrations.contact', 'registrations.partner', 'registrations.nominator'])
            ->findOrFail($sessionId);

        return view('admin.enablement.registrations', compact('session'));
    }

    public function updateRegistration(Request $request, $registrationId)
    {
        $registration = EnablementRegistration::findOrFail($registrationId);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(EnablementRegistration::STATUSES)),
            'notes' => 'nullable|string',
        ]);

        switch ($validated['status']) {
            case 'registered':
                $registration->markRegistered();
                break;
            case 'confirmed':
                $registration->markConfirmed();
                break;
            case 'attended':
                $registration->markAttended();
                break;
            case 'no_show':
                $registration->markNoShow();
                break;
            case 'cancelled':
                $registration->cancel($validated['notes'] ?? null);
                break;
            default:
                $registration->update(['status' => $validated['status']]);
        }

        if (isset($validated['notes']) && $validated['status'] !== 'cancelled') {
            $registration->update(['notes' => $validated['notes']]);
        }

        return redirect()->back()->with('success', 'Registration status updated.');
    }

    // ─── Admin Calendar View ───────────────────────────────────

    public function calendar()
    {
        return view('admin.enablement.calendar');
    }
}
