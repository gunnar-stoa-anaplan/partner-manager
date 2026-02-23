<?php

namespace App\Services\SignalProcessors;

use App\Contracts\SignalProcessor;
use App\Models\Partner;
use App\Models\PartnerHealthSnapshot;
use App\Services\PartnerHealthService;
use Illuminate\Support\Collection;

class HealthTrendProcessor implements SignalProcessor
{
    public function __construct(
        protected PartnerHealthService $healthService,
    ) {}

    public function name(): string
    {
        return 'health_trend';
    }

    public function channel(): string
    {
        return 'trend';
    }

    public function cadence(): string
    {
        return 'daily';
    }

    public function describe(): array
    {
        return [
            'description' => 'Detects health score trends over 7-day and 30-day windows. Fires declining signals when the score drops, and improving signals when the score rises significantly.',
            'data_sources' => ['PartnerHealthSnapshot (7d and 30d history)', 'PartnerHealthService (current score)'],
            'signal_types' => [
                'health_declining_7d' => [
                    'severity' => 'Drop ≥ 10pts → critical, ≥ 5pts → warning',
                    'description' => 'Health score dropped significantly vs the 7-day average.',
                ],
                'health_declining_30d' => [
                    'severity' => 'Drop ≥ 15pts → critical, ≥ 10pts → warning',
                    'description' => 'Sustained health score decline over 30 days.',
                ],
                'health_improving_30d' => [
                    'severity' => 'positive',
                    'description' => 'Health score improved ≥ 10 points over 30 days.',
                ],
            ],
            'thresholds' => [
                '7d_decline_warning' => '≥ 5 point drop from 7-day average',
                '7d_decline_critical' => '≥ 10 point drop from 7-day average',
                '7d_min_snapshots' => '3 snapshots required',
                '30d_decline_warning' => '≥ 10 point drop from 30-day average',
                '30d_decline_critical' => '≥ 15 point drop from 30-day average',
                '30d_improvement' => '≥ 10 point rise from 30-day average',
                '30d_min_snapshots' => '10 snapshots required',
                'confidence' => '0.60 + 0.05/snapshot (7d), 0.70 + 0.01/snapshot (30d), max 0.95',
                'expiry' => '3 days (7d), 7 days (30d)',
            ],
        ];
    }

    public function process(Partner $partner): Collection
    {
        $signals = collect();

        $currentHealth = $this->healthService->computeForPartner($partner);
        $currentScore = $currentHealth['score'] ?? 0;

        $snapshots7d = PartnerHealthSnapshot::where('partner_id', $partner->id)
            ->where('snapshot_date', '>=', now()->subDays(7))
            ->orderBy('snapshot_date')
            ->get();

        $snapshots30d = PartnerHealthSnapshot::where('partner_id', $partner->id)
            ->where('snapshot_date', '>=', now()->subDays(30))
            ->orderBy('snapshot_date')
            ->get();

        if ($snapshots7d->count() >= 3) {
            $avg7d = $snapshots7d->avg('score');
            $delta7d = $currentScore - $avg7d;

            if ($delta7d <= -5) {
                $signals->push([
                    'channel' => 'trend',
                    'type' => 'health_declining_7d',
                    'severity' => $delta7d <= -10 ? 'critical' : 'warning',
                    'title' => "Health score declining rapidly for {$partner->name}",
                    'description' => sprintf(
                        'Health score dropped %.1f points in 7 days (%.1f → %.1f). Current risk level: %s.',
                        abs($delta7d),
                        $avg7d,
                        $currentScore,
                        $currentHealth['risk_level'] ?? 'unknown'
                    ),
                    'recommended_action' => 'Review partner engagement and schedule a check-in call to identify root causes.',
                    'confidence_score' => min(0.6 + ($snapshots7d->count() * 0.05), 0.95),
                    'source_processor' => 'health_trend',
                    'data' => [
                        'current_score' => $currentScore,
                        'avg_7d' => round($avg7d, 1),
                        'delta_7d' => round($delta7d, 1),
                        'risk_level' => $currentHealth['risk_level'] ?? 'unknown',
                        'dimensions' => $currentHealth['dimensions'] ?? [],
                    ],
                    'expires_at' => now()->addDays(3),
                ]);
            }
        }

        if ($snapshots30d->count() >= 10) {
            $avg30d = $snapshots30d->avg('score');
            $delta30d = $currentScore - $avg30d;

            if ($delta30d <= -10) {
                $signals->push([
                    'channel' => 'trend',
                    'type' => 'health_declining_30d',
                    'severity' => $delta30d <= -15 ? 'critical' : 'warning',
                    'title' => "Sustained health decline for {$partner->name}",
                    'description' => sprintf(
                        'Health score dropped %.1f points over 30 days (%.1f → %.1f). This indicates a persistent issue requiring attention.',
                        abs($delta30d),
                        $avg30d,
                        $currentScore
                    ),
                    'recommended_action' => 'Conduct root cause analysis across all health dimensions and schedule a strategy review.',
                    'confidence_score' => min(0.7 + ($snapshots30d->count() * 0.01), 0.95),
                    'source_processor' => 'health_trend',
                    'data' => [
                        'current_score' => $currentScore,
                        'avg_30d' => round($avg30d, 1),
                        'delta_30d' => round($delta30d, 1),
                    ],
                    'expires_at' => now()->addDays(7),
                ]);
            }

            if ($delta30d >= 10) {
                $signals->push([
                    'channel' => 'opportunity',
                    'type' => 'health_improving_30d',
                    'severity' => 'positive',
                    'title' => "Strong health improvement for {$partner->name}",
                    'description' => sprintf(
                        'Health score improved %.1f points over 30 days (%.1f → %.1f). Partner is trending positively.',
                        $delta30d,
                        $avg30d,
                        $currentScore
                    ),
                    'recommended_action' => 'Consider this partner for tier upgrade or expanded responsibility.',
                    'confidence_score' => min(0.7 + ($snapshots30d->count() * 0.01), 0.95),
                    'source_processor' => 'health_trend',
                    'data' => [
                        'current_score' => $currentScore,
                        'avg_30d' => round($avg30d, 1),
                        'delta_30d' => round($delta30d, 1),
                    ],
                    'expires_at' => now()->addDays(7),
                ]);
            }
        }

        return $signals;
    }
}
