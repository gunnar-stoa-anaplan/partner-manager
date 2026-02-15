<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Blueprint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'product_area',
        'description',
        'version',
        'status',
        'created_by',
    ];

    const CATEGORIES = [
        'application' => 'Application Enablement',
        'product' => 'Product Enablement',
        'delivery' => 'Delivery Excellence',
    ];

    const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    const PRODUCT_AREAS = [
        'Supply Chain',
        'Finance',
        'Sales',
        'Workforce',
        'Platform',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function outcomes(): HasMany
    {
        return $this->hasMany(BlueprintOutcome::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(PartnerBlueprintAssignment::class);
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // ─── Methods ───────────────────────────────────────────────

    public function totalContentItems(): int
    {
        return $this->outcomes->sum(fn ($o) => $o->contentItems->count());
    }

    public function totalAssignments(): int
    {
        return $this->assignments()->count();
    }

    // ─── Boot ──────────────────────────────────────────────────

    protected static function booted()
    {
        static::creating(function ($blueprint) {
            if (empty($blueprint->slug)) {
                $blueprint->slug = Str::slug($blueprint->name);
                $original = $blueprint->slug;
                $count = 1;
                while (static::withTrashed()->where('slug', $blueprint->slug)->exists()) {
                    $blueprint->slug = $original.'-'.$count++;
                }
            }
        });
    }
}
