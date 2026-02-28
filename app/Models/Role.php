<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'display_name',
        'type',
        'is_protected',
        'bypass_permissions',
        'landing_route',
        'dashboard_group',
        'sort_order',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_protected' => 'boolean',
            'bypass_permissions' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'portal_role_id');
    }

    /**
     * Check if this role has permission for a given identifier.
     *
     * Supports "feature.section" dot notation (exact match) and
     * bare "feature" (matches any section for that feature).
     */
    public function hasPermission(string $identifier): bool
    {
        if ($this->bypass_permissions) {
            return true;
        }

        if (str_contains($identifier, '.')) {
            return $this->permissions->contains('module_slug', $identifier);
        }

        $prefix = $identifier.'.';

        return $this->permissions->contains(
            fn (RolePermission $p) => $p->module_slug === $identifier || str_starts_with($p->module_slug, $prefix),
        );
    }

    /**
     * Check if this role has write access for a given identifier.
     *
     * Supports "feature.section" and bare "feature" formats.
     */
    public function hasWritePermission(string $identifier): bool
    {
        if ($this->bypass_permissions) {
            return true;
        }

        if (str_contains($identifier, '.')) {
            return $this->permissions
                ->where('module_slug', $identifier)
                ->where('access_level', 'write')
                ->isNotEmpty();
        }

        $prefix = $identifier.'.';

        return $this->permissions
            ->filter(fn (RolePermission $p) => $p->module_slug === $identifier || str_starts_with($p->module_slug, $prefix))
            ->where('access_level', 'write')
            ->isNotEmpty();
    }

    /**
     * Get the access level for a given identifier (null if no access).
     *
     * For bare "feature" format, returns the highest level across all sections.
     */
    public function accessLevel(string $identifier): ?string
    {
        if ($this->bypass_permissions) {
            return 'write';
        }

        if (str_contains($identifier, '.')) {
            return $this->permissions
                ->where('module_slug', $identifier)
                ->first()
                ?->access_level;
        }

        $prefix = $identifier.'.';
        $levels = $this->permissions
            ->filter(fn (RolePermission $p) => $p->module_slug === $identifier || str_starts_with($p->module_slug, $prefix))
            ->pluck('access_level');

        if ($levels->contains('write')) {
            return 'write';
        }

        return $levels->first();
    }

    public function scopeInternal($query)
    {
        return $query->where('type', 'internal');
    }

    public function scopePortal($query)
    {
        return $query->where('type', 'portal');
    }
}
