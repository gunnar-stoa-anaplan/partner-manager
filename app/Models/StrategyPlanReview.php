<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategyPlanReview extends Model
{
    protected $fillable = [
        'strategy_plan_id',
        'review_type',
        'review_date',
        'summary',
        'reviewed_by',
        'status',
        'findings',
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'findings' => 'array',
        ];
    }

    const REVIEW_TYPES = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'annual' => 'Annual',
        'ad_hoc' => 'Ad Hoc',
    ];

    const REVIEW_TYPE_COLORS = [
        'monthly' => 'info',
        'quarterly' => 'primary',
        'annual' => 'success',
        'ad_hoc' => 'secondary',
    ];

    const STATUSES = [
        'scheduled' => 'Scheduled',
        'completed' => 'Completed',
    ];

    const STATUS_COLORS = [
        'scheduled' => 'warning',
        'completed' => 'success',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(StrategyPlan::class, 'strategy_plan_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
