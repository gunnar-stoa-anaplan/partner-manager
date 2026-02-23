<?php

namespace Tests\Feature;

use App\Models\AnaplanModel;
use App\Models\AnaplanVersionConfig;
use App\Models\AnaplanWorkspace;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithActivationSetup;

class AnaplanOverviewTest extends TestCase
{
    use RefreshDatabase, WithActivationSetup;

    private User $admin;

    private User $psm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->psm = User::factory()->create(['role' => 'psm']);
        $this->activateModuleForUser('provisioning_admin', $this->admin);
    }

    // ─── Index ──────────────────────────────────────────────────────

    public function test_admin_can_view_anaplan_overview(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.provisioning-summary'));

        $response->assertOk();
        $response->assertViewHas('productAreas');
        $response->assertViewHas('productAreaStats');
        $response->assertViewHas('totalPartners');
        $response->assertViewHas('totalModels');
    }

    public function test_non_admin_cannot_view_anaplan_overview(): void
    {
        $response = $this->actingAs($this->psm)->get(route('admin.provisioning-summary'));

        $response->assertForbidden();
    }

    public function test_overview_shows_version_configs(): void
    {
        AnaplanVersionConfig::create([
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
            'updated_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.provisioning-summary'));

        $response->assertOk();
        $versionConfigs = $response->viewData('versionConfigs');
        $this->assertTrue($versionConfigs->has('Finance'));
        $this->assertEquals('V2.0', $versionConfigs['Finance']->current_version);
    }

    public function test_overview_counts_assigned_partners(): void
    {
        $partner1 = Partner::factory()->create(['user_id' => $this->psm->id]);
        $partner2 = Partner::factory()->create(['user_id' => $this->psm->id]);

        AnaplanWorkspace::factory()->create(['partner_id' => $partner1->id]);
        AnaplanWorkspace::factory()->create(['partner_id' => $partner2->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.provisioning-summary'));

        $response->assertOk();
        $this->assertEquals(2, $response->viewData('totalPartners'));
    }

    public function test_overview_counts_active_models(): void
    {
        $workspace = AnaplanWorkspace::factory()->create(['partner_id' => Partner::factory()->create(['user_id' => $this->psm->id])->id]);

        AnaplanModel::factory()->count(3)->create(['anaplan_workspace_id' => $workspace->id, 'active_state' => 'PRODUCTION']);
        AnaplanModel::factory()->archived()->create(['anaplan_workspace_id' => $workspace->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.provisioning-summary'));

        $response->assertOk();
        $this->assertEquals(3, $response->viewData('totalModels'));
    }

    public function test_overview_excludes_unassigned_workspaces_from_partner_count(): void
    {
        AnaplanWorkspace::factory()->unassigned()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.provisioning-summary'));

        $response->assertOk();
        $this->assertEquals(0, $response->viewData('totalPartners'));
    }

    public function test_overview_product_area_stats_structure(): void
    {
        $partner = Partner::factory()->create(['user_id' => $this->psm->id]);
        $workspace = AnaplanWorkspace::factory()->create([
            'partner_id' => $partner->id,
            'product_area' => 'Finance',
        ]);
        AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'product_area' => 'Finance',
            'detected_version' => 'V2.0',
            'active_state' => 'PRODUCTION',
        ]);

        AnaplanVersionConfig::create([
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.provisioning-summary'));

        $stats = $response->viewData('productAreaStats');
        $this->assertArrayHasKey('Finance', $stats);
        $this->assertArrayHasKey('total_partners', $stats['Finance']);
        $this->assertArrayHasKey('total_models', $stats['Finance']);
        $this->assertArrayHasKey('total_memory', $stats['Finance']);
        $this->assertArrayHasKey('outdated_count', $stats['Finance']);
        $this->assertArrayHasKey('version_distribution', $stats['Finance']);
        $this->assertArrayHasKey('partners', $stats['Finance']);
    }

    public function test_overview_detects_outdated_partners(): void
    {
        $partner = Partner::factory()->create(['user_id' => $this->psm->id]);
        $workspace = AnaplanWorkspace::factory()->create([
            'partner_id' => $partner->id,
            'product_area' => 'Finance',
        ]);

        AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'product_area' => 'Finance',
            'detected_version' => 'V1.2',
            'active_state' => 'PRODUCTION',
        ]);

        AnaplanVersionConfig::create([
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.provisioning-summary'));

        $stats = $response->viewData('productAreaStats');
        $this->assertEquals(1, $stats['Finance']['outdated_count']);
    }

    public function test_overview_shows_current_partner_as_not_outdated(): void
    {
        $partner = Partner::factory()->create(['user_id' => $this->psm->id]);
        $workspace = AnaplanWorkspace::factory()->create([
            'partner_id' => $partner->id,
            'product_area' => 'Finance',
        ]);

        AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'product_area' => 'Finance',
            'detected_version' => 'V2.0',
            'active_state' => 'PRODUCTION',
        ]);

        AnaplanVersionConfig::create([
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.provisioning-summary'));

        $stats = $response->viewData('productAreaStats');
        $this->assertEquals(0, $stats['Finance']['outdated_count']);
    }

    // ─── Version Config Update ──────────────────────────────────────

    public function test_admin_can_update_version_config(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.provisioning-summary.update-version'), [
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('anaplan_version_configs', [
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
            'updated_by' => $this->admin->id,
        ]);
    }

    public function test_version_config_update_requires_valid_version(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.provisioning-summary.update-version'), [
            'product_area' => 'Finance',
            'current_version' => 'InvalidVersion',
        ]);

        $response->assertSessionHasErrors('current_version');
    }

    public function test_version_config_update_requires_product_area(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.provisioning-summary.update-version'), [
            'current_version' => 'V2.0',
        ]);

        $response->assertSessionHasErrors('product_area');
    }

    public function test_version_config_upserts_existing_record(): void
    {
        AnaplanVersionConfig::create([
            'product_area' => 'Finance',
            'current_version' => 'V1.3',
            'updated_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)->put(route('admin.provisioning-summary.update-version'), [
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $this->assertDatabaseCount('anaplan_version_configs', 1);
        $this->assertDatabaseHas('anaplan_version_configs', [
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);
    }

    public function test_non_admin_cannot_update_version_config(): void
    {
        $response = $this->actingAs($this->psm)->put(route('admin.provisioning-summary.update-version'), [
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $response->assertForbidden();
    }
}
