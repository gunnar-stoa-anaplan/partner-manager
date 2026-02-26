<?php

namespace Tests\Feature\Portal;

use App\Models\AnaplanModel;
use App\Models\AnaplanVersionConfig;
use App\Models\AnaplanWorkspace;
use App\Models\Contact;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private User $psm;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->psm = User::factory()->psm()->create();
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);
    }

    private function createPortalContact(string $role = 'partner_admin'): Contact
    {
        return Contact::factory()->portalRole($role)->create([
            'partner_id' => $this->partner->id,
        ]);
    }

    // ─── Index ──────────────────────────────────────────────────────

    public function test_portal_admin_can_view_workspaces(): void
    {
        $contact = $this->createPortalContact('partner_admin');

        $workspace = AnaplanWorkspace::factory()->create(['partner_id' => $this->partner->id]);
        AnaplanModel::factory()->create(['anaplan_workspace_id' => $workspace->id]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.workspaces.index'));

        $response->assertOk();
        $response->assertViewHas('workspaces');
        $response->assertViewHas('activeModelCount');
        $response->assertViewHas('totalMemory');
    }

    public function test_delivery_lead_can_view_workspaces(): void
    {
        $contact = $this->createPortalContact('delivery_lead');

        $response = $this->actingAs($contact, 'portal')->get(route('portal.workspaces.index'));

        $response->assertOk();
    }

    public function test_technical_architect_can_view_workspaces(): void
    {
        $contact = $this->createPortalContact('technical_architect');

        $response = $this->actingAs($contact, 'portal')->get(route('portal.workspaces.index'));

        $response->assertOk();
    }

    public function test_sales_role_can_view_workspaces(): void
    {
        $contact = $this->createPortalContact('sales');

        $response = $this->actingAs($contact, 'portal')->get(route('portal.workspaces.index'));

        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_view_workspaces(): void
    {
        $response = $this->get(route('portal.workspaces.index'));

        $response->assertRedirect();
    }

    // ─── Data Scoping ───────────────────────────────────────────────

    public function test_portal_only_shows_own_partner_workspaces(): void
    {
        $contact = $this->createPortalContact('partner_admin');

        $ownWorkspace = AnaplanWorkspace::factory()->create([
            'partner_id' => $this->partner->id,
            'name' => 'IFP Applications - Own Partner',
        ]);

        $otherPartner = Partner::factory()->create(['user_id' => $this->psm->id]);
        AnaplanWorkspace::factory()->create([
            'partner_id' => $otherPartner->id,
            'name' => 'IFP Applications - Other Partner',
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.workspaces.index'));

        $workspaces = $response->viewData('workspaces');
        $this->assertCount(1, $workspaces);
        $this->assertEquals($ownWorkspace->id, $workspaces->first()->id);
    }

    public function test_portal_counts_active_models_only(): void
    {
        $contact = $this->createPortalContact('partner_admin');

        $workspace = AnaplanWorkspace::factory()->create(['partner_id' => $this->partner->id]);
        AnaplanModel::factory()->count(2)->create([
            'anaplan_workspace_id' => $workspace->id,
            'active_state' => 'PRODUCTION',
        ]);
        AnaplanModel::factory()->archived()->create([
            'anaplan_workspace_id' => $workspace->id,
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.workspaces.index'));

        $this->assertEquals(2, $response->viewData('activeModelCount'));
    }

    public function test_portal_calculates_total_memory_from_active_models(): void
    {
        $contact = $this->createPortalContact('partner_admin');

        $workspace = AnaplanWorkspace::factory()->create(['partner_id' => $this->partner->id]);
        AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'active_state' => 'PRODUCTION',
            'memory_usage' => 1_000_000,
        ]);
        AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'active_state' => 'UNLOCKED',
            'memory_usage' => 2_000_000,
        ]);
        AnaplanModel::factory()->archived()->create([
            'anaplan_workspace_id' => $workspace->id,
            'memory_usage' => 999_999_999,
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.workspaces.index'));

        $this->assertEquals(3_000_000, $response->viewData('totalMemory'));
    }

    // ─── Outdated Version Detection ─────────────────────────────────

    public function test_portal_detects_outdated_product_areas(): void
    {
        $contact = $this->createPortalContact('partner_admin');

        $workspace = AnaplanWorkspace::factory()->create([
            'partner_id' => $this->partner->id,
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

        $response = $this->actingAs($contact, 'portal')->get(route('portal.workspaces.index'));

        $outdatedAreas = $response->viewData('outdatedAreas');
        $this->assertCount(1, $outdatedAreas);
        $this->assertEquals('Finance', $outdatedAreas[0]['area']);
        $this->assertEquals('V1.2', $outdatedAreas[0]['current']);
        $this->assertEquals('V2.0', $outdatedAreas[0]['target']);
    }

    public function test_portal_no_outdated_when_on_current_version(): void
    {
        $contact = $this->createPortalContact('partner_admin');

        $workspace = AnaplanWorkspace::factory()->create([
            'partner_id' => $this->partner->id,
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

        $response = $this->actingAs($contact, 'portal')->get(route('portal.workspaces.index'));

        $outdatedAreas = $response->viewData('outdatedAreas');
        $this->assertCount(0, $outdatedAreas);
    }

    public function test_portal_no_outdated_for_demo_versions(): void
    {
        $contact = $this->createPortalContact('partner_admin');

        $workspace = AnaplanWorkspace::factory()->create([
            'partner_id' => $this->partner->id,
            'product_area' => 'Finance',
        ]);
        AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'product_area' => 'Finance',
            'detected_version' => 'Demo',
            'active_state' => 'PRODUCTION',
        ]);

        AnaplanVersionConfig::create([
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.workspaces.index'));

        $outdatedAreas = $response->viewData('outdatedAreas');
        $this->assertCount(0, $outdatedAreas);
    }

    public function test_portal_shows_empty_when_partner_has_no_workspaces(): void
    {
        $contact = $this->createPortalContact('partner_admin');

        $response = $this->actingAs($contact, 'portal')->get(route('portal.workspaces.index'));

        $response->assertOk();
        $this->assertCount(0, $response->viewData('workspaces'));
        $this->assertEquals(0, $response->viewData('activeModelCount'));
        $this->assertEquals(0, $response->viewData('totalMemory'));
    }
}
