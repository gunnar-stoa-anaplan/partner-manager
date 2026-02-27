<?php

namespace App\Services\SignalProcessors;

use App\Contracts\SignalProcessor;
use App\Models\AppSetting;
use App\Models\Certification;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\PartnerHealthSnapshot;
use Illuminate\Support\Collection;

class CohortBenchmarkProcessor implements SignalProcessor
{
    /** @var array{cohort_size: int, metrics: array, computed_date: string}|null */
    protected ?array $cohortBenchmarks = null;

    public function name(): string
    {
        return 'cohort_benchmark';
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
            'description' => 'Compares individual partner metrics against anonymized cohort benchmarks from similar organizations in the signal network. Cohort benchmarks are computed by the control plane from opted-in instances with similar partner portfolio size, ACV range, and offering type.',
            'data_sources' => [
                'Cohort benchmarks (pushed from control plane via agent)',
                'PartnerHealthSnapshot (latest score per partner)',
                'Opportunity (pipeline coverage and win rate per partner)',
                'Certification (active count per partner)',
            ],
            'signal_types' => [
                'health_below_cohort' => [
                    'severity' => 'Below p25 → critical, below median → warning',
                    'description' => 'Partner health score is below the cohort benchmark for similar organizations.',
                ],
                'health_above_cohort' => [
                    'severity' => 'positive',
                    'description' => 'Partner health score is at or above the cohort p75, placing them among top performers across similar organizations.',
                ],
                'pipeline_coverage_below_cohort' => [
                    'severity' => 'Below p25 → critical, below median → warning',
                    'description' => 'Partner pipeline coverage ratio is below the cohort benchmark.',
                ],
                'pipeline_coverage_above_cohort' => [
                    'severity' => 'positive',
                    'description' => 'Partner pipeline coverage ratio is at or above the cohort p75.',
                ],
                'win_rate_below_cohort' => [
                    'severity' => 'Below p25 → critical, below median → warning',
                    'description' => 'Partner win rate is below the cohort benchmark.',
                ],
                'win_rate_above_cohort' => [
                    'severity' => 'positive',
                    'description' => 'Partner win rate is at or above the cohort p75.',
                ],
                'cert_density_below_cohort' => [
                    'severity' => 'Below p25 → critical, below median → warning',
                    'description' => 'Active certification count is below the cohort benchmark for similar organizations.',
                ],
            ],
            'thresholds' => [
                'below_p25' => 'Critical — partner metric is in the bottom quartile of the cohort',
                'below_median' => 'Warning — partner metric is below the cohort median',
                'above_p75' => 'Positive — partner metric is in the top quartile of the cohort',
                'min_cohort_size' => '3 instances required for cohort benchmarks',
                'signal_sharing' => 'Must be enabled via Configuration → Signal Network',
                'expiry' => '14 days (aligns with weekly cohort recomputation)',
            ],
        ];
    }

    public function process(Partner $partner): Collection
    {
        $signals = collect();

        $benchmarks = $this->getCohortBenchmarks();

        if (! $benchmarks || empty($benchmarks['metrics'])) {
            return $signals;
        }

        $metrics = $benchmarks['metrics'];
        $cohortSize = $benchmarks['cohort_size'] ?? 0;

        if ($cohortSize < 3) {
            return $signals;
        }

        $this->checkHealthScore($partner, $metrics, $cohortSize, $signals);
        $this->checkPipelineCoverage($partner, $metrics, $cohortSize, $signals);
        $this->checkWinRate($partner, $metrics, $cohortSize, $signals);
        $this->checkCertificationDensity($partner, $metrics, $cohortSize, $signals);

        return $signals;
    }

    protected function checkHealthScore(Partner $partner, array $metrics, int $cohortSize, Collection $signals): void
    {
        if (! isset($metrics['avg_health_score'])) {
            return;
        }

        $snapshot = PartnerHealthSnapshot::where('partner_id', $partner->id)
            ->orderByDesc('snapshot_date')
            ->first();

        if (! $snapshot) {
            return;
        }

        $score = $snapshot->score;
        $cohort = $metrics['avg_health_score'];

        if ($score < $cohort['p25']) {
            $signals->push($this->buildSignal(
                partner: $partner,
                type: 'health_below_cohort',
                severity: 'critical',
                title: "Health score in bottom quartile of industry cohort for {$partner->name}",
                description: sprintf(
                    'Health score of %.1f is below the cohort 25th percentile (%.1f). The cohort median is %.1f and top performers achieve %.1f (based on %d similar organizations).',
                    $score,
                    $cohort['p25'],
                    $cohort['median'],
                    $cohort['p75'],
                    $cohortSize
                ),
                action: 'Prioritize health improvement — this partner is in the bottom quartile compared to similar organizations across the industry. Identify the lowest-scoring dimensions and create targeted plans.',
                confidence: 0.70,
                data: [
                    'partner_score' => $score,
                    'cohort_p25' => $cohort['p25'],
                    'cohort_median' => $cohort['median'],
                    'cohort_p75' => $cohort['p75'],
                    'cohort_size' => $cohortSize,
                ],
            ));
        } elseif ($score < $cohort['median']) {
            $signals->push($this->buildSignal(
                partner: $partner,
                type: 'health_below_cohort',
                severity: 'warning',
                title: "Health score below industry cohort median for {$partner->name}",
                description: sprintf(
                    'Health score of %.1f is below the cohort median of %.1f. Top performers achieve %.1f (based on %d similar organizations).',
                    $score,
                    $cohort['median'],
                    $cohort['p75'],
                    $cohortSize
                ),
                action: 'Consider targeted improvements to move this partner above the industry median. Review which health dimensions have the most room for growth.',
                confidence: 0.65,
                data: [
                    'partner_score' => $score,
                    'cohort_p25' => $cohort['p25'],
                    'cohort_median' => $cohort['median'],
                    'cohort_p75' => $cohort['p75'],
                    'cohort_size' => $cohortSize,
                ],
            ));
        } elseif ($score >= $cohort['p75']) {
            $signals->push($this->buildSignal(
                partner: $partner,
                type: 'health_above_cohort',
                severity: 'positive',
                title: "Health score in top quartile of industry cohort for {$partner->name}",
                description: sprintf(
                    'Health score of %.1f is at or above the cohort 75th percentile (%.1f). This partner outperforms most peers across %d similar organizations.',
                    $score,
                    $cohort['p75'],
                    $cohortSize
                ),
                action: 'Recognize this partner as an industry-leading performer. Consider featuring in case studies or partner advisory boards.',
                confidence: 0.70,
                data: [
                    'partner_score' => $score,
                    'cohort_p25' => $cohort['p25'],
                    'cohort_median' => $cohort['median'],
                    'cohort_p75' => $cohort['p75'],
                    'cohort_size' => $cohortSize,
                ],
            ));
        }
    }

    protected function checkPipelineCoverage(Partner $partner, array $metrics, int $cohortSize, Collection $signals): void
    {
        if (! isset($metrics['avg_pipeline_coverage'])) {
            return;
        }

        $openPipeline = Opportunity::where('partner_id', $partner->id)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->sum('amount');

        $closedWon12m = Opportunity::where('partner_id', $partner->id)
            ->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subYear())
            ->sum('amount');

        if ($closedWon12m <= 0) {
            return;
        }

        $coverage = $openPipeline / $closedWon12m;
        $cohort = $metrics['avg_pipeline_coverage'];

        if ($coverage < $cohort['p25']) {
            $signals->push($this->buildSignal(
                partner: $partner,
                type: 'pipeline_coverage_below_cohort',
                severity: 'critical',
                title: "Pipeline coverage in bottom quartile of industry cohort for {$partner->name}",
                description: sprintf(
                    'Pipeline coverage of %.1fx is below the cohort 25th percentile (%.1fx). The cohort median is %.1fx and top performers achieve %.1fx (based on %d similar organizations).',
                    $coverage,
                    $cohort['p25'],
                    $cohort['median'],
                    $cohort['p75'],
                    $cohortSize
                ),
                action: 'Pipeline coverage is critically low compared to industry peers. Prioritize demand generation and pipeline development programs for this partner.',
                confidence: 0.75,
                data: [
                    'partner_coverage' => round($coverage, 2),
                    'open_pipeline' => $openPipeline,
                    'closed_won_12m' => $closedWon12m,
                    'cohort_p25' => $cohort['p25'],
                    'cohort_median' => $cohort['median'],
                    'cohort_p75' => $cohort['p75'],
                    'cohort_size' => $cohortSize,
                ],
            ));
        } elseif ($coverage < $cohort['median']) {
            $signals->push($this->buildSignal(
                partner: $partner,
                type: 'pipeline_coverage_below_cohort',
                severity: 'warning',
                title: "Pipeline coverage below industry cohort median for {$partner->name}",
                description: sprintf(
                    'Pipeline coverage of %.1fx is below the cohort median of %.1fx. Top performers achieve %.1fx (based on %d similar organizations).',
                    $coverage,
                    $cohort['median'],
                    $cohort['p75'],
                    $cohortSize
                ),
                action: 'Consider joint demand generation activities to strengthen pipeline relative to industry peers.',
                confidence: 0.65,
                data: [
                    'partner_coverage' => round($coverage, 2),
                    'open_pipeline' => $openPipeline,
                    'closed_won_12m' => $closedWon12m,
                    'cohort_p25' => $cohort['p25'],
                    'cohort_median' => $cohort['median'],
                    'cohort_p75' => $cohort['p75'],
                    'cohort_size' => $cohortSize,
                ],
            ));
        } elseif ($coverage >= $cohort['p75']) {
            $signals->push($this->buildSignal(
                partner: $partner,
                type: 'pipeline_coverage_above_cohort',
                severity: 'positive',
                title: "Pipeline coverage in top quartile of industry cohort for {$partner->name}",
                description: sprintf(
                    'Pipeline coverage of %.1fx is at or above the cohort 75th percentile (%.1fx), outperforming most peers across %d similar organizations.',
                    $coverage,
                    $cohort['p75'],
                    $cohortSize
                ),
                action: 'Strong pipeline coverage relative to industry. Focus on conversion and deal velocity to capitalize on this advantage.',
                confidence: 0.75,
                data: [
                    'partner_coverage' => round($coverage, 2),
                    'open_pipeline' => $openPipeline,
                    'closed_won_12m' => $closedWon12m,
                    'cohort_p25' => $cohort['p25'],
                    'cohort_median' => $cohort['median'],
                    'cohort_p75' => $cohort['p75'],
                    'cohort_size' => $cohortSize,
                ],
            ));
        }
    }

    protected function checkWinRate(Partner $partner, array $metrics, int $cohortSize, Collection $signals): void
    {
        if (! isset($metrics['avg_win_rate'])) {
            return;
        }

        $won = Opportunity::where('partner_id', $partner->id)
            ->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subYear())
            ->count();

        $lost = Opportunity::where('partner_id', $partner->id)
            ->where('stage', 'Closed Lost')
            ->where('close_date', '>=', now()->subYear())
            ->count();

        $total = $won + $lost;

        if ($total < 3) {
            return;
        }

        $winRate = ($won / $total) * 100;
        $cohort = $metrics['avg_win_rate'];

        if ($winRate < $cohort['p25']) {
            $signals->push($this->buildSignal(
                partner: $partner,
                type: 'win_rate_below_cohort',
                severity: 'critical',
                title: "Win rate in bottom quartile of industry cohort for {$partner->name}",
                description: sprintf(
                    'Win rate of %.1f%% (%d/%d deals) is below the cohort 25th percentile (%.1f%%). The cohort median is %.1f%% (based on %d similar organizations).',
                    $winRate,
                    $won,
                    $total,
                    $cohort['p25'],
                    $cohort['median'],
                    $cohortSize
                ),
                action: 'Win rate is critically below industry peers. Investigate deal qualification, competitive positioning, and sales enablement gaps.',
                confidence: 0.75,
                data: [
                    'partner_win_rate' => round($winRate, 1),
                    'deals_won' => $won,
                    'deals_lost' => $lost,
                    'deals_total' => $total,
                    'cohort_p25' => $cohort['p25'],
                    'cohort_median' => $cohort['median'],
                    'cohort_p75' => $cohort['p75'],
                    'cohort_size' => $cohortSize,
                ],
            ));
        } elseif ($winRate < $cohort['median']) {
            $signals->push($this->buildSignal(
                partner: $partner,
                type: 'win_rate_below_cohort',
                severity: 'warning',
                title: "Win rate below industry cohort median for {$partner->name}",
                description: sprintf(
                    'Win rate of %.1f%% (%d/%d deals) is below the cohort median of %.1f%%. Top performers achieve %.1f%% (based on %d similar organizations).',
                    $winRate,
                    $won,
                    $total,
                    $cohort['median'],
                    $cohort['p75'],
                    $cohortSize
                ),
                action: 'Review deal qualification criteria and competitive win/loss patterns to improve win rate toward the industry median.',
                confidence: 0.65,
                data: [
                    'partner_win_rate' => round($winRate, 1),
                    'deals_won' => $won,
                    'deals_lost' => $lost,
                    'deals_total' => $total,
                    'cohort_p25' => $cohort['p25'],
                    'cohort_median' => $cohort['median'],
                    'cohort_p75' => $cohort['p75'],
                    'cohort_size' => $cohortSize,
                ],
            ));
        } elseif ($winRate >= $cohort['p75']) {
            $signals->push($this->buildSignal(
                partner: $partner,
                type: 'win_rate_above_cohort',
                severity: 'positive',
                title: "Win rate in top quartile of industry cohort for {$partner->name}",
                description: sprintf(
                    'Win rate of %.1f%% (%d/%d deals) is at or above the cohort 75th percentile (%.1f%%), outperforming most peers across %d similar organizations.',
                    $winRate,
                    $won,
                    $total,
                    $cohort['p75'],
                    $cohortSize
                ),
                action: 'Excellent win rate relative to industry. Study this partner\'s sales approach for best practices to share across the portfolio.',
                confidence: 0.75,
                data: [
                    'partner_win_rate' => round($winRate, 1),
                    'deals_won' => $won,
                    'deals_lost' => $lost,
                    'deals_total' => $total,
                    'cohort_p25' => $cohort['p25'],
                    'cohort_median' => $cohort['median'],
                    'cohort_p75' => $cohort['p75'],
                    'cohort_size' => $cohortSize,
                ],
            ));
        }
    }

    protected function checkCertificationDensity(Partner $partner, array $metrics, int $cohortSize, Collection $signals): void
    {
        if (! isset($metrics['certification_density'])) {
            return;
        }

        $activeCerts = Certification::where('partner_id', $partner->id)
            ->where('status', 'active')
            ->count();

        $cohort = $metrics['certification_density'];

        if ($activeCerts < $cohort['p25']) {
            $signals->push($this->buildSignal(
                partner: $partner,
                type: 'cert_density_below_cohort',
                severity: 'critical',
                title: "Certification density in bottom quartile of industry cohort for {$partner->name}",
                description: sprintf(
                    '%d active certifications is below the cohort 25th percentile (%.1f). The cohort median is %.1f (based on %d similar organizations).',
                    $activeCerts,
                    $cohort['p25'],
                    $cohort['median'],
                    $cohortSize
                ),
                action: 'Certification count is critically below industry peers. Create a certification roadmap targeting the highest-impact product areas.',
                confidence: 0.70,
                data: [
                    'partner_certs' => $activeCerts,
                    'cohort_p25' => $cohort['p25'],
                    'cohort_median' => $cohort['median'],
                    'cohort_p75' => $cohort['p75'],
                    'cohort_size' => $cohortSize,
                ],
            ));
        } elseif ($activeCerts < $cohort['median']) {
            $signals->push($this->buildSignal(
                partner: $partner,
                type: 'cert_density_below_cohort',
                severity: 'warning',
                title: "Certification density below industry cohort median for {$partner->name}",
                description: sprintf(
                    '%d active certifications is below the cohort median of %.1f. Top performers achieve %.1f (based on %d similar organizations).',
                    $activeCerts,
                    $cohort['median'],
                    $cohort['p75'],
                    $cohortSize
                ),
                action: 'Consider investing in certification programs to bring this partner in line with industry peers.',
                confidence: 0.60,
                data: [
                    'partner_certs' => $activeCerts,
                    'cohort_p25' => $cohort['p25'],
                    'cohort_median' => $cohort['median'],
                    'cohort_p75' => $cohort['p75'],
                    'cohort_size' => $cohortSize,
                ],
            ));
        }
    }

    /**
     * @return array{cohort_size: int, metrics: array, computed_date: string}|null
     */
    protected function getCohortBenchmarks(): ?array
    {
        if ($this->cohortBenchmarks !== null) {
            return $this->cohortBenchmarks;
        }

        $raw = AppSetting::get('signal_engine_benchmarks');

        if (! $raw) {
            return null;
        }

        $this->cohortBenchmarks = is_string($raw) ? json_decode($raw, true) : (is_array($raw) ? $raw : null);

        return $this->cohortBenchmarks;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function buildSignal(
        Partner $partner,
        string $type,
        string $severity,
        string $title,
        string $description,
        string $action,
        float $confidence,
        array $data,
    ): array {
        return [
            'channel' => 'benchmark',
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'recommended_action' => $action,
            'confidence_score' => $confidence,
            'source_processor' => 'cohort_benchmark',
            'data' => $data,
            'expires_at' => now()->addDays(14),
        ];
    }
}
