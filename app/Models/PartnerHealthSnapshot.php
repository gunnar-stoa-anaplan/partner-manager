<?php

namespace App\Models;

use App\Models\Concerns\ScopedToActor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerHealthSnapshot extends Model
{
    use HasFactory, ScopedToActor;

    protected $fillable = [
        'partner_id',
        'score',
        'risk_level',
        'signals',
        'dimensions',
        'snapshot_date',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:1',
            'signals' => 'array',
            'dimensions' => 'array',
            'snapshot_date' => 'date',
        ];
    }

    const RISK_LEVELS = [
        'healthy' => 'Healthy',
        'monitor' => 'Monitor',
        'watch' => 'Watch',
        'at_risk' => 'At Risk',
        'critical' => 'Critical',
    ];

    const RISK_COLORS = [
        'healthy' => 'success',
        'monitor' => 'info',
        'watch' => 'info',
        'at_risk' => 'warning',
        'critical' => 'danger',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
