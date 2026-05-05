<?php

namespace App\Http\Controllers;

use App\Models\AssetStatusHistory;
use App\Models\Blocker;
use App\Models\Journey;
use App\Models\JourneyAsset;

class MyJourneysController extends Controller
{
    /**
     * Owner dashboard — lists all journeys assigned to the current user
     * with completion % by asset status, plus open blocker counts and
     * recent status changes.
     */
    public function index(): \Illuminate\View\View
    {
        $user = auth()->user();

        $journeys = Journey::query()
            ->visibleTo($user)
            ->withCount(['journeyAssets', 'blockers as open_blockers_count' => function ($q) {
                $q->where('status', '!=', 'resolved');
            }])
            ->orderBy('name')
            ->get();

        $journeyIds = $journeys->pluck('id');

        $rawCounts = JourneyAsset::query()
            ->selectRaw('journey_id, status, count(*) as c')
            ->whereIn('journey_id', $journeyIds)
            ->groupBy('journey_id', 'status')
            ->get();

        $statusByJourney = $journeys->mapWithKeys(function (Journey $j) {
            return [$j->id => array_fill_keys(JourneyAsset::STATUSES, 0)];
        });
        foreach ($rawCounts as $row) {
            $statusByJourney[$row->journey_id][$row->status] = (int) $row->c;
        }

        $aggregate = array_fill_keys(JourneyAsset::STATUSES, 0);
        foreach ($statusByJourney as $perJourney) {
            foreach (JourneyAsset::STATUSES as $status) {
                $aggregate[$status] += $perJourney[$status];
            }
        }
        $aggregate['total'] = array_sum($aggregate);

        $pipelineSum = $journeys->whereNotNull('linked_pipeline_value')->sum('linked_pipeline_value');
        $winRateValues = $journeys->whereNotNull('linked_win_rate')->pluck('linked_win_rate');
        $deliveryValues = $journeys->whereNotNull('delivery_zero_trouble_rate')->pluck('delivery_zero_trouble_rate');
        $businessPillars = [
            'pipeline_value' => $pipelineSum > 0 ? $pipelineSum : null,
            'win_rate_avg' => $winRateValues->count() > 0 ? round($winRateValues->avg(), 1) : null,
            'delivery_excellence_avg' => $deliveryValues->count() > 0 ? round($deliveryValues->avg(), 1) : null,
            'pipeline_journey_count' => $journeys->whereNotNull('linked_pipeline_value')->count(),
            'win_rate_journey_count' => $winRateValues->count(),
            'delivery_journey_count' => $deliveryValues->count(),
        ];

        $recentChanges = AssetStatusHistory::query()
            ->whereHas('journeyAsset', fn ($q) => $q->whereIn('journey_id', $journeyIds))
            ->with(['journeyAsset.journey', 'changedByUser'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('my-journeys.index', compact(
            'journeys',
            'statusByJourney',
            'aggregate',
            'recentChanges',
            'businessPillars',
        ));
    }
}
