<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerPathStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_path_id', 'stage_number', 'name', 'description',
        'color', 'icon', 'sort_order',
    ];

    public function path()
    {
        return $this->belongsTo(PartnerPath::class, 'partner_path_id');
    }

    public function gatesFrom()
    {
        return $this->hasMany(PartnerPathGate::class, 'from_stage_id');
    }

    public function gatesTo()
    {
        return $this->hasMany(PartnerPathGate::class, 'to_stage_id');
    }

    public function enrollments()
    {
        return $this->hasMany(PartnerPathEnrollment::class, 'current_stage_id');
    }

    public function taskTemplates()
    {
        return $this->hasMany(TaskTemplate::class, 'stage_id');
    }

    public function certificationRequirements()
    {
        return $this->hasMany(CertificationRequirement::class, 'stage_id');
    }

    public function tasks()
    {
        return $this->hasMany(PartnerTask::class, 'stage_id');
    }
}
