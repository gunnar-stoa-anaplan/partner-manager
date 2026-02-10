<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlueprintOutcome extends Model
{
    protected $fillable = [
        'blueprint_id',
        'sort_order',
        'title',
        'description',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function contentItems(): HasMany
    {
        return $this->hasMany(BlueprintContentItem::class)->orderBy('sort_order');
    }
}
