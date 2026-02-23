<?php

namespace Tests\Feature;

use App\Enums\TrainingPhase;
use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ActivationPath $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'psm']);

        $this->path = ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);
    }

    // ─── Index (My Training Dashboard) ───────────────────────

    public function test_unauthenticated_user_redirected_from_training(): void
    {
        $this->get(route('training.index'))->assertRedirect();
    }

    public function test_user_sees_empty_state_when_no_paths_assigned(): void
    {
        $response = $this->actingAs($this->user)->get(route('training.index'));

        $response->assertOk();
        $response->assertSee('No Training Assigned');
    }

    public function test_user_sees_assigned_paths(): void
    {
        $this->assignUser();

        $response = $this->actingAs($this->user)->get(route('training.index'));

        $response->assertOk();
        $response->assertSee($this->path->name);
    }

    public function test_completed_path_shows_completed_badge(): void
    {
        $this->path->users()->attach($this->user->id, [
            'assigned_by' => $this->user->id,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('training.index'));

        $response->assertOk();
        $response->assertSee('Completed');
    }

    // ─── Path Detail ─────────────────────────────────────────

    public function test_user_can_view_assigned_path(): void
    {
        $this->assignUser();
        ActivationStep::factory()->withPhase(TrainingPhase::Tell)->create([
            'activation_path_id' => $this->path->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('training.path', $this->path));

        $response->assertOk();
        $response->assertSee($this->path->name);
        $response->assertSee('Tell');
    }

    public function test_user_cannot_view_unassigned_path(): void
    {
        $response = $this->actingAs($this->user)->get(route('training.path', $this->path));

        $response->assertForbidden();
    }

    public function test_path_shows_phase_timeline(): void
    {
        $this->assignUser();

        ActivationStep::factory()->withPhase(TrainingPhase::Tell)->create([
            'activation_path_id' => $this->path->id,
            'order' => 1,
        ]);
        ActivationStep::factory()->withPhase(TrainingPhase::Show)->create([
            'activation_path_id' => $this->path->id,
            'order' => 2,
        ]);
        ActivationStep::factory()->withPhase(TrainingPhase::Do)->create([
            'activation_path_id' => $this->path->id,
            'order' => 3,
        ]);

        $response = $this->actingAs($this->user)->get(route('training.path', $this->path));

        $response->assertOk();
        $response->assertSee('Tell');
        $response->assertSee('Show');
        $response->assertSee('Do');
    }

    // ─── Step Detail ─────────────────────────────────────────

    public function test_user_can_view_step_content(): void
    {
        $this->assignUser();

        $step = ActivationStep::factory()->withPhase(TrainingPhase::Tell)->create([
            'activation_path_id' => $this->path->id,
            'content_body' => '## Welcome to the Feature',
            'objectives' => ['Learn the basics', 'Understand the workflow'],
        ]);

        $response = $this->actingAs($this->user)->get(route('training.step', $step));

        $response->assertOk();
        $response->assertSee('Welcome to the Feature');
        $response->assertSee('Learn the basics');
        $response->assertSee('Understand the workflow');
    }

    public function test_user_cannot_view_step_from_unassigned_path(): void
    {
        $step = ActivationStep::factory()->create([
            'activation_path_id' => $this->path->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('training.step', $step));

        $response->assertForbidden();
    }

    // ─── Complete Step ───────────────────────────────────────

    public function test_user_can_complete_tell_step(): void
    {
        $this->assignUser();

        $step = ActivationStep::factory()->withPhase(TrainingPhase::Tell)->create([
            'activation_path_id' => $this->path->id,
            'criteria' => ['target' => 1],
        ]);

        $response = $this->actingAs($this->user)->post(route('training.step.complete', $step));

        $response->assertRedirect(route('training.path', $this->path));

        $this->assertDatabaseHas('step_progress', [
            'activation_step_id' => $step->id,
            'user_id' => $this->user->id,
            'completed' => true,
        ]);
    }

    public function test_complete_step_returns_json_when_requested(): void
    {
        $this->assignUser();

        $step = ActivationStep::factory()->withPhase(TrainingPhase::Show)->create([
            'activation_path_id' => $this->path->id,
            'criteria' => ['target' => 1],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('training.step.complete', $step));

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_cannot_complete_step_from_unassigned_path(): void
    {
        $step = ActivationStep::factory()->create([
            'activation_path_id' => $this->path->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('training.step.complete', $step));

        $response->assertForbidden();
    }

    public function test_completing_all_steps_marks_path_complete(): void
    {
        $this->assignUser();

        $step = ActivationStep::factory()->create([
            'activation_path_id' => $this->path->id,
            'is_required' => true,
            'criteria' => ['target' => 1],
        ]);

        $this->actingAs($this->user)->post(route('training.step.complete', $step));

        $pivot = $this->path->users()->where('user_id', $this->user->id)->first()->pivot;
        $this->assertNotNull($pivot->completed_at);
    }

    // ─── Tour Data ───────────────────────────────────────────

    public function test_tour_data_returns_json(): void
    {
        $this->assignUser();

        $tourSteps = [
            ['target' => '#nav', 'title' => 'Navigation', 'text' => 'This is the nav.', 'position' => 'bottom'],
        ];

        $step = ActivationStep::factory()->withPhase(TrainingPhase::Show)->create([
            'activation_path_id' => $this->path->id,
            'tour_route' => 'welcome',
            'tour_steps' => $tourSteps,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('training.step.tour-data', $step));

        $response->assertOk();
        $response->assertJsonPath('step_id', $step->id);
        $response->assertJsonPath('route', 'welcome');
        $response->assertJsonCount(1, 'steps');
        $response->assertJsonPath('steps.0.target', '#nav');
    }

    public function test_tour_data_forbidden_for_unassigned_user(): void
    {
        $step = ActivationStep::factory()->create([
            'activation_path_id' => $this->path->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('training.step.tour-data', $step));

        $response->assertForbidden();
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function assignUser(): void
    {
        $this->path->users()->attach($this->user->id, [
            'assigned_by' => $this->user->id,
            'assigned_at' => now(),
        ]);
    }
}
