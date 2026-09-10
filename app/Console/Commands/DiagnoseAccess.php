<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\ModuleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * "Why can't role X see screen Y?" — prints the three gates a screen passes
 * through: (1) module enabled + Pennant feature active (instance-wide),
 * (2) the role's permission on the section, (3) any route middleware stricter
 * than the module permission (e.g. the admin/IsAdmin group). Read-only.
 */
class DiagnoseAccess extends Command
{
    protected $signature = 'access:diagnose {role : Role slug, e.g. psm} {module? : Product slug to focus on, e.g. launch_programs}';

    protected $description = 'Diagnose why a role can or cannot see/use a module section (feature / permission / route guards)';

    /** Middleware that never blocks a section on its own — everything else is surfaced. */
    private const SAFE_MW = ['web', 'auth', 'auth:web', 'auth:sanctum', 'verified', 'signed', 'bindings', 'substitutebindings'];

    public function handle(ModuleRegistry $registry): int
    {
        $roleSlug = $this->argument('role');
        $role = Role::where('slug', $roleSlug)->first();
        if (! $role) {
            $this->error("No role with slug '{$roleSlug}'. Known: ".Role::orderBy('id')->pluck('slug')->implode(', '));

            return self::FAILURE;
        }
        $bypass = (bool) $role->bypass_permissions;
        $adminish = $bypass || in_array($role->slug, ['super_admin', 'admin'], true);

        $modules = $registry->allModules();
        if ($only = $this->argument('module')) {
            if (! isset($modules[$only])) {
                $this->error("No module '{$only}'. Known: ".implode(', ', array_keys($modules)));

                return self::FAILURE;
            }
            $modules = [$only => $modules[$only]];
        }

        // Role permissions: module_slug ("product.section") => access_level.
        $perms = DB::table('role_permissions')->where('role_id', $role->id)
            ->pluck('access_level', 'module_slug')->all();

        // Map "product.section" => [ [name, extras[]], ... ] for routes gated by module:<key>.
        $routeGuards = [];
        foreach (Route::getRoutes() as $route) {
            $mw = $route->gatherMiddleware();
            foreach ($mw as $m) {
                if (! is_string($m) || ! str_starts_with($m, 'module:')) {
                    continue;
                }
                $key = substr($m, strlen('module:'));
                $extras = array_values(array_filter($mw, fn ($x) => is_string($x)
                    && ! str_starts_with($x, 'module:')
                    && ! str_starts_with($x, 'throttle:')
                    && ! in_array(strtolower($x), self::SAFE_MW, true)));
                $routeGuards[$key][] = ['name' => $route->getName() ?: $route->uri(), 'extras' => $extras];
            }
        }

        $this->line('');
        $this->line("Access for role <info>{$role->display_name}</info> (<comment>{$role->slug}</comment>)"
            .($adminish ? ' — <comment>admin-ish: bypasses the admin guard</comment>' : ''));

        foreach ($modules as $slug => $def) {
            $enabled = (bool) ($def['enabled'] ?? false);
            $featureActive = $registry->isFeatureActive($slug);
            $mHead = sprintf('module: %s   feature: %s',
                $enabled ? '<info>enabled</info>' : '<error>DISABLED</error>',
                $featureActive ? '<info>ACTIVE</info>' : '<error>INACTIVE (hidden for everyone)</error>');
            $this->line('');
            $this->line("── <options=bold>{$def['name']}</> ({$slug})   {$mHead}");

            foreach (($def['sections'] ?? []) as $key => $sec) {
                $permKey = "{$slug}.{$key}";
                $access = $perms[$permKey] ?? null;
                $routes = $routeGuards[$permKey] ?? [];
                $extraGuards = collect($routes)->pluck('extras')->flatten()->unique()->values()->all();
                $adminGuard = in_array('admin', $extraGuards, true);

                // Verdict — first failing gate wins.
                if (! $enabled) {
                    $verdict = '<error>HIDDEN — module disabled</error>';
                } elseif (! $featureActive) {
                    $verdict = '<error>HIDDEN — feature inactive</error>';
                } elseif (! $access && ! $bypass) {
                    $verdict = '<error>HIDDEN — role has no permission</error>';
                } elseif ($adminGuard && ! $adminish) {
                    $verdict = '<error>403 — route requires admin (stricter than the module permission)</error>';
                } elseif (! empty($extraGuards)) {
                    $verdict = '<comment>OK — but extra route guards apply: '.implode(', ', $extraGuards).'</comment>';
                } elseif (empty($routes)) {
                    $verdict = '<comment>permission OK — no route is gated by module:'.$permKey.' (nav route may use a different key)</comment>';
                } else {
                    $verdict = '<info>ACCESSIBLE</info>';
                }

                $routeStr = empty($routes) ? '—'
                    : $routes[0]['name'].(count($routes) > 1 ? ' (+'.(count($routes) - 1).')' : '');
                $this->line(sprintf('   %-26s perm=%-6s guards=%-14s routes: %-34s %s',
                    $key,
                    $access ?: ($bypass ? 'bypass' : 'NONE'),
                    empty($extraGuards) ? '—' : implode(',', $extraGuards),
                    $routeStr,
                    $verdict));
            }
        }
        $this->line('');
        $this->line('<comment>Gates:</comment> module enabled → feature active (instance-wide) → role permission → route middleware. All must pass.');

        return self::SUCCESS;
    }
}
