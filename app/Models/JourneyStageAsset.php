<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyStageAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'journey_key',
        'stage',
        'asset_id',
        'required',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'stage' => 'integer',
            'required' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ContentAsset::class, 'asset_id');
    }
}
