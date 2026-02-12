<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerPathEnrollment;
use App\Models\PartnerPathGate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GateEvaluationService
{
    public function evaluateGates(Partner $partner, int $fromStageId, int $toStageId): Collection
    {
        $gates = PartnerPathGate::where('from_stage_id', $fromStageId)
            ->where('to_stage_id', $toStageId)
            ->orderBy('sort_order')
            ->get();
        
        return $gates->map(function ($gate) use ($partner) {
            $actualValue = $this->getActualValue($partner, $gate->requirement_key);
            $met = $this->compare($actualValue, $gate->comparison, (float) $gate->threshold_value);
            
            return [
                'gate_id' => $gate->id,
                'requirement_key' => $gate->requirement_key,
                'requirement_label' => $gate->requirement_label,
                'threshold' => $gate->threshold_value,
                'unit' => $gate->unit,
                'comparison' => $gate->comparison,
                'actual_value' => $actualValue,
                'met' => $met,
            ];
        });
    }
    
    public function allGatesMet(Partner $partner, int $fromStageId, int $toStageId): bool
    {
        return $this->evaluateGates($partner, $fromStageId, $toStageId)
            ->every(fn ($g) => $g['met']);
    }
    
    protected function getActualValue(Partner $partner, string $key): float
    {
        return match ($key) {
            'certified_mb_count' => DB::table('certifications')->where('partner_id', $partner->id)
                ->where('type', 'model_builder')->where('status', 'active')->count(),
            'certified_sa_count' => DB::table('certifications')->where('partner_id', $partner->id)
                ->where('type', 'solution_architect')->where('status', 'active')->count(),
            'certified_resource_count' => DB::table('certifications')->where('partner_id', $partner->id)
                ->where('status', 'active')->count(),
            'core_blueprint_completed' => DB::table('partner_blueprint_assignments')
                ->where('partner_id', $partner->id)->where('status', 'completed')->count(),
            'onboarding_tasks_pct' => $this->getTaskCompletionPct($partner, 'account'),
            'salesforce_connected' => $partner->salesforce_id ? 1 : 0,
            'contact_count' => DB::table('contacts')->where('partner_id', $partner->id)->count(),
            'open_opportunity_count' => DB::table('opportunities')->where('partner_id', $partner->id)
                ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])->count(),
            'pipeline_value' => (float) DB::table('opportunities')->where('partner_id', $partner->id)
                ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])->sum('amount'),
            'won_opportunity_count' => DB::table('opportunities')->where('partner_id', $partner->id)
                ->where('stage', 'Closed Won')->where('close_date', '>=', now()->subMonths(12))->count(),
            'completed_project_count' => DB::table('projects')->where('partner_id', $partner->id)
                ->where('status', 'completed')->count(),
            'qbr_count' => DB::table('partner_events')->where('partner_id', $partner->id)
                ->where('type', 'qbr')->where('start_at', '>=', now()->subMonths(12))->count(),
            'health_score' => (float) (DB::table('partner_scorecards')->where('partner_id', $partner->id)
                ->orderByDesc('scored_at')->value('overall_score') ?? 0),
            'revenue_achievement_pct' => $this->getRevenueAchievementPct($partner),
            'win_rate' => $this->getWinRate($partner),
            'pipeline_coverage' => $this->getPipelineCoverage($partner),
            'avg_csat' => (float) (DB::table('projects')->where('partner_id', $partner->id)
                ->where('status', 'completed')->avg('customer_satisfaction') ?? 0),
            'ontime_delivery_pct' => $this->getOnTimeDeliveryPct($partner),
            'product_area_count' => DB::table('certifications')->where('partner_id', $partner->id)
                ->where('status', 'active')->whereNotNull('product_area')->distinct()->count('product_area'),
            'partner_originated_pct' => $this->getPartnerOriginatedPct($partner),
            'solution_count' => DB::table('partner_solutions')->where('partner_id', $partner->id)
                ->where('status', 'active')->count(),
            'referenceable_pct' => $this->getReferenceablePct($partner),
            'expansion_rate' => $this->getExpansionRate($partner),
            default => 0,
        };
    }
    
    protected function compare(float $actual, string $comparison, float $threshold): bool
    {
        return match ($comparison) {
            'gte' => $actual >= $threshold,
            'lte' => $actual <= $threshold,
            'eq' => abs($actual - $threshold) < 0.01,
            'gt' => $actual > $threshold,
            'lt' => $actual < $threshold,
            default => false,
        };
    }
    
    protected function getTaskCompletionPct(Partner $partner, string $category): float
    {
        $total = DB::table('partner_tasks')->where('partner_id', $partner->id)
            ->where('category', $category)->where('requirement_level', 'required')->count();
        $completed = DB::table('partner_tasks')->where('partner_id', $partner->id)
            ->where('category', $category)->where('requirement_level', 'required')
            ->where('status', 'completed')->count();
        return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
    }
    
    protected function getRevenueAchievementPct(Partner $partner): float
    {
        $fyStart = (int) \App\Models\AppSetting::get('fiscal_year_start_month', 2);
        $now = now();
        $fy = $now->month >= $fyStart ? 'FY' . substr($now->year + 1, 2) : 'FY' . substr($now->year, 2);
        $goal = $partner->goals()->where('fiscal_year', $fy)->first();
        if (!$goal) return 0;
        $target = $goal->sourced_nnacv_target + $goal->influenced_nnacv_target;
        if ($target <= 0) return 0;
        $won = $partner->opportunities()->where('stage', 'Closed Won')->where('fiscal_year', $fy)->sum('amount');
        return round(($won / $target) * 100, 1);
    }
    
    protected function getWinRate(Partner $partner): float
    {
        $won = $partner->opportunities()->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subMonths(12))->count();
        $lost = $partner->opportunities()->where('stage', 'Closed Lost')
            ->where('close_date', '>=', now()->subMonths(12))->count();
        return ($won + $lost) > 0 ? round(($won / ($won + $lost)) * 100, 1) : 0;
    }
    
    protected function getPipelineCoverage(Partner $partner): float
    {
        $goal = $partner->goals()->orderByDesc('fiscal_year')->first();
        if (!$goal) return 0;
        $target = $goal->sourced_nnacv_target + $goal->influenced_nnacv_target;
        if ($target <= 0) return 0;
        $pipeline = $partner->opportunities()->whereNotIn('stage', ['Closed Won', 'Closed Lost'])->sum('amount');
        return round($pipeline / $target, 2);
    }
    
    protected function getOnTimeDeliveryPct(Partner $partner): float
    {
        $completed = DB::table('projects')->where('partner_id', $partner->id)->where('status', 'completed')->count();
        if ($completed === 0) return 0;
        $onTime = DB::table('projects')->where('partner_id', $partner->id)->where('status', 'completed')
            ->where('percent_complete', '>=', 100)->count();
        return round(($onTime / $completed) * 100, 1);
    }
    
    protected function getPartnerOriginatedPct(Partner $partner): float
    {
        $total = $partner->opportunities()->whereNotIn('stage', ['Closed Lost'])->count();
        if ($total === 0) return 0;
        $originated = $partner->opportunities()->where('origin', 'partner_originated')->count();
        return round(($originated / $total) * 100, 1);
    }
    
    protected function getReferenceablePct(Partner $partner): float
    {
        $completed = DB::table('projects')->where('partner_id', $partner->id)->where('status', 'completed')->count();
        if ($completed === 0) return 0;
        $ref = DB::table('projects')->where('partner_id', $partner->id)->where('status', 'completed')
            ->where('is_referenceable', true)->count();
        return round(($ref / $completed) * 100, 1);
    }
    
    protected function getExpansionRate(Partner $partner): float
    {
        $completed = DB::table('projects')->where('partner_id', $partner->id)->where('status', 'completed')->count();
        if ($completed === 0) return 0;
        $expanded = DB::table('projects')->where('partner_id', $partner->id)->where('status', 'completed')
            ->where('led_to_expansion', true)->count();
        return round(($expanded / $completed) * 100, 1);
    }
}
