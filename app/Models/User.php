<?php

namespace App\Models;

use App\Models\IdeaRoom\Bucket;
use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\Room;
use App\Models\IdeaRoom\Snapshot;
use App\Models\IdeaRoom\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'manager_id',
        'avatar',
        'preferences',
        'timezone',
        'external_id',
        'directory_source',
        'last_synced_at',
    ];

    /**
     * Attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attributes casting.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * One-to-one relationship with SalesforceCredential.
     */
    public function salesforceCredential()
    {
        return $this->hasOne(SalesforceCredential::class);
    }

    /**
     * One-to-many relationship with Partner (owned partners).
     */
    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    /**
     * Many-to-many relationship with Partner through user_partner pivot (PSM assignments).
     */
    public function assignedPartners()
    {
        return $this->belongsToMany(Partner::class, 'user_partner')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get partners where this user is assigned as PSM.
     */
    public function psmPartners()
    {
        return $this->belongsToMany(Partner::class, 'user_partner')
            ->wherePivot('role', 'psm')
            ->withTimestamps();
    }

    /**
     * Workshops created by this user.
     */
    public function workshopsCreated(): HasMany
    {
        return $this->hasMany(Workshop::class, 'created_by');
    }

    /**
     * Workshop sessions this user is a facilitator/monitor for.
     */
    public function workshopMonitorships(): HasMany
    {
        return $this->hasMany(WorkshopSessionMonitor::class);
    }

    /**
     * The user's assigned role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * The user's direct manager.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Users who report directly to this user.
     */
    public function directReports(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    /**
     * Check if user has admin-level access (admin or super_admin).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role?->slug, ['admin', 'super_admin']);
    }

    /**
     * Check if user can manage the reporting hierarchy.
     */
    public function canManageHierarchy(): bool
    {
        if ($this->bypassesPermissions()) {
            return true;
        }

        return $this->hasModulePermission('partner_management.administration');
    }

    /**
     * Check if user is a PSM.
     */
    public function isPsm(): bool
    {
        return $this->role?->slug === 'psm';
    }

    /**
     * Check if this user's role bypasses all permission checks.
     */
    public function bypassesPermissions(): bool
    {
        return $this->role?->bypass_permissions ?? false;
    }

    /**
     * Check if the user has any access (read or write) to the given module.
     */
    public function hasModulePermission(string $moduleSlug): bool
    {
        return $this->role?->hasPermission($moduleSlug) ?? false;
    }

    /**
     * Check if the user has at least read access to the given module.
     */
    public function canReadModule(string $moduleSlug): bool
    {
        return $this->hasModulePermission($moduleSlug);
    }

    /**
     * Check if the user has write access to the given module.
     */
    public function canWriteModule(string $moduleSlug): bool
    {
        return $this->role?->hasWritePermission($moduleSlug) ?? false;
    }

    /**
     * Check if this user is synced from an external directory.
     */
    public function isSyncedFromDirectory(): bool
    {
        return $this->directory_source !== null;
    }

    /**
     * Check if a given field is read-only because it's managed by directory sync.
     */
    public function isFieldReadOnly(string $field): bool
    {
        return $this->isSyncedFromDirectory()
            && in_array($field, config('team.directory.synced_fields', []));
    }

    // ── Priorities & Progress relationships ─────────────────────

    public function psmObjectives(): HasMany
    {
        return $this->hasMany(PsmObjective::class);
    }

    public function weeklyFocuses(): HasMany
    {
        return $this->hasMany(WeeklyFocus::class);
    }

    // ── Activation Path relationships ────────────────────────────

    public function activationPaths(): BelongsToMany
    {
        return $this->belongsToMany(ActivationPath::class)
            ->withPivot('assigned_by', 'assigned_at', 'completed_at', 'training_bypassed_at')
            ->withTimestamps();
    }

    public function hasCompletedActivationFor(string $featureSlug): bool
    {
        return $this->activationPaths()
            ->where('feature_slug', $featureSlug)
            ->wherePivotNotNull('completed_at')
            ->exists();
    }

    // ── Idea Room relationships ──────────────────────────────────

    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class);
    }

    public function ideaBuckets(): HasMany
    {
        return $this->hasMany(Bucket::class);
    }

    public function ideaTags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function ideaRooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function ideaSnapshots(): HasMany
    {
        return $this->hasMany(Snapshot::class, 'created_by');
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && \Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/'.$this->avatar);
        }

        // Generate anonymous avatar with user's initials
        $name = urlencode($this->name);

        return "https://ui-avatars.com/api/?name={$name}&size=200&background=667eea&color=ffffff&bold=true";
    }
}
