<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class EnablementEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'category',
        'product_area',
        'description',
        'objectives',
        'prerequisites',
        'target_audience',
        'duration_hours',
        'max_participants',
        'materials_url',
        'thumbnail',
        'status',
        'created_by',
    ];

    protected $casts = [
        'duration_hours' => 'decimal:2',
        'max_participants' => 'integer',
    ];

    const TYPES = [
        'workshop' => 'Workshop',
        'academy' => 'Academy',
        'certification_exam' => 'Certification Exam',
        'webinar' => 'Webinar',
        'bootcamp' => 'Bootcamp',
        'onboarding' => 'Onboarding',
        'hackathon' => 'Hackathon',
        'connect' => 'Connect',
        'other' => 'Other',
    ];

    const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    const CATEGORIES = [
        'Delivery',
        'Sales',
        'Pre-Sales',
        'Platform',
        'Awareness',
    ];

    const PRODUCT_AREAS = [
        'Applications' => [
            'IFP' => 'IFP (Integrated Financial Planning)',
            'SCM' => 'SCM (Supply Chain Management)',
            'SPM' => 'SPM (Sales Performance Management)',
            'OWP' => 'OWP (Operational Workforce Planning)',
        ],
        'Technical' => [
            'Polaris' => 'Polaris',
            'ADO' => 'ADO',
            'AI' => 'AI',
            'Forecaster' => 'Forecaster',
            'Other' => 'Other Technical',
        ],
    ];

    const TYPE_COLORS = [
        'workshop' => '#7B1FA2',
        'academy' => '#2196F3',
        'certification_exam' => '#fd7e14',
        'webinar' => '#00BCD4',
        'bootcamp' => '#dc3545',
        'onboarding' => '#20c997',
        'hackathon' => '#886ab5',
        'connect' => '#1B5E20',
        'other' => '#868e96',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function sessions(): HasMany
    {
        return $this->hasMany(EnablementSession::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations(): HasManyThrough
    {
        return $this->hasManyThrough(EnablementRegistration::class, EnablementSession::class);
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'published')
            ->whereHas('sessions', function ($q) {
                $q->where('start_at', '>', now())
                  ->where('status', '!=', 'cancelled');
            });
    }

    // ─── Methods ───────────────────────────────────────────────

    public function totalRegistrations(): int
    {
        return $this->registrations()->count();
    }

    public function upcomingSessions()
    {
        return $this->sessions()
            ->where('start_at', '>', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_at')
            ->get();
    }

    // ─── Boot ──────────────────────────────────────────────────

    protected static function booted()
    {
        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);
                $original = $event->slug;
                $count = 1;
                while (static::withTrashed()->where('slug', $event->slug)->exists()) {
                    $event->slug = $original . '-' . $count++;
                }
            }
        });
    }
}
