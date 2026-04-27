<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyStageItem extends Model
{
    use HasFactory;

    public const ITEM_TYPE_ASSET = 'asset';

    public const ITEM_TYPE_JOURNEY = 'journey';

    protected $fillable = [
        'journey_id',
        'stage_number',
        'item_type',
        'item_id',
        'position',
        'required',
        'added_by',
    ];

    protected function casts(): array
    {
        return [
            'stage_number' => 'integer',
            'item_id' => 'integer',
            'position' => 'integer',
            'required' => 'boolean',
        ];
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'item_id');
    }

    public function referencedJourney(): BelongsTo
    {
        return $this->belongsTo(Journey::class, 'item_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function resolveItem(): Asset|Journey|null
    {
        return match ($this->item_type) {
            self::ITEM_TYPE_ASSET => $this->asset,
            self::ITEM_TYPE_JOURNEY => $this->referencedJourney,
            default => null,
        };
    }
}
