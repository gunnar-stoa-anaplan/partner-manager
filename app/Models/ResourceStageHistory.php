<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceStageHistory extends Model
{
    use HasFactory;
    use \App\Models\Concerns\ScopedToActor;

    protected $table = 'resource_stage_history';

    protected $fillable = [
        'contact_id', 'partner_id', 'resource_path_id', 'from_stage_id', 'to_stage_id',
        'transitioned_at', 'approved_by', 'gate_results', 'notes',
    ];

    protected $casts = [
        'transitioned_at' => 'datetime',
        'gate_results' => 'array',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
