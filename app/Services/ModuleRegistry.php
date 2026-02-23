<?php

namespace App\Services;

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
     * Register a module and its navigation items.
     *
     * @param  array{name: string, description: string, icon: string, nav_section: string, nav_items: array<int, array<string, mixed>>}  $definition
     */
    public function register(string $slug, array $definition): void
    {
        $this->modules[$slug] = $definition;
    }

    public function isEnabled(string $slug): bool
    {
        // Env override: if explicitly disabled in config, respect that
        if (! config("modules.{$slug}.enabled", false)) {
            return false;
        }

        // In local/testing, trust the config flag (skip Pennant for dev convenience)
        if (app()->environment('local', 'testing')) {
            return true;
        }

        // Production: check Pennant (scope = null for single-tenant instances)
        return Feature::for(null)->active($slug);
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
