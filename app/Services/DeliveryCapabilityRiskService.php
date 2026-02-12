<?php

namespace App\Services;

use App\Models\Partner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DeliveryCapabilityRiskService
{
    public function assessPartner(Partner $partner): Collection
    {
        // Get open pipeline grouped by product area
        $pipeline = DB::table('opportunities')
            ->where('partner_id', $partner->id)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->whereNotNull('product_area')
            ->selectRaw('product_area, COUNT(*) as opp_count, SUM(amount) as total_amount')
            ->groupBy('product_area')
            ->get();
        
        $risks = collect();
        
        foreach ($pipeline as $area) {
            if ($area->total_amount <= 0) continue;
            
            // Check blueprint assignments for this product area with delivery role
            $assignment = DB::table('partner_blueprint_assignments as pba')
                ->join('blueprints as b', 'pba.blueprint_id', '=', 'b.id')
                ->where('pba.partner_id', $partner->id)
                ->where('b.product_area', $area->product_area)
                ->where('pba.role', 'delivery')
                ->first();
            
            // Count completed delivery resources for this area
            $completedResources = 0;
            if ($assignment) {
                $completedResources = DB::table('blueprint_enrollments')
                    ->where('partner_blueprint_assignment_id', $assignment->id)
                    ->where('status', 'completed')
                    ->count();
            }
            
            // Count certified resources for this area
            $certifiedResources = DB::table('certifications')
                ->where('partner_id', $partner->id)
                ->where('product_area', $area->product_area)
                ->where('status', 'active')
                ->count();
            
            // Determine risk level
            $riskLevel = $this->classifyRisk(
                $area->total_amount, $assignment !== null, $completedResources, $certifiedResources
            );
            
            if ($riskLevel === 'none') continue;
            
            // Find next available enablement session
            $nextSession = DB::table('enablement_sessions as es')
                ->join('enablement_events as ee', 'es.enablement_event_id', '=', 'ee.id')
                ->where('ee.product_area', $area->product_area)
                ->where('es.start_at', '>', now())
                ->where('es.status', 'active')
                ->orderBy('es.start_at')
                ->select('es.*', 'ee.title as event_title')
                ->first();
            
            $risks->push([
                'product_area' => $area->product_area,
                'pipeline_amount' => $area->total_amount,
                'opportunity_count' => $area->opp_count,
                'blueprint_assigned' => $assignment !== null,
                'completed_resources' => $completedResources,
                'certified_resources' => $certifiedResources,
                'risk_level' => $riskLevel,
                'next_session' => $nextSession,
                'blueprint_name' => $assignment->name ?? null,
            ]);
        }
        
        return $risks->sortByDesc(function ($r) {
            return match ($r['risk_level']) {
                'critical' => 3, 'high' => 2, 'warning' => 1, default => 0,
            };
        })->values();
    }
    
    protected function classifyRisk(float $amount, bool $assigned, int $completed, int $certified): string
    {
        if (!$assigned && $completed === 0 && $certified === 0) return 'critical';
        if ($assigned && $completed === 0 && $certified === 0) return 'critical';
        if ($assigned && $completed <= 1 && $certified <= 1) return 'high';
        if ($amount >= 500000 && $completed < 3 && $certified < 3) return 'high';
        if ($assigned && $completed >= 2 && $certified >= 2) return 'low';
        return 'warning';
    }
    
    public function assessAllPartners(): Collection
    {
        $results = collect();
        Partner::select('id', 'name')->chunk(50, function ($partners) use (&$results) {
            foreach ($partners as $partner) {
                $risks = $this->assessPartner($partner);
                if ($risks->isNotEmpty()) {
                    $results->push([
                        'partner_id' => $partner->id,
                        'partner_name' => $partner->name,
                        'risks' => $risks,
                    ]);
                }
            }
        });
        return $results;
    }
}
