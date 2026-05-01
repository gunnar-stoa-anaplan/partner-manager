<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Journey;
use App\Models\JourneyAsset;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JourneyAssetController extends Controller
{
    /**
     * List all assets for a journey, grouped by stage, with status counts.
     */
    public function index(Journey $journey): \Illuminate\View\View
    {
        $this->authorizeJourneyAccess($journey);

        $assets = $journey->journeyAssets()
            ->with(['linkedAsset', 'assignedToUser'])
            ->orderBy('stage_number')
            ->orderBy('task_id')
            ->orderBy('asset_name')
            ->get();

        $byStage = $assets->groupBy('stage_number');

        $statusCounts = $this->statusCounts($assets);
        $stageStatusCounts = $byStage->map(fn ($stageAssets) => $this->statusCounts($stageAssets));

        $assignableUsers = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $linkableAssets = Asset::query()
            ->where('status', 'active')
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('journeys.assets.index', compact(
            'journey',
            'byStage',
            'statusCounts',
            'stageStatusCounts',
            'assignableUsers',
            'linkableAssets',
        ));
    }

    /**
     * Update status, link an asset, assign a user, or update notes for one journey_asset row.
     */
    public function update(Request $request, Journey $journey, JourneyAsset $asset): RedirectResponse
    {
        $this->authorizeJourneyAccess($journey);
        abort_unless($asset->journey_id === $journey->id, 404);

        $validated = $request->validate([
            'status' => ['nullable', 'in:'.implode(',', JourneyAsset::STATUSES)],
            'linked_asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (array_key_exists('linked_asset_id', $validated)) {
            $asset->linked_asset_id = $validated['linked_asset_id'];
        }
        if (array_key_exists('assigned_to_user_id', $validated)) {
            $asset->assigned_to_user_id = $validated['assigned_to_user_id'];
        }
        if (array_key_exists('notes', $validated)) {
            $asset->notes = $validated['notes'];
        }
        if (! empty($validated['status']) && $validated['status'] !== $asset->status) {
            $asset->status = $validated['status'];
        }
        $asset->save();

        return redirect()
            ->route('journeys.assets.index', $journey)
            ->with('flash.success', "Updated '{$asset->asset_name}'.");
    }

    /**
     * @param  iterable<JourneyAsset>  $assets
     * @return array<string, int>
     */
    protected function statusCounts(iterable $assets): array
    {
        $counts = array_fill_keys(JourneyAsset::STATUSES, 0);
        foreach ($assets as $asset) {
            if (isset($counts[$asset->status])) {
                $counts[$asset->status]++;
            }
        }
        $counts['total'] = array_sum($counts);

        return $counts;
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
