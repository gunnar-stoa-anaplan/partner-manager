<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlueprintProgress extends Model
{
    protected $fillable = [
        'blueprint_enrollment_id',
        'blueprint_content_item_id',
        'completed',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(BlueprintEnrollment::class, 'blueprint_enrollment_id');
    }

    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(BlueprintContentItem::class, 'blueprint_content_item_id');
    }
}
