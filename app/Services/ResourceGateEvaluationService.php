<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\ResourcePathGate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResourceGateEvaluationService
{
    public function evaluateGates(Contact $contact, int $fromStageId, int $toStageId): Collection
    {
        $gates = ResourcePathGate::where('from_stage_id', $fromStageId)
            ->where('to_stage_id', $toStageId)
            ->orderBy('sort_order')
            ->get();

        return $gates->map(function ($gate) use ($contact) {
            $actualValue = $this->getActualValue($contact, $gate->requirement_key);
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

    public function allGatesMet(Contact $contact, int $fromStageId, int $toStageId): bool
    {
        return $this->evaluateGates($contact, $fromStageId, $toStageId)
            ->every(fn ($g) => $g['met']);
    }

    protected function getActualValue(Contact $contact, string $key): float
    {
        return match ($key) {
            'active_certification_count' => DB::table('certifications')
                ->where('contact_id', $contact->id)->where('status', 'active')->count(),
            'mb_certification_count' => DB::table('certifications')
                ->where('contact_id', $contact->id)->where('type', 'model_builder')->where('status', 'active')->count(),
            'sa_certification_count' => DB::table('certifications')
                ->where('contact_id', $contact->id)->where('type', 'solution_architect')->where('status', 'active')->count(),
            'master_certification_count' => DB::table('certifications')
                ->where('contact_id', $contact->id)->where('type', 'master_anaplanner')->where('status', 'active')->count(),
            'training_hours' => (float) DB::table('enablement_registrations as er')
                ->join('enablement_sessions as es', 'er.enablement_session_id', '=', 'es.id')
                ->join('enablement_events as ee', 'es.enablement_event_id', '=', 'ee.id')
                ->where('er.contact_id', $contact->id)->where('er.status', 'completed')
                ->sum('ee.duration_hours'),
            'enablement_event_count' => DB::table('contact_partner_event')
                ->where('contact_id', $contact->id)->count(),
            'blueprint_enrollment_count' => DB::table('blueprint_enrollments')
                ->where('contact_id', $contact->id)->count(),
            'blueprint_completion_count' => DB::table('blueprint_enrollments')
                ->where('contact_id', $contact->id)->where('status', 'completed')->count(),
            'project_assignment_count' => DB::table('contact_project')
                ->where('contact_id', $contact->id)->count(),
            'completed_project_count' => DB::table('contact_project')
                ->join('projects', 'projects.id', '=', 'contact_project.project_id')
                ->where('contact_project.contact_id', $contact->id)
                ->where('projects.status', 'completed')->count(),
            'product_area_count' => DB::table('certifications')
                ->where('contact_id', $contact->id)->where('status', 'active')
                ->whereNotNull('product_area')->distinct()->count('product_area'),
            'avg_project_rating' => (float) (DB::table('contact_project')
                ->join('projects', 'projects.id', '=', 'contact_project.project_id')
                ->where('contact_project.contact_id', $contact->id)
                ->where('projects.status', 'completed')
                ->avg('projects.customer_satisfaction') ?? 0),
            'deal_registration_count' => DB::table('deal_registrations')
                ->where('contact_id', $contact->id)->count(),
            'won_deal_count' => DB::table('deal_registrations')
                ->where('contact_id', $contact->id)->where('status', 'approved')->count(),
            'resource_task_completion_pct' => $this->getTaskCompletionPct($contact),
            'mentoring_hours' => 0, // future integration
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

    protected function getTaskCompletionPct(Contact $contact): float
    {
        $total = DB::table('resource_tasks')->where('contact_id', $contact->id)
            ->where('requirement_level', 'required')->count();
        $completed = DB::table('resource_tasks')->where('contact_id', $contact->id)
            ->where('requirement_level', 'required')
            ->where('status', 'completed')->count();

        return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
    }
}
