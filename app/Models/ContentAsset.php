<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentAsset extends Model
{
    use HasFactory;

    public const TYPES = [
        'pitch_deck' => 'Pitch Deck',
        'icp' => 'ICP',
        'datasheet' => 'Datasheet',
        'faq' => 'FAQ',
        'positioning' => 'Positioning & Messaging',
        'seismic_course' => 'Seismic Course',
        'consensus_demo' => 'Consensus Demo',
        'feature_demo' => 'Feature Demo Video',
        'technical_doc' => 'Technical Documentation',
        'academy_course' => 'Academy Course',
    ];

    public const PERSONAS = ['sales', 'pre_sales', 'delivery', 'all'];

    public const LOBS = ['Finance', 'HR', 'RPM', 'Retail', 'SCM', 'Platform'];

    public const STATUSES = ['available', 'partial', 'not_available', 'deprecated'];

    protected $fillable = [
        'name',
        'type',
        'lob',
        'application',
        'persona',
        'url',
        'owner_name',
        'secondary_owner_name',
        'status',
        'core_app',
        'launch_year',
        'is_priority_need',
        'is_launching_in_progress',
        'validated_at',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_priority_need' => 'boolean',
            'is_launching_in_progress' => 'boolean',
            'validated_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    public function scopeForPersona(Builder $query, string $persona): Builder
    {
        return $query->whereIn('persona', [$persona, 'all']);
    }

    public function scopeForLob(Builder $query, string $lob): Builder
    {
        return $query->where('lob', $lob);
    }

    public function scopeForApplication(Builder $query, string $application): Builder
    {
        return $query->where('application', $application);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
