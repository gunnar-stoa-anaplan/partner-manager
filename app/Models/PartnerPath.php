<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerPath extends Model
{
    protected $fillable = [
        'name', 'description', 'is_default', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stages()
    {
        return $this->hasMany(PartnerPathStage::class)->orderBy('sort_order');
    }

    public function gates()
    {
        return $this->hasMany(PartnerPathGate::class);
    }

    public function enrollments()
    {
        return $this->hasMany(PartnerPathEnrollment::class);
    }

    public function taskTemplates()
    {
        return $this->hasMany(TaskTemplate::class);
    }

    public function certificationRequirements()
    {
        return $this->hasMany(CertificationRequirement::class);
    }

    public function stageHistory()
    {
        return $this->hasMany(PartnerStageHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
