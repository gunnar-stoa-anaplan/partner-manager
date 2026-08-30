<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workshop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'short_description',
        'status',
        'archived_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    const STATUSES = [
        'draft' => 'Draft',
        'active' => 'Active',
        'archived' => 'Archived',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WorkshopSession::class);
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(WorkshopCredential::class);
    }

    public function surveyTemplates(): HasMany
    {
        return $this->hasMany(WorkshopSurveyTemplate::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(WorkshopPage::class)->orderBy('sort_order');
    }

    /**
     * Pages a partner participant is allowed to see - excludes
     * facilitator-only reference material (run-of-show scripts, launch
     * toolkits) that should never reach the portal.
     */
    public function participantPages(): HasMany
    {
        return $this->pages()->participantFacing();
    }

    public function facilitatorPages(): HasMany
    {
        return $this->pages()->facilitatorOnly();
    }

    public function toolkitAssets(): HasMany
    {
        return $this->hasMany(WorkshopToolkitAsset::class);
    }
}
