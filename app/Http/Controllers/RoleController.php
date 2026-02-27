<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveRoleRequest;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $internalRoles = Role::internal()
            ->withCount('users')
            ->orderBy('sort_order')
            ->get();

        $portalRoles = Role::portal()
            ->withCount('contacts')
            ->orderBy('sort_order')
            ->get();

        return view('admin.roles.index', compact('internalRoles', 'portalRoles'));
    }

    public function create(): View
    {
        $moduleGroups = $this->getModuleGroups();

        return view('admin.roles.edit', [
            'role' => null,
            'moduleGroups' => $moduleGroups,
        ]);
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');
        $moduleGroups = $this->getModuleGroups();

        return view('admin.roles.edit', compact('role', 'moduleGroups'));
    }

    public function store(SaveRoleRequest $request): RedirectResponse
    {
        $role = Role::create($request->safe()->except('permissions'));

        $this->syncPermissions($role, $request->input('permissions', []));

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->display_name}\" created successfully.");
    }

    public function update(SaveRoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->is_protected) {
            $role->update($request->safe()->only('display_name', 'description', 'landing_route', 'dashboard_group', 'sort_order'));
        } else {
            $role->update($request->safe()->except('permissions'));
        }

        if (! $role->bypass_permissions) {
            $this->syncPermissions($role, $request->input('permissions', []));
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->display_name}\" updated successfully.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_protected) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Protected roles cannot be deleted.');
        }

        $assignedCount = $role->type === 'portal'
            ? $role->contacts()->count()
            : $role->users()->count();

        if ($assignedCount > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', "Cannot delete role \"{$role->display_name}\" — it is assigned to {$assignedCount} ".($role->type === 'portal' ? 'contacts' : 'users').'.');
        }

        $name = $role->display_name;
        $role->permissions()->delete();
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$name}\" deleted.");
    }

    /**
     * Sync role permissions from the form submission.
     *
     * @param  array<string, string>  $permissions  module_slug => access_level
     */
    private function syncPermissions(Role $role, array $permissions): void
    {
        $role->permissions()->delete();

        $rows = [];
        foreach ($permissions as $moduleSlug => $accessLevel) {
            if (in_array($accessLevel, ['read', 'write'])) {
                $rows[] = [
                    'role_id' => $role->id,
                    'module_slug' => $moduleSlug,
                    'access_level' => $accessLevel,
                ];
            }
        }

        if ($rows) {
            RolePermission::insert($rows);
        }

        // Reload cached permissions on the model
        $role->load('permissions');
    }

    /**
     * Build grouped module list for the permission matrix UI.
     *
     * @return array<string, array<string, string>>
     */
    private function getModuleGroups(): array
    {
        $modules = config('modules', []);

        // Virtual module keys not in config/modules.php
        $virtualModules = [
            'admin_dashboard' => 'Admin Dashboard',
            'user_management' => 'User Management',
            'configuration' => 'Configuration',
            'data_imports' => 'Data Imports',
            'health_alerts' => 'Health & Alerts',
        ];

        $groups = [
            'System' => $virtualModules,
            'Partner Management' => [],
            'Enablement' => [],
            'Deal Registration' => [],
            'Launch Programs' => [],
            'Project Health' => [],
            'QBR' => [],
            'Goals & Targets' => [],
            'Benchmarks' => [],
            'Action Plans' => [],
            'Provisioning' => [],
            'Partner Portal' => [],
            'AI & Knowledge' => [],
            'Workshop & Labs' => [],
            'Integration' => [],
            'Analytics & Strategy' => [],
        ];

        $slugToGroup = [
            'partner_management' => 'Partner Management',
            'enablement' => 'Enablement',
            'enablement_events' => 'Enablement',
            'blueprints' => 'Enablement',
            'deal_registration' => 'Deal Registration',
            'launch_programs' => 'Launch Programs',
            'project_health' => 'Project Health',
            'qbr' => 'QBR',
            'goals_targets' => 'Goals & Targets',
            'benchmark' => 'Benchmarks',
            'action_plans' => 'Action Plans',
            'provisioning' => 'Provisioning',
            'partner_portal' => 'Partner Portal',
            'partner_portal_partner' => 'Partner Portal',
            'ai_agents' => 'AI & Knowledge',
            'knowledge_base' => 'AI & Knowledge',
            'zoom_chat' => 'AI & Knowledge',
            'workshop_delivery' => 'Workshop & Labs',
            'workshop_labs' => 'Workshop & Labs',
            'integration_hub' => 'Integration',
            'mcp_server' => 'Integration',
            'feature_activation_coe' => 'Analytics & Strategy',
            'analytics' => 'Analytics & Strategy',
            'competency' => 'Analytics & Strategy',
            'strategy' => 'Analytics & Strategy',
            'idea_room' => 'Analytics & Strategy',
        ];

        foreach ($modules as $slug => $meta) {
            $group = $slugToGroup[$slug] ?? 'Other';
            if (! isset($groups[$group])) {
                $groups[$group] = [];
            }
            $groups[$group][$slug] = $meta['name'] ?? $slug;
        }

        // Remove empty groups
        return array_filter($groups);
    }
}
