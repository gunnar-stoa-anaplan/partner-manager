<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Certification;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\PartnerHealthSnapshot;
use App\Models\Signal;

class SignalAggregateService
{
    /**
     * Build anonymized portfolio-level statistics for Tier 2.
     * No partner names, IDs, or deal amounts are included.
     *
     * @return array{profile: array, signal_counts: array, health_distribution: array, dimension_averages: array, portfolio_metrics: array, collected_at: string}|null
     */
    public function buildAggregate(): ?array
    {
        if (! AppSetting::get('signal_sharing_enabled', '0')) {
            return null;
        }
        $partnerCount = Partner::count();

        return [
            'profile' => $this->buildProfile($partnerCount),
            'signal_counts' => $this->buildSignalCounts(),
            'health_distribution' => $this->buildHealthDistribution(),
            'dimension_averages' => $this->buildDimensionAverages(),
            'portfolio_metrics' => $this->buildPortfolioMetrics($partnerCount),
            'collected_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array{partner_count_range: string, acv_range: string, offering_type: string|null, certification_density: float}
     */
    protected function buildProfile(int $partnerCount): array
    {
        $explicit = json_decode(AppSetting::get('instance_profile', '{}'), true) ?: [];

        $partnerCountRange = $explicit['partner_count_range'] ?? match (true) {
            $partnerCount <= 10 => '1-10',
            $partnerCount <= 50 => '10-50',
            $partnerCount <= 200 => '50-200',
            default => '200+',
        };

        $avgPipeline = Opportunity::query()
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->avg('amount') ?? 0;

        $acvRange = $explicit['acv_range'] ?? match (true) {
            $avgPipeline < 100000 => '0-100k',
            $avgPipeline < 500000 => '100k-500k',
            $avgPipeline < 2000000 => '500k-2m',
            default => '2m+',
        };

        $certDensity = $partnerCount > 0
            ? round(Certification::where('status', 'active')->count() / $partnerCount, 2)
            : 0;

        return [
            'partner_count_range' => $partnerCountRange,
            'acv_range' => $acvRange,
            'offering_type' => $explicit['offering_type'] ?? null,
            'certification_density' => $certDensity,
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function buildSignalCounts(): array
    {
        $counts = [];
        foreach (Signal::CHANNELS as $key => $label) {
            $counts[$key] = Signal::active()->where('channel', $key)->count();
        }
        $counts['total_active'] = Signal::active()->count();
        $counts['critical'] = Signal::active()->where('severity', 'critical')->count();

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    protected function buildHealthDistribution(): array
    {
        $latestSnapshots = PartnerHealthSnapshot::query()
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('partner_health_snapshots')
                    ->groupBy('partner_id');
            })
            ->get();

        return [
            'healthy' => $latestSnapshots->where('risk_level', 'healthy')->count(),
            'watch' => $latestSnapshots->where('risk_level', 'watch')->count(),
            'at_risk' => $latestSnapshots->where('risk_level', 'at_risk')->count(),
            'critical' => $latestSnapshots->where('risk_level', 'critical')->count(),
        ];
    }

    /**
     * @return array<string, float>
     */
    protected function buildDimensionAverages(): array
    {
        $latestSnapshots = PartnerHealthSnapshot::query()
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('partner_health_snapshots')
                    ->groupBy('partner_id');
            })
            ->whereNotNull('dimensions')
            ->get();

        if ($latestSnapshots->isEmpty()) {
            return [];
        }

        $dimensionTotals = [];
        $dimensionCounts = [];

        foreach ($latestSnapshots as $snapshot) {
            foreach ($snapshot->dimensions as $key => $value) {
                if (is_numeric($value)) {
                    $dimensionTotals[$key] = ($dimensionTotals[$key] ?? 0) + $value;
                    $dimensionCounts[$key] = ($dimensionCounts[$key] ?? 0) + 1;
                }
            }
        }

        $averages = [];
        foreach ($dimensionTotals as $key => $total) {
            $averages[$key] = round($total / $dimensionCounts[$key], 2);
        }

        return $averages;
    }

    /**
     * @return array{partner_count: int, avg_pipeline_coverage: float, avg_win_rate: float, avg_health_score: float, certification_density: float, avg_active_signals: float}
     */
    protected function buildPortfolioMetrics(int $partnerCount): array
    {
        $openPipeline = Opportunity::query()
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->sum('amount');

        $closedWon12m = Opportunity::query()
            ->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subYear())
            ->sum('amount');

        $totalWon = Opportunity::where('stage', 'Closed Won')->count();
        $totalClosed = Opportunity::whereIn('stage', ['Closed Won', 'Closed Lost'])->count();

        $avgHealth = PartnerHealthSnapshot::query()
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('partner_health_snapshots')
                    ->groupBy('partner_id');
            })
            ->avg('score') ?? 0;

        return [
            'partner_count' => $partnerCount,
            'avg_pipeline_coverage' => $closedWon12m > 0 ? round($openPipeline / $closedWon12m, 2) : 0,
            'avg_win_rate' => $totalClosed > 0 ? round(($totalWon / $totalClosed) * 100, 1) : 0,
            'avg_health_score' => round($avgHealth, 1),
            'certification_density' => $partnerCount > 0
                ? round(Certification::where('status', 'active')->count() / $partnerCount, 2)
                : 0,
            'avg_active_signals' => $partnerCount > 0
                ? round(Signal::active()->count() / $partnerCount, 1)
                : 0,
        ];
    }
}
