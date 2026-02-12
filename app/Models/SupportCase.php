<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportCase extends Model
{
    protected $table = 'cases';

    protected $fillable = [
        'partner_id', 'project_id', 'account_name', 'case_number',
        'subject', 'severity', 'status', 'category', 'opened_date',
        'resolved_date', 'escalated', 'escalation_reason',
        'resolution_notes', 'resolution_days', 'import_id',
    ];

    protected $casts = [
        'opened_date' => 'date',
        'resolved_date' => 'date',
        'escalated' => 'boolean',
    ];

    const SEVERITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ];

    const STATUSES = [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'escalated' => 'Escalated',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'escalated']);
    }

    public function scopeEscalated($query)
    {
        return $query->where('escalated', true);
    }
}
