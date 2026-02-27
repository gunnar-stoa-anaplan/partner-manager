<?php

namespace App\Services\SignalProcessors;

use App\Contracts\SignalProcessor;
use App\Models\ContactInteraction;
use App\Models\Partner;
use Illuminate\Support\Collection;

class EngagementAnomalyProcessor implements SignalProcessor
{
    public function name(): string
    {
        return 'engagement_anomaly';
    }

    public function channel(): string
    {
        return 'anomaly';
    }

    public function cadence(): string
    {
        return 'daily';
    }

    public function describe(): array
    {
        return [
            'description' => 'Detects engagement anomalies by comparing recent 30-day interaction counts against a 90-day baseline. Identifies drops, spikes, and dormant partners.',
            'data_sources' => ['ContactInteraction (30d recent, 90d rolling baseline, last interaction date)'],
            'signal_types' => [
                'engagement_drop' => [
                    'severity' => 'Zero interactions → critical, otherwise warning',
                    'description' => 'Recent 30-day interactions dropped to ≤ 30% of the 90-day monthly average.',
                ],
                'engagement_spike' => [
                    'severity' => 'info',
                    'description' => 'Recent 30-day interactions exceed 3x the 90-day monthly average.',
                ],
                'engagement_dormant' => [
                    'severity' => '> 180 days → critical, > 90 days → warning',
                    'description' => 'No recorded interactions for an extended period.',
                ],
            ],
            'thresholds' => [
                'drop_threshold' => '≤ 30% of 90-day monthly average (min 2 avg required)',
                'spike_threshold' => '> 3x the 90-day monthly average',
                'dormant_warning' => '> 90 days since last interaction',
                'dormant_critical' => '> 180 days since last interaction',
                'baseline_window' => '90 days (days 31-120, divided by 3 months)',
                'expiry' => '5 days (drop/dormant), 7 days (spike)',
            ],
        ];
    }

    public function process(Partner $partner): Collection
    {
        $signals = collect();

        $recent30d = ContactInteraction::where('partner_id', $partner->id)
            ->where('interaction_date', '>=', now()->subDays(30))
            ->count();

        $rolling90d = ContactInteraction::where('partner_id', $partner->id)
            ->where('interaction_date', '>=', now()->subDays(120))
            ->where('interaction_date', '<', now()->subDays(30))
            ->count();

        $monthlyAvg90d = $rolling90d / 3;

        if ($monthlyAvg90d >= 2 && $recent30d <= $monthlyAvg90d * 0.3) {
            $signals->push([
                'channel' => 'anomaly',
                'type' => 'engagement_drop',
                'severity' => $recent30d === 0 ? 'critical' : 'warning',
                'title' => "Engagement drop detected for {$partner->name}",
                'description' => sprintf(
                    'Only %d interactions in the last 30 days vs a 90-day monthly average of %.1f. This is a %.0f%% decrease.',
                    $recent30d,
                    $monthlyAvg90d,
                    $monthlyAvg90d > 0 ? ((1 - ($recent30d / $monthlyAvg90d)) * 100) : 0
                ),
                'recommended_action' => 'Schedule an immediate check-in. Sudden engagement drops often precede churn or relationship issues.',
                'confidence_score' => $recent30d === 0 ? 0.90 : 0.80,
                'source_processor' => 'engagement_anomaly',
                'data' => [
                    'recent_30d_count' => $recent30d,
                    'monthly_avg_90d' => round($monthlyAvg90d, 1),
                    'rolling_90d_total' => $rolling90d,
                ],
                'expires_at' => now()->addDays(5),
            ]);
        }

        if ($monthlyAvg90d > 0 && $recent30d > $monthlyAvg90d * 3) {
            $signals->push([
                'channel' => 'anomaly',
                'type' => 'engagement_spike',
                'severity' => 'info',
                'title' => "Unusual engagement spike for {$partner->name}",
                'description' => sprintf(
                    '%d interactions in the last 30 days vs a 90-day monthly average of %.1f. Investigate whether this reflects a campaign, escalation, or organic growth.',
                    $recent30d,
                    $monthlyAvg90d
                ),
                'recommended_action' => 'Review interaction types to understand the driver. If campaign-related, measure effectiveness. If escalation-related, investigate.',
                'confidence_score' => 0.70,
                'source_processor' => 'engagement_anomaly',
                'data' => [
                    'recent_30d_count' => $recent30d,
                    'monthly_avg_90d' => round($monthlyAvg90d, 1),
                    'spike_multiplier' => round($recent30d / $monthlyAvg90d, 1),
                ],
                'expires_at' => now()->addDays(7),
            ]);
        }

        $lastInteraction = ContactInteraction::where('partner_id', $partner->id)
            ->orderByDesc('interaction_date')
            ->first();

        if ($lastInteraction && $lastInteraction->interaction_date->lt(now()->subDays(90))) {
            $daysSince = $lastInteraction->interaction_date->diffInDays(now());

            $signals->push([
                'channel' => 'anomaly',
                'type' => 'engagement_dormant',
                'severity' => $daysSince > 180 ? 'critical' : 'warning',
                'title' => "No engagement in {$daysSince} days for {$partner->name}",
                'description' => sprintf(
                    'Last interaction was %d days ago (%s). Extended periods without engagement may indicate relationship risk.',
                    $daysSince,
                    $lastInteraction->interaction_date->toDateString()
                ),
                'recommended_action' => 'Initiate outreach immediately. Schedule a QBR or casual check-in to re-establish engagement.',
                'confidence_score' => 0.85,
                'source_processor' => 'engagement_anomaly',
                'data' => [
                    'days_since_last' => $daysSince,
                    'last_interaction_date' => $lastInteraction->interaction_date->toDateString(),
                    'last_interaction_type' => $lastInteraction->interaction_type,
                ],
                'expires_at' => now()->addDays(7),
            ]);
        }

        return $signals;
    }
}
