<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
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
            'ai_knowledge.agents' => 'write',
            'ai_knowledge.knowledge_base' => 'write',
            'partner_management.analytics' => 'write',
            'enablement.blueprints' => 'write',
            'enablement.blueprint_admin' => 'write',
            'competency.paths' => 'write',
            'competency.administration' => 'write',
            'integration_hub.directory_sync' => 'write',
            'enablement.calendar' => 'write',
            'enablement.field' => 'write',
            'enablement.events' => 'write',
            'enablement.administration' => 'write',
            'launch_programs.programs' => 'write',
            'launch_programs.administration' => 'write',
            'measurement.benchmarks' => 'write',
            'measurement.health' => 'write',
            'partner_management.administration' => 'write',
            'strategy.strategy' => 'write',
            'system_administration.admin' => 'write',
            'enablement.workshops' => 'write',
            'enablement.workshop_admin' => 'write',
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
            'launch_programs.action_plans' => 'read',
            'partner_management.analytics' => 'read',
            'enablement.blueprints' => 'read',
            'competency.paths' => 'read',
            'deal_registration.deals' => 'read',
            'enablement.calendar' => 'read',
            'enablement.field' => 'read',
            'enablement.events' => 'read',
            'goals_targets.goals' => 'read',
            'launch_programs.programs' => 'read',
            'measurement.health' => 'read',
            'measurement.qbr' => 'read',
            'partner_management.administration' => 'write',
            'partner_management.management' => 'read',
            'launch_programs.projects' => 'read',
            'strategy.strategy' => 'read',
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
            'launch_programs.action_plans' => 'write',
            'partner_management.analytics' => 'read',
            'enablement.blueprints' => 'write',
            'competency.paths' => 'write',
            'deal_registration.deals' => 'read',
            'deal_registration.marketplace' => 'write',
            'enablement.calendar' => 'write',
            'enablement.events' => 'write',
            'enablement.field' => 'write',
            'goals_targets.goals' => 'write',
            'launch_programs.programs' => 'write',
            'measurement.health' => 'read',
            'measurement.qbr' => 'write',
            'partner_management.management' => 'write',
            'portal.management' => 'write',
            'launch_programs.projects' => 'write',
            'partner_management.provisioning' => 'write',
            'strategy.strategy' => 'read',
            'enablement.workshops' => 'write',
            'enablement.workshop_admin' => 'write',
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
            'launch_programs.action_plans' => 'write',
            'partner_management.analytics' => 'read',
            'enablement.blueprints' => 'read',
            'competency.paths' => 'read',
            'deal_registration.deals' => 'write',
            'deal_registration.business_development' => 'write',
            'deal_registration.lead_assignment' => 'write',
            'deal_registration.marketplace' => 'read',
            'enablement.calendar' => 'read',
            'enablement.events' => 'read',
            'enablement.field' => 'read',
            'goals_targets.goals' => 'write',
            'launch_programs.programs' => 'read',
            'measurement.health' => 'read',
            'measurement.qbr' => 'write',
            'partner_management.management' => 'write',
            'launch_programs.projects' => 'read',
            'strategy.strategy' => 'read',
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
            'integration_hub.hub' => 'write',
            'integration_hub.mcp' => 'write',
            'integration_hub.api_tokens' => 'write',
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
                'permissions' => [
                    'enablement.blueprints' => 'read',
                    'calendar' => 'write',
                    'cases' => 'write',
                    'certifications' => 'write',
                    'company_profile' => 'write',
                    'contacts' => 'write',
                    'deal_registration.deals' => 'write',
                    'deal_registrations' => 'write',
                    'enablement.calendar' => 'read',
                    'enablement.events' => 'read',
                    'goals' => 'write',
                    'goals_targets.goals' => 'read',
                    'labs' => 'read',
                    'launch_programs.programs' => 'read',
                    'partner_management.management' => 'read',
                    'portal.partner' => 'write',
                    'path' => 'read',
                    'launch_programs.projects' => 'write',
                    'projects' => 'write',
                    'partner_management.provisioning' => 'read',
                    'measurement.qbr' => 'write',
                    'scorecard' => 'write',
                ],
            ],
            [
                'slug' => 'delivery_lead',
                'display_name' => 'Delivery Lead',
                'dashboard_group' => 'delivery',
                'sort_order' => 1,
                'description' => 'Manages delivery projects, cases, and enablement content.',
                'permissions' => [
                    'enablement.blueprints' => 'read',
                    'calendar' => 'read',
                    'cases' => 'read',
                    'enablement.calendar' => 'read',
                    'enablement.events' => 'read',
                    'labs' => 'read',
                    'path' => 'read',
                    'portal.partner' => 'read',
                    'launch_programs.projects' => 'write',
                    'projects' => 'write',
                    'workspaces' => 'read',
                ],
            ],
            [
                'slug' => 'engagement_manager',
                'display_name' => 'Engagement Manager',
                'dashboard_group' => 'admin',
                'sort_order' => 2,
                'description' => 'Manages partner engagement, enablement programs, and goals.',
                'permissions' => [
                    'enablement.blueprints' => 'read',
                    'calendar' => 'read',
                    'cases' => 'read',
                    'enablement.calendar' => 'read',
                    'enablement.events' => 'read',
                    'goals' => 'write',
                    'launch_programs.programs' => 'read',
                    'path' => 'read',
                    'portal.partner' => 'read',
                    'launch_programs.projects' => 'write',
                    'projects' => 'write',
                ],
            ],
            [
                'slug' => 'practice_lead',
                'display_name' => 'Practice Lead',
                'dashboard_group' => 'admin',
                'sort_order' => 3,
                'description' => 'Oversees enablement content, certifications, and goal tracking.',
                'permissions' => [
                    'enablement.blueprints' => 'read',
                    'calendar' => 'read',
                    'cases' => 'write',
                    'certifications' => 'read',
                    'company_profile' => 'read',
                    'contacts' => 'read',
                    'deal_registration.deals' => 'write',
                    'enablement.calendar' => 'read',
                    'enablement.events' => 'read',
                    'goals' => 'read',
                    'goals_targets.goals' => 'read',
                    'launch_programs.programs' => 'read',
                    'partner_management.management' => 'read',
                    'portal.partner' => 'read',
                    'path' => 'read',
                    'launch_programs.projects' => 'write',
                    'partner_management.provisioning' => 'read',
                    'measurement.qbr' => 'write',
                    'scorecard' => 'read',
                ],
            ],
            [
                'slug' => 'marketing',
                'display_name' => 'Marketing',
                'dashboard_group' => 'sales',
                'sort_order' => 4,
                'description' => 'View-only access to enablement content and deal registration.',
                'permissions' => [
                    'enablement.blueprints' => 'read',
                    'calendar' => 'read',
                    'deal_registration.deals' => 'read',
                    'enablement.calendar' => 'read',
                    'enablement.events' => 'read',
                    'portal.partner' => 'read',
                ],
            ],
            [
                'slug' => 'sales',
                'display_name' => 'Sales',
                'dashboard_group' => 'sales',
                'sort_order' => 5,
                'description' => 'Deal registration, enablement content, and calendar.',
                'permissions' => [
                    'enablement.blueprints' => 'read',
                    'calendar' => 'read',
                    'deal_registration.deals' => 'write',
                    'deal_registrations' => 'write',
                    'enablement.calendar' => 'read',
                    'enablement.events' => 'read',
                    'portal.partner' => 'read',
                ],
            ],
            [
                'slug' => 'pre_sales',
                'display_name' => 'Pre-Sales',
                'dashboard_group' => 'sales',
                'sort_order' => 6,
                'description' => 'Deal registration, enablement content, and calendar.',
                'permissions' => [
                    'calendar' => 'read',
                    'deal_registration.deals' => 'write',
                    'deal_registrations' => 'write',
                    'enablement.calendar' => 'read',
                    'enablement.events' => 'read',
                    'portal.partner' => 'read',
                ],
            ],
            [
                'slug' => 'technical_architect',
                'display_name' => 'Technical Architect',
                'dashboard_group' => 'delivery',
                'sort_order' => 7,
                'description' => 'Enablement content, cases, and calendar.',
                'permissions' => [
                    'enablement.blueprints' => 'read',
                    'calendar' => 'read',
                    'cases' => 'read',
                    'enablement.calendar' => 'read',
                    'enablement.events' => 'read',
                    'labs' => 'read',
                    'portal.partner' => 'read',
                    'workspaces' => 'read',
                ],
            ],
            [
                'slug' => 'delivery_resource',
                'display_name' => 'Delivery Resource',
                'dashboard_group' => 'delivery',
                'sort_order' => 8,
                'description' => 'Enablement content, cases, and calendar.',
                'permissions' => [
                    'calendar' => 'read',
                    'cases' => 'read',
                    'enablement.calendar' => 'read',
                    'enablement.events' => 'read',
                    'portal.partner' => 'read',
                    'launch_programs.projects' => 'write',
                    'projects' => 'write',
                ],
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
