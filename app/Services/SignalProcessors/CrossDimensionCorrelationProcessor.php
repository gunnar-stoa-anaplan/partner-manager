<?php

namespace App\Services\SignalProcessors;

use App\Contracts\SignalProcessor;
use App\Models\Partner;
use App\Models\Signal;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CrossDimensionCorrelationProcessor implements SignalProcessor
{
    public function name(): string
    {
        return 'cross_dimension_correlation';
    }

    public function channel(): string
    {
        return 'recommendation';
    }

    public function cadence(): string
    {
        return 'daily';
    }

    public function describe(): array
    {
        return [
            'description' => 'Correlates signals across multiple channels to identify compounding risk patterns or positive momentum clusters. Runs after all other processors and groups related signals.',
            'data_sources' => ['Signal (active signals from the last 7 days for each partner)'],
            'signal_types' => [
                'compounding_risk_pattern' => [
                    'severity' => 'critical',
                    'description' => 'Risk + Trend + Anomaly signals all present — compounding risks across multiple dimensions.',
                ],
                'escalating_risk_pattern' => [
                    'severity' => 'warning',
                    'description' => 'Risk + Trend signals present with ≥ 2 critical-severity signals.',
                ],
                'momentum_cluster' => [
                    'severity' => 'positive',
                    'description' => '≥ 2 opportunity signals detected — multi-dimensional positive momentum.',
                ],
            ],
            'thresholds' => [
                'compounding_risk' => 'Requires active signals in risk, trend, AND anomaly channels',
                'escalating_risk' => 'Requires risk + trend channels with ≥ 2 critical signals',
                'momentum_cluster' => '≥ 2 opportunity-channel signals',
                'min_signals' => '2 active signals in last 7 days to trigger any correlation',
                'confidence' => '0.70 + 0.03 per correlated signal (compounding), 0.65 + 0.05 per critical (escalating), 0.80 (momentum)',
                'expiry' => '5 days (risk), 7 days (momentum)',
            ],
        ];
    }

    public function process(Partner $partner): Collection
    {
        $signals = collect();

        $activeSignals = Signal::where('partner_id', $partner->id)
            ->active()
            ->where('created_at', '>=', now()->subDays(7))
            ->get();

        if ($activeSignals->count() < 2) {
            return $signals;
        }

        $channelGroups = $activeSignals->groupBy('channel');
        $criticalCount = $activeSignals->where('severity', 'critical')->count();
        $warningCount = $activeSignals->where('severity', 'warning')->count();

        $hasRisk = $channelGroups->has('risk');
        $hasTrend = $channelGroups->has('trend');
        $hasAnomaly = $channelGroups->has('anomaly');

        if ($hasRisk && $hasTrend && $hasAnomaly) {
            $correlationId = 'corr_'.Str::random(16);
            $relatedSignalIds = $activeSignals->pluck('id')->toArray();

            $this->tagSignalsWithCorrelation($relatedSignalIds, $correlationId);

            $signals->push([
                'channel' => 'recommendation',
                'type' => 'compounding_risk_pattern',
                'severity' => 'critical',
                'title' => "Compounding risk pattern detected for {$partner->name}",
                'description' => sprintf(
                    'Multiple risk indicators across %d dimensions: %s. When risks compound across pipeline, health, and engagement, intervention success rates decline rapidly.',
                    $channelGroups->count(),
                    $channelGroups->keys()->map(fn ($c) => Signal::CHANNELS[$c] ?? $c)->implode(', ')
                ),
                'recommended_action' => 'Convene a cross-functional review immediately. Assign a dedicated recovery plan with clear milestones and weekly check-ins.',
                'confidence_score' => min(0.70 + ($activeSignals->count() * 0.03), 0.95),
                'source_processor' => 'cross_dimension_correlation',
                'correlation_id' => $correlationId,
                'data' => [
                    'correlated_signal_count' => count($relatedSignalIds),
                    'channels_affected' => $channelGroups->keys()->toArray(),
                    'critical_count' => $criticalCount,
                    'warning_count' => $warningCount,
                    'signal_types' => $activeSignals->pluck('type')->unique()->values()->toArray(),
                ],
                'related_entity_ids' => [
                    'signals' => $relatedSignalIds,
                ],
                'expires_at' => now()->addDays(5),
            ]);
        }

        if ($hasRisk && $hasTrend && ! $hasAnomaly && $criticalCount >= 2) {
            $correlationId = 'corr_'.Str::random(16);
            $riskAndTrendIds = $activeSignals
                ->whereIn('channel', ['risk', 'trend'])
                ->pluck('id')
                ->toArray();

            $this->tagSignalsWithCorrelation($riskAndTrendIds, $correlationId);

            $signals->push([
                'channel' => 'recommendation',
                'type' => 'escalating_risk_pattern',
                'severity' => 'warning',
                'title' => "Escalating risk pattern for {$partner->name}",
                'description' => sprintf(
                    '%d active risk signals combined with declining trends suggest an accelerating problem. %d are critical severity.',
                    $channelGroups->get('risk', collect())->count(),
                    $criticalCount
                ),
                'recommended_action' => 'Schedule an urgent review with the partner management team. Prioritize the critical signals and create an action plan.',
                'confidence_score' => min(0.65 + ($criticalCount * 0.05), 0.90),
                'source_processor' => 'cross_dimension_correlation',
                'correlation_id' => $correlationId,
                'data' => [
                    'risk_count' => $channelGroups->get('risk', collect())->count(),
                    'trend_count' => $channelGroups->get('trend', collect())->count(),
                    'critical_count' => $criticalCount,
                ],
                'related_entity_ids' => [
                    'signals' => $riskAndTrendIds,
                ],
                'expires_at' => now()->addDays(5),
            ]);
        }

        $opportunitySignals = $channelGroups->get('opportunity', collect());
        if ($opportunitySignals->count() >= 2) {
            $correlationId = 'corr_'.Str::random(16);
            $oppSignalIds = $opportunitySignals->pluck('id')->toArray();

            $this->tagSignalsWithCorrelation($oppSignalIds, $correlationId);

            $signals->push([
                'channel' => 'recommendation',
                'type' => 'momentum_cluster',
                'severity' => 'positive',
                'title' => "Positive momentum cluster for {$partner->name}",
                'description' => sprintf(
                    '%d positive signals detected across enablement, pipeline, and engagement. This partner shows strong multi-dimensional growth.',
                    $opportunitySignals->count()
                ),
                'recommended_action' => 'Accelerate investment in this partner. Consider tier upgrade evaluation, expanded product areas, or co-marketing opportunities.',
                'confidence_score' => 0.80,
                'source_processor' => 'cross_dimension_correlation',
                'correlation_id' => $correlationId,
                'data' => [
                    'positive_signal_count' => $opportunitySignals->count(),
                    'signal_types' => $opportunitySignals->pluck('type')->unique()->values()->toArray(),
                ],
                'related_entity_ids' => [
                    'signals' => $oppSignalIds,
                ],
                'expires_at' => now()->addDays(7),
            ]);
        }

        return $signals;
    }

    protected function tagSignalsWithCorrelation(array $signalIds, string $correlationId): void
    {
        Signal::whereIn('id', $signalIds)
            ->whereNull('correlation_id')
            ->update(['correlation_id' => $correlationId]);
    }
}
