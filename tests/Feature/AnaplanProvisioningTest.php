<?php

namespace Tests\Feature;

use App\Models\AnaplanModel;
use App\Models\AnaplanWorkspace;
use App\Models\Partner;
use App\Models\User;
use App\Services\AnaplanApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnaplanProvisioningTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->psm = User::factory()->create(['role' => 'psm']);
    }

    // ─── Index ──────────────────────────────────────────────────────

    public function test_admin_can_view_provisioning_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.anaplan-provisioning.index'));

        $response->assertOk();
        $response->assertViewHas('workspaces');
        $response->assertViewHas('partners');
        $response->assertViewHas('unassignedCount');
        $response->assertViewHas('totalModels');
        $response->assertViewHas('activeModels');
    }

    public function test_non_admin_cannot_view_provisioning(): void
    {
        $response = $this->actingAs($this->psm)->get(route('admin.anaplan-provisioning.index'));

        $response->assertForbidden();
    }

    public function test_index_shows_workspaces_ordered_by_name(): void
    {
        AnaplanWorkspace::factory()->create(['name' => 'Zebra Workspace']);
        AnaplanWorkspace::factory()->create(['name' => 'Alpha Workspace']);

        $response = $this->actingAs($this->admin)->get(route('admin.anaplan-provisioning.index'));

        $workspaces = $response->viewData('workspaces');
        $this->assertEquals('Alpha Workspace', $workspaces->first()->name);
        $this->assertEquals('Zebra Workspace', $workspaces->last()->name);
    }

    public function test_index_counts_unassigned_workspaces(): void
    {
        AnaplanWorkspace::factory()->create(); // assigned (factory default)
        AnaplanWorkspace::factory()->unassigned()->create();
        AnaplanWorkspace::factory()->unassigned()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.anaplan-provisioning.index'));

        $this->assertEquals(2, $response->viewData('unassignedCount'));
    }

    public function test_index_counts_active_vs_total_models(): void
    {
        $workspace = AnaplanWorkspace::factory()->create();

        AnaplanModel::factory()->count(3)->create(['anaplan_workspace_id' => $workspace->id, 'active_state' => 'PRODUCTION']);
        AnaplanModel::factory()->archived()->create(['anaplan_workspace_id' => $workspace->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.anaplan-provisioning.index'));

        $this->assertEquals(4, $response->viewData('totalModels'));
        $this->assertEquals(3, $response->viewData('activeModels'));
    }

    // ─── Assign Partner ─────────────────────────────────────────────

    public function test_admin_can_assign_partner_to_workspace(): void
    {
        $workspace = AnaplanWorkspace::factory()->unassigned()->create();
        $partner = Partner::factory()->create(['user_id' => $this->psm->id]);

        $response = $this->actingAs($this->admin)->put(
            route('admin.anaplan-provisioning.assign', $workspace),
            ['partner_id' => $partner->id]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('anaplan_workspaces', [
            'id' => $workspace->id,
            'partner_id' => $partner->id,
        ]);
    }

    public function test_admin_can_unassign_workspace(): void
    {
        $workspace = AnaplanWorkspace::factory()->create();

        $response = $this->actingAs($this->admin)->put(
            route('admin.anaplan-provisioning.assign', $workspace),
            ['partner_id' => null]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('anaplan_workspaces', [
            'id' => $workspace->id,
            'partner_id' => null,
        ]);
    }

    public function test_assign_validates_partner_exists(): void
    {
        $workspace = AnaplanWorkspace::factory()->unassigned()->create();

        $response = $this->actingAs($this->admin)->put(
            route('admin.anaplan-provisioning.assign', $workspace),
            ['partner_id' => 99999]
        );

        $response->assertSessionHasErrors('partner_id');
    }

    public function test_non_admin_cannot_assign_partner(): void
    {
        $workspace = AnaplanWorkspace::factory()->unassigned()->create();
        $partner = Partner::factory()->create(['user_id' => $this->psm->id]);

        $response = $this->actingAs($this->psm)->put(
            route('admin.anaplan-provisioning.assign', $workspace),
            ['partner_id' => $partner->id]
        );

        $response->assertForbidden();
    }

    // ─── Sync ───────────────────────────────────────────────────────

    public function test_sync_fails_when_api_not_configured(): void
    {
        $this->mock(AnaplanApiService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(false);
        });

        $response = $this->actingAs($this->admin)->post(route('admin.anaplan-provisioning.sync'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_sync_succeeds_when_configured(): void
    {
        $this->mock(AnaplanApiService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('syncAll')->andReturn([
                'workspaces_synced' => 5,
                'models_synced' => 12,
                'models_archived' => 0,
                'workspaces_linked' => 2,
                'errors' => [],
            ]);
        });

        $response = $this->actingAs($this->admin)->post(route('admin.anaplan-provisioning.sync'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_sync_reports_warnings_on_errors(): void
    {
        $this->mock(AnaplanApiService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('syncAll')->andReturn([
                'workspaces_synced' => 3,
                'models_synced' => 5,
                'models_archived' => 0,
                'workspaces_linked' => 0,
                'errors' => ['Failed to fetch models for workspace X'],
            ]);
        });

        $response = $this->actingAs($this->admin)->post(route('admin.anaplan-provisioning.sync'));

        $response->assertRedirect();
        $response->assertSessionHas('warning');
    }

    public function test_non_admin_cannot_trigger_sync(): void
    {
        $response = $this->actingAs($this->psm)->post(route('admin.anaplan-provisioning.sync'));

        $response->assertForbidden();
    }
}
