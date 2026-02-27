<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    // ─── bypassesPermissions ─────────────────────────────────────

    public function test_bypass_permissions_role_skips_all_checks(): void
    {
        $role = Role::factory()->create([
            'bypass_permissions' => true,
            'type' => 'internal',
        ]);

        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->bypassesPermissions());
        $this->assertTrue($user->hasModulePermission('anything'));
        $this->assertTrue($user->canWriteModule('anything'));
    }

    public function test_non_bypass_role_does_not_bypass(): void
    {
        $role = Role::factory()->create([
            'bypass_permissions' => false,
            'type' => 'internal',
        ]);

        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->bypassesPermissions());
        $this->assertFalse($user->hasModulePermission('anything'));
    }

    public function test_user_without_role_does_not_bypass(): void
    {
        $user = User::factory()->create(['role_id' => null]);

        $this->assertFalse($user->bypassesPermissions());
        $this->assertFalse($user->hasModulePermission('anything'));
    }

    // ─── hasModulePermission ─────────────────────────────────────

    public function test_has_module_permission_with_read_access(): void
    {
        $role = Role::factory()->create(['type' => 'internal']);
        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'enablement',
            'access_level' => 'read',
        ]);

        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->hasModulePermission('enablement'));
        $this->assertFalse($user->hasModulePermission('other_module'));
    }

    public function test_has_module_permission_with_write_access(): void
    {
        $role = Role::factory()->create(['type' => 'internal']);
        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'enablement',
            'access_level' => 'write',
        ]);

        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->hasModulePermission('enablement'));
    }

    // ─── canWriteModule ──────────────────────────────────────────

    public function test_can_write_module_returns_true_for_write_access(): void
    {
        $role = Role::factory()->create(['type' => 'internal']);
        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'enablement',
            'access_level' => 'write',
        ]);

        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->canWriteModule('enablement'));
    }

    public function test_can_write_module_returns_false_for_read_only_access(): void
    {
        $role = Role::factory()->create(['type' => 'internal']);
        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'enablement',
            'access_level' => 'read',
        ]);

        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->canWriteModule('enablement'));
        $this->assertTrue($user->hasModulePermission('enablement'));
    }

    // ─── isAdmin / isPsm ─────────────────────────────────────────

    public function test_is_admin_checks_role_slug(): void
    {
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $psm = User::factory()->psm()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($superAdmin->isAdmin());
        $this->assertFalse($psm->isAdmin());
    }

    public function test_is_psm_checks_role_slug(): void
    {
        $psm = User::factory()->psm()->create();
        $admin = User::factory()->admin()->create();

        $this->assertTrue($psm->isPsm());
        $this->assertFalse($admin->isPsm());
    }

    // ─── Role model methods ──────────────────────────────────────

    public function test_role_has_permission(): void
    {
        $role = Role::factory()->create(['type' => 'internal']);
        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'enablement',
            'access_level' => 'write',
        ]);

        $this->assertTrue($role->hasPermission('enablement'));
        $this->assertFalse($role->hasPermission('other_module'));
    }

    public function test_role_has_write_permission(): void
    {
        $role = Role::factory()->create(['type' => 'internal']);
        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'enablement',
            'access_level' => 'read',
        ]);

        $this->assertFalse($role->hasWritePermission('enablement'));
        $this->assertTrue($role->hasPermission('enablement'));
    }

    public function test_role_access_level(): void
    {
        $role = Role::factory()->create(['type' => 'internal']);
        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'enablement',
            'access_level' => 'read',
        ]);

        $this->assertEquals('read', $role->accessLevel('enablement'));
        $this->assertNull($role->accessLevel('other_module'));
    }

    public function test_bypass_role_access_level_is_always_write(): void
    {
        $role = Role::factory()->create([
            'type' => 'internal',
            'bypass_permissions' => true,
        ]);

        $this->assertEquals('write', $role->accessLevel('any_module'));
        $this->assertTrue($role->hasPermission('any_module'));
        $this->assertTrue($role->hasWritePermission('any_module'));
    }

    // ─── Portal contact permissions ──────────────────────────────

    public function test_contact_can_access_via_role(): void
    {
        $role = Role::factory()->portal()->create(['slug' => 'test_portal']);
        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'projects',
            'access_level' => 'write',
        ]);

        $contact = Contact::factory()->create(['portal_role_id' => $role->id]);

        $this->assertTrue($contact->canAccess('projects'));
        $this->assertFalse($contact->canAccess('goals'));
    }

    public function test_contact_without_role_has_no_access(): void
    {
        $contact = Contact::factory()->create(['portal_role_id' => null]);

        $this->assertFalse($contact->canAccess('projects'));
    }

    public function test_contact_dashboard_group_from_role(): void
    {
        $role = Role::factory()->portal()->create([
            'slug' => 'test_sales',
            'dashboard_group' => 'sales',
        ]);

        $contact = Contact::factory()->create(['portal_role_id' => $role->id]);

        $this->assertEquals('sales', $contact->dashboardGroup());
    }

    public function test_contact_dashboard_group_defaults_to_delivery(): void
    {
        $contact = Contact::factory()->create(['portal_role_id' => null]);

        $this->assertEquals('delivery', $contact->dashboardGroup());
    }

    // ─── Role scopes ─────────────────────────────────────────────

    public function test_internal_scope(): void
    {
        Role::factory()->create(['type' => 'internal', 'slug' => 'test_int']);
        Role::factory()->create(['type' => 'portal', 'slug' => 'test_portal']);

        $this->assertTrue(Role::internal()->where('slug', 'test_int')->exists());
        $this->assertFalse(Role::internal()->where('slug', 'test_portal')->exists());
    }

    public function test_portal_scope(): void
    {
        Role::factory()->create(['type' => 'internal', 'slug' => 'test_int2']);
        Role::factory()->create(['type' => 'portal', 'slug' => 'test_portal2']);

        $this->assertTrue(Role::portal()->where('slug', 'test_portal2')->exists());
        $this->assertFalse(Role::portal()->where('slug', 'test_int2')->exists());
    }
}
