<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * All portal capability keys.
     *
     * @var string[]
     */
    private const ALL_PORTAL_CAPABILITIES = [
        'projects',
        'cases',
        'enablement',
        'blueprints',
        'labs',
        'workspaces',
        'path',
        'goals',
        'certifications',
        'calendar',
        'launch_programs',
        'deal_registrations',
        'contacts',
        'scorecard',
        'company_profile',
    ];

    public function run(): void
    {
        $this->seedInternalRoles();
        $this->seedPortalRoles();
    }

    private function seedInternalRoles(): void
    {
        // Super Admin
        Role::updateOrCreate(['slug' => 'super_admin'], [
            'display_name' => 'Super Admin',
            'type' => 'internal',
            'is_protected' => true,
            'bypass_permissions' => true,
            'landing_route' => 'admin.dashboard',
            'sort_order' => 0,
            'description' => 'Full system access including impersonation and all administrative functions.',
        ]);

        // Admin
        Role::updateOrCreate(['slug' => 'admin'], [
            'display_name' => 'Administrator',
            'type' => 'internal',
            'is_protected' => true,
            'bypass_permissions' => true,
            'landing_route' => 'admin.dashboard',
            'sort_order' => 1,
            'description' => 'Full system access to all administrative and operational functions.',
        ]);

        // CoE
        $coe = Role::updateOrCreate(['slug' => 'coe'], [
            'display_name' => 'Center of Excellence',
            'type' => 'internal',
            'is_protected' => true,
            'bypass_permissions' => false,
            'landing_route' => 'strategy.dashboard',
            'sort_order' => 2,
            'description' => 'Strategy design, enablement program management, and knowledge base curation.',
        ]);
        $this->syncPermissions($coe, [
            'action_plans_admin' => 'write',
            'ai_agents_admin' => 'write',
            'analytics' => 'write',
            'benchmark_admin' => 'write',
            'blueprints_admin' => 'write',
            'competency' => 'write',
            'deal_registration_admin' => 'write',
            'enablement_admin' => 'write',
            'enablement_events_admin' => 'write',
            'feature_activation_coe' => 'write',
            'goals_targets_admin' => 'write',
            'health_alerts' => 'write',
            'knowledge_base_admin' => 'write',
            'launch_programs_admin' => 'write',
            'partner_management_admin' => 'write',
            'partner_portal_admin' => 'write',
            'project_health_admin' => 'write',
            'qbr_admin' => 'write',
            'strategy' => 'write',
            'user_management' => 'write',
            'workshop_delivery_admin' => 'write',
            'workshop_labs_admin' => 'write',
        ]);

        // Manager
        $manager = Role::updateOrCreate(['slug' => 'manager'], [
            'display_name' => 'Manager',
            'type' => 'internal',
            'is_protected' => true,
            'bypass_permissions' => false,
            'landing_route' => 'welcome',
            'sort_order' => 3,
            'description' => 'Team oversight, reporting hierarchy management, and portfolio-level analytics.',
        ]);
        $this->syncPermissions($manager, [
            'action_plans_user' => 'read',
            'analytics' => 'write',
            'benchmark_user' => 'read',
            'blueprints_user' => 'read',
            'competency' => 'write',
            'deal_registration_user' => 'read',
            'enablement_events_user' => 'read',
            'enablement_user' => 'read',
            'goals_targets_user' => 'read',
            'health_alerts' => 'write',
            'launch_programs_user' => 'read',
            'partner_management_user' => 'read',
            'project_health_user' => 'read',
            'qbr_user' => 'read',
            'user_management' => 'write',
        ]);

        // PSM
        $psm = Role::updateOrCreate(['slug' => 'psm'], [
            'display_name' => 'Partner Success Manager',
            'type' => 'internal',
            'is_protected' => true,
            'bypass_permissions' => false,
            'landing_route' => 'welcome',
            'sort_order' => 4,
            'description' => 'Day-to-day partner management, task execution, and field enablement.',
        ]);
        $this->syncPermissions($psm, [
            'blueprints_user' => 'write',
            'deal_registration_user' => 'write',
            'enablement_events_user' => 'write',
            'enablement_user' => 'write',
            'launch_programs_user' => 'write',
            'partner_management_user' => 'write',
            'project_health_user' => 'write',
            'workshop_delivery_user' => 'write',
            'workshop_labs_user' => 'write',
        ]);

        // PAM
        $pam = Role::updateOrCreate(['slug' => 'pam'], [
            'display_name' => 'Partner Account Manager',
            'type' => 'internal',
            'is_protected' => true,
            'bypass_permissions' => false,
            'landing_route' => 'welcome',
            'sort_order' => 5,
            'description' => 'Partner account ownership, relationship management, and commercial oversight.',
        ]);
        $this->syncPermissions($pam, [
            'action_plans_user' => 'write',
            'analytics' => 'read',
            'benchmark_user' => 'read',
            'blueprints_user' => 'read',
            'competency' => 'read',
            'deal_registration_user' => 'write',
            'enablement_events_user' => 'read',
            'enablement_user' => 'read',
            'goals_targets_user' => 'write',
            'health_alerts' => 'write',
            'launch_programs_user' => 'read',
            'partner_management_user' => 'write',
            'project_health_user' => 'write',
            'qbr_user' => 'write',
        ]);

        // Integration
        $integration = Role::updateOrCreate(['slug' => 'integration'], [
            'display_name' => 'Integration',
            'type' => 'internal',
            'is_protected' => true,
            'bypass_permissions' => false,
            'landing_route' => 'integration_hub.dashboard',
            'sort_order' => 6,
            'description' => 'Data connector operations, import/export jobs, and field mappings.',
        ]);
        $this->syncPermissions($integration, [
            'data_imports' => 'write',
            'integration_hub' => 'write',
        ]);
    }

    private function seedPortalRoles(): void
    {
        $portalRoles = [
            [
                'slug' => 'partner_admin',
                'display_name' => 'Partner Admin',
                'dashboard_group' => 'admin',
                'sort_order' => 0,
                'description' => 'Full access to all portal features and team management.',
                'permissions' => array_fill_keys(self::ALL_PORTAL_CAPABILITIES, 'write'),
            ],
            [
                'slug' => 'delivery_lead',
                'display_name' => 'Delivery Lead',
                'dashboard_group' => 'delivery',
                'sort_order' => 1,
                'description' => 'Manages delivery projects, cases, and enablement content.',
                'permissions' => array_fill_keys(['projects', 'cases', 'enablement', 'blueprints', 'labs', 'workspaces', 'path'], 'write'),
            ],
            [
                'slug' => 'engagement_manager',
                'display_name' => 'Engagement Manager',
                'dashboard_group' => 'admin',
                'sort_order' => 2,
                'description' => 'Manages partner engagement, enablement programs, and goals.',
                'permissions' => array_fill_keys(['projects', 'enablement', 'blueprints', 'path', 'goals', 'calendar', 'launch_programs'], 'write'),
            ],
            [
                'slug' => 'practice_lead',
                'display_name' => 'Practice Lead',
                'dashboard_group' => 'admin',
                'sort_order' => 3,
                'description' => 'Oversees enablement content, labs, certifications, and goal tracking.',
                'permissions' => array_fill_keys(['enablement', 'blueprints', 'labs', 'certifications', 'path', 'goals'], 'write'),
            ],
            [
                'slug' => 'marketing',
                'display_name' => 'Marketing',
                'dashboard_group' => 'sales',
                'sort_order' => 4,
                'description' => 'View-only access to enablement content and blueprints.',
                'permissions' => array_fill_keys(['enablement', 'blueprints'], 'read'),
            ],
            [
                'slug' => 'sales',
                'display_name' => 'Sales',
                'dashboard_group' => 'sales',
                'sort_order' => 5,
                'description' => 'Deal registration, enablement content, and blueprints.',
                'permissions' => array_fill_keys(['deal_registrations', 'enablement', 'blueprints'], 'write'),
            ],
            [
                'slug' => 'pre_sales',
                'display_name' => 'Pre-Sales',
                'dashboard_group' => 'sales',
                'sort_order' => 6,
                'description' => 'Deal registration, enablement content, blueprints, and lab access.',
                'permissions' => array_fill_keys(['deal_registrations', 'enablement', 'blueprints', 'labs'], 'write'),
            ],
            [
                'slug' => 'technical_architect',
                'display_name' => 'Technical Architect',
                'dashboard_group' => 'delivery',
                'sort_order' => 7,
                'description' => 'Labs, workspaces, blueprints, and enablement content.',
                'permissions' => array_fill_keys(['labs', 'workspaces', 'blueprints', 'enablement'], 'write'),
            ],
            [
                'slug' => 'delivery_resource',
                'display_name' => 'Delivery Resource',
                'dashboard_group' => 'delivery',
                'sort_order' => 8,
                'description' => 'Project and case access with enablement content.',
                'permissions' => array_fill_keys(['projects', 'cases', 'enablement', 'blueprints'], 'write'),
            ],
        ];

        foreach ($portalRoles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::updateOrCreate(['slug' => $roleData['slug']], [
                ...$roleData,
                'type' => 'portal',
                'is_protected' => true,
                'bypass_permissions' => false,
            ]);

            $this->syncPermissions($role, $permissions);
        }
    }

    /**
     * Sync permissions for a role, removing stale entries and upserting current ones.
     *
     * @param  array<string, string>  $permissions  module_slug => access_level
     */
    private function syncPermissions(Role $role, array $permissions): void
    {
        // Remove permissions not in the current set
        RolePermission::where('role_id', $role->id)
            ->whereNotIn('module_slug', array_keys($permissions))
            ->delete();

        // Upsert current permissions
        foreach ($permissions as $slug => $accessLevel) {
            RolePermission::updateOrCreate(
                ['role_id' => $role->id, 'module_slug' => $slug],
                ['access_level' => $accessLevel],
            );
        }

        // Reload the permissions relationship
        $role->load('permissions');
    }
}
