<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerEventController extends Controller
{
    /**
     * Fetch events for FullCalendar within a date range.
     * Supports partner_id, types[], and status filters.
     * Expands recurring events into virtual occurrences.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'partner_id' => 'nullable|integer|exists:partners,id',
            'types' => 'nullable|array',
            'types.*' => 'string|in:' . implode(',', array_keys(PartnerEvent::TYPES)),
            'status' => 'nullable|string|in:scheduled,completed,cancelled',
        ]);

        $rangeStart = Carbon::parse($request->start);
        $rangeEnd = Carbon::parse($request->end);
        $user = auth()->user();

        $query = PartnerEvent::with(['partner:id,name', 'participants:id,first_name,last_name'])
            ->whereNull('parent_event_id');

        // Scope to user's partners unless admin
        if ($user->role !== 'admin') {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
            $query->whereIn('partner_id', $partnerIds);
        }

        if ($request->partner_id) {
            $partner = Partner::findOrFail($request->partner_id);
            if ($partner->user_id !== $user->id && $user->role !== 'admin') {
                abort(403);
            }
            $query->where('partner_id', $request->partner_id);
        }

        if ($request->types) {
            $query->whereIn('type', $request->types);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Fetch events overlapping the range
        $query->where(function ($q) use ($rangeStart, $rangeEnd) {
            // Non-recurring events in range
            $q->where(function ($sub) use ($rangeStart, $rangeEnd) {
                $sub->where('recurrence_type', 'none')
                    ->where('start_at', '<', $rangeEnd)
                    ->where(function ($inner) use ($rangeStart) {
                        $inner->where('end_at', '>', $rangeStart)
                            ->orWhereNull('end_at')
                            ->orWhere('start_at', '>=', $rangeStart);
                    });
            })
            // Recurring events that could produce occurrences in range
            ->orWhere(function ($sub) use ($rangeStart, $rangeEnd) {
                $sub->where('recurrence_type', '!=', 'none')
                    ->where('start_at', '<', $rangeEnd)
                    ->where(function ($inner) use ($rangeStart) {
                        $inner->whereNull('recurrence_end')
                            ->orWhere('recurrence_end', '>=', $rangeStart);
                    });
            });
        });

        $events = $query->get();

        // Expand recurring events
        $calendarEvents = [];
        foreach ($events as $event) {
            $calendarEvents = array_merge(
                $calendarEvents,
                $event->expandOccurrences($rangeStart, $rangeEnd)
            );
        }

        // Fetch exception children in range
        $recurringIds = $events->filter(fn($e) => $e->recurrence_type !== 'none')->pluck('id');
        if ($recurringIds->isNotEmpty()) {
            $exceptions = PartnerEvent::with(['partner:id,name', 'participants:id,first_name,last_name'])
                ->whereIn('parent_event_id', $recurringIds)
                ->where('start_at', '>=', $rangeStart)
                ->where('start_at', '<', $rangeEnd)
                ->get();
            foreach ($exceptions as $ex) {
                $calendarEvents[] = $ex->toCalendarArray();
            }
        }

        return response()->json($calendarEvents);
    }

    /**
     * Create a new event.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'partner_id' => 'required|integer|exists:partners,id',
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(PartnerEvent::TYPES)),
            'status' => 'nullable|string|in:scheduled,completed,cancelled',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'all_day' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'outcome' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'opportunity_id' => 'nullable|integer|exists:opportunities,id',
            'project_id' => 'nullable|integer|exists:projects,id',
            'recurrence_type' => 'nullable|string|in:none,daily,weekly,biweekly,monthly',
            'recurrence_end' => 'nullable|date|after:start_at',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'integer|exists:contacts,id',
        ]);

        $partner = Partner::findOrFail($validated['partner_id']);
        $user = auth()->user();
        if ($partner->user_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        $validated['created_by'] = $user->id;
        $validated['status'] = $validated['status'] ?? 'scheduled';
        $validated['recurrence_type'] = $validated['recurrence_type'] ?? 'none';

        $event = PartnerEvent::create($validated);

        if (!empty($validated['participant_ids'])) {
            $validContactIds = $partner->contacts()
                ->whereIn('id', $validated['participant_ids'])
                ->pluck('id');
            $event->participants()->attach($validContactIds);
        }

        $event->load(['partner:id,name', 'participants:id,first_name,last_name']);

        return response()->json($event->toCalendarArray(), 201);
    }

    /**
     * Get full event details for the edit modal.
     */
    public function show(int $id): JsonResponse
    {
        $event = PartnerEvent::with(['partner', 'participants', 'opportunity', 'project', 'creator'])
            ->findOrFail($id);

        $user = auth()->user();
        if ($event->partner->user_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        return response()->json([
            'id' => $event->id,
            'partner_id' => $event->partner_id,
            'partner_name' => $event->partner->name,
            'created_by' => $event->created_by,
            'creator_name' => $event->creator->name,
            'title' => $event->title,
            'type' => $event->type,
            'type_label' => PartnerEvent::TYPES[$event->type] ?? $event->type,
            'status' => $event->status,
            'start_at' => $event->start_at->toIso8601String(),
            'end_at' => $event->end_at?->toIso8601String(),
            'all_day' => $event->all_day,
            'location' => $event->location,
            'description' => $event->description,
            'outcome' => $event->outcome,
            'next_steps' => $event->next_steps,
            'opportunity_id' => $event->opportunity_id,
            'opportunity_name' => $event->opportunity?->opportunity_name,
            'project_id' => $event->project_id,
            'project_name' => $event->project?->name,
            'recurrence_type' => $event->recurrence_type,
            'recurrence_end' => $event->recurrence_end?->toDateString(),
            'parent_event_id' => $event->parent_event_id,
            'participants' => $event->participants->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->first_name . ' ' . $c->last_name,
                'email' => $c->email,
                'title' => $c->title,
            ]),
        ]);
    }

    /**
     * Update an event.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $event = PartnerEvent::findOrFail($id);
        $user = auth()->user();
        if ($event->partner->user_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:' . implode(',', array_keys(PartnerEvent::TYPES)),
            'status' => 'sometimes|string|in:scheduled,completed,cancelled',
            'start_at' => 'sometimes|date',
            'end_at' => 'nullable|date',
            'all_day' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'outcome' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'opportunity_id' => 'nullable|integer|exists:opportunities,id',
            'project_id' => 'nullable|integer|exists:projects,id',
            'recurrence_type' => 'nullable|string|in:none,daily,weekly,biweekly,monthly',
            'recurrence_end' => 'nullable|date',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'integer|exists:contacts,id',
        ]);

        $event->update($validated);

        if (array_key_exists('participant_ids', $validated)) {
            $validContactIds = $event->partner->contacts()
                ->whereIn('id', $validated['participant_ids'] ?? [])
                ->pluck('id');
            $event->participants()->sync($validContactIds);
        }

        $event->load(['partner:id,name', 'participants:id,first_name,last_name']);

        return response()->json($event->toCalendarArray());
    }

    /**
     * Delete an event (and its exceptions if recurring parent).
     */
    public function destroy(int $id): JsonResponse
    {
        $event = PartnerEvent::findOrFail($id);
        $user = auth()->user();
        if ($event->partner->user_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        if ($event->recurrence_type !== 'none') {
            $event->exceptions()->delete();
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted']);
    }

    /**
     * Return contacts for a partner (for participant picker).
     */
    public function partnerContacts(int $partnerId): JsonResponse
    {
        $partner = Partner::findOrFail($partnerId);
        $user = auth()->user();
        if ($partner->user_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        $contacts = $partner->contacts()
            ->select('id', 'first_name', 'last_name', 'email', 'title')
            ->orderBy('first_name')
            ->get();

        return response()->json($contacts);
    }
}
