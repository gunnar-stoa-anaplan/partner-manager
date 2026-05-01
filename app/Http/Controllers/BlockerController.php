<?php

namespace App\Http\Controllers;

use App\Models\Blocker;
use App\Models\Journey;
use App\Models\JourneyAsset;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlockerController extends Controller
{
    public function index(Journey $journey): \Illuminate\View\View
    {
        $this->authorizeJourneyAccess($journey);

        $blockers = $journey->blockers()
            ->with(['journeyAsset', 'assignedToUser', 'createdBy'])
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 ELSE 2 END")
            ->orderBy('target_resolution_date')
            ->get();

        $assignableUsers = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $journeyAssets = $journey->journeyAssets()
            ->orderBy('stage_number')
            ->orderBy('asset_name')
            ->get(['id', 'stage_number', 'asset_name', 'status']);

        return view('journeys.blockers.index', compact('journey', 'blockers', 'assignableUsers', 'journeyAssets'));
    }

    public function store(Request $request, Journey $journey): RedirectResponse
    {
        $this->authorizeJourneyAccess($journey);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'journey_asset_id' => ['nullable', 'integer', 'exists:journey_assets,id'],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'assigned_to_external' => ['nullable', 'string', 'max:255'],
            'target_resolution_date' => ['nullable', 'date'],
            'raised_in_standup' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['journey_asset_id'])) {
            $asset = JourneyAsset::findOrFail($validated['journey_asset_id']);
            abort_unless($asset->journey_id === $journey->id, 422, 'Asset does not belong to this journey.');
        }

        $blocker = $journey->blockers()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'journey_asset_id' => $validated['journey_asset_id'] ?? null,
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
            'assigned_to_external' => $validated['assigned_to_external'] ?? null,
            'target_resolution_date' => $validated['target_resolution_date'] ?? null,
            'raised_in_standup_at' => ($validated['raised_in_standup'] ?? false) ? now() : null,
            'status' => 'open',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('journeys.blockers.index', $journey)->with('flash.success', 'Blocker logged.');
    }

    public function update(Request $request, Journey $journey, Blocker $blocker): RedirectResponse
    {
        $this->authorizeJourneyAccess($journey);
        abort_unless($blocker->journey_id === $journey->id, 404);

        $validated = $request->validate([
            'status' => ['nullable', 'in:'.implode(',', Blocker::STATUSES)],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'assigned_to_external' => ['nullable', 'string', 'max:255'],
            'target_resolution_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        if (array_key_exists('assigned_to_user_id', $validated)) {
            $blocker->assigned_to_user_id = $validated['assigned_to_user_id'];
        }
        if (array_key_exists('assigned_to_external', $validated)) {
            $blocker->assigned_to_external = $validated['assigned_to_external'];
        }
        if (array_key_exists('target_resolution_date', $validated)) {
            $blocker->target_resolution_date = $validated['target_resolution_date'];
        }
        if (array_key_exists('description', $validated)) {
            $blocker->description = $validated['description'];
        }
        if (! empty($validated['status']) && $validated['status'] !== $blocker->status) {
            $blocker->status = $validated['status'];
            if ($validated['status'] === 'resolved') {
                $blocker->resolved_at = now();
            } else {
                $blocker->resolved_at = null;
            }
        }
        $blocker->save();

        return redirect()->route('journeys.blockers.index', $journey)->with('flash.success', 'Blocker updated.');
    }

    public function destroy(Journey $journey, Blocker $blocker): RedirectResponse
    {
        $this->authorizeJourneyAccess($journey);
        abort_unless($blocker->journey_id === $journey->id, 404);
        $blocker->delete();

        return redirect()->route('journeys.blockers.index', $journey)->with('flash.success', 'Blocker removed.');
    }

    private function authorizeJourneyAccess(Journey $journey): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return;
        }
        $assigned = $journey->assignments()->where('user_id', $user->id)->exists();
        if (! $assigned) {
            abort(403, 'You are not assigned to this journey.');
        }
    }
}
