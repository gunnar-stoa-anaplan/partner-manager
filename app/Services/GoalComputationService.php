<?php

namespace App\Services;

use App\Models\BlueprintEnrollment;
use App\Models\BlueprintProgress;
use App\Models\Certification;
use App\Models\EnablementRegistration;
use App\Models\PartnerAssignedGoal;
use App\Models\PartnerSolution;
use Illuminate\Support\Facades\DB;

class GoalComputationService
{
    /**
     * Compute the actual value for a goal based on its metric_key.
     */
    public function compute(PartnerAssignedGoal $goal): float
    {
        $partnerId = $goal->partner_id;
        $fy = $goal->fiscal_year;

        return match ($goal->metric_key) {
            'sourced_nnacv' => $this->computeSourcedNnacv($partnerId, $fy),
            'influenced_nnacv' => $this->computeInfluencedNnacv($partnerId, $fy),
            'total_pipeline' => $this->computeTotalPipeline($partnerId),
            'win_rate' => $this->computeWinRate($partnerId),
            'certification_mb' => $this->computeCertifications($partnerId, 'model_builder'),
            'certification_sa' => $this->computeCertifications($partnerId, 'solution_architect'),
            'enablement_sessions' => $this->computeEnablementSessions($partnerId),
            'product_areas_covered' => $this->computeProductAreasCovered($partnerId),
            'solutions_registered' => $this->computeSolutionsRegistered($partnerId),
            'projects_delivered' => $this->computeProjectsDelivered($partnerId),
            'projects_ontime_pct' => $this->computeProjectsOnTimePct($partnerId),
            'customer_satisfaction' => $this->computeCustomerSatisfaction($partnerId),
            'blueprints_completed' => $this->computeBlueprintsCompleted($partnerId),
            'blueprint_items_completed' => $this->computeBlueprintItemsCompleted($partnerId),
            'beta_enrollments', 'beta_feedback_submitted' => 0,
            default => $goal->actual_value ?? 0,
        };
    }

    /**
     * Compute and update actual + status for a goal. Returns the updated goal.
     */
    public function computeAndUpdate(PartnerAssignedGoal $goal): PartnerAssignedGoal
    {
        $actual = $this->compute($goal);
        $goal->actual_value = $actual;
        $goal->status = $this->determineStatus($goal);
        $goal->save();

        return $goal;
    }

    /**
     * Compute actuals for all goals matching a fiscal year. Returns count updated.
     */
    public function computeAll(?string $fiscalYear = null): int
    {
        $query = PartnerAssignedGoal::query();
        if ($fiscalYear) {
            $query->where('fiscal_year', $fiscalYear);
        }

        $count = 0;
        $query->chunk(100, function ($goals) use (&$count) {
            foreach ($goals as $goal) {
                $this->computeAndUpdate($goal);
                $count++;
            }
        });

        return $count;
    }

    protected function determineStatus(PartnerAssignedGoal $goal): string
    {
        if (! $goal->target_value || $goal->target_value == 0) {
            return $goal->status ?? 'on_track';
        }

        $pct = ($goal->actual_value / $goal->target_value) * 100;

        return match (true) {
            $pct >= 100 => 'exceeded',
            $pct >= 70 => 'on_track',
            $pct >= 40 => 'at_risk',
            default => 'behind',
        };
    }

    protected function computeSourcedNnacv(int $partnerId, string $fy): float
    {
        return (float) DB::table('opportunities')
            ->where('partner_id', $partnerId)
            ->where('stage', 'Closed Won')
            ->where('revenue_type', 'sourced')
            ->where('fiscal_year', $this->fyToInt($fy))
            ->sum('amount');
    }

    protected function computeInfluencedNnacv(int $partnerId, string $fy): float
    {
        return (float) DB::table('opportunities')
            ->where('partner_id', $partnerId)
            ->where('stage', 'Closed Won')
            ->where('revenue_type', 'influenced')
            ->where('fiscal_year', $this->fyToInt($fy))
            ->sum('amount');
    }

    protected function computeTotalPipeline(int $partnerId): float
    {
        return (float) DB::table('opportunities')
            ->where('partner_id', $partnerId)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->sum('amount');
    }

    protected function computeWinRate(int $partnerId): float
    {
        $won = DB::table('opportunities')
            ->where('partner_id', $partnerId)
            ->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subMonths(12))
            ->count();

        $lost = DB::table('opportunities')
            ->where('partner_id', $partnerId)
            ->where('stage', 'Closed Lost')
            ->where('close_date', '>=', now()->subMonths(12))
            ->count();

        $total = $won + $lost;

        return $total > 0 ? round(($won / $total) * 100, 1) : 0;
    }

    protected function computeCertifications(int $partnerId, string $type): float
    {
        return (float) Certification::where('partner_id', $partnerId)
            ->where('status', 'active')
            ->where('type', $type)
            ->count();
    }

    protected function computeEnablementSessions(int $partnerId): float
    {
        return (float) EnablementRegistration::where('partner_id', $partnerId)
            ->where('status', 'attended')
            ->count();
    }

    protected function computeProductAreasCovered(int $partnerId): float
    {
        return (float) DB::table('contact_product')
            ->join('contacts', 'contacts.id', '=', 'contact_product.contact_id')
            ->where('contacts.partner_id', $partnerId)
            ->distinct('contact_product.product_id')
            ->count('contact_product.product_id');
    }

    protected function computeSolutionsRegistered(int $partnerId): float
    {
        return (float) PartnerSolution::where('partner_id', $partnerId)
            ->where('status', 'active')
            ->count();
    }

    protected function computeProjectsDelivered(int $partnerId): float
    {
        return (float) DB::table('projects')
            ->where('partner_id', $partnerId)
            ->where('status', 'completed')
            ->count();
    }

    protected function computeProjectsOnTimePct(int $partnerId): float
    {
        $completed = DB::table('projects')
            ->where('partner_id', $partnerId)
            ->where('status', 'completed')
            ->count();

        if ($completed === 0) {
            return 0;
        }

        // Projects with go_live_date are considered on-time by default
        // (no separate deadline column exists — 100% of completed projects count)
        $withGoLive = DB::table('projects')
            ->where('partner_id', $partnerId)
            ->where('status', 'completed')
            ->whereNotNull('go_live_date')
            ->count();

        return $completed > 0 ? round(($withGoLive / $completed) * 100, 1) : 0;
    }

    protected function computeCustomerSatisfaction(int $partnerId): float
    {
        return (float) (DB::table('projects')
            ->where('partner_id', $partnerId)
            ->where('status', 'completed')
            ->whereNotNull('customer_satisfaction')
            ->avg('customer_satisfaction') ?? 0);
    }

    protected function computeBlueprintsCompleted(int $partnerId): float
    {
        return (float) BlueprintEnrollment::whereHas('assignment', function ($q) use ($partnerId) {
            $q->where('partner_id', $partnerId);
        })
            ->where('status', 'completed')
            ->count();
    }

    protected function computeBlueprintItemsCompleted(int $partnerId): float
    {
        return (float) BlueprintProgress::whereHas('enrollment.assignment', function ($q) use ($partnerId) {
            $q->where('partner_id', $partnerId);
        })
            ->where('completed', true)
            ->count();
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
