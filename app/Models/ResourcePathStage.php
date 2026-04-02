<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourcePathStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_path_id', 'stage_number', 'name', 'description',
        'color', 'icon', 'sort_order',
    ];

    public function path()
    {
        return $this->belongsTo(ResourcePath::class, 'resource_path_id');
    }

    public function gatesFrom()
    {
        return $this->hasMany(ResourcePathGate::class, 'from_stage_id');
    }

    public function gatesTo()
    {
        return $this->hasMany(ResourcePathGate::class, 'to_stage_id');
    }

    public function enrollments()
    {
        return $this->hasMany(ResourcePathEnrollment::class, 'current_stage_id');
    }

    public function taskTemplates()
    {
        return $this->hasMany(ResourceTaskTemplate::class, 'stage_id');
    }

    public function tasks()
    {
        return $this->hasMany(ResourceTask::class, 'stage_id');
    }
}
