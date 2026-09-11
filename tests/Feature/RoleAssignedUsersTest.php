<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The role edit page lists the people assigned to that role: internal users by
 * primary role (role_id) or multi-role pivot; portal roles list their contacts.
 */
class RoleAssignedUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_internal_users_by_primary_and_pivot_role(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $role = Role::factory()->create(['type' => 'internal']);

        $primary = User::factory()->create(['role_id' => $role->id, 'name' => 'Primary Person']);
        $viaPivot = User::factory()->create(['name' => 'Pivot Person']);
        $viaPivot->roles()->attach($role->id);
        User::factory()->create(['name' => 'Unrelated Person']);

        $res = $this->actingAs($admin)->get(route('admin.roles.edit', $role));

        $res->assertOk();
        $res->assertSee('Primary Person');
        $res->assertSee('Pivot Person');
        $res->assertDontSee('Unrelated Person');
        $res->assertSee('Assigned Users');
    }

    public function test_lists_portal_contacts_for_a_portal_role(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $role = Role::factory()->create(['type' => 'portal']);
        $partner = Partner::factory()->create(['name' => 'Acme Co']);

        Contact::factory()->create([
            'portal_role_id' => $role->id,
            'partner_id' => $partner->id,
            'first_name' => 'Portal',
            'last_name' => 'Person',
        ]);
        Contact::factory()->create(['first_name' => 'Other', 'last_name' => 'Contact']);

        $res = $this->actingAs($admin)->get(route('admin.roles.edit', $role));

        $res->assertOk();
        $res->assertSee('Assigned Contacts');
        $res->assertSee('Portal Person');
        $res->assertSee('Acme Co');
        $res->assertDontSee('Other Contact');
    }
}
