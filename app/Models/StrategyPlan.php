<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrategyPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'fiscal_year',
        'period_type',
        'quarter',
        'status',
        'committed_at',
        'committed_by',
        'parent_plan_id',
        'version',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'committed_at' => 'datetime',
            'quarter' => 'integer',
            'version' => 'integer',
        ];
    }

    const STATUSES = [
        'draft' => 'Draft',
        'committed' => 'Committed',
        'active' => 'Active',
        'closed' => 'Closed',
    ];

    const STATUS_COLORS = [
        'draft' => 'secondary',
        'committed' => 'info',
        'active' => 'success',
        'closed' => 'dark',
    ];

    const PERIOD_TYPES = [
        'annual' => 'Annual',
        'quarterly' => 'Quarterly',
    ];

    public function targets(): HasMany
    {
        return $this->hasMany(StrategyPlanTarget::class);
    }

    public function assumptions(): HasMany
    {
        return $this->hasMany(StrategyPlanAssumption::class);
    }

    public function initiatives(): HasMany
    {
        return $this->hasMany(StrategyPlanInitiative::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(StrategyPlanBudget::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(StrategyPlanReview::class);
    }

    public function scenarios(): HasMany
    {
        return $this->hasMany(StrategyScenario::class, 'base_plan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function committedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'committed_by');
    }

    public function parentPlan(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_plan_id');
    }

    public function childVersions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_plan_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForFiscalYear(Builder $query, string $fiscalYear): Builder
    {
        return $query->where('fiscal_year', $fiscalYear);
    }

    public function isCommitted(): bool
    {
        return in_array($this->status, ['committed', 'active', 'closed']);
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function getTotalBudgetAttribute(): float
    {
        return $this->budgets->sum('planned_amount');
    }

    public function getTotalBudgetActualAttribute(): float
    {
        return $this->budgets->sum('actual_amount');
    }
}
