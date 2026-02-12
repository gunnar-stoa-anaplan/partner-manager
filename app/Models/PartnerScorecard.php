<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerScorecard extends Model
{
    protected $fillable = [
        'partner_id', 'scored_at', 'overall_score',
        'certification_score', 'pipeline_score', 'delivery_score',
        'engagement_score', 'growth_score', 'self_sufficiency_score',
        'tier', 'gate_eligible', 'score_details', 'scoring_config',
    ];

    protected $casts = [
        'scored_at' => 'date',
        'overall_score' => 'decimal:1',
        'certification_score' => 'decimal:1',
        'pipeline_score' => 'decimal:1',
        'delivery_score' => 'decimal:1',
        'engagement_score' => 'decimal:1',
        'growth_score' => 'decimal:1',
        'self_sufficiency_score' => 'decimal:1',
        'gate_eligible' => 'boolean',
        'score_details' => 'array',
        'scoring_config' => 'array',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('scored_at');
    }

    public function getDimensionScores(): array
    {
        return [
            'certification' => $this->certification_score,
            'pipeline' => $this->pipeline_score,
            'delivery' => $this->delivery_score,
            'engagement' => $this->engagement_score,
            'growth' => $this->growth_score,
            'self_sufficiency' => $this->self_sufficiency_score,
        ];
    }
}
