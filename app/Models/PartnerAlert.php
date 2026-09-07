<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerAlert extends Model
{
    use \App\Models\Concerns\ScopedToActor;

    protected $fillable = [
        'partner_id', 'type', 'severity', 'title', 'description',
        'recommended_action', 'data', 'status', 'acknowledged_by',
        'resolved_at', 'auto_resolve_when', 'linked_task_id',
    ];

    protected $casts = [
        'data' => 'array',
        'resolved_at' => 'datetime',
    ];

    const TYPES = [
        'certification_expiry' => 'Certification Expiry',
        'pipeline_risk' => 'Pipeline Risk',
        'delivery_risk' => 'Delivery Risk',
        'delivery_capability_risk' => 'Delivery Capability Risk',
        'engagement_gap' => 'Engagement Gap',
        'goal_gap' => 'Goal Gap',
        'stale_partner' => 'Stale Partner',
        'task_overdue' => 'Task Overdue',
        'case_escalation' => 'Case Escalation',
        'path_gate_ready' => 'Path Gate Ready',
        'path_gate_blocked' => 'Path Gate Blocked',
        'scorecard_decline' => 'Scorecard Decline',
        'blueprint_overdue' => 'Blueprint Overdue',
        'system' => 'System',
        'new_opportunity' => 'New Opportunity',
        'stage_change' => 'Stage Change',
        'close_date_slip' => 'Close Date Slip',
    ];

    const SEVERITIES = [
        'info' => 'Info',
        'warning' => 'Warning',
        'critical' => 'Critical',
        'positive' => 'Positive',
    ];

    const STATUSES = [
        'active' => 'Active',
        'acknowledged' => 'Acknowledged',
        'resolved' => 'Resolved',
        'dismissed' => 'Dismissed',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function acknowledgedByUser()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function linkedTask()
    {
        return $this->belongsTo(PartnerTask::class, 'linked_task_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', ['active', 'acknowledged']);
    }
}
