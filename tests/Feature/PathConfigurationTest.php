<?php

namespace Tests\Feature;

use App\Models\PartnerPath;
use App\Models\PartnerPathGate;
use App\Models\PartnerPathStage;
use App\Models\TaskTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PathConfigurationTest extends TestCase
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

    // ─── Path CRUD ────────────────────────────────────────────────

    public function test_admin_can_view_path_configuration_index(): void
    {
        PartnerPath::factory()->create(['name' => 'Implementation Partner']);

        $response = $this->actingAs($this->admin)->get(route('admin.paths.index'));

        $response->assertOk();
        $response->assertSee('Implementation Partner');
    }

    public function test_non_admin_cannot_access_path_configuration(): void
    {
        $response = $this->actingAs($this->psm)->get(route('admin.paths.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_create_path_with_default_stages(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.paths.store'), [
            'name' => 'New Partner Path',
            'description' => 'A test path',
            'is_default' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('partner_paths', [
            'name' => 'New Partner Path',
            'description' => 'A test path',
            'is_default' => true,
            'created_by' => $this->admin->id,
        ]);

        $path = PartnerPath::where('name', 'New Partner Path')->first();
        $this->assertCount(5, $path->stages);
        $this->assertEquals('Onboarded', $path->stages->sortBy('stage_number')->first()->name);
        $this->assertEquals('Strategic', $path->stages->sortBy('stage_number')->last()->name);
    }

    public function test_path_creation_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.paths.store'), [
            'description' => 'No name provided',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_can_view_path_detail(): void
    {
        $path = PartnerPath::factory()->create();
        PartnerPathStage::factory()->create([
            'partner_path_id' => $path->id,
            'stage_number' => 1,
            'name' => 'Onboarded',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.paths.show', $path));

        $response->assertOk();
        $response->assertSee('Onboarded');
    }

    public function test_admin_can_update_path(): void
    {
        $path = PartnerPath::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)->put(route('admin.paths.update', $path), [
            'name' => 'Updated Path Name',
            'description' => 'Updated description',
            'is_default' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('partner_paths', [
            'id' => $path->id,
            'name' => 'Updated Path Name',
        ]);
    }

    // ─── Stage Management ─────────────────────────────────────────

    public function test_admin_can_add_stage_to_path(): void
    {
        $path = PartnerPath::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.paths.stages.store', $path), [
            'stage_number' => 6,
            'name' => 'Elite',
            'color' => '#FF0000',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('partner_path_stages', [
            'partner_path_id' => $path->id,
            'name' => 'Elite',
            'stage_number' => 6,
            'color' => '#FF0000',
        ]);
    }

    public function test_stage_creation_requires_name_and_number(): void
    {
        $path = PartnerPath::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.paths.stages.store', $path), [
            'color' => '#FF0000',
        ]);

        $response->assertSessionHasErrors(['name', 'stage_number']);
    }

    public function test_admin_can_update_stage(): void
    {
        $path = PartnerPath::factory()->create();
        $stage = PartnerPathStage::factory()->create([
            'partner_path_id' => $path->id,
            'name' => 'Old Stage',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.path-stages.update', $stage), [
            'name' => 'Renamed Stage',
            'color' => '#00FF00',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('partner_path_stages', [
            'id' => $stage->id,
            'name' => 'Renamed Stage',
        ]);
    }

    public function test_admin_can_delete_stage(): void
    {
        $path = PartnerPath::factory()->create();
        $stage = PartnerPathStage::factory()->create(['partner_path_id' => $path->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.path-stages.destroy', $stage));

        $response->assertRedirect();
        $this->assertDatabaseMissing('partner_path_stages', ['id' => $stage->id]);
    }

    // ─── Gate Management ──────────────────────────────────────────

    public function test_admin_can_add_gate_to_path(): void
    {
        $path = PartnerPath::factory()->create();
        $stage1 = PartnerPathStage::factory()->create(['partner_path_id' => $path->id, 'stage_number' => 1, 'sort_order' => 0]);
        $stage2 = PartnerPathStage::factory()->create(['partner_path_id' => $path->id, 'stage_number' => 2, 'sort_order' => 1]);

        $response = $this->actingAs($this->admin)->post(route('admin.paths.gates.store', $path), [
            'from_stage_id' => $stage1->id,
            'to_stage_id' => $stage2->id,
            'requirement_key' => 'certified_mb_count',
            'requirement_label' => 'Model Builder Certifications',
            'threshold_value' => 2,
            'comparison' => 'gte',
            'unit' => 'count',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('partner_path_gates', [
            'partner_path_id' => $path->id,
            'requirement_key' => 'certified_mb_count',
            'threshold_value' => '2.00',
        ]);
    }

    public function test_gate_creation_validates_required_fields(): void
    {
        $path = PartnerPath::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.paths.gates.store', $path), [
            'description' => 'Missing required fields',
        ]);

        $response->assertSessionHasErrors(['from_stage_id', 'to_stage_id', 'requirement_key', 'requirement_label', 'threshold_value']);
    }

    public function test_admin_can_update_gate(): void
    {
        $path = PartnerPath::factory()->create();
        $stage1 = PartnerPathStage::factory()->create(['partner_path_id' => $path->id, 'stage_number' => 1, 'sort_order' => 0]);
        $stage2 = PartnerPathStage::factory()->create(['partner_path_id' => $path->id, 'stage_number' => 2, 'sort_order' => 1]);

        $gate = PartnerPathGate::create([
            'partner_path_id' => $path->id,
            'from_stage_id' => $stage1->id,
            'to_stage_id' => $stage2->id,
            'requirement_key' => 'contact_count',
            'requirement_label' => 'Contacts',
            'threshold_value' => 3,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.path-gates.update', $gate), [
            'requirement_label' => 'Minimum Contacts',
            'threshold_value' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('partner_path_gates', [
            'id' => $gate->id,
            'requirement_label' => 'Minimum Contacts',
            'threshold_value' => '5.00',
        ]);
    }

    public function test_admin_can_delete_gate(): void
    {
        $path = PartnerPath::factory()->create();
        $stage1 = PartnerPathStage::factory()->create(['partner_path_id' => $path->id, 'stage_number' => 1, 'sort_order' => 0]);
        $stage2 = PartnerPathStage::factory()->create(['partner_path_id' => $path->id, 'stage_number' => 2, 'sort_order' => 1]);

        $gate = PartnerPathGate::create([
            'partner_path_id' => $path->id,
            'from_stage_id' => $stage1->id,
            'to_stage_id' => $stage2->id,
            'requirement_key' => 'contact_count',
            'requirement_label' => 'Contacts',
            'threshold_value' => 3,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.path-gates.destroy', $gate));

        $response->assertRedirect();
        $this->assertDatabaseMissing('partner_path_gates', ['id' => $gate->id]);
    }

    // ─── Task Template Management ─────────────────────────────────

    public function test_admin_can_view_task_templates(): void
    {
        $path = PartnerPath::factory()->create();
        $stage = PartnerPathStage::factory()->create(['partner_path_id' => $path->id, 'stage_number' => 1, 'sort_order' => 0]);

        TaskTemplate::factory()->create([
            'partner_path_id' => $path->id,
            'stage_id' => $stage->id,
            'title' => 'Connect Salesforce',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.task-templates.index'));

        $response->assertOk();
        $response->assertSee('Connect Salesforce');
    }

    public function test_admin_can_create_task_template(): void
    {
        $path = PartnerPath::factory()->create();
        $stage = PartnerPathStage::factory()->create(['partner_path_id' => $path->id, 'stage_number' => 1, 'sort_order' => 0]);

        $response = $this->actingAs($this->admin)->post(route('admin.task-templates.store'), [
            'partner_path_id' => $path->id,
            'stage_id' => $stage->id,
            'title' => 'Register Contacts',
            'category' => 'account',
            'requirement_level' => 'required',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('task_templates', [
            'title' => 'Register Contacts',
            'category' => 'account',
            'requirement_level' => 'required',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_task_template_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.task-templates.store'), [
            'description' => 'Missing title',
        ]);

        $response->assertSessionHasErrors(['title', 'category', 'requirement_level']);
    }

    public function test_admin_can_update_task_template(): void
    {
        $template = TaskTemplate::factory()->create(['title' => 'Old Title']);

        $response = $this->actingAs($this->admin)->put(route('admin.task-templates.update', $template), [
            'title' => 'Updated Title',
            'category' => 'enablement',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('task_templates', [
            'id' => $template->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_admin_can_delete_task_template(): void
    {
        $template = TaskTemplate::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.task-templates.destroy', $template));

        $response->assertRedirect();
        $this->assertDatabaseMissing('task_templates', ['id' => $template->id]);
    }

    public function test_task_templates_can_be_filtered_by_path(): void
    {
        $path1 = PartnerPath::factory()->create();
        $path2 = PartnerPath::factory()->create();

        TaskTemplate::factory()->create(['partner_path_id' => $path1->id, 'title' => 'Path 1 Template']);
        TaskTemplate::factory()->create(['partner_path_id' => $path2->id, 'title' => 'Path 2 Template']);

        $response = $this->actingAs($this->admin)->get(route('admin.task-templates.index', ['path_id' => $path1->id]));

        $response->assertOk();
        $response->assertSee('Path 1 Template');
        $response->assertDontSee('Path 2 Template');
    }
}
