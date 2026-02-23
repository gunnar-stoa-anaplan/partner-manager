<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrategyScenario extends Model
{
    protected $fillable = [
        'name',
        'description',
        'base_plan_id',
        'created_by',
        'status',
    ];

    const STATUSES = [
        'draft' => 'Draft',
        'finalized' => 'Finalized',
    ];

    const STATUS_COLORS = [
        'draft' => 'secondary',
        'finalized' => 'success',
    ];

    public function basePlan(): BelongsTo
    {
        return $this->belongsTo(StrategyPlan::class, 'base_plan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(StrategyScenarioOverride::class);
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }
}
