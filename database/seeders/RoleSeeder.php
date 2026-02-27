<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * All _user module slugs that PSM and Manager roles get by default.
     *
     * @var string[]
     */
    private const USER_MODULES = [
        'partner_management_user',
        'enablement_user',
        'enablement_events_user',
        'deal_registration_user',
        'launch_programs_user',
        'project_health_user',
        'qbr_user',
        'provisioning_user',
        'goals_targets_user',
        'benchmark_user',
        'action_plans_user',
        'partner_portal_user',
        'ai_agents_user',
        'knowledge_base_user',
        'zoom_chat_user',
        'workshop_delivery_user',
        'workshop_labs_user',
        'analytics',
        'competency',
        'idea_room',
    ];

    /**
     * Modules the CoE role gets write access to.
     *
     * @var string[]
     */
    private const COE_MODULES = [
        'strategy',
        'feature_activation_coe',
        'enablement_events_admin',
        'enablement_admin',
        'knowledge_base_admin',
        'ai_agents_admin',
        'workshop_labs_admin',
        'workshop_delivery_admin',
        'launch_programs_admin',
        'goals_targets_admin',
        'deal_registration_admin',
        'partner_management_admin',
        'partner_management_user',
        'benchmark_admin',
        'provisioning_admin',
        'action_plans_admin',
        'project_health_admin',
        'qbr_admin',
        'partner_portal_admin',
        'user_management',
        'configuration',
        'enablement_events_user',
        'enablement_user',
        'knowledge_base_user',
        'ai_agents_user',
        'workshop_labs_user',
        'workshop_delivery_user',
        'launch_programs_user',
        'goals_targets_user',
        'deal_registration_user',
        'benchmark_user',
        'provisioning_user',
        'action_plans_user',
        'project_health_user',
        'qbr_user',
        'partner_portal_user',
        'analytics',
        'competency',
        'idea_room',
        'directory_sync_admin',
        'health_alerts',
    ];

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
        $this->syncPermissions($coe, self::COE_MODULES, 'write');

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
        $this->syncPermissions($manager, [...self::USER_MODULES, 'user_management', 'health_alerts'], 'write');

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
        $this->syncPermissions($psm, [...self::USER_MODULES, 'health_alerts'], 'write');

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
        $this->syncPermissions($pam, [...self::USER_MODULES, 'health_alerts'], 'write');

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
        $this->syncPermissions($integration, ['integration_hub', 'data_imports'], 'write');
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
                'permissions' => self::ALL_PORTAL_CAPABILITIES,
                'access_level' => 'write',
            ],
            [
                'slug' => 'delivery_lead',
                'display_name' => 'Delivery Lead',
                'dashboard_group' => 'delivery',
                'sort_order' => 1,
                'description' => 'Manages delivery projects, cases, and enablement content.',
                'permissions' => ['projects', 'cases', 'enablement', 'blueprints', 'labs', 'workspaces', 'path'],
                'access_level' => 'write',
            ],
            [
                'slug' => 'engagement_manager',
                'display_name' => 'Engagement Manager',
                'dashboard_group' => 'admin',
                'sort_order' => 2,
                'description' => 'Manages partner engagement, enablement programs, and goals.',
                'permissions' => ['projects', 'enablement', 'blueprints', 'path', 'goals', 'calendar', 'launch_programs'],
                'access_level' => 'write',
            ],
            [
                'slug' => 'practice_lead',
                'display_name' => 'Practice Lead',
                'dashboard_group' => 'admin',
                'sort_order' => 3,
                'description' => 'Oversees enablement content, labs, certifications, and goal tracking.',
                'permissions' => ['enablement', 'blueprints', 'labs', 'certifications', 'path', 'goals'],
                'access_level' => 'write',
            ],
            [
                'slug' => 'marketing',
                'display_name' => 'Marketing',
                'dashboard_group' => 'sales',
                'sort_order' => 4,
                'description' => 'View-only access to enablement content and blueprints.',
                'permissions' => ['enablement', 'blueprints'],
                'access_level' => 'read',
            ],
            [
                'slug' => 'sales',
                'display_name' => 'Sales',
                'dashboard_group' => 'sales',
                'sort_order' => 5,
                'description' => 'Deal registration, enablement content, and blueprints.',
                'permissions' => ['deal_registrations', 'enablement', 'blueprints'],
                'access_level' => 'write',
            ],
            [
                'slug' => 'pre_sales',
                'display_name' => 'Pre-Sales',
                'dashboard_group' => 'sales',
                'sort_order' => 6,
                'description' => 'Deal registration, enablement content, blueprints, and lab access.',
                'permissions' => ['deal_registrations', 'enablement', 'blueprints', 'labs'],
                'access_level' => 'write',
            ],
            [
                'slug' => 'technical_architect',
                'display_name' => 'Technical Architect',
                'dashboard_group' => 'delivery',
                'sort_order' => 7,
                'description' => 'Labs, workspaces, blueprints, and enablement content.',
                'permissions' => ['labs', 'workspaces', 'blueprints', 'enablement'],
                'access_level' => 'write',
            ],
            [
                'slug' => 'delivery_resource',
                'display_name' => 'Delivery Resource',
                'dashboard_group' => 'delivery',
                'sort_order' => 8,
                'description' => 'Project and case access with enablement content.',
                'permissions' => ['projects', 'cases', 'enablement', 'blueprints'],
                'access_level' => 'write',
            ],
        ];

        foreach ($portalRoles as $roleData) {
            $permissions = $roleData['permissions'];
            $accessLevel = $roleData['access_level'];
            unset($roleData['permissions'], $roleData['access_level']);

            $role = Role::updateOrCreate(['slug' => $roleData['slug']], [
                ...$roleData,
                'type' => 'portal',
                'is_protected' => true,
                'bypass_permissions' => false,
            ]);

            $this->syncPermissions($role, $permissions, $accessLevel);
        }
    }

    /**
     * Sync permissions for a role, removing stale entries and upserting current ones.
     *
     * @param  string[]  $moduleSlugs
     */
    private function syncPermissions(Role $role, array $moduleSlugs, string $accessLevel): void
    {
        // Remove permissions not in the current set
        RolePermission::where('role_id', $role->id)
            ->whereNotIn('module_slug', $moduleSlugs)
            ->delete();

        // Upsert current permissions
        foreach ($moduleSlugs as $slug) {
            RolePermission::updateOrCreate(
                ['role_id' => $role->id, 'module_slug' => $slug],
                ['access_level' => $accessLevel],
            );
        }

        // Reload the permissions relationship
        $role->load('permissions');
    }
}
