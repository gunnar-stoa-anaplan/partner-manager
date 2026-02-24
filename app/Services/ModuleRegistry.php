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
     * @var array<string, array{name: string, description: string, icon: string, nav_section: string, nav_items: array<int, array<string, mixed>>}>
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
     * Register a module and its navigation items.
     *
     * @param  array{name: string, description: string, icon: string, nav_section: string, nav_items: array<int, array<string, mixed>>}  $definition
     */
    public function register(string $slug, array $definition): void
    {
        $this->modules[$slug] = $definition;
    }

    public function isEnabled(string $slug, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        // Admins bypass all feature gating — full visibility regardless of flag state
        if ($user?->role === 'admin') {
            return true;
        }

        // 1. Env override: if explicitly disabled in config, respect that
        if (! config("modules.{$slug}.enabled", false)) {
            return false;
        }

        // 2. Pennant (instance-level)
        if (! Feature::for(null)->active($slug)) {
            return false;
        }

        // 3. Per-user activation check
        if ($user === null) {
            return false;
        }

        // No path defined for this slug = no user gating (allow)
        if (! $this->hasActivePath($slug)) {
            return true;
        }

        return $this->userHasCompleted($user, $slug);
    }

    /**
     * Check if a module is accessible to the user — either fully enabled or
     * the user has completed the Tell phase and reached Show/Do (needs real UI access).
     */
    public function isAccessible(string $slug, ?User $user = null): bool
    {
        if ($this->isEnabled($slug, $user)) {
            return true;
        }

        $user = $user ?? Auth::user();

        if ($user === null) {
            return false;
        }

        return $this->userHasCompletedTellPhase($user, $slug);
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

    /**
     * @return array<string, array{name: string, description: string, icon: string, nav_section: string, nav_items: array<int, array<string, mixed>>}>
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
     * @return array<string, array{name: string, description: string, icon: string, nav_section: string, nav_items: array<int, array<string, mixed>>}>
     */
    public function allModules(): array
    {
        return $this->modules;
    }

    /**
     * Get navigation items grouped by section for enabled modules.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function navigationItems(): array
    {
        $grouped = [];

        foreach ($this->enabledModules() as $slug => $module) {
            if (! isset($module['nav_section'], $module['nav_items'])) {
                continue;
            }

            $section = $module['nav_section'];

            if (! isset($grouped[$section])) {
                $grouped[$section] = [];
            }

            foreach ($module['nav_items'] as $item) {
                $item['module'] = $slug;
                $grouped[$section][] = $item;
            }
        }

        return $grouped;
    }

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
     * Read license features from the shared agent volume.
     *
     * @return array<int, string>|null
     */
    private function getLicenseFeatures(): ?array
    {
        $data = $this->getLicenseData();

        return $data['features'] ?? null;
    }

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
