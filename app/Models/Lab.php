<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lab extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'enablement_session_id',
        'name',
        'slug',
        'description',
        'instructions',
        'golden_model_id',
        'golden_snapshot',
        'tenant',
        'status',
        'published_at',
        'duration_minutes',
        'created_by',
    ];

    const TENANTS = [
        'ifp' => 'IFP',
        'owp' => 'OWP',
        'spm' => 'SPM',
    ];

    const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    const STATUS_COLORS = [
        'draft' => '#868e96',
        'published' => '#28a745',
        'archived' => '#6c757d',
    ];

    /**
     * Get the services config key for this lab's tenant.
     */
    public function tenantConfigKey(): string
    {
        return 'anaplan_'.($this->tenant ?? 'owp');
    }

    protected static function booted(): void
    {
        static::creating(function (Lab $lab) {
            if (empty($lab->slug)) {
                $lab->slug = Str::slug($lab->name);
                $original = $lab->slug;
                $count = 1;
                while (static::withTrashed()->where('slug', $lab->slug)->exists()) {
                    $lab->slug = $original.'-'.$count++;
                }
            }
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'golden_snapshot' => 'array',
            'published_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(EnablementSession::class, 'enablement_session_id');
    }

    public function goldenModel(): BelongsTo
    {
        return $this->belongsTo(AnaplanModel::class, 'golden_model_id');
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(LabCheckpoint::class)->orderBy('sort_order');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(LabParticipant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function totalPoints(): int
    {
        return $this->checkpoints()->sum('points');
    }

    /**
     * @return array{total: int, completed: int, in_progress: int, not_started: int, avg_completion: float}
     */
    public function completionStats(): array
    {
        $participants = $this->participants;
        $total = $participants->count();

        return [
            'total' => $total,
            'completed' => $participants->where('status', 'completed')->count(),
            'in_progress' => $participants->where('status', 'in_progress')->count(),
            'not_started' => $participants->where('status', 'not_started')->count(),
            'avg_completion' => $total > 0
                ? round($participants->avg(fn ($p) => $p->completionPercentage()), 1)
                : 0,
        ];
    }
}
