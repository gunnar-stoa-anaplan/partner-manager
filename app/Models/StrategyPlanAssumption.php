<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategyPlanAssumption extends Model
{
    protected $fillable = [
        'strategy_plan_id',
        'assumption_key',
        'label',
        'planned_value',
        'actual_value',
        'unit',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'planned_value' => 'decimal:4',
            'actual_value' => 'decimal:4',
        ];
    }

    const UNITS = [
        'percentage' => 'Percentage',
        'months' => 'Months',
        'currency' => 'Currency',
        'ratio' => 'Ratio',
        'days' => 'Days',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(StrategyPlan::class, 'strategy_plan_id');
    }

    public function getVariancePercentage(): float
    {
        if (! $this->planned_value || $this->planned_value == 0) {
            return 0;
        }

        return round((($this->actual_value - $this->planned_value) / $this->planned_value) * 100, 1);
    }

    public function getFormattedPlannedAttribute(): string
    {
        return $this->formatValue($this->planned_value);
    }

    public function getFormattedActualAttribute(): string
    {
        return $this->formatValue($this->actual_value ?? 0);
    }

    protected function formatValue(float $value): string
    {
        return match ($this->unit) {
            'percentage' => number_format($value * 100, 1).'%',
            'currency' => '$'.number_format($value),
            'ratio' => number_format($value, 1).'x',
            'months' => number_format($value, 0).' mo',
            'days' => number_format($value, 0).' days',
            default => number_format($value, 2),
        };
    }
}
