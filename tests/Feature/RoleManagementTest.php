<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->admin()->create();
    }

    // ─── Index ───────────────────────────────────────────────────

    public function test_admin_can_view_roles_index(): void
    {
        Role::factory()->create(['type' => 'internal', 'slug' => 'test_internal']);
        Role::factory()->create(['type' => 'portal', 'slug' => 'test_portal']);

        $response = $this->actingAs($this->admin)->get(route('admin.roles.index'));

        $response->assertOk();
        $response->assertSee('test_internal');
        $response->assertSee('test_portal');
    }

    public function test_non_admin_cannot_view_roles_index(): void
    {
        $psm = User::factory()->psm()->create();

        $response = $this->actingAs($psm)->get(route('admin.roles.index'));

        $response->assertForbidden();
    }

    // ─── Create ──────────────────────────────────────────────────

    public function test_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.roles.create'));

        $response->assertOk();
    }

    public function test_admin_can_create_role(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
            'slug' => 'custom_role',
            'display_name' => 'Custom Role',
            'type' => 'internal',
            'description' => 'A custom role for testing',
            'permissions' => [
                'enablement' => 'write',
                'partner_management' => 'read',
            ],
        ]);

        $response->assertRedirect(route('admin.roles.index'));

        $role = Role::where('slug', 'custom_role')->first();
        $this->assertNotNull($role);
        $this->assertEquals('Custom Role', $role->display_name);
        $this->assertEquals('internal', $role->type);

        $this->assertTrue($role->hasWritePermission('enablement'));
        $this->assertTrue($role->hasPermission('partner_management'));
        $this->assertFalse($role->hasWritePermission('partner_management'));
    }

    public function test_create_role_validates_slug_format(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
            'slug' => 'Invalid Slug!',
            'display_name' => 'Test',
            'type' => 'internal',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_create_role_validates_unique_slug(): void
    {
        Role::factory()->create(['slug' => 'existing_role']);

        $response = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
            'slug' => 'existing_role',
            'display_name' => 'Test',
            'type' => 'internal',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_create_role_validates_type(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
            'slug' => 'test_role',
            'display_name' => 'Test',
            'type' => 'invalid',
        ]);

        $response->assertSessionHasErrors('type');
    }

    // ─── Edit ────────────────────────────────────────────────────

    public function test_admin_can_view_edit_form(): void
    {
        $role = Role::factory()->create(['slug' => 'editable_role']);

        $response = $this->actingAs($this->admin)->get(route('admin.roles.edit', $role));

        $response->assertOk();
    }

    // ─── Update ──────────────────────────────────────────────────

    public function test_admin_can_update_role(): void
    {
        $role = Role::factory()->create([
            'slug' => 'updatable_role',
            'display_name' => 'Original Name',
            'type' => 'internal',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.roles.update', $role), [
            'slug' => 'updatable_role',
            'display_name' => 'Updated Name',
            'type' => 'internal',
            'permissions' => [
                'enablement' => 'write',
            ],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertEquals('Updated Name', $role->fresh()->display_name);
        $this->assertTrue($role->fresh()->hasWritePermission('enablement'));
    }

    public function test_protected_role_only_allows_limited_field_updates(): void
    {
        $role = Role::factory()->create([
            'slug' => 'protected_role',
            'display_name' => 'Original',
            'type' => 'internal',
            'is_protected' => true,
            'bypass_permissions' => false,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.roles.update', $role), [
            'slug' => 'changed_slug',
            'display_name' => 'Updated Name',
            'type' => 'portal',
            'bypass_permissions' => true,
        ]);

        $response->assertRedirect(route('admin.roles.index'));

        $role->refresh();
        $this->assertEquals('Updated Name', $role->display_name);
        $this->assertEquals('protected_role', $role->slug);
        $this->assertEquals('internal', $role->type);
        $this->assertFalse($role->bypass_permissions);
    }

    public function test_bypass_role_permissions_are_not_synced(): void
    {
        $role = Role::factory()->create([
            'slug' => 'bypass_role',
            'type' => 'internal',
            'bypass_permissions' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.roles.update', $role), [
            'slug' => 'bypass_role',
            'display_name' => 'Bypass Role',
            'type' => 'internal',
            'permissions' => [
                'enablement' => 'write',
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(0, $role->permissions()->count());
    }

    // ─── Delete ──────────────────────────────────────────────────

    public function test_admin_can_delete_unprotected_role_with_no_users(): void
    {
        $role = Role::factory()->create([
            'slug' => 'deletable_role',
            'is_protected' => false,
            'type' => 'internal',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertNull(Role::find($role->id));
    }

    public function test_cannot_delete_protected_role(): void
    {
        $role = Role::factory()->create([
            'slug' => 'protected_del',
            'is_protected' => true,
            'type' => 'internal',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('error');
        $this->assertNotNull(Role::find($role->id));
    }

    public function test_cannot_delete_role_with_assigned_users(): void
    {
        $role = Role::factory()->create([
            'slug' => 'assigned_role',
            'is_protected' => false,
            'type' => 'internal',
        ]);

        User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('error');
        $this->assertNotNull(Role::find($role->id));
    }

    public function test_deleting_role_also_deletes_permissions(): void
    {
        $role = Role::factory()->create([
            'slug' => 'del_with_perms',
            'is_protected' => false,
            'type' => 'internal',
        ]);

        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'enablement',
            'access_level' => 'write',
        ]);

        $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $role));

        $this->assertEquals(0, RolePermission::where('role_id', $role->id)->count());
    }

    // ─── Permission Matrix Sync ──────────────────────────────────

    public function test_permissions_sync_replaces_existing(): void
    {
        $role = Role::factory()->create([
            'slug' => 'sync_test',
            'type' => 'internal',
        ]);

        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'old_module',
            'access_level' => 'write',
        ]);

        $this->actingAs($this->admin)->put(route('admin.roles.update', $role), [
            'slug' => 'sync_test',
            'display_name' => 'Sync Test',
            'type' => 'internal',
            'permissions' => [
                'new_module' => 'read',
            ],
        ]);

        $role->refresh()->load('permissions');
        $this->assertFalse($role->hasPermission('old_module'));
        $this->assertTrue($role->hasPermission('new_module'));
        $this->assertFalse($role->hasWritePermission('new_module'));
    }

    public function test_empty_permissions_clears_all(): void
    {
        $role = Role::factory()->create([
            'slug' => 'clear_test',
            'type' => 'internal',
        ]);

        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'some_module',
            'access_level' => 'write',
        ]);

        $this->actingAs($this->admin)->put(route('admin.roles.update', $role), [
            'slug' => 'clear_test',
            'display_name' => 'Clear Test',
            'type' => 'internal',
            'permissions' => [],
        ]);

        $this->assertEquals(0, $role->permissions()->count());
    }
}
