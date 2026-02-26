<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\EnablementEvent;
use App\Models\EnablementRegistration;
use App\Models\EnablementSession;
use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnablementController extends Controller
{
    /**
     * Fetch sessions for FullCalendar feed.
     * Admins see all sessions; PSMs see only published event sessions.
     */
    public function sessions(Request $request): JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'type' => 'nullable|string',
            'geography' => 'nullable|string',
        ]);

        $rangeStart = Carbon::parse($request->start);
        $rangeEnd = Carbon::parse($request->end);

        $query = EnablementSession::with(['event', 'registrations'])
            ->where('start_at', '<', $rangeEnd)
            ->where(function ($q) {
                $q->where('end_at', '>', now()->subYear())
                    ->orWhereNull('end_at');
            })
            ->where('start_at', '>=', $rangeStart->copy()->subMonth());

        $user = auth()->user();

        // Non-admin users only see sessions from published events
        if (! $user->isAdmin()) {
            $query->whereHas('event', fn ($q) => $q->where('status', 'published'));
        }

        // Optional filters
        if ($request->filled('type')) {
            $query->whereHas('event', fn ($q) => $q->where('type', $request->type));
        }

        if ($request->filled('geography')) {
            $query->where('geography', $request->geography);
        }

        $sessions = $query->get();

        $calendarEvents = $sessions->map(fn ($s) => $s->toCalendarArray())->toArray();

        return response()->json($calendarEvents);
    }

    /**
     * Get full session detail for modal display.
     */
    public function sessionDetail(Request $request, $id): JsonResponse
    {
        $session = EnablementSession::with([
            'event',
            'registrations.contact',
            'registrations.partner:id,name',
            'registrations.nominator:id,name',
        ])->findOrFail($id);

        $user = auth()->user();

        // Non-admin: only show published events
        if (! $user->isAdmin() && $session->event->status !== 'published') {
            abort(403);
        }

        $registrations = $session->registrations->map(fn ($r) => [
            'id' => $r->id,
            'contact_name' => $r->contact->first_name.' '.$r->contact->last_name,
            'contact_email' => $r->contact->email,
            'partner_name' => $r->partner->name ?? '',
            'partner_id' => $r->partner_id,
            'nominated_by' => $r->nominator->name ?? '',
            'status' => $r->status,
            'status_label' => $r->status_label,
            'status_color' => $r->status_color,
            'nominated_at' => $r->nominated_at?->toIso8601String(),
        ]);

        // For PSMs, filter to only show registrations from their partners
        if (! $user->isAdmin()) {
            $myPartnerIds = Partner::where('user_id', $user->id)->pluck('id')->toArray();
            $registrations = $registrations->filter(fn ($r) => in_array($r['partner_id'], $myPartnerIds))->values();
        }

        return response()->json([
            'id' => $session->id,
            'title' => $session->display_title,
            'event_id' => $session->enablement_event_id,
            'event_title' => $session->event->title,
            'event_type' => $session->event->type,
            'event_type_label' => EnablementEvent::TYPES[$session->event->type] ?? $session->event->type,
            'category' => $session->event->category,
            'description' => $session->event->description,
            'objectives' => $session->event->objectives,
            'prerequisites' => $session->event->prerequisites,
            'target_audience' => $session->event->target_audience,
            'duration_hours' => $session->event->duration_hours,
            'start_at' => $session->start_at->toIso8601String(),
            'end_at' => $session->end_at?->toIso8601String(),
            'all_day' => $session->all_day,
            'timezone' => $session->timezone,
            'location' => $session->location,
            'meeting_url' => $session->meeting_url,
            'instructor' => $session->instructor,
            'geography' => $session->geography,
            'region' => $session->region,
            'status' => $session->status,
            'status_label' => EnablementSession::STATUSES[$session->status] ?? $session->status,
            'spots_remaining' => $session->spotsRemaining(),
            'registration_open' => $session->isRegistrationOpen(),
            'registrations' => $registrations,
        ]);
    }

    /**
     * Admin creates a session via JSON (for drag-and-drop).
     */
    public function storeSession(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'enablement_event_id' => 'required|integer|exists:enablement_events,id',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'instructor' => 'nullable|string|max:255',
            'geography' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = $user->id;
        $validated['all_day'] = $request->boolean('all_day');

        $session = EnablementSession::create($validated);
        $session->load('event');

        return response()->json($session->toCalendarArray(), 201);
    }

    /**
     * Admin updates a session via JSON (for drag-and-drop resize/move).
     */
    public function updateSessionApi(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $session = EnablementSession::findOrFail($id);

        $validated = $request->validate([
            'start_at' => 'sometimes|date',
            'end_at' => 'nullable|date',
            'all_day' => 'sometimes|boolean',
        ]);

        $session->update($validated);
        $session->load('event');

        return response()->json($session->toCalendarArray());
    }

    /**
     * Admin deletes a session via API.
     */
    public function deleteSessionApi($id): JsonResponse
    {
        $user = auth()->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $session = EnablementSession::findOrFail($id);
        $session->delete();

        return response()->json(['message' => 'Session deleted.']);
    }

    /**
     * PSM registers a partner for a session (partner-level, before contact assignment).
     */
    public function registerPartner(Request $request, $sessionId): JsonResponse
    {
        $session = EnablementSession::with('event')->findOrFail($sessionId);

        if ($session->event->status !== 'published') {
            return response()->json(['message' => 'This event is not published.'], 422);
        }

        $validated = $request->validate([
            'partner_id' => 'required|integer|exists:partners,id',
        ]);

        $user = auth()->user();
        $partner = Partner::findOrFail($validated['partner_id']);

        if (! $user->isAdmin() && $partner->user_id !== $user->id) {
            return response()->json(['message' => 'You can only register your assigned partners.'], 403);
        }

        // Check if already registered
        $exists = $partner->enablementSessions()->where('enablement_session_id', $session->id)->exists();
        if ($exists) {
            return response()->json(['message' => 'Partner is already registered for this session.'], 422);
        }

        $partner->enablementSessions()->attach($session->id, ['added_by' => $user->id]);

        return response()->json(['message' => 'Partner registered for session.'], 201);
    }

    /**
     * PSM removes a partner from a session.
     */
    public function unregisterPartner(Request $request, $sessionId): JsonResponse
    {
        $validated = $request->validate([
            'partner_id' => 'required|integer|exists:partners,id',
        ]);

        $user = auth()->user();
        $partner = Partner::findOrFail($validated['partner_id']);

        if (! $user->isAdmin() && $partner->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $partner->enablementSessions()->detach($sessionId);

        // Also remove any contact registrations for this partner/session
        EnablementRegistration::where('enablement_session_id', $sessionId)
            ->where('partner_id', $partner->id)
            ->delete();

        return response()->json(['message' => 'Partner removed from session.']);
    }

    /**
     * Get sessions for a specific partner's enablement calendar.
     */
    public function partnerSessions(Request $request, $partnerId): JsonResponse
    {
        $partner = Partner::findOrFail($partnerId);
        $user = auth()->user();

        if (! $user->isAdmin() && $partner->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $rangeStart = Carbon::parse($request->start);
        $rangeEnd = Carbon::parse($request->end);

        // Get sessions this partner is registered for
        $sessions = $partner->enablementSessions()
            ->with(['event', 'registrations' => function ($q) use ($partnerId) {
                $q->where('partner_id', $partnerId);
            }, 'registrations.contact'])
            ->where('start_at', '>=', $rangeStart)
            ->where('start_at', '<', $rangeEnd)
            ->get();

        $calendarEvents = $sessions->map(function ($s) use ($partnerId) {
            $arr = $s->toCalendarArray();
            // Add partner-specific registration info
            $regs = $s->registrations->map(fn ($r) => [
                'id' => $r->id,
                'contact_name' => $r->contact->first_name.' '.$r->contact->last_name,
                'contact_id' => $r->contact_id,
                'status' => $r->status,
                'status_label' => $r->status_label,
                'status_color' => $r->status_color,
            ]);
            $arr['extendedProps']['partner_registrations'] = $regs->toArray();
            $arr['extendedProps']['partner_id'] = (int) $partnerId;

            return $arr;
        })->toArray();

        return response()->json($calendarEvents);
    }

    /**
     * PSM nominates one or more contacts to a session.
     */
    public function nominate(Request $request, $sessionId): JsonResponse
    {
        $session = EnablementSession::with('event')->findOrFail($sessionId);

        // Must be a published event
        if ($session->event->status !== 'published') {
            return response()->json(['message' => 'This event is not published.'], 422);
        }

        if (! $session->isRegistrationOpen()) {
            return response()->json(['message' => 'Registration is closed for this session.'], 422);
        }

        $validated = $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'required|integer|exists:contacts,id',
            'partner_id' => 'required|integer|exists:partners,id',
        ]);

        $user = auth()->user();
        $partner = Partner::findOrFail($validated['partner_id']);

        // PSMs can only nominate from their own partners
        if (! $user->isAdmin() && $partner->user_id !== $user->id) {
            return response()->json(['message' => 'You can only nominate contacts from your assigned partners.'], 403);
        }

        // Verify contacts belong to the partner
        $validContactIds = $partner->contacts()
            ->whereIn('id', $validated['contact_ids'])
            ->pluck('id');

        $created = [];
        foreach ($validContactIds as $contactId) {
            // Skip if already registered
            $existing = EnablementRegistration::where('enablement_session_id', $session->id)
                ->where('contact_id', $contactId)
                ->first();

            if ($existing) {
                continue;
            }

            $registration = EnablementRegistration::create([
                'enablement_session_id' => $session->id,
                'contact_id' => $contactId,
                'partner_id' => $partner->id,
                'nominated_by' => $user->id,
            ]);

            $created[] = $registration;
        }

        return response()->json([
            'message' => count($created).' contact(s) nominated successfully.',
            'nominations' => count($created),
        ], 201);
    }

    /**
     * PSM removes a nomination they created.
     */
    public function removeNomination($registrationId): JsonResponse
    {
        $registration = EnablementRegistration::findOrFail($registrationId);
        $user = auth()->user();

        // Only the nominator or admin can remove
        if (! $user->isAdmin() && $registration->nominated_by !== $user->id) {
            return response()->json(['message' => 'You can only remove your own nominations.'], 403);
        }

        // Can only remove if still in nominated status
        if (! in_array($registration->status, ['nominated', 'registered'])) {
            return response()->json(['message' => 'Cannot remove a registration with status: '.$registration->status], 422);
        }

        $registration->delete();

        return response()->json(['message' => 'Nomination removed.']);
    }

    /**
     * Get PSM's nominations across all sessions.
     */
    public function myNominations(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = EnablementRegistration::with([
            'session.event',
            'contact',
            'partner:id,name',
        ])->where('nominated_by', $user->id)
            ->orderByDesc('created_at');

        // Admin can see all nominations
        if ($user->isAdmin() && $request->filled('all')) {
            $query = EnablementRegistration::with([
                'session.event',
                'contact',
                'partner:id,name',
                'nominator:id,name',
            ])->orderByDesc('created_at');
        }

        $registrations = $query->get()->map(fn ($r) => [
            'id' => $r->id,
            'session_id' => $r->enablement_session_id,
            'session_title' => $r->session->display_title ?? '',
            'event_title' => $r->session->event->title ?? '',
            'event_type' => $r->session->event->type ?? '',
            'session_start' => $r->session->start_at?->toIso8601String(),
            'session_location' => $r->session->location,
            'contact_name' => $r->contact->first_name.' '.$r->contact->last_name,
            'contact_email' => $r->contact->email,
            'partner_name' => $r->partner->name ?? '',
            'nominated_by' => $r->nominator->name ?? $user->name,
            'status' => $r->status,
            'status_label' => $r->status_label,
            'status_color' => $r->status_color,
            'nominated_at' => $r->nominated_at?->toIso8601String(),
            'can_remove' => in_array($r->status, ['nominated', 'registered']),
        ]);

        return response()->json($registrations);
    }
}
