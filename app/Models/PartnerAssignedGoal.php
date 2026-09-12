<?php

namespace App\Models;

use App\Models\Concerns\ScopedToActor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerAssignedGoal extends Model
{
    use HasFactory, ScopedToActor;

    protected $fillable = [
        'partner_id',
        'goal_template_id',
        'name',
        'category',
        'metric_key',
        'unit',
        'cadence',
        'fiscal_year',
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
        'status',
        'notes',
        'assigned_by',
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

    const STATUSES = [
        'on_track' => 'On Track',
        'at_risk' => 'At Risk',
        'behind' => 'Behind',
        'exceeded' => 'Exceeded',
        'completed' => 'Completed',
    ];

    const STATUS_COLORS = [
        'on_track' => 'success',
        'at_risk' => 'warning',
        'behind' => 'danger',
        'exceeded' => 'primary',
        'completed' => 'info',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function goalTemplate(): BelongsTo
    {
        return $this->belongsTo(GoalTemplate::class);
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getAttainmentPercentage(): float
    {
        if (! $this->target_value || $this->target_value == 0) {
            return 0;
        }

        return round(($this->actual_value / $this->target_value) * 100, 1);
    }

    public function getFormattedTargetAttribute(): string
    {
        return $this->formatValue($this->target_value);
    }

    public function getFormattedActualAttribute(): string
    {
        return $this->formatValue($this->actual_value);
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
