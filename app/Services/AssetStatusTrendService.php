<?php

namespace App\Services;

use App\Models\AssetStatusHistory;
use App\Models\Journey;
use App\Models\JourneyAsset;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AssetStatusTrendService
{
    /**
     * Compute a daily / weekly / monthly time-series of status counts for one journey.
     *
     * Returns a list of { bucket: 'YYYY-MM-DD', counts: ['missing'=>n, 'in_review'=>n, ...] }.
     * Each bucket carries the AS-OF status counts at the END of that period. The series is
     * derived by replaying status_history events forward from a baseline of all-missing.
     *
     * @return Collection<int, array{bucket: string, label: string, counts: array<string, int>}>
     */
    public function forJourney(Journey $journey, string $resolution = 'daily', int $window = 30): Collection
    {
        $resolution = in_array($resolution, ['daily', 'weekly', 'monthly'], true) ? $resolution : 'daily';

        $history = AssetStatusHistory::query()
            ->whereHas('journeyAsset', fn ($q) => $q->where('journey_id', $journey->id))
            ->orderBy('created_at')
            ->get(['journey_asset_id', 'old_status', 'new_status', 'created_at']);

        $assetIds = $journey->journeyAssets()->pluck('id');
        $perAssetStatus = [];
        foreach ($assetIds as $id) {
            $perAssetStatus[$id] = 'missing';
        }

        [$start, $end, $stride] = $this->windowBounds($resolution, $window);

        $eventsByBucket = [];
        foreach ($history as $event) {
            $bucketKey = $this->bucketKey(CarbonImmutable::parse($event->created_at), $resolution);
            $eventsByBucket[$bucketKey][] = $event;
        }

        $series = collect();
        $cursor = $start;
        while ($cursor->lessThanOrEqualTo($end)) {
            $bucketKey = $this->bucketKey($cursor, $resolution);
            foreach ($eventsByBucket[$bucketKey] ?? [] as $event) {
                if (isset($perAssetStatus[$event->journey_asset_id])) {
                    $perAssetStatus[$event->journey_asset_id] = $event->new_status;
                }
            }
            $counts = array_fill_keys(JourneyAsset::STATUSES, 0);
            foreach ($perAssetStatus as $status) {
                if (isset($counts[$status])) {
                    $counts[$status]++;
                }
            }
            $series->push([
                'bucket' => $bucketKey,
                'label' => $this->bucketLabel($cursor, $resolution),
                'counts' => $counts,
            ]);
            $cursor = $cursor->add($stride);
        }

        return $series;
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: \DateInterval}
     */
    protected function windowBounds(string $resolution, int $window): array
    {
        $today = CarbonImmutable::now()->startOfDay();

        return match ($resolution) {
            'weekly' => [
                $today->subWeeks($window)->startOfWeek(),
                $today->endOfWeek(),
                new \DateInterval('P1W'),
            ],
            'monthly' => [
                $today->subMonths($window)->startOfMonth(),
                $today->endOfMonth(),
                new \DateInterval('P1M'),
            ],
            default => [
                $today->subDays($window),
                $today,
                new \DateInterval('P1D'),
            ],
        };
    }

    protected function bucketKey(CarbonImmutable $when, string $resolution): string
    {
        return match ($resolution) {
            'weekly' => $when->startOfWeek()->format('Y-m-d'),
            'monthly' => $when->startOfMonth()->format('Y-m'),
            default => $when->format('Y-m-d'),
        };
    }

    protected function bucketLabel(CarbonImmutable $when, string $resolution): string
    {
        return match ($resolution) {
            'weekly' => 'Wk of '.$when->startOfWeek()->format('M j'),
            'monthly' => $when->format('M Y'),
            default => $when->format('M j'),
        };
    }
}
