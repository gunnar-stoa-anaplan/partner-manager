<?php

namespace App\Services;

use App\Models\StrategyPlan;
use App\Models\StrategyPlanTarget;
use Illuminate\Support\Facades\DB;

class StrategyActualsService
{
    /**
     * Refresh all target actuals for a committed/active plan.
     */
    public function refreshPlan(StrategyPlan $plan): int
    {
        $count = 0;

        $plan->targets->each(function (StrategyPlanTarget $target) use (&$count, $plan) {
            $actual = $this->compute($target, $plan->fiscal_year);
            $target->update(['actual_value' => $actual]);
            $this->computeQuarterlyActuals($target, $plan->fiscal_year);
            $count++;
        });

        // Also refresh assumption actuals
        $plan->assumptions->each(function ($assumption) use ($plan) {
            $actual = $this->computeAssumption($assumption->assumption_key, $plan->fiscal_year);
            if ($actual !== null) {
                $assumption->update(['actual_value' => $actual]);
            }
        });

        // Also refresh budget actuals
        $plan->budgets->each(function ($budget) {
            // Budget actuals are manually entered — skip auto-refresh
        });

        return $count;
    }

    /**
     * Compute the actual value for a strategy target.
     */
    public function compute(StrategyPlanTarget $target, string $fiscalYear): float
    {
        return match ($target->metric_key) {
            'partner_sourced_revenue' => $this->computeRevenue('sourced', $fiscalYear),
            'partner_influenced_revenue' => $this->computeRevenue('influenced', $fiscalYear),
            'total_pipeline' => $this->computeTotalPipeline(),
            'new_tier1_partners' => $this->computePartnersByTier('Platinum'),
            'new_tier2_partners' => $this->computePartnersByTier('Gold'),
            'enablement_completions' => $this->computeEnablementCompletions(),
            'active_certifications' => $this->computeActiveCertifications(),
            'avg_win_rate' => $this->computeAvgWinRate(),
            'avg_deal_size' => $this->computeAvgDealSize(),
            'projects_delivered' => $this->computeProjectsDelivered(),
            'avg_csat' => $this->computeAvgCsat(),
            default => $target->actual_value ?? 0,
        };
    }

    /**
     * Compute quarterly actuals for a target.
     */
    protected function computeQuarterlyActuals(StrategyPlanTarget $target, string $fiscalYear): void
    {
        $fyInt = $this->fyToInt($fiscalYear);

        // Anaplan FY starts in February
        $quarters = [
            1 => ["{$fyInt}-02-01", "{$fyInt}-04-30"],
            2 => ["{$fyInt}-05-01", "{$fyInt}-07-31"],
            3 => ["{$fyInt}-08-01", "{$fyInt}-10-31"],
            4 => ["{$fyInt}-11-01", ($fyInt + 1).'-01-31'],
        ];

        foreach ($quarters as $q => $dates) {
            $actual = $this->computeForPeriod($target->metric_key, $dates[0], $dates[1]);
            if ($actual !== null) {
                $target->update(["q{$q}_actual" => $actual]);
            }
        }
    }

    /**
     * Compute a metric value for a specific date range.
     */
    protected function computeForPeriod(string $metricKey, string $start, string $end): ?float
    {
        return match ($metricKey) {
            'partner_sourced_revenue' => $this->computeRevenueForPeriod('sourced', $start, $end),
            'partner_influenced_revenue' => $this->computeRevenueForPeriod('influenced', $start, $end),
            'projects_delivered' => (float) DB::table('projects')
                ->where('status', 'completed')
                ->whereBetween('go_live_date', [$start, $end])
                ->count(),
            default => null,
        };
    }

    protected function computeRevenue(string $revenueType, string $fiscalYear): float
    {
        return (float) DB::table('opportunities')
            ->where('stage', 'Closed Won')
            ->where('revenue_type', $revenueType)
            ->where('fiscal_year', $this->fyToInt($fiscalYear))
            ->sum('amount');
    }

    protected function computeRevenueForPeriod(string $revenueType, string $start, string $end): float
    {
        return (float) DB::table('opportunities')
            ->where('stage', 'Closed Won')
            ->where('revenue_type', $revenueType)
            ->whereBetween('close_date', [$start, $end])
            ->sum('amount');
    }

    protected function computeTotalPipeline(): float
    {
        return (float) DB::table('opportunities')
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->sum('amount');
    }

    protected function computePartnersByTier(string $tier): float
    {
        return (float) DB::table('partner_scorecards')
            ->where('tier', $tier)
            ->whereIn('id', function ($q) {
                $q->select(DB::raw('MAX(id)'))
                    ->from('partner_scorecards')
                    ->groupBy('partner_id');
            })
            ->count();
    }

    protected function computeEnablementCompletions(): float
    {
        return (float) DB::table('enablement_registrations')
            ->where('status', 'attended')
            ->count();
    }

    protected function computeActiveCertifications(): float
    {
        return (float) DB::table('certifications')
            ->where('status', 'active')
            ->count();
    }

    protected function computeAvgWinRate(): float
    {
        $won = DB::table('opportunities')
            ->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subMonths(12))
            ->count();

        $lost = DB::table('opportunities')
            ->where('stage', 'Closed Lost')
            ->where('close_date', '>=', now()->subMonths(12))
            ->count();

        $total = $won + $lost;

        return $total > 0 ? round(($won / $total) * 100, 1) : 0;
    }

    protected function computeAvgDealSize(): float
    {
        return (float) (DB::table('opportunities')
            ->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subMonths(12))
            ->avg('amount') ?? 0);
    }

    protected function computeProjectsDelivered(): float
    {
        return (float) DB::table('projects')
            ->where('status', 'completed')
            ->count();
    }

    protected function computeAvgCsat(): float
    {
        return (float) (DB::table('projects')
            ->where('status', 'completed')
            ->whereNotNull('customer_satisfaction')
            ->avg('customer_satisfaction') ?? 0);
    }

    /**
     * Compute actual value for an assumption key.
     */
    protected function computeAssumption(string $key, string $fiscalYear): ?float
    {
        return match ($key) {
            'conversion_rate' => $this->computeConversionRate(),
            'avg_deal_size' => $this->computeAvgDealSize(),
            'avg_win_rate' => $this->computeAvgWinRate() / 100,
            default => null,
        };
    }

    protected function computeConversionRate(): float
    {
        $won = DB::table('opportunities')
            ->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subMonths(12))
            ->count();

        $total = DB::table('opportunities')
            ->whereIn('stage', ['Closed Won', 'Closed Lost'])
            ->where('close_date', '>=', now()->subMonths(12))
            ->count();

        return $total > 0 ? round($won / $total, 4) : 0;
    }

    /**
     * Convert FY string (e.g. "FY27") to integer year for DB comparison.
     */
    protected function fyToInt(string $fy): int
    {
        $num = (int) substr($fy, 2);

        return $num > 50 ? 1900 + $num : 2000 + $num;
    }
}
