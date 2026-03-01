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
     * @var array<string, array{name: string, description: string, icon: string, group: string, enabled: bool, training_mode: string, sections: array, extends?: array}>
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
     * Per-request cache of bypassed feature slugs keyed by user ID.
     *
     * @var array<int, array<string, bool>>
     */
    private array $userBypassedSlugs = [];

    /**
     * Per-request cache of training progress keyed by user ID.
     *
     * @var array<int, array<string, array{completed: int, total: int, assigned: bool}>>
     */
    private array $userTrainingProgress = [];

    /**
     * Per-request cache of section unlocks from graduated training steps.
     *
     * @var array<int, array<string, array<string, bool>>>
     */
    private array $userSectionUnlocks = [];

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

    // ── Feature State ────────────────────────────────────────────────

    /**
     * Determine the lifecycle state of a feature for a user.
     *
     * Returns: hidden, locked, nudge, unlocked
     *
     * - hidden:   feature inactive or user's role has no permission
     * - locked:   training_mode=required and user hasn't completed training
     * - nudge:    training_mode=recommended, full access with training prompt
     * - unlocked: full access (training complete, bypassed, or not required)
     */
    public function getFeatureState(string $feature, ?User $user = null): string
    {
        $authUser = $user ?? Auth::user();
        $internalUser = $authUser instanceof User ? $authUser : null;

        // Bypass roles always see everything unlocked
        if ($internalUser?->bypassesPermissions()) {
            return 'unlocked';
        }

        // Feature must be active at instance level
        if (! $this->isFeatureActive($feature)) {
            return 'hidden';
        }

        // Portal contacts pass through
        if ($internalUser === null) {
            return $authUser !== null ? 'unlocked' : 'hidden';
        }

        // Role permission check
        if ($internalUser->role_id !== null && ! $internalUser->hasModulePermission($feature)) {
            return 'hidden';
        }

        // Check training mode
        $trainingMode = $this->modules[$feature]['training_mode'] ?? 'none';

        if ($trainingMode === 'none') {
            return 'unlocked';
        }

        if ($trainingMode === 'recommended') {
            return 'nudge';
        }

        // training_mode = required
        if (! $this->hasActivePath($feature)) {
            return 'unlocked'; // No training path configured yet
        }

        if ($this->userHasBypassed($internalUser, $feature)) {
            return 'unlocked';
        }

        if ($this->userHasCompleted($internalUser, $feature)) {
            return 'unlocked';
        }

        return 'locked';
    }

    /**
     * Determine the lifecycle state of a specific section within a feature.
     * Supports graduated unlock via unlocks_sections on training steps.
     */
    public function getSectionState(string $feature, string $sectionKey, ?User $user = null): string
    {
        $authUser = $user ?? Auth::user();
        $internalUser = $authUser instanceof User ? $authUser : null;

        // Bypass roles skip all checks
        if ($internalUser?->bypassesPermissions()) {
            return 'unlocked';
        }

        // Feature must be active
        if (! $this->isFeatureActive($feature)) {
            return 'hidden';
        }

        // Portal contacts pass through
        if ($internalUser === null) {
            return $authUser !== null ? 'unlocked' : 'hidden';
        }

        // Section-level role permission check
        $permissionKey = "{$feature}.{$sectionKey}";
        if ($internalUser->role_id !== null && ! $internalUser->hasModulePermission($permissionKey)) {
            return 'hidden';
        }

        $featureState = $this->getFeatureState($feature, $user);

        // If the feature is already unlocked/nudge/hidden, sections inherit that state
        if ($featureState !== 'locked') {
            return $featureState;
        }

        // Feature is locked — check graduated unlock
        if ($this->hasSectionUnlock($internalUser, $feature, $sectionKey)) {
            return 'unlocked';
        }

        return 'locked';
    }

    // ── Feature-Level Checks ────────────────────────────────────────

    /**
     * Check if a feature is active at the instance level (registered + Pennant).
     * No user-specific checks — purely deployment/tenant level.
     */
    public function isFeatureActive(string $feature): bool
    {
        if (! ($this->modules[$feature]['enabled'] ?? false)) {
            return false;
        }

        return Feature::for(null)->active($feature);
    }

    /**
     * Toggle a module's enabled flag in the registry. Useful for tests.
     */
    public function setModuleEnabled(string $slug, bool $enabled): void
    {
        if (isset($this->modules[$slug])) {
            $this->modules[$slug]['enabled'] = $enabled;
        }
    }

    /**
     * Set a module's training mode in the registry. Useful for tests.
     */
    public function setTrainingMode(string $slug, string $mode): void
    {
        if (isset($this->modules[$slug])) {
            $this->modules[$slug]['training_mode'] = $mode;
        }
    }

    /**
     * Check if a module identifier is enabled for a user (full functional access).
     * Accepts "feature" or "feature.section" format.
     *
     * Returns true when state is unlocked or nudge (recommended = full access).
     */
    public function isEnabled(string $identifier, ?User $user = null): bool
    {
        [$feature, $section] = $this->parseIdentifier($identifier);

        if ($section !== null) {
            $state = $this->getSectionState($feature, $section, $user);
        } else {
            $state = $this->getFeatureState($feature, $user);
        }

        return in_array($state, ['unlocked', 'nudge'], true);
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
     * Get all sections for the nav, annotated with lifecycle state.
     * Includes locked sections (unlike getAccessibleSections which hides them).
     *
     * @return array<int, array{feature: string, section: string, label: string, icon: string, items: array, group: string, state: string, training_progress: array|null}>
     */
    public function getNavigationSections(?User $user = null): array
    {
        $sections = [];
        $authUser = $user ?? Auth::user();
        $bypass = $authUser instanceof User && $authUser->bypassesPermissions();

        foreach ($this->modules as $feature => $definition) {
            if (! $bypass && ! $this->isFeatureActive($feature)) {
                continue;
            }

            foreach ($definition['sections'] ?? [] as $sectionKey => $sectionDef) {
                $state = $bypass ? 'unlocked' : $this->getSectionState($feature, $sectionKey, $user);

                if ($state === 'hidden') {
                    continue;
                }

                $items = $sectionDef['items'] ?? [];

                // Only merge extended items for unlocked/nudge sections
                if ($state !== 'locked') {
                    $items = array_merge($items, $this->getExtendedItems($feature, $sectionKey, $user));
                }

                $trainingProgress = null;
                if ($state === 'locked' || $state === 'nudge') {
                    $trainingProgress = $this->getTrainingProgress($feature, $user);
                }

                $sections[] = [
                    'feature' => $feature,
                    'section' => $sectionKey,
                    'label' => $sectionDef['label'],
                    'icon' => $sectionDef['icon'],
                    'items' => $items,
                    'group' => $definition['group'] ?? 'other',
                    'state' => $state,
                    'training_progress' => $trainingProgress,
                ];
            }
        }

        return $sections;
    }

    /**
     * Get all sections the current user can access (backward-compatible).
     * Filters out locked sections — only returns unlocked/nudge.
     *
     * @return array<int, array{feature: string, section: string, label: string, icon: string, items: array, group: string}>
     */
    public function getAccessibleSections(?User $user = null): array
    {
        return array_values(array_filter(
            $this->getNavigationSections($user),
            fn (array $section) => in_array($section['state'], ['unlocked', 'nudge'], true),
        ));
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
        $authUser = $user ?? Auth::user();
        $bypass = $authUser instanceof User && $authUser->bypassesPermissions();

        foreach ($this->modules as $sourceFeature => $definition) {
            if ($sourceFeature === $targetFeature) {
                continue;
            }

            if (! $bypass && ! $this->isFeatureActive($sourceFeature)) {
                continue;
            }

            $extends = $definition['extends'] ?? [];

            if (! isset($extends[$target]['items'])) {
                continue;
            }

            // Contributing feature must be enabled (unlocked/nudge) for the user
            if (! $this->isEnabled($sourceFeature, $user)) {
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
     * Get partner detail tabs from all active features, annotated with state.
     * Sorted by the 'sort' key for consistent ordering.
     *
     * @return array<int, array{label: string, slug: string, icon: string, partial: string, feature: string, section: string, sort: int, state: string}>
     */
    public function getPartnerDetailTabs(?User $user = null): array
    {
        $tabs = [];
        $authUser = $user ?? Auth::user();
        $bypass = $authUser instanceof User && $authUser->bypassesPermissions();

        foreach ($this->modules as $feature => $definition) {
            if (! $bypass && ! $this->isFeatureActive($feature)) {
                continue;
            }

            $extendsTabs = $definition['extends']['partner_detail']['tabs'] ?? [];

            foreach ($extendsTabs as $tab) {
                $section = $tab['section'] ?? null;
                $state = $bypass ? 'unlocked' : ($section
                    ? $this->getSectionState($feature, $section, $user)
                    : $this->getFeatureState($feature, $user));

                if ($state === 'hidden') {
                    continue;
                }

                $tab['feature'] = $feature;
                $tab['state'] = $state;
                $tabs[] = $tab;
            }
        }

        usort($tabs, fn (array $a, array $b) => ($a['sort'] ?? 50) <=> ($b['sort'] ?? 50));

        return $tabs;
    }

    // ── Training Progress ───────────────────────────────────────────

    /**
     * Get training progress for a feature for a user.
     *
     * @return array{completed: int, total: int, assigned: bool}
     */
    public function getTrainingProgress(string $feature, ?User $user = null): array
    {
        $authUser = $user ?? Auth::user();

        if (! $authUser instanceof User) {
            return ['completed' => 0, 'total' => 0, 'assigned' => false];
        }

        $this->loadUserTrainingProgress($authUser);

        return $this->userTrainingProgress[$authUser->id][$feature]
            ?? ['completed' => 0, 'total' => 0, 'assigned' => false];
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
        $this->loadUserActivationData($user);

        return $this->userCompletedSlugs[$user->id][$slug] ?? false;
    }

    /**
     * Check if a user has been granted early access (training bypass).
     * Cached per-request per-user.
     */
    private function userHasBypassed(User $user, string $slug): bool
    {
        $this->loadUserActivationData($user);

        return $this->userBypassedSlugs[$user->id][$slug] ?? false;
    }

    /**
     * Load both completed and bypassed slugs for a user in a single query.
     */
    private function loadUserActivationData(User $user): void
    {
        if (isset($this->userCompletedSlugs[$user->id])) {
            return;
        }

        $paths = $user->activationPaths()->get();

        $this->userCompletedSlugs[$user->id] = $paths
            ->filter(fn ($path) => $path->pivot->completed_at !== null)
            ->pluck('feature_slug')
            ->mapWithKeys(fn (string $s) => [$s => true])
            ->all();

        $this->userBypassedSlugs[$user->id] = $paths
            ->filter(fn ($path) => $path->pivot->training_bypassed_at !== null)
            ->pluck('feature_slug')
            ->mapWithKeys(fn (string $s) => [$s => true])
            ->all();
    }

    /**
     * Load training progress (step counts) for all assigned paths for a user.
     */
    private function loadUserTrainingProgress(User $user): void
    {
        if (isset($this->userTrainingProgress[$user->id])) {
            return;
        }

        $this->userTrainingProgress[$user->id] = [];

        $paths = $user->activationPaths()
            ->with('steps.allProgress')
            ->get();

        foreach ($paths as $path) {
            $totalRequired = $path->steps->where('is_required', true)->count();
            $completedRequired = $path->steps
                ->where('is_required', true)
                ->filter(fn ($s) => $s->progressFor($user)->first()?->completed)
                ->count();

            $this->userTrainingProgress[$user->id][$path->feature_slug] = [
                'completed' => $completedRequired,
                'total' => $totalRequired,
                'assigned' => true,
            ];
        }
    }

    /**
     * Check if a specific section has been unlocked via graduated training steps.
     */
    private function hasSectionUnlock(User $user, string $feature, string $sectionKey): bool
    {
        $this->loadUserSectionUnlocks($user, $feature);

        return $this->userSectionUnlocks[$user->id][$feature][$sectionKey] ?? false;
    }

    /**
     * Load section-level unlock data from steps with unlocks_sections defined.
     */
    private function loadUserSectionUnlocks(User $user, string $feature): void
    {
        if (isset($this->userSectionUnlocks[$user->id][$feature])) {
            return;
        }

        $this->userSectionUnlocks[$user->id][$feature] = [];

        $path = $user->activationPaths()
            ->where('feature_slug', $feature)
            ->first();

        if ($path === null) {
            return;
        }

        $steps = $path->steps()
            ->whereNotNull('unlocks_sections')
            ->with(['allProgress' => fn ($q) => $q->where('user_id', $user->id)])
            ->get();

        // If no steps define unlocks_sections, graduated unlock is not configured
        if ($steps->isEmpty()) {
            return;
        }

        foreach ($steps as $step) {
            $progress = $step->allProgress->first();

            if ($progress?->completed) {
                foreach ($step->unlocks_sections ?? [] as $sectionKey) {
                    $this->userSectionUnlocks[$user->id][$feature][$sectionKey] = true;
                }
            }
        }
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
