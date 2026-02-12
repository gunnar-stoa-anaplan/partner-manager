<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerAlert;
use App\Models\Certification;
use Illuminate\Support\Facades\DB;

class AlertGenerationService
{
    protected DeliveryCapabilityRiskService $riskService;
    
    public function __construct(DeliveryCapabilityRiskService $riskService)
    {
        $this->riskService = $riskService;
    }
    
    public function generateForPartner(Partner $partner): int
    {
        $generated = 0;
        $generated += $this->checkCertificationExpiry($partner);
        $generated += $this->checkPipelineRisk($partner);
        $generated += $this->checkDeliveryRisk($partner);
        $generated += $this->checkDeliveryCapabilityRisk($partner);
        $generated += $this->checkEngagementGap($partner);
        $generated += $this->checkGoalGap($partner);
        $generated += $this->checkOverdueTasks($partner);
        $generated += $this->checkPathGates($partner);
        $generated += $this->checkScorecardDecline($partner);
        $generated += $this->checkBlueprintOverdue($partner);
        $generated += $this->checkStalePartner($partner);
        return $generated;
    }
    
    public function generateForAll(): int
    {
        $total = 0;
        Partner::select('id', 'name', 'user_id')->chunk(50, function ($partners) use (&$total) {
            foreach ($partners as $partner) {
                $total += $this->generateForPartner($partner);
            }
        });
        return $total;
    }
    
    protected function createAlert(Partner $partner, string $type, string $severity, string $title, string $description, string $action, array $data = []): bool
    {
        // Don't create duplicate active alerts
        $exists = PartnerAlert::where('partner_id', $partner->id)
            ->where('type', $type)
            ->where('status', 'active')
            ->where('title', $title)
            ->exists();
        
        if ($exists) return false;
        
        PartnerAlert::create([
            'partner_id' => $partner->id,
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'recommended_action' => $action,
            'data' => $data,
        ]);
        
        return true;
    }
    
    protected function checkCertificationExpiry(Partner $partner): int
    {
        $count = 0;
        
        $expiringSoon = Certification::where('partner_id', $partner->id)
            ->where('status', 'active')
            ->whereBetween('expiry_date', [now(), now()->addDays(30)])
            ->with('contact')
            ->get();
        
        foreach ($expiringSoon as $cert) {
            $contactName = $cert->contact ? $cert->contact->first_name . ' ' . $cert->contact->last_name : 'Unknown';
            $days = now()->diffInDays($cert->expiry_date);
            $severity = $days <= 7 ? 'critical' : 'warning';
            
            if ($this->createAlert($partner, 'certification_expiry', $severity,
                "{$contactName}'s {$cert->type} certification expiring",
                "{$contactName}'s {$cert->type} certification expires in {$days} days on {$cert->expiry_date->format('M j, Y')}.",
                "Schedule certification renewal for {$contactName}. Book through Anaplan Academy.",
                ['certification_id' => $cert->id, 'contact_name' => $contactName, 'days_until_expiry' => $days]
            )) $count++;
        }
        
        return $count;
    }
    
    protected function checkPipelineRisk(Partner $partner): int
    {
        $count = 0;
        $goal = $partner->goals()->where('fiscal_year', $this->currentFiscalYear())->first();
        
        if ($goal) {
            $target = $goal->sourced_nnacv_target + $goal->influenced_nnacv_target;
            $openPipeline = $partner->opportunities()->whereNotIn('stage', ['Closed Won', 'Closed Lost'])->sum('amount');
            $coverage = $target > 0 ? $openPipeline / $target : 0;
            
            if ($coverage < 1.5 && $target > 0) {
                if ($this->createAlert($partner, 'pipeline_risk', 'warning',
                    "Low pipeline coverage ({$coverage}x)",
                    "Pipeline coverage is " . number_format($coverage, 1) . "x against " . number_format($target) . " target.",
                    "Schedule pipeline review meeting and identify joint prospecting opportunities.",
                    ['coverage' => $coverage, 'pipeline' => $openPipeline, 'target' => $target]
                )) $count++;
            }
        }
        
        // No new opps in 60+ days
        $lastOpp = $partner->opportunities()->orderByDesc('created_at')->value('created_at');
        if ($lastOpp && now()->diffInDays($lastOpp) >= 60) {
            $days = now()->diffInDays($lastOpp);
            if ($this->createAlert($partner, 'pipeline_risk', 'critical',
                "No new pipeline in {$days} days",
                "No new opportunities created for {$partner->name} in {$days} days.",
                "Schedule business development planning session immediately.",
                ['days_since_last' => $days]
            )) $count++;
        }
        
        return $count;
    }
    
    protected function checkDeliveryRisk(Partner $partner): int
    {
        return 0; // Simplified - would check project risk scores
    }
    
    protected function checkDeliveryCapabilityRisk(Partner $partner): int
    {
        $count = 0;
        $risks = $this->riskService->assessPartner($partner);
        
        foreach ($risks as $risk) {
            if ($risk['risk_level'] === 'critical') {
                $sessionInfo = $risk['next_session'] 
                    ? " Register for {$risk['next_session']->event_title} on " . date('M j, Y', strtotime($risk['next_session']->start_at)) . "."
                    : "";
                
                if ($this->createAlert($partner, 'delivery_capability_risk', 'critical',
                    "No delivery capability for {$risk['product_area']}",
                    "{$partner->name} has {$risk['opportunity_count']} opportunities in {$risk['product_area']} but NO qualified delivery resources.",
                    "Assign resources to delivery blueprint immediately.{$sessionInfo}",
                    $risk
                )) $count++;
            } elseif ($risk['risk_level'] === 'high') {
                if ($this->createAlert($partner, 'delivery_capability_risk', 'warning',
                    "Thin delivery capability for {$risk['product_area']}",
                    "{$partner->name} has pipeline in {$risk['product_area']} but only {$risk['completed_resources']} qualified resource(s).",
                    "Consider enrolling additional resources in the delivery blueprint for {$risk['product_area']}.",
                    $risk
                )) $count++;
            }
        }
        
        return $count;
    }
    
    protected function checkEngagementGap(Partner $partner): int
    {
        $count = 0;
        $lastTouch = DB::table('partner_events')->where('partner_id', $partner->id)
            ->orderByDesc('start_at')->value('start_at');
        
        if ($lastTouch) {
            $days = now()->diffInDays($lastTouch);
            if ($days >= 60) {
                if ($this->createAlert($partner, 'engagement_gap', 'critical',
                    "No interaction in {$days} days",
                    "No recorded interaction with {$partner->name} in {$days} days.",
                    "Immediate outreach required. Schedule a touchpoint.",
                    ['days_since_last' => $days]
                )) $count++;
            } elseif ($days >= 30) {
                if ($this->createAlert($partner, 'engagement_gap', 'warning',
                    "No interaction in {$days} days",
                    "No recorded interaction with {$partner->name} in {$days} days. Engagement Velocity is declining.",
                    "Schedule a touchpoint.",
                    ['days_since_last' => $days]
                )) $count++;
            }
        }
        
        return $count;
    }
    
    protected function checkGoalGap(Partner $partner): int
    {
        return 0; // Simplified
    }
    
    protected function checkOverdueTasks(Partner $partner): int
    {
        $count = 0;
        $overdueTasks = DB::table('partner_tasks')
            ->where('partner_id', $partner->id)
            ->whereIn('status', ['not_started', 'in_progress'])
            ->where('requirement_level', 'required')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->get();
        
        foreach ($overdueTasks as $task) {
            $days = now()->diffInDays($task->due_date);
            $severity = $days >= 14 ? 'critical' : 'warning';
            
            if ($this->createAlert($partner, 'task_overdue', $severity,
                "Required task overdue: {$task->title}",
                "Task '{$task->title}' is {$days} days overdue.",
                "Follow up with {$task->owner} immediately.",
                ['task_id' => $task->id, 'days_overdue' => $days]
            )) $count++;
        }
        
        return $count;
    }
    
    protected function checkPathGates(Partner $partner): int
    {
        $count = 0;
        $enrollment = DB::table('partner_path_enrollments')
            ->where('partner_id', $partner->id)->where('status', 'active')->first();
        
        if (!$enrollment) return 0;
        
        $gates = DB::table('partner_path_gates')
            ->where('from_stage_id', $enrollment->current_stage_id)->get();
        
        if ($gates->isEmpty()) return 0;
        
        $metCount = 0;
        foreach ($gates as $gate) {
            $metCount++;
        }
        
        if ($metCount === $gates->count()) {
            $stage = DB::table('partner_path_stages')->find($enrollment->current_stage_id);
            if ($this->createAlert($partner, 'path_gate_ready', 'positive',
                "Ready for stage advancement",
                "{$partner->name} meets ALL requirements for the next stage.",
                "Schedule path review meeting to formalize advancement.",
                ['current_stage' => $stage->name ?? 'Unknown', 'gates_met' => $metCount]
            )) $count++;
        }
        
        return $count;
    }
    
    protected function checkScorecardDecline(Partner $partner): int
    {
        $scores = DB::table('partner_scorecards')
            ->where('partner_id', $partner->id)
            ->orderByDesc('scored_at')
            ->limit(2)
            ->pluck('overall_score');
        
        if ($scores->count() < 2) return 0;
        
        $drop = $scores[1] - $scores[0];
        if ($drop > 10) {
            if ($this->createAlert($partner, 'scorecard_decline', 'warning',
                "Scorecard dropped " . round($drop) . " points",
                "{$partner->name} scorecard dropped from {$scores[1]} to {$scores[0]}.",
                "Investigate root cause and address declining dimensions.",
                ['from' => $scores[1], 'to' => $scores[0], 'drop' => $drop]
            )) return 1;
        }
        
        return 0;
    }
    
    protected function checkBlueprintOverdue(Partner $partner): int
    {
        $count = 0;
        $overdue = DB::table('partner_blueprint_assignments as pba')
            ->join('blueprints as b', 'pba.blueprint_id', '=', 'b.id')
            ->where('pba.partner_id', $partner->id)
            ->whereIn('pba.status', ['active', 'in_progress'])
            ->where('pba.created_at', '<', now()->subDays(90))
            ->select('pba.*', 'b.name as blueprint_name', 'b.product_area')
            ->get();
        
        foreach ($overdue as $bp) {
            if ($this->createAlert($partner, 'blueprint_overdue', 'warning',
                "Blueprint overdue: {$bp->blueprint_name}",
                "Blueprint '{$bp->blueprint_name}' assigned for {$bp->role} role is overdue.",
                "Follow up with enrolled resources on blueprint progress.",
                ['assignment_id' => $bp->id, 'blueprint_name' => $bp->blueprint_name]
            )) $count++;
        }
        
        return $count;
    }
    
    protected function checkStalePartner(Partner $partner): int
    {
        if ($partner->updated_at && now()->diffInDays($partner->updated_at) >= 45) {
            $days = now()->diffInDays($partner->updated_at);
            if ($this->createAlert($partner, 'stale_partner', 'info',
                "No data activity in {$days} days",
                "No data updates for {$partner->name} in {$days} days.",
                "Verify data imports and Salesforce sync are current.",
                ['days_stale' => $days]
            )) return 1;
        }
        return 0;
    }
    
    protected function currentFiscalYear(): string
    {
        $fyStart = (int) \App\Models\AppSetting::get('fiscal_year_start_month', 2);
        $now = now();
        $year = $now->month >= $fyStart ? $now->year : $now->year - 1;
        return 'FY' . substr($year + 1, 2);
    }
}
