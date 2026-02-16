<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerPathEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id', 'partner_path_id', 'current_stage_id',
        'enrolled_at', 'stage_entered_at', 'last_reviewed_at',
        'reviewed_by', 'status', 'notes',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'stage_entered_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function path()
    {
        return $this->belongsTo(PartnerPath::class, 'partner_path_id');
    }

    public function currentStage()
    {
        return $this->belongsTo(PartnerPathStage::class, 'current_stage_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function tasks()
    {
        return $this->hasMany(PartnerTask::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
