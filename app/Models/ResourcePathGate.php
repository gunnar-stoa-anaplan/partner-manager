<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourcePathGate extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_path_id', 'from_stage_id', 'to_stage_id',
        'requirement_key', 'requirement_label', 'comparison',
        'threshold_value', 'unit', 'description', 'sort_order',
    ];

    protected $casts = [
        'threshold_value' => 'decimal:2',
    ];

    public function path()
    {
        return $this->belongsTo(ResourcePath::class, 'resource_path_id');
    }

    public function fromStage()
    {
        return $this->belongsTo(ResourcePathStage::class, 'from_stage_id');
    }

    public function toStage()
    {
        return $this->belongsTo(ResourcePathStage::class, 'to_stage_id');
    }

    public function taskTemplates()
    {
        return $this->hasMany(ResourceTaskTemplate::class, 'gate_requirement_id');
    }
}
