<?php

namespace App\Services;

use App\Enums\TrainingPhase;
use App\Models\ActivationPath;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class ModuleRegistry
{
    /**
     * @var array<string, array{name: string, description: string, icon: string, group: string, enabled: bool, sections: array, extends?: array}>
     */
    private array $modules = [];

    /**
     * @var array<string, mixed>|null
     */
    private ?array $licenseData = null;

    /**
     * Per-request cache of completed feature slugs keyed by user ID.
     *
     * @var array<int, array<string, bool>>
     */
    private array $userCompletedSlugs = [];

    /**
     * Per-request cache of feature slugs that have an active path.
     *
     * @var array<string, bool>|null
     */
    private ?array $activePaths = null;

    /**
     * Register a module and its definition.
     *
     * @param  array{name: string, description: string, icon: string, group: string, enabled: bool, sections: array, extends?: array}  $definition
     */
    public function register(string $slug, array $definition): void
    {
        $this->modules[$slug] = $definition;
    }

    // ── Identifier Parsing ──────────────────────────────────────────

    /**
     * Parse a "feature" or "feature.section" identifier.
     *
     * @return array{0: string, 1: string|null}
     */
    public function parseIdentifier(string $identifier): array
    {
        if (str_contains($identifier, '.')) {
            return explode('.', $identifier, 2);
        }

        return [$identifier, null];
    }

    // ── Feature-Level Checks ────────────────────────────────────────

    /**
     * Check if a feature is active at the instance level (config + Pennant).
     * No user-specific checks — purely deployment/tenant level.
     */
    public function isFeatureActive(string $feature): bool
    {
        if (! config("modules.{$feature}.enabled", false)) {
            return false;
        }

        return Feature::for(null)->active($feature);
    }

    /**
     * Check if a module identifier is enabled for a user.
     * Accepts "feature" or "feature.section" format.
     *
     * Checks: bypass → config → Pennant → role permission → activation path.
     */
    public function isEnabled(string $identifier, ?User $user = null): bool
    {
        [$feature, $section] = $this->parseIdentifier($identifier);

        $authUser = $user ?? Auth::user();

        // Only apply internal-user checks to User models (not portal Contacts)
        $internalUser = $authUser instanceof User ? $authUser : null;

        // System roles (bypass_permissions) skip all feature gating
        if ($internalUser?->bypassesPermissions()) {
            return true;
        }

        // 1. Instance-level: config + Pennant
        if (! $this->isFeatureActive($feature)) {
            return false;
        }

        // 2. Portal contacts pass through — access is controlled by PortalCan/PortalRole middleware
        if ($internalUser === null) {
            return $authUser !== null;
        }

        // 3. Role permission check (enforced when user has a role_id)
        if ($internalUser->role_id !== null) {
            $permissionKey = $section ? "{$feature}.{$section}" : $feature;

            if (! $internalUser->hasModulePermission($permissionKey)) {
                return false;
            }
        }

        // 4. Per-user activation check
        if (! $this->hasActivePath($feature)) {
            return true;
        }

        return $this->userHasCompleted($internalUser, $feature);
    }

    /**
     * Check if a module identifier is accessible to the user — either fully
     * enabled or the user has completed the Tell phase (needs real UI access).
     * Accepts "feature" or "feature.section" format.
     */
    public function isAccessible(string $identifier, ?User $user = null): bool
    {
        if ($this->isEnabled($identifier, $user)) {
            return true;
        }

        [$feature, $section] = $this->parseIdentifier($identifier);

        $authUser = $user ?? Auth::user();

        if (! $authUser instanceof User) {
            return false;
        }

        // Instance-level must still be active
        if (! $this->isFeatureActive($feature)) {
            return false;
        }

        // Role permission is required even for Tell-phase access (when role_id is populated)
        if ($authUser->role_id !== null) {
            $permissionKey = $section ? "{$feature}.{$section}" : $feature;

            if (! $authUser->hasModulePermission($permissionKey)) {
                return false;
            }
        }

        return $this->userHasCompletedTellPhase($authUser, $feature);
    }

    // ── Section & Extension Queries ─────────────────────────────────

    /**
     * Get all sections the current user can access, structured for nav rendering.
     * Includes extended items contributed by other features.
     *
     * @return array<int, array{feature: string, section: string, label: string, icon: string, items: array, group: string}>
     */
    public function getAccessibleSections(?User $user = null): array
    {
        $sections = [];

        foreach ($this->modules as $feature => $definition) {
            if (! $this->isFeatureActive($feature)) {
                continue;
            }

            foreach ($definition['sections'] ?? [] as $sectionKey => $sectionDef) {
                $identifier = "{$feature}.{$sectionKey}";

                if (! $this->isAccessible($identifier, $user)) {
                    continue;
                }

                $items = $sectionDef['items'] ?? [];

                // Merge in items contributed by other features via extends
                $items = array_merge($items, $this->getExtendedItems($feature, $sectionKey, $user));

                $sections[] = [
                    'feature' => $feature,
                    'section' => $sectionKey,
                    'label' => $sectionDef['label'],
                    'icon' => $sectionDef['icon'],
                    'items' => $items,
                    'group' => $definition['group'] ?? 'other',
                ];
            }
        }

        return $sections;
    }

    /**
     * Get nav items contributed by other features extending this section.
     *
     * @return array<int, array{label: string, route: string, icon: string}>
     */
    public function getExtendedItems(string $targetFeature, string $targetSection, ?User $user = null): array
    {
        $items = [];
        $target = "{$targetFeature}.{$targetSection}";

        foreach ($this->modules as $sourceFeature => $definition) {
            if ($sourceFeature === $targetFeature) {
                continue;
            }

            if (! $this->isFeatureActive($sourceFeature)) {
                continue;
            }

            $extends = $definition['extends'] ?? [];

            if (! isset($extends[$target]['items'])) {
                continue;
            }

            // Contributing feature must be accessible to the user (any section)
            if (! $this->isAccessible($sourceFeature, $user)) {
                continue;
            }

            foreach ($extends[$target]['items'] as $item) {
                $item['source_feature'] = $sourceFeature;
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Get partner detail tabs from all active features' extends.partner_detail config.
     * Sorted by the 'sort' key for consistent ordering.
     *
     * @return array<int, array{label: string, slug: string, icon: string, partial: string, feature: string, section: string, sort: int}>
     */
    public function getPartnerDetailTabs(?User $user = null): array
    {
        $tabs = [];

        foreach ($this->modules as $feature => $definition) {
            if (! $this->isFeatureActive($feature)) {
                continue;
            }

            $extendsTabs = $definition['extends']['partner_detail']['tabs'] ?? [];

            foreach ($extendsTabs as $tab) {
                $section = $tab['section'] ?? null;
                $identifier = $section ? "{$feature}.{$section}" : $feature;

                if (! $this->isAccessible($identifier, $user)) {
                    continue;
                }

                $tab['feature'] = $feature;
                $tabs[] = $tab;
            }
        }

        usort($tabs, fn (array $a, array $b) => ($a['sort'] ?? 50) <=> ($b['sort'] ?? 50));

        return $tabs;
    }

    // ── Module Listings ─────────────────────────────────────────────

    /**
     * @return array<string, array{name: string, description: string, icon: string, group: string, enabled: bool, sections: array, extends?: array}>
     */
    public function enabledModules(): array
    {
        return array_filter(
            $this->modules,
            fn (array $def, string $slug) => $this->isEnabled($slug),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /**
     * @return array<string, array{name: string, description: string, icon: string, group: string, enabled: bool, sections: array, extends?: array}>
     */
    public function allModules(): array
    {
        return $this->modules;
    }

    /**
     * Get navigation sections for the current user.
     *
     * @return array<int, array{feature: string, section: string, label: string, icon: string, items: array, group: string}>
     */
    public function navigationItems(): array
    {
        return $this->getAccessibleSections();
    }

    // ── License & Entitlements ──────────────────────────────────────

    /**
     * Get per-product entitlements from the license file.
     *
     * @return array<string, array{max_users: int|null, features: array<int, string>}>|null
     */
    public function getEntitlements(): ?array
    {
        $data = $this->getLicenseData();

        return $data['entitlements'] ?? null;
    }

    /**
     * Get the per-module user limit for a given feature slug.
     */
    public function getModuleUserLimit(string $slug): ?int
    {
        $entitlements = $this->getEntitlements();

        if ($entitlements === null) {
            return null;
        }

        foreach ($entitlements as $entitlement) {
            if (in_array($slug, $entitlement['features'] ?? [], true)) {
                return $entitlement['max_users'] ?? null;
            }
        }

        return null;
    }

    // ── Activation Path Helpers ─────────────────────────────────────

    /**
     * Check if an active activation path exists for this feature slug.
     * Cached per-request to avoid repeated queries during nav rendering.
     */
    private function hasActivePath(string $slug): bool
    {
        if ($this->activePaths === null) {
            $this->activePaths = ActivationPath::where('is_active', true)
                ->pluck('feature_slug')
                ->mapWithKeys(fn (string $s) => [$s => true])
                ->all();
        }

        return $this->activePaths[$slug] ?? false;
    }

    /**
     * Check if a user has completed the activation path for a feature slug.
     * Cached per-request per-user to avoid N+1 during nav rendering.
     */
    private function userHasCompleted(User $user, string $slug): bool
    {
        if (! isset($this->userCompletedSlugs[$user->id])) {
            $this->userCompletedSlugs[$user->id] = $user->activationPaths()
                ->wherePivotNotNull('completed_at')
                ->pluck('feature_slug')
                ->mapWithKeys(fn (string $s) => [$s => true])
                ->all();
        }

        return $this->userCompletedSlugs[$user->id][$slug] ?? false;
    }

    /**
     * Check if the user has completed all Tell-phase steps for this module's path.
     * Once Tell is done they move to Show/Do which require real module access.
     */
    private function userHasCompletedTellPhase(User $user, string $slug): bool
    {
        $path = $user->activationPaths()
            ->where('feature_slug', $slug)
            ->first();

        if ($path === null) {
            return false;
        }

        $tellSteps = $path->steps()
            ->where('phase', TrainingPhase::Tell)
            ->where('is_required', true)
            ->get();

        // No Tell steps defined — path not fully configured yet, deny access
        if ($tellSteps->isEmpty()) {
            return false;
        }

        foreach ($tellSteps as $step) {
            $progress = $step->progressFor($user)->first();

            if (! $progress?->completed) {
                return false;
            }
        }

        return true;
    }

    // ── License File ────────────────────────────────────────────────

    /**
     * Read and cache the full license data from the shared agent volume.
     *
     * @return array<string, mixed>|null
     */
    private function getLicenseData(): ?array
    {
        if ($this->licenseData !== null) {
            return $this->licenseData;
        }

        $statusPath = config('version.agent_status_path');
        $file = rtrim($statusPath, '/').'/license-status.json';

        if (! file_exists($file)) {
            return null;
        }

        $this->licenseData = json_decode(file_get_contents($file), true);

        return $this->licenseData;
    }
}
