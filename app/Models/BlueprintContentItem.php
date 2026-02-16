<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlueprintContentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'blueprint_outcome_id',
        'sort_order',
        'title',
        'url',
        'duration_minutes',
        'duration_display',
        'source',
        'prerequisites',
        'gtm_applicable',
        'pre_sales_applicable',
        'delivery_applicable',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'duration_minutes' => 'integer',
    ];

    const APPLICABILITY_OPTIONS = [
        'required' => 'Required',
        'optional' => 'Optional',
        'na' => 'N/A',
    ];

    const SOURCES = [
        'Academy',
        'Seismic',
        'Highspot',
        'Anapedia',
        'Community',
        'Support',
        'App COE',
        'PS/App COE',
        'PSM',
        'Tenant',
        'Sharepoint',
        'Various',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function outcome(): BelongsTo
    {
        return $this->belongsTo(BlueprintOutcome::class, 'blueprint_outcome_id');
    }

    // ─── Methods ───────────────────────────────────────────────

    public function applicabilityForRole(string $role): string
    {
        $column = match ($role) {
            'gtm' => 'gtm_applicable',
            'pre_sales' => 'pre_sales_applicable',
            'delivery' => 'delivery_applicable',
            default => 'na',
        };

        return $this->$column ?? 'na';
    }

    public function isApplicableForRole(string $role): bool
    {
        return $this->applicabilityForRole($role) !== 'na';
    }
}
