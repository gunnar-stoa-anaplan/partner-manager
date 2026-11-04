<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blocker extends Model
{
    public const STATUSES = ['open', 'in_progress', 'resolved'];

    protected $fillable = [
        'journey_id',
        'journey_asset_id',
        'title',
        'description',
        'assigned_to_user_id',
        'assigned_to_external',
        'target_resolution_date',
        'status',
        'raised_in_standup_at',
        'resolved_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_resolution_date' => 'date',
            'raised_in_standup_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    public function journeyAsset(): BelongsTo
    {
        return $this->belongsTo(JourneyAsset::class);
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
