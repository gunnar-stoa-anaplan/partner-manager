<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsmObjective extends Model
{
    /** @use HasFactory<\Database\Factories\PsmObjectiveFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'partner_id',
        'title',
        'description',
        'category',
        'metric_type',
        'metric_config',
        'target_value',
        'baseline_value',
        'current_value',
        'target_date',
        'status',
        'completed_at',
        'suggested_by',
        'source_signal',
        'priority',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'metric_config' => 'array',
            'target_value' => 'decimal:2',
            'baseline_value' => 'decimal:2',
            'current_value' => 'decimal:2',
            'target_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    const CATEGORIES = [
        'enablement' => 'Enablement',
        'pipeline' => 'Pipeline',
        'delivery' => 'Delivery',
        'certification' => 'Certification',
        'general' => 'General',
    ];

    const METRIC_TYPES = [
        'blueprint_completions' => 'Blueprint Completions',
        'workshop_registrations' => 'Workshop Registrations',
        'certifications' => 'Certifications',
        'pipeline_value' => 'Pipeline Value',
        'opportunity_count' => 'Opportunity Count',
        'resources_registered' => 'Resources Registered',
        'enablement_sessions' => 'Enablement Sessions',
        'projects_delivered' => 'Projects Delivered',
        'marketplace_completion' => 'Marketplace Completion',
        'manual' => 'Manual',
    ];

    const STATUSES = [
        'active' => 'Active',
        'completed' => 'Completed',
        'missed' => 'Missed',
        'cancelled' => 'Cancelled',
    ];

    const STATUS_COLORS = [
        'active' => 'primary',
        'completed' => 'success',
        'missed' => 'danger',
        'cancelled' => 'secondary',
    ];

    const PRIORITIES = [
        1 => 'High',
        2 => 'Medium',
        3 => 'Low',
    ];

    const PRIORITY_COLORS = [
        1 => 'danger',
        2 => 'warning',
        3 => 'info',
    ];

    const CATEGORY_COLORS = [
        'enablement' => 'info',
        'pipeline' => 'primary',
        'delivery' => 'warning',
        'certification' => 'success',
        'general' => 'secondary',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function suggestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suggested_by');
    }

    public function progressPercentage(): float
    {
        if (! $this->target_value || $this->target_value == 0) {
            return 0;
        }

        $netProgress = $this->current_value - $this->baseline_value;
        $netTarget = $this->target_value - $this->baseline_value;

        if ($netTarget <= 0) {
            return $this->current_value >= $this->target_value ? 100 : 0;
        }

        return min(100, round(($netProgress / $netTarget) * 100, 1));
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active' && $this->target_date->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPartner($query, int $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }
}
