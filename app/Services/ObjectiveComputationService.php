<?php

namespace App\Services;

use App\Models\BlueprintEnrollment;
use App\Models\Certification;
use App\Models\EnablementRegistration;
use App\Models\PsmObjective;
use Illuminate\Support\Facades\DB;

class ObjectiveComputationService
{
    /**
     * Compute the current actual value for an objective based on its metric_type + metric_config.
     */
    public function compute(PsmObjective $objective): float
    {
        $partnerId = $objective->partner_id;
        $config = $objective->metric_config ?? [];

        return match ($objective->metric_type) {
            'blueprint_completions' => $this->computeBlueprintCompletions($partnerId, $config),
            'workshop_registrations' => $this->computeWorkshopRegistrations($partnerId, $config),
            'certifications' => $this->computeCertifications($partnerId, $config),
            'pipeline_value' => $this->computePipelineValue($partnerId, $config),
            'opportunity_count' => $this->computeOpportunityCount($partnerId, $config),
            'resources_registered' => $this->computeResourcesRegistered($partnerId),
            'enablement_sessions' => $this->computeEnablementSessions($partnerId),
            'projects_delivered' => $this->computeProjectsDelivered($partnerId),
            'marketplace_completion' => $this->computeMarketplaceCompletion($partnerId),
            'manual' => (float) $objective->current_value,
            default => (float) $objective->current_value,
        };
    }

    /**
     * Compute the actual value and update the objective. Auto-completes if target met.
     */
    public function computeAndUpdate(PsmObjective $objective): PsmObjective
    {
        if ($objective->status !== 'active') {
            return $objective;
        }

        $actual = $this->compute($objective);
        $objective->current_value = $actual;

        // Auto-complete when actual >= target
        if ($objective->target_value > 0 && $actual >= $objective->target_value) {
            $objective->status = 'completed';
            $objective->completed_at = now();
        }

        // Auto-miss when past deadline and not met
        if ($objective->target_date->isPast() && $objective->status === 'active') {
            $objective->status = 'missed';
        }

        $objective->save();

        return $objective;
    }

    /**
     * Batch update all active objectives. Returns count updated.
     */
    public function computeAll(): int
    {
        $count = 0;

        PsmObjective::where('status', 'active')
            ->where('metric_type', '!=', 'manual')
            ->chunk(100, function ($objectives) use (&$count) {
                foreach ($objectives as $objective) {
                    $this->computeAndUpdate($objective);
                    $count++;
                }
            });

        return $count;
    }

    protected function computeBlueprintCompletions(int $partnerId, array $config): float
    {
        $query = BlueprintEnrollment::whereHas('assignment', function ($q) use ($partnerId) {
            $q->where('partner_id', $partnerId);
        })->where('status', 'completed');

        if (isset($config['blueprint_id'])) {
            $query->whereHas('assignment', function ($q) use ($config) {
                $q->where('blueprint_id', $config['blueprint_id']);
            });
        }

        return (float) $query->count();
    }

    protected function computeWorkshopRegistrations(int $partnerId, array $config): float
    {
        $query = EnablementRegistration::where('partner_id', $partnerId);

        if (isset($config['session_id'])) {
            $query->where('enablement_session_id', $config['session_id']);
        }

        return (float) $query->count();
    }

    protected function computeCertifications(int $partnerId, array $config): float
    {
        $query = Certification::where('partner_id', $partnerId)
            ->where('status', 'active');

        if (isset($config['cert_type'])) {
            $query->where('type', $config['cert_type']);
        }

        return (float) $query->count();
    }

    protected function computePipelineValue(int $partnerId, array $config): float
    {
        $query = DB::table('opportunities')
            ->where('partner_id', $partnerId)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost']);

        if (isset($config['product_area'])) {
            $query->where('product_area', $config['product_area']);
        }

        return (float) $query->sum('amount');
    }

    protected function computeOpportunityCount(int $partnerId, array $config): float
    {
        $query = DB::table('opportunities')
            ->where('partner_id', $partnerId);

        if (isset($config['stage'])) {
            $query->where('stage', $config['stage']);
        }

        if (isset($config['product_area'])) {
            $query->where('product_area', $config['product_area']);
        }

        return (float) $query->count();
    }

    protected function computeResourcesRegistered(int $partnerId): float
    {
        return (float) DB::table('contacts')
            ->where('partner_id', $partnerId)
            ->where('contact_type', 'resource')
            ->count();
    }

    protected function computeEnablementSessions(int $partnerId): float
    {
        return (float) EnablementRegistration::where('partner_id', $partnerId)
            ->where('status', 'attended')
            ->count();
    }

    protected function computeProjectsDelivered(int $partnerId): float
    {
        return (float) DB::table('projects')
            ->where('partner_id', $partnerId)
            ->where('status', 'completed')
            ->count();
    }

    protected function computeMarketplaceCompletion(int $partnerId): float
    {
        $status = DB::table('partners')
            ->where('id', $partnerId)
            ->value('marketplace_status');

        return $status === 'approved' ? 1.0 : 0.0;
    }
}
