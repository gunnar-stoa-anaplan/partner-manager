<?php

namespace App\Services;

use App\Models\PartnerPathEnrollment;
use App\Models\TaskTemplate;
use App\Models\PartnerTask;

class TaskAutoPopulationService
{
    public function populateTasksForEnrollment(PartnerPathEnrollment $enrollment): int
    {
        $templates = TaskTemplate::where('is_active', true)
            ->where(function ($q) use ($enrollment) {
                $q->where('partner_path_id', $enrollment->partner_path_id)
                  ->orWhereNull('partner_path_id');
            })
            ->where(function ($q) use ($enrollment) {
                $q->where('stage_id', $enrollment->current_stage_id)
                  ->orWhereNull('stage_id');
            })
            ->orderBy('sort_order')
            ->get();
        
        $created = 0;
        foreach ($templates as $template) {
            // Skip if equivalent task already exists
            $exists = PartnerTask::where('partner_id', $enrollment->partner_id)
                ->where('task_template_id', $template->id)
                ->whereIn('status', ['not_started', 'in_progress', 'completed'])
                ->exists();
            
            if ($exists) continue;
            
            $dueDate = $template->due_days_offset
                ? now()->addDays($template->due_days_offset)->toDateString()
                : null;
            
            PartnerTask::create([
                'partner_id' => $enrollment->partner_id,
                'task_template_id' => $template->id,
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
                'partner_path_enrollment_id' => $enrollment->id,
            ]);
            $created++;
        }
        
        return $created;
    }
}
