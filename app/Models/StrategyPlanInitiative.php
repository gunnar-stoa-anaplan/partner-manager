<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategyPlanInitiative extends Model
{
    protected $fillable = [
        'strategy_plan_id',
        'title',
        'description',
        'category',
        'status',
        'owner_id',
        'target_start_date',
        'target_end_date',
        'actual_start_date',
        'actual_end_date',
        'expected_impact',
    ];

    protected function casts(): array
    {
        return [
            'target_start_date' => 'date',
            'target_end_date' => 'date',
            'actual_start_date' => 'date',
            'actual_end_date' => 'date',
        ];
    }

    const CATEGORIES = [
        'growth' => 'Growth',
        'enablement' => 'Enablement',
        'go_to_market' => 'Go-to-Market',
        'operational' => 'Operational',
        'investment' => 'Investment',
    ];

    const CATEGORY_COLORS = [
        'growth' => 'success',
        'enablement' => 'primary',
        'go_to_market' => 'info',
        'operational' => 'warning',
        'investment' => 'secondary',
    ];

    const STATUSES = [
        'planned' => 'Planned',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    const STATUS_COLORS = [
        'planned' => 'secondary',
        'in_progress' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(StrategyPlan::class, 'strategy_plan_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isOverdue(): bool
    {
        return $this->target_end_date
            && $this->target_end_date->isPast()
            && ! in_array($this->status, ['completed', 'cancelled']);
    }
}
