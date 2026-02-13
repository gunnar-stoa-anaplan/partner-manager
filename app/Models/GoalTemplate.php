<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoalTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'metric_key',
        'default_target',
        'unit',
        'cadence',
        'is_common',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'default_target' => 'decimal:2',
            'is_common' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    const CATEGORIES = [
        'revenue' => 'Revenue Growth',
        'enablement' => 'Enablement Growth',
        'product_expertise' => 'Product Expertise',
        'delivery' => 'Delivery Readiness',
        'blueprint' => 'Blueprint Completion',
        'beta_testing' => 'Beta Testing',
    ];

    const METRIC_KEYS = [
        'sourced_nnacv' => 'Sourced NNACV',
        'influenced_nnacv' => 'Influenced NNACV',
        'total_pipeline' => 'Total Pipeline Value',
        'win_rate' => 'Win Rate',
        'certification_mb' => 'Model Builder Certifications',
        'certification_sa' => 'Solution Architect Certifications',
        'enablement_sessions' => 'Enablement Sessions Attended',
        'product_areas_covered' => 'Product Areas Covered',
        'solutions_registered' => 'Solutions Registered',
        'projects_delivered' => 'Projects Delivered',
        'projects_ontime_pct' => 'On-Time Delivery %',
        'customer_satisfaction' => 'Customer Satisfaction Score',
        'blueprints_completed' => 'Blueprints Completed',
        'blueprint_items_completed' => 'Blueprint Items Completed',
        'beta_enrollments' => 'Beta Program Enrollments',
        'beta_feedback_submitted' => 'Beta Feedback Submitted',
        'manual' => 'Manual / Custom',
    ];

    const UNITS = [
        'currency' => 'Currency ($)',
        'count' => 'Count (#)',
        'percentage' => 'Percentage (%)',
    ];

    const CADENCES = [
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'annual' => 'Annual',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedGoals(): HasMany
    {
        return $this->hasMany(PartnerAssignedGoal::class);
    }
}
