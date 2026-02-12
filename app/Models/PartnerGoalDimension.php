<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerGoalDimension extends Model
{
    protected $fillable = [
        'partner_goal_id', 'dimension', 'target_value', 'actual_value',
        'unit', 'label', 'q1_target', 'q2_target', 'q3_target', 'q4_target',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'q1_target' => 'decimal:2',
        'q2_target' => 'decimal:2',
        'q3_target' => 'decimal:2',
        'q4_target' => 'decimal:2',
    ];

    const DIMENSIONS = [
        'sourced_nnacv' => 'Sourced NNACV',
        'influenced_nnacv' => 'Influenced NNACV',
        'certification_mb' => 'Model Builder Certifications',
        'certification_sa' => 'Solution Architect Certifications',
        'projects_delivered' => 'Projects Delivered',
        'projects_ontime_pct' => 'Projects On-Time %',
        'enablement_sessions' => 'Enablement Sessions',
        'pipeline_coverage_ratio' => 'Pipeline Coverage Ratio',
        'win_rate' => 'Win Rate',
        'customer_satisfaction' => 'Customer Satisfaction',
        'partner_originated_pct' => 'Partner Originated %',
        'solutions_registered' => 'Solutions Registered',
        'pa_supply_chain' => 'Supply Chain Pipeline',
        'pa_finance' => 'Finance Pipeline',
        'pa_sales' => 'Sales Pipeline',
        'pa_workforce' => 'Workforce Pipeline',
        'pa_platform' => 'Platform Pipeline',
        'custom' => 'Custom',
    ];

    public function goal()
    {
        return $this->belongsTo(PartnerGoal::class, 'partner_goal_id');
    }

    public function getAttainmentPercentage(): ?float
    {
        if (!$this->target_value || $this->target_value == 0) {
            return null;
        }

        return ($this->actual_value / $this->target_value) * 100;
    }
}
