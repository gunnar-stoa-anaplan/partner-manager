<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceTaskTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_path_id', 'stage_id', 'category', 'requirement_level',
        'title', 'description', 'completion_criteria', 'effort_size',
        'default_owner', 'due_days_offset', 'auto_verify_rule',
        'evidence_type', 'sort_order', 'is_active', 'gate_requirement_id',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const CATEGORIES = [
        'training' => 'Training',
        'certification' => 'Certification',
        'project' => 'Project',
        'delivery' => 'Delivery',
        'enablement' => 'Enablement',
        'assessment' => 'Assessment',
    ];

    const REQUIREMENT_LEVELS = [
        'required' => 'Required',
        'recommended' => 'Recommended',
        'optional' => 'Optional',
    ];

    const EFFORT_SIZES = [
        'small' => 'Small (< 2h)',
        'medium' => 'Medium (2-8h)',
        'large' => 'Large (8h+)',
    ];

    const OWNERS = [
        'psm' => 'PSM',
        'partner' => 'Partner',
        'resource' => 'Resource',
        'joint' => 'Joint',
        'admin' => 'Admin',
    ];

    const EVIDENCE_TYPES = [
        'system_verified' => 'System Verified',
        'file_upload' => 'File Upload',
        'psm_attestation' => 'PSM Attestation',
        'url_link' => 'URL Link',
    ];

    public function path()
    {
        return $this->belongsTo(ResourcePath::class, 'resource_path_id');
    }

    public function stage()
    {
        return $this->belongsTo(ResourcePathStage::class, 'stage_id');
    }

    public function gateRequirement()
    {
        return $this->belongsTo(ResourcePathGate::class, 'gate_requirement_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks()
    {
        return $this->hasMany(ResourceTask::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
