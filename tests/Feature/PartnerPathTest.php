<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\PartnerPath;
use App\Models\PartnerPathEnrollment;
use App\Models\PartnerPathGate;
use App\Models\PartnerPathStage;
use App\Models\PartnerStageHistory;
use App\Models\TaskTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerPathTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    private Partner $partner;

    private PartnerPath $path;

    /** @var \Illuminate\Support\Collection<int, PartnerPathStage> */
    private $stages;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->admin()->create();
        $this->psm = User::factory()->psm()->create();
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);

        $this->path = PartnerPath::factory()->create(['is_active' => true]);
        $this->stages = collect();

        foreach (['Onboarded', 'Enabled', 'Performing', 'Accelerating', 'Strategic'] as $i => $name) {
            $this->stages->push(PartnerPathStage::factory()->create([
                'partner_path_id' => $this->path->id,
                'stage_number' => $i + 1,
                'name' => $name,
                'sort_order' => $i,
            ]));
        }
    }

    private function createEnrollment(int $stageIndex = 0): PartnerPathEnrollment
    {
        return PartnerPathEnrollment::factory()->create([
            'partner_id' => $this->partner->id,
            'partner_path_id' => $this->path->id,
            'current_stage_id' => $this->stages[$stageIndex]->id,
            'enrolled_at' => now()->subDays(30),
            'stage_entered_at' => now()->subDays(10),
            'status' => 'active',
        ]);
    }

    private function createGate(int $fromIndex, int $toIndex, string $key = 'contact_count', float $threshold = 3): PartnerPathGate
    {
        return PartnerPathGate::create([
            'partner_path_id' => $this->path->id,
            'from_stage_id' => $this->stages[$fromIndex]->id,
            'to_stage_id' => $this->stages[$toIndex]->id,
            'requirement_key' => $key,
            'requirement_label' => ucwords(str_replace('_', ' ', $key)),
            'comparison' => 'gte',
            'threshold_value' => $threshold,
            'unit' => 'count',
        ]);
    }

    // ─── PSM Path Dashboard ───────────────────────────────────────

    public function test_psm_can_view_paths_dashboard(): void
    {
        $this->createEnrollment();

        $response = $this->actingAs($this->psm)->get(route('plan.paths.index'));

        $response->assertOk();
        $response->assertViewHas('enrollments');
        $response->assertSee($this->partner->name);
    }

    public function test_admin_can_view_all_partner_enrollments(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        PartnerPathEnrollment::factory()->create([
            'partner_id' => $otherPartner->id,
            'partner_path_id' => $this->path->id,
            'current_stage_id' => $this->stages[0]->id,
        ]);
        $this->createEnrollment();

        $response = $this->actingAs($this->admin)->get(route('plan.paths.index'));

        $response->assertOk();
        $response->assertViewHas('canSeeAll', true);
        $enrollments = $response->viewData('enrollments');
        $this->assertCount(2, $enrollments);
    }

    public function test_psm_only_sees_own_partner_enrollments(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        PartnerPathEnrollment::factory()->create([
            'partner_id' => $otherPartner->id,
            'partner_path_id' => $this->path->id,
            'current_stage_id' => $this->stages[0]->id,
        ]);
        $this->createEnrollment();

        $response = $this->actingAs($this->psm)->get(route('plan.paths.index'));

        $response->assertOk();
        $response->assertViewHas('canSeeAll', false);
        $enrollments = $response->viewData('enrollments');
        $this->assertCount(1, $enrollments);
        $this->assertEquals($this->partner->id, $enrollments->first()->partner_id);
    }

    public function test_psm_filter_narrows_enrollments(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        PartnerPathEnrollment::factory()->create([
            'partner_id' => $otherPartner->id,
            'partner_path_id' => $this->path->id,
            'current_stage_id' => $this->stages[0]->id,
        ]);
        $this->createEnrollment();

        $response = $this->actingAs($this->admin)->get(route('plan.paths.index', ['psm_id' => $this->psm->id]));

        $response->assertOk();
        $enrollments = $response->viewData('enrollments');
        $this->assertCount(1, $enrollments);
        $this->assertEquals($this->partner->id, $enrollments->first()->partner_id);
    }

    // ─── Enrollment ───────────────────────────────────────────────

    public function test_psm_can_enroll_partner_in_path(): void
    {
        $response = $this->actingAs($this->psm)->post(route('plan.paths.enroll'), [
            'partner_id' => $this->partner->id,
            'partner_path_id' => $this->path->id,
        ]);

        $response->assertRedirect(route('plan.paths.index'));

        $this->assertDatabaseHas('partner_path_enrollments', [
            'partner_id' => $this->partner->id,
            'partner_path_id' => $this->path->id,
            'current_stage_id' => $this->stages[0]->id,
            'status' => 'active',
        ]);
    }

    public function test_enrollment_validates_partner_and_path(): void
    {
        $response = $this->actingAs($this->psm)->post(route('plan.paths.enroll'), [
            'partner_id' => 99999,
            'partner_path_id' => 99999,
        ]);

        $response->assertSessionHasErrors(['partner_id', 'partner_path_id']);
    }

    public function test_enrollment_auto_populates_tasks_from_templates(): void
    {
        TaskTemplate::factory()->create([
            'partner_path_id' => $this->path->id,
            'stage_id' => $this->stages[0]->id,
            'title' => 'Connect Salesforce',
            'category' => 'account',
            'requirement_level' => 'required',
            'is_active' => true,
        ]);

        $this->actingAs($this->psm)->post(route('plan.paths.enroll'), [
            'partner_id' => $this->partner->id,
            'partner_path_id' => $this->path->id,
        ]);

        $this->assertDatabaseHas('partner_tasks', [
            'partner_id' => $this->partner->id,
            'title' => 'Connect Salesforce',
            'status' => 'not_started',
        ]);
    }

    // ─── Enrollment Detail ────────────────────────────────────────

    public function test_psm_can_view_enrollment_detail(): void
    {
        $enrollment = $this->createEnrollment(1);

        $this->createGate(1, 2, 'contact_count', 3);

        // Create contacts to evaluate the gate
        Contact::factory()->count(2)->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->get(route('plan.paths.show', $enrollment));

        $response->assertOk();
        $response->assertViewHas('enrollment');
        $response->assertViewHas('gateResults');
        $response->assertViewHas('nextStage');
        $response->assertSee($this->partner->name);
    }

    public function test_enrollment_detail_shows_gate_results(): void
    {
        $enrollment = $this->createEnrollment(0);

        $this->createGate(0, 1, 'contact_count', 3);

        Contact::factory()->count(5)->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->get(route('plan.paths.show', $enrollment));

        $response->assertOk();
        $gateResults = $response->viewData('gateResults');
        $this->assertCount(1, $gateResults);
        $this->assertTrue($gateResults->first()['met']);
    }

    public function test_enrollment_at_final_stage_has_no_next_stage(): void
    {
        $enrollment = $this->createEnrollment(4); // Strategic = last stage

        $response = $this->actingAs($this->psm)->get(route('plan.paths.show', $enrollment));

        $response->assertOk();
        $this->assertNull($response->viewData('nextStage'));
    }

    // ─── Stage Advancement ────────────────────────────────────────

    public function test_psm_can_advance_partner_to_next_stage(): void
    {
        $enrollment = $this->createEnrollment(0);

        // No gates — should advance freely
        $response = $this->actingAs($this->psm)->post(route('plan.paths.advance', $enrollment));

        $response->assertRedirect(route('plan.paths.show', $enrollment));
        $response->assertSessionHas('success');

        $enrollment->refresh();
        $this->assertEquals($this->stages[1]->id, $enrollment->current_stage_id);
    }

    public function test_stage_advancement_creates_history_record(): void
    {
        $enrollment = $this->createEnrollment(0);

        $this->actingAs($this->psm)->post(route('plan.paths.advance', $enrollment));

        $this->assertDatabaseHas('partner_stage_history', [
            'partner_id' => $this->partner->id,
            'from_stage_id' => $this->stages[0]->id,
            'to_stage_id' => $this->stages[1]->id,
            'approved_by' => $this->psm->id,
        ]);
    }

    public function test_stage_advancement_populates_new_tasks(): void
    {
        $enrollment = $this->createEnrollment(0);

        TaskTemplate::factory()->create([
            'partner_path_id' => $this->path->id,
            'stage_id' => $this->stages[1]->id,
            'title' => 'Stage 2 Task',
            'is_active' => true,
        ]);

        $this->actingAs($this->psm)->post(route('plan.paths.advance', $enrollment));

        $this->assertDatabaseHas('partner_tasks', [
            'partner_id' => $this->partner->id,
            'title' => 'Stage 2 Task',
            'stage_id' => $this->stages[1]->id,
        ]);
    }

    public function test_cannot_advance_past_final_stage(): void
    {
        $enrollment = $this->createEnrollment(4); // Strategic = last

        $response = $this->actingAs($this->psm)->post(route('plan.paths.advance', $enrollment));

        $response->assertNotFound();
    }

    // ─── Path Reviews ─────────────────────────────────────────────

    public function test_psm_can_view_path_reviews(): void
    {
        $this->createEnrollment(1);

        $response = $this->actingAs($this->psm)->get(route('act.path-reviews.index'));

        $response->assertOk();
        $response->assertSee($this->partner->name);
    }

    public function test_psm_can_view_path_review_detail(): void
    {
        $enrollment = $this->createEnrollment(0);
        $this->createGate(0, 1, 'contact_count', 2);

        $response = $this->actingAs($this->psm)->get(route('act.path-reviews.show', $enrollment));

        $response->assertOk();
        $response->assertViewHas('totalGates', 1);
        $response->assertSee('Contact Count');
    }

    public function test_reviewer_can_advance_when_all_gates_met(): void
    {
        $enrollment = $this->createEnrollment(0);
        $this->createGate(0, 1, 'contact_count', 2);

        Contact::factory()->count(3)->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->post(route('act.path-reviews.advance', $enrollment));

        $response->assertRedirect(route('act.path-reviews.show', $enrollment));
        $response->assertSessionHas('success');

        $enrollment->refresh();
        $this->assertEquals($this->stages[1]->id, $enrollment->current_stage_id);
    }

    public function test_reviewer_cannot_advance_when_gates_not_met(): void
    {
        $enrollment = $this->createEnrollment(0);
        $this->createGate(0, 1, 'contact_count', 10);

        // Only 1 contact — threshold is 10
        Contact::factory()->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->post(route('act.path-reviews.advance', $enrollment));

        $response->assertRedirect(route('act.path-reviews.show', $enrollment));
        $response->assertSessionHas('error');

        $enrollment->refresh();
        $this->assertEquals($this->stages[0]->id, $enrollment->current_stage_id);
    }

    public function test_reviewer_can_place_enrollment_on_hold(): void
    {
        $enrollment = $this->createEnrollment(0);

        $response = $this->actingAs($this->psm)->post(route('act.path-reviews.hold', $enrollment), [
            'notes' => 'Partner needs more support.',
        ]);

        $response->assertRedirect(route('act.path-reviews.index'));

        $enrollment->refresh();
        $this->assertEquals('paused', $enrollment->status);
        $this->assertEquals('Partner needs more support.', $enrollment->notes);
        $this->assertEquals($this->psm->id, $enrollment->reviewed_by);
    }

    public function test_held_enrollment_not_shown_in_review_list(): void
    {
        $enrollment = $this->createEnrollment(0);
        $enrollment->update(['status' => 'paused']);

        $response = $this->actingAs($this->psm)->get(route('act.path-reviews.index'));

        $response->assertOk();
        $enrollments = $response->viewData('enrollments');
        $this->assertCount(0, $enrollments);
    }

    // ─── Stage History ────────────────────────────────────────────

    public function test_enrollment_detail_shows_stage_history(): void
    {
        $enrollment = $this->createEnrollment(1);

        PartnerStageHistory::factory()->create([
            'partner_id' => $this->partner->id,
            'partner_path_id' => $this->path->id,
            'from_stage_id' => $this->stages[0]->id,
            'to_stage_id' => $this->stages[1]->id,
            'transitioned_at' => now()->subDays(10),
            'approved_by' => $this->psm->id,
            'notes' => 'All gates met — good progress',
        ]);

        $response = $this->actingAs($this->psm)->get(route('plan.paths.show', $enrollment));

        $response->assertOk();
        $history = $response->viewData('history');
        $this->assertCount(1, $history);
        $response->assertSee('All gates met');
    }
}
