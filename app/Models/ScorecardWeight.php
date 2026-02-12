<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScorecardWeight extends Model
{
    protected $fillable = [
        'dimension', 'weight', 'is_active', 'updated_by',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    const DIMENSIONS = [
        'certification' => 'Certification Readiness',
        'pipeline' => 'Pipeline Maturity',
        'delivery' => 'Delivery Quality',
        'engagement' => 'Engagement Velocity',
        'growth' => 'Growth Trajectory',
        'self_sufficiency' => 'Self-Sufficiency',
    ];

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
