<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategyPlanTarget extends Model
{
    protected $fillable = [
        'strategy_plan_id',
        'category',
        'metric_key',
        'metric_label',
        'unit',
        'target_value',
        'actual_value',
        'q1_target',
        'q2_target',
        'q3_target',
        'q4_target',
        'q1_actual',
        'q2_actual',
        'q3_actual',
        'q4_actual',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'actual_value' => 'decimal:2',
            'q1_target' => 'decimal:2',
            'q2_target' => 'decimal:2',
            'q3_target' => 'decimal:2',
            'q4_target' => 'decimal:2',
            'q1_actual' => 'decimal:2',
            'q2_actual' => 'decimal:2',
            'q3_actual' => 'decimal:2',
            'q4_actual' => 'decimal:2',
        ];
    }

    const CATEGORIES = [
        'revenue' => 'Revenue',
        'pipeline' => 'Pipeline',
        'growth' => 'Growth',
        'enablement' => 'Enablement',
        'certification' => 'Certification',
        'delivery' => 'Delivery',
    ];

    const CATEGORY_COLORS = [
        'revenue' => 'success',
        'pipeline' => 'primary',
        'growth' => 'info',
        'enablement' => 'warning',
        'certification' => 'secondary',
        'delivery' => 'danger',
    ];

    const UNITS = [
        'currency' => 'Currency',
        'count' => 'Count',
        'percentage' => 'Percentage',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(StrategyPlan::class, 'strategy_plan_id');
    }

    public function getAttainmentPercentage(): float
    {
        if (! $this->target_value || $this->target_value == 0) {
            return 0;
        }

        return round(($this->actual_value / $this->target_value) * 100, 1);
    }

    public function getVariancePercentage(): float
    {
        if (! $this->target_value || $this->target_value == 0) {
            return 0;
        }

        return round((($this->actual_value - $this->target_value) / $this->target_value) * 100, 1);
    }

    public function getFormattedTargetAttribute(): string
    {
        return $this->formatValue($this->target_value);
    }

    public function getFormattedActualAttribute(): string
    {
        return $this->formatValue($this->actual_value ?? 0);
    }

    protected function formatValue(float $value): string
    {
        return match ($this->unit) {
            'currency' => '$'.number_format($value),
            'percentage' => number_format($value, 1).'%',
            default => number_format($value),
        };
    }
}
