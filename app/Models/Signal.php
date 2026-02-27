<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Signal extends Model
{
    protected $fillable = [
        'partner_id',
        'channel',
        'type',
        'severity',
        'title',
        'description',
        'recommended_action',
        'confidence_score',
        'source_processor',
        'correlation_id',
        'data',
        'related_entity_ids',
        'status',
        'acknowledged_by',
        'resolved_at',
        'expires_at',
        'linked_alert_id',
    ];

    protected function casts(): array
    {
        return [
            'confidence_score' => 'float',
            'data' => 'array',
            'related_entity_ids' => 'array',
            'resolved_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    const CHANNELS = [
        'risk' => 'Risk',
        'opportunity' => 'Opportunity',
        'trend' => 'Trend',
        'anomaly' => 'Anomaly',
        'recommendation' => 'Recommendation',
        'benchmark' => 'Benchmark',
    ];

    const CHANNEL_COLORS = [
        'risk' => 'danger',
        'opportunity' => 'success',
        'trend' => 'info',
        'anomaly' => 'warning',
        'recommendation' => 'primary',
        'benchmark' => 'secondary',
    ];

    const CHANNEL_ICONS = [
        'risk' => 'alert-triangle',
        'opportunity' => 'trending-up',
        'trend' => 'activity',
        'anomaly' => 'zap',
        'recommendation' => 'lightbulb',
        'benchmark' => 'bar-chart-2',
    ];

    const SEVERITIES = [
        'info' => 'Info',
        'warning' => 'Warning',
        'critical' => 'Critical',
        'positive' => 'Positive',
    ];

    const SEVERITY_COLORS = [
        'info' => 'info',
        'warning' => 'warning',
        'critical' => 'danger',
        'positive' => 'success',
    ];

    const STATUSES = [
        'active' => 'Active',
        'acknowledged' => 'Acknowledged',
        'resolved' => 'Resolved',
        'expired' => 'Expired',
        'dismissed' => 'Dismissed',
    ];

    const STATUS_COLORS = [
        'active' => 'primary',
        'acknowledged' => 'info',
        'resolved' => 'success',
        'expired' => 'secondary',
        'dismissed' => 'dark',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function linkedAlert(): BelongsTo
    {
        return $this->belongsTo(PartnerAlert::class, 'linked_alert_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['resolved', 'expired', 'dismissed']);
    }

    public function scopeHighConfidence(Builder $query, float $min = 0.7): Builder
    {
        return $query->where('confidence_score', '>=', $min);
    }

    public function getCorrelatedSignals(): Collection
    {
        if (! $this->correlation_id) {
            return collect();
        }

        return static::where('correlation_id', $this->correlation_id)
            ->where('id', '!=', $this->id)
            ->get();
    }
}
