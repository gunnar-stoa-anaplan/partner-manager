<?php

namespace App\Services;

use App\Models\ResourcePathEnrollment;
use App\Models\ResourceTask;
use App\Models\ResourceTaskTemplate;

class ResourceTaskAutoPopulationService
{
    public function populateTasksForEnrollment(ResourcePathEnrollment $enrollment): int
    {
        $templates = ResourceTaskTemplate::where('is_active', true)
            ->where(function ($q) use ($enrollment) {
                $q->where('resource_path_id', $enrollment->resource_path_id)
                    ->orWhereNull('resource_path_id');
            })
            ->where(function ($q) use ($enrollment) {
                $q->where('stage_id', $enrollment->current_stage_id)
                    ->orWhereNull('stage_id');
            })
            ->orderBy('sort_order')
            ->get();

        $created = 0;
        foreach ($templates as $template) {
            $exists = ResourceTask::where('contact_id', $enrollment->contact_id)
                ->where('resource_task_template_id', $template->id)
                ->whereIn('status', ['not_started', 'in_progress', 'completed'])
                ->exists();

            if ($exists) {
                continue;
            }

            $dueDate = $template->due_days_offset
                ? now()->addDays($template->due_days_offset)->toDateString()
                : null;

            ResourceTask::create([
                'contact_id' => $enrollment->contact_id,
                'partner_id' => $enrollment->partner_id,
                'resource_task_template_id' => $template->id,
                'title' => $template->title,
                'description' => $template->description,
                'category' => $template->category,
                'requirement_level' => $template->requirement_level,
                'status' => 'not_started',
                'priority' => $template->requirement_level === 'required' ? 'high' : 'medium',
                'effort_size' => $template->effort_size,
                'owner' => $template->default_owner,
                'due_date' => $dueDate,
                'stage_id' => $enrollment->current_stage_id,
                'resource_path_enrollment_id' => $enrollment->id,
            ]);
            $created++;
        }

        return $created;
    }
}
