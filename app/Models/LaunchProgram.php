<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LaunchProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'product_id',
        'description',
        'status',
        'starts_at',
        'target_ga_date',
        'ends_at',
        'max_partners',
        'partner_strategy',
        'exclusion_reason',
        'created_by',
        'owner_id',
    ];

    const STATUSES = [
        'draft' => 'Draft',
        'accepting_nominations' => 'Accepting Nominations',
        'active' => 'Active',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    const PARTNER_STRATEGIES = [
        'include' => 'Include Partners',
        'exclude' => 'Exclude Partners',
    ];

    const STATUS_COLORS = [
        'draft' => '#868e96',
        'accepting_nominations' => '#fd7e14',
        'active' => '#28a745',
        'completed' => '#007bff',
        'cancelled' => '#dc3545',
    ];

    protected static function booted(): void
    {
        static::creating(function (LaunchProgram $program) {
            if (empty($program->slug)) {
                $program->slug = Str::slug($program->name);
            }
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'target_ga_date' => 'date',
            'ends_at' => 'date',
            'max_partners' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(LaunchProgramMilestone::class)->orderBy('sort_order');
    }

    public function programPartners(): HasMany
    {
        return $this->hasMany(LaunchProgramPartner::class);
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'launch_program_partners')
            ->withPivot('status', 'nominated_by', 'accepted_at', 'notes')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function dateChanges(): HasMany
    {
        return $this->hasMany(LaunchProgramDateChange::class)->orderByDesc('created_at');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(EnablementSession::class);
    }

    public function isAcceptingNominations(): bool
    {
        return $this->status === 'accepting_nominations';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function partnerCount(): int
    {
        return $this->programPartners()->whereNotIn('status', ['withdrawn'])->count();
    }

    public function isIncludingPartners(): bool
    {
        return $this->partner_strategy !== 'exclude';
    }

    public function completionPercentage(): float
    {
        $partners = $this->programPartners()->with('participants.milestoneCompletions')->get();
        if ($partners->isEmpty()) {
            return 0;
        }

        $total = 0;
        foreach ($partners as $partner) {
            $total += $partner->completionPercentage();
        }

        return round($total / $partners->count(), 1);
    }
}
