<?php

namespace App\Services\SignalProcessors;

use App\Contracts\SignalProcessor;
use App\Models\Opportunity;
use App\Models\Partner;
use Illuminate\Support\Collection;

class PipelineVelocityProcessor implements SignalProcessor
{
    public function name(): string
    {
        return 'pipeline_velocity';
    }

    public function channel(): string
    {
        return 'risk';
    }

    public function cadence(): string
    {
        return 'daily';
    }

    public function describe(): array
    {
        return [
            'description' => 'Monitors pipeline coverage ratio, identifies deals slipping past close date, and detects pipeline creation surges. Generates risk signals for weak pipeline and opportunity signals for momentum.',
            'data_sources' => ['Opportunity (open pipeline, closed-won trailing 12m, close dates, creation dates)'],
            'signal_types' => [
                'pipeline_coverage_low' => [
                    'severity' => 'Coverage < 1.0x → critical, < 1.5x → warning',
                    'description' => 'Open pipeline is below target coverage relative to trailing 12-month closed-won revenue.',
                ],
                'close_date_slip_cluster' => [
                    'severity' => '≥ 5 slipping → critical, ≥ 2 → warning',
                    'description' => 'Multiple opportunities have passed their expected close date.',
                ],
                'pipeline_creation_surge' => [
                    'severity' => 'positive',
                    'description' => 'Pipeline creation in the last 30 days exceeds the prior 30-day period by ≥ 50%.',
                ],
            ],
            'thresholds' => [
                'coverage_warning' => '< 1.5x (open pipeline / 12m closed-won)',
                'coverage_critical' => '< 1.0x',
                'healthy_coverage' => '≥ 3.0x',
                'slipping_warning' => '≥ 2 deals past close date',
                'slipping_critical' => '≥ 5 deals past close date',
                'surge_threshold' => '> 150% of prior 30-day creation count',
                'expiry' => '5 days (coverage/slip), 7 days (surge)',
            ],
        ];
    }

    public function process(Partner $partner): Collection
    {
        $signals = collect();

        $openPipeline = Opportunity::where('partner_id', $partner->id)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->sum('amount');

        $closedWon12m = Opportunity::where('partner_id', $partner->id)
            ->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subYear())
            ->sum('amount');

        if ($closedWon12m > 0) {
            $coverageRatio = $openPipeline / $closedWon12m;

            if ($coverageRatio < 1.5) {
                $signals->push([
                    'channel' => 'risk',
                    'type' => 'pipeline_coverage_low',
                    'severity' => $coverageRatio < 1.0 ? 'critical' : 'warning',
                    'title' => "Low pipeline coverage for {$partner->name}",
                    'description' => sprintf(
                        'Pipeline coverage ratio is %.1fx ($%s open vs $%s closed-won trailing 12m). Healthy coverage is typically 3x or higher.',
                        $coverageRatio,
                        number_format($openPipeline),
                        number_format($closedWon12m)
                    ),
                    'recommended_action' => 'Review pipeline development strategy. Consider joint demand generation activities or deal registration incentives.',
                    'confidence_score' => 0.85,
                    'source_processor' => 'pipeline_velocity',
                    'data' => [
                        'open_pipeline' => $openPipeline,
                        'closed_won_12m' => $closedWon12m,
                        'coverage_ratio' => round($coverageRatio, 2),
                    ],
                    'expires_at' => now()->addDays(5),
                ]);
            }
        }

        $slippingDeals = Opportunity::where('partner_id', $partner->id)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->where('close_date', '<', now())
            ->get();

        if ($slippingDeals->count() >= 2) {
            $totalSlipAmount = $slippingDeals->sum('amount');

            $signals->push([
                'channel' => 'risk',
                'type' => 'close_date_slip_cluster',
                'severity' => $slippingDeals->count() >= 5 ? 'critical' : 'warning',
                'title' => "{$slippingDeals->count()} deals past close date for {$partner->name}",
                'description' => sprintf(
                    '%d opportunities worth $%s have passed their expected close date. This pattern may indicate forecasting accuracy issues or deal execution challenges.',
                    $slippingDeals->count(),
                    number_format($totalSlipAmount)
                ),
                'recommended_action' => 'Review each slipping deal with the partner. Update close dates and validate pipeline accuracy.',
                'confidence_score' => 0.90,
                'source_processor' => 'pipeline_velocity',
                'data' => [
                    'slipping_count' => $slippingDeals->count(),
                    'slipping_amount' => $totalSlipAmount,
                    'deals' => $slippingDeals->map(fn ($o) => [
                        'id' => $o->id,
                        'name' => $o->opportunity_name,
                        'amount' => $o->amount,
                        'close_date' => $o->close_date?->toDateString(),
                        'stage' => $o->stage,
                    ])->take(10)->values()->toArray(),
                ],
                'related_entity_ids' => [
                    'opportunities' => $slippingDeals->pluck('id')->toArray(),
                ],
                'expires_at' => now()->addDays(5),
            ]);
        }

        $recentCreated = Opportunity::where('partner_id', $partner->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $previousCreated = Opportunity::where('partner_id', $partner->id)
            ->where('created_at', '>=', now()->subDays(60))
            ->where('created_at', '<', now()->subDays(30))
            ->count();

        if ($previousCreated > 0 && $recentCreated > $previousCreated * 1.5) {
            $signals->push([
                'channel' => 'opportunity',
                'type' => 'pipeline_creation_surge',
                'severity' => 'positive',
                'title' => "Pipeline creation surge for {$partner->name}",
                'description' => sprintf(
                    '%d new opportunities created in the last 30 days vs %d in the prior period — a %.0f%% increase.',
                    $recentCreated,
                    $previousCreated,
                    (($recentCreated - $previousCreated) / $previousCreated) * 100
                ),
                'recommended_action' => 'Reinforce momentum with additional enablement or executive engagement.',
                'confidence_score' => 0.80,
                'source_processor' => 'pipeline_velocity',
                'data' => [
                    'recent_30d' => $recentCreated,
                    'previous_30d' => $previousCreated,
                    'growth_pct' => round((($recentCreated - $previousCreated) / $previousCreated) * 100, 1),
                ],
                'expires_at' => now()->addDays(7),
            ]);
        }

        return $signals;
    }
}
