<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use App\Models\JourneyAsset;
use App\Services\AssetStatusTrendService;
use Illuminate\Http\Request;

class JourneyStandupController extends Controller
{
    /**
     * Standup view: status counts today, trend (D/W/M), open blockers.
     */
    public function show(Request $request, Journey $journey, AssetStatusTrendService $trends): \Illuminate\View\View
    {
        $this->authorizeJourneyAccess($journey);

        $resolution = $request->query('resolution', 'daily');
        $resolution = in_array($resolution, ['daily', 'weekly', 'monthly'], true) ? $resolution : 'daily';
        $window = match ($resolution) {
            'weekly' => 12,
            'monthly' => 6,
            default => 30,
        };

        $assets = $journey->journeyAssets()->get(['id', 'status']);
        $statusCounts = array_fill_keys(JourneyAsset::STATUSES, 0);
        foreach ($assets as $asset) {
            if (isset($statusCounts[$asset->status])) {
                $statusCounts[$asset->status]++;
            }
        }
        $statusCounts['total'] = array_sum($statusCounts);

        $series = $trends->forJourney($journey, $resolution, $window);

        $blockers = $journey->blockers()
            ->where('status', '!=', 'resolved')
            ->with(['journeyAsset', 'assignedToUser'])
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 ELSE 2 END")
            ->orderBy('target_resolution_date')
            ->get();

        return view('journeys.standup', compact('journey', 'statusCounts', 'series', 'resolution', 'blockers'));
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
