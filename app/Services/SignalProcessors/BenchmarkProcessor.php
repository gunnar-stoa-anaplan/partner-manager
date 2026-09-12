<?php

namespace App\Services\SignalProcessors;

use App\Contracts\SignalProcessor;
use App\Models\Certification;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\PartnerHealthSnapshot;
use Illuminate\Support\Collection;

class BenchmarkProcessor implements SignalProcessor
{
    public function name(): string
    {
        return 'benchmark';
    }

    public function channel(): string
    {
        return 'benchmark';
    }

    public function cadence(): string
    {
        return 'weekly';
    }

    public function describe(): array
    {
        return [
            'description' => 'Compares individual partner metrics (health, pipeline, certifications) against portfolio-wide averages. Identifies significant over- or under-performers.',
            'data_sources' => ['PartnerHealthSnapshot (latest score)', 'Opportunity (open pipeline per partner)', 'Certification (active count per partner)', 'Portfolio-wide averages computed at runtime'],
            'signal_types' => [
                'health_below_portfolio' => [
                    'severity' => '≥ 40% below avg → critical, ≥ 25% → warning',
                    'description' => 'Partner health score is significantly below the portfolio average.',
                ],
                'health_above_portfolio' => [
                    'severity' => 'positive',
                    'description' => 'Partner health score is ≥ 25% above the portfolio average.',
                ],
                'pipeline_below_portfolio' => [
                    'severity' => 'warning',
                    'description' => 'Partner open pipeline is ≥ 50% below the portfolio average.',
                ],
                'certifications_below_portfolio' => [
                    'severity' => 'warning',
                    'description' => 'Active certification count is ≥ 50% below the portfolio average.',
                ],
            ],
            'thresholds' => [
                'health_below_warning' => '≥ 25% below portfolio average',
                'health_below_critical' => '≥ 40% below portfolio average',
                'health_above_positive' => '≥ 25% above portfolio average',
                'pipeline_below_warning' => '≥ 50% below portfolio average',
                'certs_below_warning' => '≥ 50% below portfolio average',
                'min_partners' => '3 partners required for portfolio averages',
                'expiry' => '14 days',
            ],
        ];
    }

    public function process(Partner $partner): Collection
    {
        $signals = collect();

        $portfolioMetrics = $this->computePortfolioAverages();

        if (! $portfolioMetrics) {
            return $signals;
        }

        $this->checkHealthBenchmark($partner, $portfolioMetrics, $signals);
        $this->checkPipelineBenchmark($partner, $portfolioMetrics, $signals);
        $this->checkCertificationBenchmark($partner, $portfolioMetrics, $signals);

        return $signals;
    }

    /**
     * @return array{avg_health: float, avg_pipeline: float, avg_certs: float, partner_count: int}|null
     */
    protected function computePortfolioAverages(): ?array
    {
        $partnerCount = Partner::count();

        if ($partnerCount < 3) {
            return null;
        }

        // Portfolio average — spans the full population regardless of the
        // current actor's row-level visibility.
        $avgHealth = PartnerHealthSnapshot::query()
            ->withoutGlobalScope('actor_visibility')
            ->where('snapshot_date', '>=', now()->subDays(7))
            ->selectRaw('AVG(score) as avg_score')
            ->value('avg_score');

        $avgPipeline = Opportunity::query()
            ->selectRaw('AVG(partner_totals.total) as avg_pipeline')
            ->fromSub(
                Opportunity::query()
                    ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
                    ->groupBy('partner_id')
                    ->selectRaw('partner_id, SUM(amount) as total'),
                'partner_totals'
            )
            ->value('avg_pipeline');

        $avgCerts = Certification::query()
            ->selectRaw('AVG(partner_totals.total) as avg_certs')
            ->fromSub(
                Certification::query()
                    ->where('status', 'active')
                    ->groupBy('partner_id')
                    ->selectRaw('partner_id, COUNT(*) as total'),
                'partner_totals'
            )
            ->value('avg_certs');

        return [
            'avg_health' => (float) ($avgHealth ?? 0),
            'avg_pipeline' => (float) ($avgPipeline ?? 0),
            'avg_certs' => (float) ($avgCerts ?? 0),
            'partner_count' => $partnerCount,
        ];
    }

    protected function checkHealthBenchmark(Partner $partner, array $metrics, Collection $signals): void
    {
        $latestSnapshot = PartnerHealthSnapshot::where('partner_id', $partner->id)
            ->orderByDesc('snapshot_date')
            ->first();

        if (! $latestSnapshot || $metrics['avg_health'] <= 0) {
            return;
        }

        $deviation = $latestSnapshot->score - $metrics['avg_health'];
        $deviationPct = ($deviation / $metrics['avg_health']) * 100;

        if ($deviationPct <= -25) {
            $signals->push([
                'channel' => 'benchmark',
                'type' => 'health_below_portfolio',
                'severity' => $deviationPct <= -40 ? 'critical' : 'warning',
                'title' => "Health score significantly below portfolio average for {$partner->name}",
                'description' => sprintf(
                    'Health score of %.1f is %.1f%% below the portfolio average of %.1f (across %d partners).',
                    $latestSnapshot->score,
                    abs($deviationPct),
                    $metrics['avg_health'],
                    $metrics['partner_count']
                ),
                'recommended_action' => 'Identify the lowest-scoring health dimensions and create targeted improvement plans.',
                'confidence_score' => 0.75,
                'source_processor' => 'benchmark',
                'data' => [
                    'partner_score' => $latestSnapshot->score,
                    'portfolio_avg' => round($metrics['avg_health'], 1),
                    'deviation_pct' => round($deviationPct, 1),
                    'benchmark_source' => 'portfolio',
                ],
                'expires_at' => now()->addDays(14),
            ]);
        }

        if ($deviationPct >= 25) {
            $signals->push([
                'channel' => 'benchmark',
                'type' => 'health_above_portfolio',
                'severity' => 'positive',
                'title' => "Health score well above portfolio average for {$partner->name}",
                'description' => sprintf(
                    'Health score of %.1f is %.1f%% above the portfolio average of %.1f. This partner is a top performer.',
                    $latestSnapshot->score,
                    $deviationPct,
                    $metrics['avg_health']
                ),
                'recommended_action' => 'Recognize this partner as a benchmark. Consider featuring in partner success stories.',
                'confidence_score' => 0.75,
                'source_processor' => 'benchmark',
                'data' => [
                    'partner_score' => $latestSnapshot->score,
                    'portfolio_avg' => round($metrics['avg_health'], 1),
                    'deviation_pct' => round($deviationPct, 1),
                    'benchmark_source' => 'portfolio',
                ],
                'expires_at' => now()->addDays(14),
            ]);
        }
    }

    protected function checkPipelineBenchmark(Partner $partner, array $metrics, Collection $signals): void
    {
        if ($metrics['avg_pipeline'] <= 0) {
            return;
        }

        $partnerPipeline = Opportunity::where('partner_id', $partner->id)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->sum('amount');

        $deviationPct = (($partnerPipeline - $metrics['avg_pipeline']) / $metrics['avg_pipeline']) * 100;

        if ($deviationPct <= -50) {
            $signals->push([
                'channel' => 'benchmark',
                'type' => 'pipeline_below_portfolio',
                'severity' => 'warning',
                'title' => "Pipeline significantly below portfolio average for {$partner->name}",
                'description' => sprintf(
                    'Open pipeline of $%s is %.0f%% below the portfolio average of $%s.',
                    number_format($partnerPipeline),
                    abs($deviationPct),
                    number_format($metrics['avg_pipeline'])
                ),
                'recommended_action' => 'Assess whether this partner needs pipeline development support or demand generation assistance.',
                'confidence_score' => 0.70,
                'source_processor' => 'benchmark',
                'data' => [
                    'partner_pipeline' => $partnerPipeline,
                    'portfolio_avg' => round($metrics['avg_pipeline'], 2),
                    'deviation_pct' => round($deviationPct, 1),
                    'benchmark_source' => 'portfolio',
                ],
                'expires_at' => now()->addDays(14),
            ]);
        }
    }

    protected function checkCertificationBenchmark(Partner $partner, array $metrics, Collection $signals): void
    {
        if ($metrics['avg_certs'] <= 0) {
            return;
        }

        $partnerCerts = Certification::where('partner_id', $partner->id)
            ->where('status', 'active')
            ->count();

        $deviationPct = (($partnerCerts - $metrics['avg_certs']) / $metrics['avg_certs']) * 100;

        if ($deviationPct <= -50) {
            $signals->push([
                'channel' => 'benchmark',
                'type' => 'certifications_below_portfolio',
                'severity' => 'warning',
                'title' => "Certification density below portfolio average for {$partner->name}",
                'description' => sprintf(
                    '%d active certifications vs portfolio average of %.1f. Low certification density limits delivery capability.',
                    $partnerCerts,
                    $metrics['avg_certs']
                ),
                'recommended_action' => 'Create a certification roadmap for this partner. Identify key gaps by product area and level.',
                'confidence_score' => 0.70,
                'source_processor' => 'benchmark',
                'data' => [
                    'partner_certs' => $partnerCerts,
                    'portfolio_avg' => round($metrics['avg_certs'], 1),
                    'deviation_pct' => round($deviationPct, 1),
                    'benchmark_source' => 'portfolio',
                ],
                'expires_at' => now()->addDays(14),
            ]);
        }
    }
}
