<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Services\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class ModulePermissionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    // ─── IsAdmin Middleware ──────────────────────────────────────

    public function test_admin_middleware_allows_bypass_role(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('admin.roles.index'));

        $response->assertOk();
    }

    public function test_admin_middleware_blocks_non_bypass_role(): void
    {
        $user = User::factory()->psm()->create();

        $response = $this->actingAs($user)->get(route('admin.roles.index'));

        $response->assertForbidden();
    }

    public function test_admin_middleware_blocks_user_without_role(): void
    {
        $user = User::factory()->create(['role_id' => null]);

        $response = $this->actingAs($user)->get(route('admin.roles.index'));

        $response->assertForbidden();
    }

    // ─── EnsureAdminOrIntegration Middleware ─────────────────────

    public function test_admin_or_integration_allows_bypass_role(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('imports.index'));

        $response->assertOk();
    }

    public function test_admin_or_integration_allows_data_imports_permission(): void
    {
        $role = Role::factory()->create(['type' => 'internal']);
        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'data_imports',
            'access_level' => 'write',
        ]);

        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('imports.index'));

        $response->assertOk();
    }

    public function test_admin_or_integration_blocks_user_without_permission(): void
    {
        $user = User::factory()->psm()->create();

        $response = $this->actingAs($user)->get(route('imports.index'));

        $response->assertForbidden();
    }

    // ─── ModuleRegistry::isEnabled ──────────────────────────────

    public function test_module_registry_bypass_role_always_enabled(): void
    {
        $registry = app(ModuleRegistry::class);
        $user = User::factory()->admin()->create();

        $this->assertTrue($registry->isEnabled('enablement', $user));
    }

    public function test_module_registry_checks_role_permission(): void
    {
        $registry = app(ModuleRegistry::class);

        // Register the module and enable via config and Pennant
        config(['modules.test_module.enabled' => true]);
        $registry->register('test_module', [
            'name' => 'Test Module',
            'description' => 'Test',
            'icon' => 'test',
            'nav_section' => 'test',
            'nav_items' => [],
        ]);
        Feature::for(null)->activate('test_module');

        $role = Role::factory()->create(['type' => 'internal']);
        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'test_module',
            'access_level' => 'write',
        ]);

        $userWith = User::factory()->create(['role_id' => $role->id]);
        $userWithout = User::factory()->psm()->create();

        $this->assertTrue($registry->isEnabled('test_module', $userWith));
        $this->assertFalse($registry->isEnabled('test_module', $userWithout));
    }

    // ─── EnsureModuleEnabled Write Access ────────────────────────

    public function test_module_middleware_read_only_role_can_read(): void
    {
        $role = Role::factory()->create(['type' => 'internal']);
        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'enablement',
            'access_level' => 'read',
        ]);

        // Enable the module
        config(['modules.enablement.enabled' => true]);
        Feature::for(null)->activate('enablement');

        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user);
        $this->assertTrue($user->hasModulePermission('enablement'));
        $this->assertFalse($user->canWriteModule('enablement'));
    }

    // ─── PortalCan Middleware ────────────────────────────────────

    public function test_portal_can_allows_contact_with_capability(): void
    {
        $role = Role::factory()->portal()->create(['slug' => 'test_portal_ok']);
        RolePermission::create([
            'role_id' => $role->id,
            'module_slug' => 'goals',
            'access_level' => 'write',
        ]);

        $partner = Partner::factory()->create();
        $contact = Contact::factory()->create([
            'partner_id' => $partner->id,
            'portal_access' => true,
            'portal_role_id' => $role->id,
            'password' => 'password',
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.goals.index'));

        $response->assertOk();
    }

    public function test_portal_can_blocks_contact_without_capability(): void
    {
        $role = Role::factory()->portal()->create(['slug' => 'test_portal_deny']);

        $partner = Partner::factory()->create();
        $contact = Contact::factory()->create([
            'partner_id' => $partner->id,
            'portal_access' => true,
            'portal_role_id' => $role->id,
            'password' => 'password',
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.goals.index'));

        $response->assertForbidden();
    }

    // ─── Factory states ──────────────────────────────────────────

    public function test_user_factory_admin_state(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->bypassesPermissions());
        $this->assertEquals('admin', $user->role->slug);
    }

    public function test_user_factory_psm_state(): void
    {
        $user = User::factory()->psm()->create();

        $this->assertTrue($user->isPsm());
        $this->assertFalse($user->bypassesPermissions());
        $this->assertEquals('psm', $user->role->slug);
    }

    public function test_user_factory_with_role_state(): void
    {
        $user = User::factory()->withRole('custom_test')->create();

        $this->assertEquals('custom_test', $user->role->slug);
    }

    public function test_contact_factory_portal_admin_state(): void
    {
        $contact = Contact::factory()->portalAdmin()->create();

        $this->assertTrue($contact->isPortalAdmin());
        $this->assertEquals('partner_admin', $contact->role->slug);
    }

    public function test_contact_factory_portal_role_state(): void
    {
        $contact = Contact::factory()->portalRole('delivery_lead')->create();

        $this->assertEquals('delivery_lead', $contact->role->slug);
    }
}
