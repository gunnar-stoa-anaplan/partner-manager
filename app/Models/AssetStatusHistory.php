<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetStatusHistory extends Model
{
    protected $table = 'asset_status_history';

    public $timestamps = false;

    protected $fillable = [
        'journey_asset_id',
        'old_status',
        'new_status',
        'changed_by_user_id',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function journeyAsset(): BelongsTo
    {
        return $this->belongsTo(JourneyAsset::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
