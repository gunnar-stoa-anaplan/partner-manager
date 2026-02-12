<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerScorecard;
use App\Models\ScorecardWeight;
use App\Models\Certification;
use App\Models\CertificationRequirement;
use App\Models\PartnerPathEnrollment;
use Illuminate\Support\Facades\DB;

class ScorecardCalculationService
{
    public function calculateForPartner(Partner $partner): PartnerScorecard
    {
        $weights = ScorecardWeight::where('is_active', true)->pluck('weight', 'dimension')->toArray();
        
        $defaults = [
            'certification' => 0.15, 'pipeline' => 0.20, 'delivery' => 0.20,
            'engagement' => 0.15, 'growth' => 0.20, 'self_sufficiency' => 0.10,
        ];
        $weights = array_merge($defaults, $weights);
        
        $certScore = $this->calculateCertificationScore($partner);
        $pipelineScore = $this->calculatePipelineScore($partner);
        $deliveryScore = $this->calculateDeliveryScore($partner);
        $engagementScore = $this->calculateEngagementScore($partner);
        $growthScore = $this->calculateGrowthScore($partner);
        $selfSufficiencyScore = $this->calculateSelfSufficiencyScore($partner);
        
        $overall = round(
            ($certScore * $weights['certification']) +
            ($pipelineScore * $weights['pipeline']) +
            ($deliveryScore * $weights['delivery']) +
            ($engagementScore * $weights['engagement']) +
            ($growthScore * $weights['growth']) +
            ($selfSufficiencyScore * $weights['self_sufficiency']),
            1
        );
        
        $enrollment = PartnerPathEnrollment::where('partner_id', $partner->id)->where('status', 'active')->first();
        $tier = $enrollment ? optional($enrollment->currentStage)->name : null;
        
        // Check gate eligibility
        $gateEligible = false;
        if ($enrollment) {
            $nextGates = DB::table('partner_path_gates')
                ->where('from_stage_id', $enrollment->current_stage_id)
                ->get();
            if ($nextGates->isNotEmpty()) {
                $healthGate = $nextGates->firstWhere('requirement_key', 'health_score');
                $gateEligible = $healthGate ? $overall >= $healthGate->threshold_value : $overall >= 30;
            }
        }
        
        $details = [
            'certification' => ['score' => $certScore, 'weight' => $weights['certification']],
            'pipeline' => ['score' => $pipelineScore, 'weight' => $weights['pipeline']],
            'delivery' => ['score' => $deliveryScore, 'weight' => $weights['delivery']],
            'engagement' => ['score' => $engagementScore, 'weight' => $weights['engagement']],
            'growth' => ['score' => $growthScore, 'weight' => $weights['growth']],
            'self_sufficiency' => ['score' => $selfSufficiencyScore, 'weight' => $weights['self_sufficiency']],
        ];
        
        return PartnerScorecard::create([
            'partner_id' => $partner->id,
            'scored_at' => now()->toDateString(),
            'overall_score' => $overall,
            'certification_score' => $certScore,
            'pipeline_score' => $pipelineScore,
            'delivery_score' => $deliveryScore,
            'engagement_score' => $engagementScore,
            'growth_score' => $growthScore,
            'self_sufficiency_score' => $selfSufficiencyScore,
            'tier' => $tier ? strtolower($tier) : null,
            'gate_eligible' => $gateEligible,
            'score_details' => $details,
            'scoring_config' => $weights,
        ]);
    }
    
    protected function calculateCertificationScore(Partner $partner): float
    {
        $activeCerts = Certification::where('partner_id', $partner->id)->where('status', 'active')->count();
        $mbCount = Certification::where('partner_id', $partner->id)->where('status', 'active')->where('type', 'model_builder')->count();
        $saCount = Certification::where('partner_id', $partner->id)->where('status', 'active')->where('type', 'solution_architect')->count();
        $expiring = Certification::where('partner_id', $partner->id)->where('status', 'active')
            ->whereBetween('expiry_date', [now(), now()->addDays(90)])->count();
        $productAreas = Certification::where('partner_id', $partner->id)->where('status', 'active')
            ->whereNotNull('product_area')->distinct('product_area')->count();
        
        // Score components
        $mbScore = min(100, ($mbCount / max(1, 2)) * 100) * 0.30;
        $saScore = min(100, ($saCount / max(1, 1)) * 100) * 0.30;
        $ratioScore = ($activeCerts >= 4 ? 100 : ($activeCerts / 4) * 100) * 0.20;
        $expiryScore = max(0, 100 - ($expiring * 20)) * 0.10;
        $areaScore = min(100, ($productAreas / 3) * 100) * 0.10;
        
        return round($mbScore + $saScore + $ratioScore + $expiryScore + $areaScore, 1);
    }
    
    protected function calculatePipelineScore(Partner $partner): float
    {
        $openPipeline = $partner->opportunities()->whereNotIn('stage', ['Closed Won', 'Closed Lost'])->sum('amount');
        $goal = $partner->goals()->where('fiscal_year', $this->currentFiscalYear())->first();
        $target = $goal ? ($goal->sourced_nnacv_target + $goal->influenced_nnacv_target) : 1000000;
        
        $coverageRatio = $target > 0 ? $openPipeline / $target : 0;
        $coverageScore = min(100, max(0, ($coverageRatio / 3) * 100)) * 0.25;
        
        $won = $partner->opportunities()->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subMonths(12))->count();
        $lost = $partner->opportunities()->where('stage', 'Closed Lost')
            ->where('close_date', '>=', now()->subMonths(12))->count();
        $winRate = ($won + $lost) > 0 ? $won / ($won + $lost) : 0;
        $winRateScore = min(100, max(0, (($winRate - 0.10) / 0.40) * 100)) * 0.25;
        
        $staleDeals = $partner->opportunities()->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->where('updated_at', '<', now()->subDays(30))->count();
        $staleScore = max(0, 100 - ($staleDeals * 15)) * 0.10;
        
        $productAreas = $partner->opportunities()->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->whereNotNull('product_area')->distinct('product_area')->count();
        $diversityScore = min(100, ($productAreas / 3) * 100) * 0.10;
        
        // Avg deal size trend and velocity simplified
        $avgDealScore = 50 * 0.15;
        $velocityScore = 50 * 0.15;
        
        return round($coverageScore + $winRateScore + $staleScore + $diversityScore + $avgDealScore + $velocityScore, 1);
    }
    
    protected function calculateDeliveryScore(Partner $partner): float
    {
        $projects = DB::table('projects')->where('partner_id', $partner->id);
        $completed = (clone $projects)->where('status', 'completed');
        $totalCompleted = $completed->count();
        
        if ($totalCompleted === 0) return 50.0; // Neutral if no projects
        
        $onTime = (clone $projects)->where('status', 'completed')
            ->whereColumn('end_date', '>=', DB::raw('COALESCE(go_live_date, end_date)'))->count();
        $onTimeRate = $totalCompleted > 0 ? $onTime / $totalCompleted : 0;
        $onTimeScore = min(100, ($onTimeRate / 0.90) * 100) * 0.25;
        
        $avgCsat = (clone $projects)->where('status', 'completed')
            ->whereNotNull('customer_satisfaction')->avg('customer_satisfaction') ?? 3.0;
        $csatScore = min(100, max(0, (($avgCsat - 2.0) / 2.5) * 100)) * 0.25;
        
        $escalated = DB::table('cases')->where('partner_id', $partner->id)->where('escalated', true)->count();
        $totalCases = DB::table('cases')->where('partner_id', $partner->id)->count();
        $escalationRate = $totalCases > 0 ? $escalated / $totalCases : 0;
        $escalationScore = max(0, min(100, (1 - ($escalationRate / 0.25)) * 100)) * 0.20;
        
        $referenceable = (clone $projects)->where('status', 'completed')->where('is_referenceable', true)->count();
        $refRate = $totalCompleted > 0 ? $referenceable / $totalCompleted : 0;
        $refScore = min(100, ($refRate / 0.50) * 100) * 0.15;
        
        $avgScopeChanges = (clone $projects)->where('status', 'completed')->avg('scope_changes') ?? 0;
        $scopeScore = max(0, 100 - ($avgScopeChanges * 10)) * 0.15;
        
        return round($onTimeScore + $csatScore + $escalationScore + $refScore + $scopeScore, 1);
    }
    
    protected function calculateEngagementScore(Partner $partner): float
    {
        $touchpoints = DB::table('partner_events')->where('partner_id', $partner->id)
            ->where('start_at', '>=', now()->subDays(90))->count();
        $touchScore = min(100, ($touchpoints / 12) * 100) * 0.30;
        
        $registered = DB::table('enablement_registrations')->where('partner_id', $partner->id)->count();
        $attended = DB::table('enablement_registrations')->where('partner_id', $partner->id)
            ->where('status', 'attended')->count();
        $attendRate = $registered > 0 ? $attended / $registered : 0;
        $attendScore = min(100, ($attendRate / 0.80) * 100) * 0.25;
        
        $totalBp = DB::table('partner_blueprint_assignments')->where('partner_id', $partner->id)->count();
        $completedBp = DB::table('partner_blueprint_assignments')->where('partner_id', $partner->id)
            ->where('status', 'completed')->count();
        $bpRate = $totalBp > 0 ? $completedBp / $totalBp : 0;
        $bpScore = ($bpRate * 100) * 0.20;
        
        // Action plan and response time simplified
        $actionScore = 50 * 0.15;
        $responseScore = 50 * 0.10;
        
        return round($touchScore + $attendScore + $bpScore + $actionScore + $responseScore, 1);
    }
    
    protected function calculateGrowthScore(Partner $partner): float
    {
        // QoQ revenue growth
        $currentQ = $partner->opportunities()->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subMonths(3))->sum('amount');
        $prevQ = $partner->opportunities()->where('stage', 'Closed Won')
            ->whereBetween('close_date', [now()->subMonths(6), now()->subMonths(3)])->sum('amount');
        $qoqGrowth = $prevQ > 0 ? ($currentQ - $prevQ) / $prevQ : ($currentQ > 0 ? 1 : 0);
        $growthScore = min(100, max(0, ($qoqGrowth / 0.30) * 100)) * 0.30;
        
        // New logos
        $newLogos = $partner->opportunities()->where('stage', 'Closed Won')
            ->where('close_date', '>=', now()->subMonths(12))
            ->distinct('account_name')->count();
        $logoScore = min(100, ($newLogos / 3) * 100) * 0.20;
        
        // Expansion rate
        $totalProjects = DB::table('projects')->where('partner_id', $partner->id)->where('status', 'completed')->count();
        $expansions = DB::table('projects')->where('partner_id', $partner->id)->where('status', 'completed')
            ->where('led_to_expansion', true)->count();
        $expRate = $totalProjects > 0 ? $expansions / $totalProjects : 0;
        $expScore = min(100, ($expRate / 0.30) * 100) * 0.20;
        
        // Goal attainment
        $goal = $partner->goals()->where('fiscal_year', $this->currentFiscalYear())->first();
        $wonAmount = $partner->opportunities()->where('stage', 'Closed Won')
            ->where('fiscal_year', $this->currentFiscalYear())->sum('amount');
        $goalTarget = $goal ? ($goal->sourced_nnacv_target + $goal->influenced_nnacv_target) : 1;
        $attainment = $goalTarget > 0 ? $wonAmount / $goalTarget : 0;
        $attainScore = min(100, $attainment * 100) * 0.15;
        
        // Pipeline growth simplified
        $pipeGrowthScore = 50 * 0.15;
        
        return round($growthScore + $logoScore + $expScore + $attainScore + $pipeGrowthScore, 1);
    }
    
    protected function calculateSelfSufficiencyScore(Partner $partner): float
    {
        $totalOpps = $partner->opportunities()->whereNotIn('stage', ['Closed Lost'])->count();
        $partnerOriginated = $partner->opportunities()->whereNotIn('stage', ['Closed Lost'])
            ->where('origin', 'partner_originated')->count();
        $originRate = $totalOpps > 0 ? $partnerOriginated / $totalOpps : 0;
        $originScore = min(100, ($originRate / 0.50) * 100) * 0.40;
        
        // Independent deal closing simplified
        $independentScore = 50 * 0.30;
        
        // Solution assets
        $solutions = DB::table('partner_solutions')->where('partner_id', $partner->id)
            ->where('status', 'active')->count();
        $solutionScore = min(100, ($solutions / 2) * 100) * 0.30;
        
        return round($originScore + $independentScore + $solutionScore, 1);
    }
    
    protected function currentFiscalYear(): string
    {
        $fyStart = (int) \App\Models\AppSetting::get('fiscal_year_start_month', 2);
        $now = now();
        $year = $now->month >= $fyStart ? $now->year : $now->year - 1;
        return 'FY' . substr($year + 1, 2);
    }
    
    public function calculateForAllPartners(): int
    {
        $partners = Partner::all();
        $count = 0;
        foreach ($partners as $partner) {
            $this->calculateForPartner($partner);
            $count++;
        }
        return $count;
    }
}
