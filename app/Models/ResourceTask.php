<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceTask extends Model
{
    use HasFactory;
    use \App\Models\Concerns\ScopedToActor;

    protected $fillable = [
        'contact_id', 'partner_id', 'resource_task_template_id',
        'title', 'description', 'category', 'requirement_level',
        'status', 'priority', 'effort_size', 'owner',
        'assigned_to_user_id', 'due_date', 'started_at',
        'completed_at', 'completed_by', 'completion_evidence',
        'evidence_file', 'auto_verified', 'auto_verified_at',
        'waived_by', 'waived_reason', 'notes',
        'stage_id', 'resource_path_enrollment_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'auto_verified_at' => 'datetime',
        'auto_verified' => 'boolean',
    ];

    const STATUSES = [
        'not_started' => 'Not Started',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'blocked' => 'Blocked',
        'waived' => 'Waived',
    ];

    const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function template()
    {
        return $this->belongsTo(ResourceTaskTemplate::class, 'resource_task_template_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function waivedByUser()
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    public function stage()
    {
        return $this->belongsTo(ResourcePathStage::class, 'stage_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(ResourcePathEnrollment::class, 'resource_path_enrollment_id');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('status', '!=', 'waived')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    public function scopeRequired($query)
    {
        return $query->where('requirement_level', 'required');
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, ['completed', 'waived']);
    }
}
