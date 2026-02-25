<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class WorkshopGuide extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'enablement_event_id',
        'title',
        'slug',
        'description',
        'navigation',
        'product_area',
        'status',
        'requires_auth',
        'published_at',
        'estimated_duration_hours',
        'created_by',
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

    // ─── Casts ──────────────────────────────────────────────────

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'navigation' => 'array',
            'requires_auth' => 'boolean',
            'published_at' => 'datetime',
            'estimated_duration_hours' => 'decimal:2',
        ];
    }

    // ─── Boot ───────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (WorkshopGuide $guide) {
            if (empty($guide->slug)) {
                $guide->slug = Str::slug($guide->title);
                $original = $guide->slug;
                $count = 1;
                while (static::withTrashed()->where('slug', $guide->slug)->exists()) {
                    $guide->slug = $original.'-'.$count++;
                }
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────

    public function enablementEvent(): BelongsTo
    {
        return $this->belongsTo(EnablementEvent::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(WorkshopGuidePage::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ─────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // ─── Methods ────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Get the filesystem path for this guide's content.
     */
    public function storagePath(): string
    {
        return 'workshop-guides/'.$this->slug;
    }

    /**
     * Get the first page slug from the navigation structure.
     */
    public function firstPageSlug(): ?string
    {
        $navigation = $this->navigation ?? [];

        foreach ($navigation as $pages) {
            if (is_array($pages)) {
                foreach ($pages as $slug) {
                    return $slug;
                }
            }
        }

        return null;
    }

    /**
     * Get the flat list of page slugs in navigation order.
     *
     * @return array<int, string>
     */
    public function orderedPageSlugs(): array
    {
        $slugs = [];
        $navigation = $this->navigation ?? [];

        foreach ($navigation as $pages) {
            if (is_array($pages)) {
                foreach ($pages as $slug) {
                    $slugs[] = $slug;
                }
            }
        }

        return $slugs;
    }

    /**
     * Get the previous and next page slugs relative to the given page.
     *
     * @return array{prev: ?string, next: ?string}
     */
    public function surroundingPages(string $currentSlug): array
    {
        $slugs = $this->orderedPageSlugs();
        $index = array_search($currentSlug, $slugs);

        if ($index === false) {
            return ['prev' => null, 'next' => null];
        }

        return [
            'prev' => $index > 0 ? $slugs[$index - 1] : null,
            'next' => $index < count($slugs) - 1 ? $slugs[$index + 1] : null,
        ];
    }
}
