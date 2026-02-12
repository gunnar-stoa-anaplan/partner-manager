<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = [
        'contact_id', 'partner_id', 'type', 'product_area', 'level',
        'status', 'issued_date', 'expiry_date', 'exam_score', 'version',
        'credential_id', 'source', 'import_id',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
    ];

    const TYPES = [
        'model_builder' => 'Model Builder',
        'solution_architect' => 'Solution Architect',
        'master_anaplanner' => 'Master Anaplanner',
        'data_integration_specialist' => 'Data Integration Specialist',
        'planning_consultant' => 'Planning Consultant',
    ];

    const LEVELS = [
        'associate' => 'Associate',
        'professional' => 'Professional',
        'master' => 'Master',
    ];

    const STATUSES = [
        'active' => 'Active',
        'expired' => 'Expired',
        'in_progress' => 'In Progress',
        'scheduled' => 'Scheduled',
        'revoked' => 'Revoked',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 90)
    {
        return $query->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now());
    }

    public function isExpiringSoon($days = 90): bool
    {
        return $this->status === 'active'
            && $this->expiry_date
            && $this->expiry_date->lte(now()->addDays($days))
            && $this->expiry_date->gt(now());
    }
}
