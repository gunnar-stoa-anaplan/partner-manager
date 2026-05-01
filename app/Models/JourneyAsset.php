<?php

namespace App\Models;

use App\Observers\JourneyAssetObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([JourneyAssetObserver::class])]
class JourneyAsset extends Model
{
    public const STATUSES = ['missing', 'in_review', 'needs_revision', 'approved'];

    protected $fillable = [
        'journey_id',
        'stage_number',
        'task_id',
        'asset_name',
        'asset_kind',
        'description',
        'required',
        'linked_asset_id',
        'status',
        'assigned_to_user_id',
        'notes',
        'status_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'status_changed_at' => 'datetime',
        ];
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    public function linkedAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'linked_asset_id');
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(AssetStatusHistory::class);
    }

    public function blockers(): HasMany
    {
        return $this->hasMany(Blocker::class);
    }
}
