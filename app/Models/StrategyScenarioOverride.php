<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategyScenarioOverride extends Model
{
    protected $fillable = [
        'strategy_scenario_id',
        'override_type',
        'reference_id',
        'original_value',
        'override_value',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'original_value' => 'decimal:4',
            'override_value' => 'decimal:4',
            'reference_id' => 'integer',
        ];
    }

    const OVERRIDE_TYPES = [
        'target' => 'Target',
        'assumption' => 'Assumption',
        'budget' => 'Budget',
    ];

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(StrategyScenario::class, 'strategy_scenario_id');
    }

    public function getDeltaAttribute(): float
    {
        return $this->override_value - $this->original_value;
    }

    public function getDeltaPercentageAttribute(): float
    {
        if (! $this->original_value || $this->original_value == 0) {
            return 0;
        }

        return round(($this->delta / $this->original_value) * 100, 1);
    }
}
