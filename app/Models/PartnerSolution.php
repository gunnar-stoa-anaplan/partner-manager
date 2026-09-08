<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerSolution extends Model
{
    protected $fillable = [
        'partner_id', 'name', 'description', 'product_area',
        'industry_focus', 'lifecycle_stage', 'stage_entered_at',
        'status', 'demo_url', 'documentation_url', 'estimated_acv',
        'deployment_duration_weeks', 'created_by', 'notes',
    ];

    protected $casts = [
        'stage_entered_at' => 'datetime',
        'estimated_acv' => 'decimal:2',
    ];

    const LIFECYCLE_STAGES = [
        'build' => 'Build',
        'market' => 'Market',
        'sell' => 'Sell',
        'grow' => 'Grow',
    ];

    const STATUSES = [
        'active' => 'Active',
        'paused' => 'Paused',
        'retired' => 'Retired',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stageHistory()
    {
        return $this->hasMany(SolutionStageHistory::class)->orderByDesc('transitioned_at');
    }
}
