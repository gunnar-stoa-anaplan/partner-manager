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
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
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
     * All roles this user holds (multi-role). `role_id` remains the "primary"
     * role for landing/back-compat; permission checks union across every role.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    /**
     * Teams / functions this user belongs to (Sales, Marketing, Service,
     * Implementation, …) — the same teams partner contacts can join.
     */
    public function teams(): MorphToMany
    {
        return $this->morphToMany(Team::class, 'member', 'team_members')
            ->withPivot('role_in_team')
            ->withTimestamps();
    }

    /**
     * Primary role plus every pivot role, de-duplicated. The basis for all
     * permission resolution so a multi-role user gets the union of access.
     */
    public function allRoles(): Collection
    {
        if ($this->allRolesCache !== null) {
            return $this->allRolesCache;
        }

        $roles = $this->relationLoaded('roles') ? $this->roles : $this->roles()->get();
        $roles = collect($roles);

        if ($this->role_id && ! $roles->contains('id', $this->role_id) && $this->role) {
            $roles->push($this->role);
        }

        return $this->allRolesCache = $roles->unique('id')->values();
    }

    /** @var Collection|null */
    protected $allRolesCache = null;

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
        return $this->allRoles()->contains(fn (Role $r) => in_array($r->slug, ['admin', 'super_admin'], true));
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
        return $this->allRoles()->contains(fn (Role $r) => $r->slug === 'psm');
    }

    /**
     * Dashboard-only users see just the Welcome screen and the Partner Success
     * dashboards — nothing else in the app (nav hidden + routes blocked by the
     * RestrictDashboardOnly middleware).
     */
    public function isDashboardOnly(): bool
    {
        return $this->role?->slug === 'dashboard_viewer';
    }

    /**
     * Explicit privilege granting unrestricted partner data visibility: the
     * actor sees every partner regardless of ownership, assignment, or
     * reporting hierarchy. This is a capability grant (a role_permissions row),
     * NOT an implicit consequence of a role slug. See
     * docs/authorization-spec.md DECISION 5.
     */
    public const PRIVILEGE_ALL_PARTNERS = 'data_scope.all_partners';

    /**
     * Check if this user's role bypasses all permission checks.
     */
    public function bypassesPermissions(): bool
    {
        return $this->allRoles()->contains(fn (Role $r) => (bool) $r->bypass_permissions);
    }

    /**
     * Whether this user holds the explicit unrestricted-partner-visibility
     * privilege. Bypass roles (super_admin) see everything by design and are
     * handled separately in VisibilityContext; this covers only the explicit
     * grant, so no role confers company-wide visibility via its slug.
     */
    public function hasUnrestrictedPartnerVisibility(): bool
    {
        return $this->hasModulePermission(self::PRIVILEGE_ALL_PARTNERS);
    }

    /**
     * Check if the user has any access (read or write) to the given module.
     */
    public function hasModulePermission(string $moduleSlug): bool
    {
        return $this->allRoles()->contains(fn (Role $r) => $r->hasPermission($moduleSlug));
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
        return $this->allRoles()->contains(fn (Role $r) => $r->hasWritePermission($moduleSlug));
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
