<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerPathGate extends Model
{
    protected $fillable = [
        'partner_path_id', 'from_stage_id', 'to_stage_id',
        'requirement_key', 'requirement_label', 'comparison',
        'threshold_value', 'unit', 'description', 'sort_order',
    ];

    protected $casts = [
        'threshold_value' => 'decimal:2',
    ];

    public function path()
    {
        return $this->belongsTo(PartnerPath::class, 'partner_path_id');
    }

    public function fromStage()
    {
        return $this->belongsTo(PartnerPathStage::class, 'from_stage_id');
    }

    public function toStage()
    {
        return $this->belongsTo(PartnerPathStage::class, 'to_stage_id');
    }

    public function taskTemplates()
    {
        return $this->hasMany(TaskTemplate::class, 'gate_requirement_id');
    }
}
