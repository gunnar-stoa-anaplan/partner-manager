<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerStageHistory extends Model
{
    use HasFactory;

    protected $table = 'partner_stage_history';

    protected $fillable = [
        'partner_id', 'partner_path_id', 'from_stage_id', 'to_stage_id',
        'transitioned_at', 'approved_by', 'gate_results', 'notes',
    ];

    protected $casts = [
        'transitioned_at' => 'datetime',
        'gate_results' => 'array',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
