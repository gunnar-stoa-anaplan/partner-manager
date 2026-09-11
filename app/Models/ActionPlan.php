<?php

namespace App\Models;

use App\Models\Concerns\ScopedToActor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionPlan extends Model
{
    use HasFactory, ScopedToActor;

    protected $fillable = [
        'partner_id', 'title', 'description', 'source', 'status',
        'priority', 'owner_type', 'owner_user_id', 'owner_contact_id',
        'due_date', 'completed_at', 'outcome', 'created_by',
        'fiscal_quarter', 'partner_event_id', 'linked_task_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    const SOURCES = [
        'qbr' => 'QBR',
        'partner_review' => 'Partner Review',
        'escalation' => 'Escalation',
        'psm_initiative' => 'PSM Initiative',
        'system_recommendation' => 'System Recommendation',
        'path_review' => 'Path Review',
    ];

    const STATUSES = [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'overdue' => 'Overdue',
    ];

    const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function ownerUser()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function ownerContact()
    {
        return $this->belongsTo(Contact::class, 'owner_contact_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function partnerEvent()
    {
        return $this->belongsTo(PartnerEvent::class);
    }

    public function linkedTask()
    {
        return $this->belongsTo(PartnerTask::class, 'linked_task_id');
    }

    public function items()
    {
        return $this->hasMany(ActionPlanItem::class)->orderBy('sort_order');
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled'])
            ->where('due_date', '<', now());
    }
}
