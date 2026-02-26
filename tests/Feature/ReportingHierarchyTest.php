<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $superAdmin;

    private User $coe;

    private User $manager;

    private User $psm;

    private User $integration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->admin()->create();
        $this->superAdmin = User::factory()->superAdmin()->create();
        $this->coe = User::factory()->coe()->create();
        $this->manager = User::factory()->manager()->create();
        $this->psm = User::factory()->psm()->create();
        $this->integration = User::factory()->integration()->create();
    }

    public function test_admin_can_access_reporting_hierarchy(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reporting-hierarchy.index'))
            ->assertOk();
    }

    public function test_super_admin_can_access_reporting_hierarchy(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('reporting-hierarchy.index'))
            ->assertOk();
    }

    public function test_coe_can_access_reporting_hierarchy(): void
    {
        $this->actingAs($this->coe)
            ->get(route('reporting-hierarchy.index'))
            ->assertOk();
    }

    public function test_manager_can_access_reporting_hierarchy(): void
    {
        $this->actingAs($this->manager)
            ->get(route('reporting-hierarchy.index'))
            ->assertOk();
    }

    public function test_psm_cannot_access_reporting_hierarchy(): void
    {
        $this->actingAs($this->psm)
            ->get(route('reporting-hierarchy.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('reporting-hierarchy.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_update_user_manager(): void
    {
        $this->actingAs($this->admin)
            ->put(route('reporting-hierarchy.update', $this->psm), [
                'manager_id' => $this->manager->id,
            ])
            ->assertRedirect(route('reporting-hierarchy.index'));

        $this->assertDatabaseHas('users', [
            'id' => $this->psm->id,
            'manager_id' => $this->manager->id,
        ]);
    }

    public function test_manager_can_update_user_manager(): void
    {
        $this->actingAs($this->manager)
            ->put(route('reporting-hierarchy.update', $this->psm), [
                'manager_id' => $this->admin->id,
            ])
            ->assertRedirect(route('reporting-hierarchy.index'));

        $this->assertDatabaseHas('users', [
            'id' => $this->psm->id,
            'manager_id' => $this->admin->id,
        ]);
    }

    public function test_psm_cannot_update_user_manager(): void
    {
        $this->actingAs($this->psm)
            ->put(route('reporting-hierarchy.update', $this->manager), [
                'manager_id' => $this->admin->id,
            ])
            ->assertForbidden();
    }

    public function test_can_clear_manager(): void
    {
        $this->psm->update(['manager_id' => $this->manager->id]);

        $this->actingAs($this->admin)
            ->put(route('reporting-hierarchy.update', $this->psm), [
                'manager_id' => null,
            ])
            ->assertRedirect(route('reporting-hierarchy.index'));

        $this->assertDatabaseHas('users', [
            'id' => $this->psm->id,
            'manager_id' => null,
        ]);
    }

    public function test_cannot_set_user_as_own_manager(): void
    {
        $this->actingAs($this->admin)
            ->put(route('reporting-hierarchy.update', $this->psm), [
                'manager_id' => $this->psm->id,
            ])
            ->assertSessionHasErrors('manager_id');
    }

    public function test_cannot_set_non_existent_manager(): void
    {
        $this->actingAs($this->admin)
            ->put(route('reporting-hierarchy.update', $this->psm), [
                'manager_id' => 99999,
            ])
            ->assertSessionHasErrors('manager_id');
    }

    public function test_index_displays_users_with_managers(): void
    {
        $this->psm->update(['manager_id' => $this->manager->id]);

        $this->actingAs($this->admin)
            ->get(route('reporting-hierarchy.index'))
            ->assertOk()
            ->assertSee($this->psm->name)
            ->assertSee($this->manager->name);
    }

    public function test_multi_level_hierarchy(): void
    {
        $director = User::factory()->admin()->create();
        $this->manager->update(['manager_id' => $director->id]);
        $this->psm->update(['manager_id' => $this->manager->id]);

        $this->psm->refresh();
        $this->manager->refresh();

        $this->assertEquals($this->manager->id, $this->psm->manager_id);
        $this->assertEquals($director->id, $this->manager->manager_id);
        $this->assertCount(1, $this->manager->directReports);
        $this->assertCount(1, $director->directReports);
    }
}
