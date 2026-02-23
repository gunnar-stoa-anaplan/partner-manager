<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategyPlanBudget extends Model
{
    protected $fillable = [
        'strategy_plan_id',
        'category',
        'label',
        'planned_amount',
        'actual_amount',
        'q1_planned',
        'q2_planned',
        'q3_planned',
        'q4_planned',
        'q1_actual',
        'q2_actual',
        'q3_actual',
        'q4_actual',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'planned_amount' => 'decimal:2',
            'actual_amount' => 'decimal:2',
            'q1_planned' => 'decimal:2',
            'q2_planned' => 'decimal:2',
            'q3_planned' => 'decimal:2',
            'q4_planned' => 'decimal:2',
            'q1_actual' => 'decimal:2',
            'q2_actual' => 'decimal:2',
            'q3_actual' => 'decimal:2',
            'q4_actual' => 'decimal:2',
        ];
    }

    const CATEGORIES = [
        'mdf' => 'Market Development Funds',
        'enablement' => 'Enablement',
        'events' => 'Events',
        'tooling' => 'Tooling',
        'headcount' => 'Headcount',
        'marketing' => 'Marketing',
        'travel' => 'Travel',
    ];

    const CATEGORY_COLORS = [
        'mdf' => 'success',
        'enablement' => 'primary',
        'events' => 'info',
        'tooling' => 'warning',
        'headcount' => 'danger',
        'marketing' => 'secondary',
        'travel' => 'dark',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(StrategyPlan::class, 'strategy_plan_id');
    }

    public function getUtilizationPercentage(): float
    {
        if (! $this->planned_amount || $this->planned_amount == 0) {
            return 0;
        }

        return round(($this->actual_amount / $this->planned_amount) * 100, 1);
    }

    public function getFormattedPlannedAttribute(): string
    {
        return '$'.number_format($this->planned_amount);
    }

    public function getFormattedActualAttribute(): string
    {
        return '$'.number_format($this->actual_amount ?? 0);
    }
}
