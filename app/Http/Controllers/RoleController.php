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
            'partner_management_admin' => 'Partner Management',
            'partner_management_user' => 'Partner Management',
            'enablement_admin' => 'Enablement',
            'enablement_user' => 'Enablement',
            'enablement_events_admin' => 'Enablement',
            'enablement_events_user' => 'Enablement',
            'blueprints_admin' => 'Enablement',
            'blueprints_user' => 'Enablement',
            'deal_registration_admin' => 'Deal Registration',
            'deal_registration_user' => 'Deal Registration',
            'launch_programs_admin' => 'Launch Programs',
            'launch_programs_user' => 'Launch Programs',
            'project_health_admin' => 'Project Health',
            'project_health_user' => 'Project Health',
            'qbr_admin' => 'QBR',
            'qbr_user' => 'QBR',
            'goals_targets_admin' => 'Goals & Targets',
            'goals_targets_user' => 'Goals & Targets',
            'benchmark_admin' => 'Benchmarks',
            'benchmark_user' => 'Benchmarks',
            'action_plans_admin' => 'Action Plans',
            'action_plans_user' => 'Action Plans',
            'provisioning_admin' => 'Provisioning',
            'provisioning_user' => 'Provisioning',
            'partner_portal_admin' => 'Partner Portal',
            'partner_portal_user' => 'Partner Portal',
            'partner_portal_partner_admin' => 'Partner Portal',
            'partner_portal_partner_user' => 'Partner Portal',
            'ai_agents_admin' => 'AI & Knowledge',
            'ai_agents_user' => 'AI & Knowledge',
            'knowledge_base_admin' => 'AI & Knowledge',
            'knowledge_base_user' => 'AI & Knowledge',
            'zoom_chat_admin' => 'AI & Knowledge',
            'zoom_chat_user' => 'AI & Knowledge',
            'workshop_delivery_admin' => 'Workshop & Labs',
            'workshop_delivery_user' => 'Workshop & Labs',
            'workshop_labs_admin' => 'Workshop & Labs',
            'workshop_labs_user' => 'Workshop & Labs',
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
