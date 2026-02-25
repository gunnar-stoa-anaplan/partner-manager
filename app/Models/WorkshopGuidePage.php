<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopGuidePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_guide_id',
        'title',
        'slug',
        'section',
        'sort_order',
        'lab_id',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────

    public function guide(): BelongsTo
    {
        return $this->belongsTo(WorkshopGuide::class, 'workshop_guide_id');
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }
}
