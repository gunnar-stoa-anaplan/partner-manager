<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    public const TYPES = [
        'academy_course' => 'Academy Course',
        'ilt_workshop' => 'ILT Workshop',
        'specialist_engagement' => 'Specialist Engagement',
        'blueprint' => 'Blueprint',
        'reading' => 'Reading',
        'video' => 'Video',
        'mentor_session' => 'Mentor Session',
    ];

    protected $fillable = [
        'type',
        'name',
        'description',
        'url',
        'external_id',
        'duration_minutes',
        'tags',
        'metadata',
        'status',
        'scope',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'metadata' => 'array',
            'duration_minutes' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailableTo(\Illuminate\Database\Eloquent\Builder $query, Journey $journey): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where(function ($q) use ($journey) {
            $q->where('scope', 'global')
                ->orWhere('scope', 'journey:'.$journey->id);
        });
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
