<?php

namespace Tests\Feature;

use App\Enums\StepType;
use App\Enums\TrainingPhase;
use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Models\User;
use App\Services\ActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivationTrainingPhaseTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_step_phase_is_cast_to_training_phase_enum(): void
    {
        $path = ActivationPath::factory()->create();
        $step = ActivationStep::factory()
            ->withPhase(TrainingPhase::Tell)
            ->create(['activation_path_id' => $path->id]);

        $step->refresh();

        $this->assertInstanceOf(TrainingPhase::class, $step->phase);
        $this->assertEquals(TrainingPhase::Tell, $step->phase);
    }

    public function test_step_phase_can_be_null(): void
    {
        $path = ActivationPath::factory()->create();
        $step = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'phase' => null,
        ]);

        $step->refresh();

        $this->assertNull($step->phase);
    }

    public function test_step_phase_persists_all_enum_values(): void
    {
        $path = ActivationPath::factory()->create();

        foreach (TrainingPhase::cases() as $index => $phase) {
            $step = ActivationStep::factory()
                ->withPhase($phase)
                ->create([
                    'activation_path_id' => $path->id,
                    'order' => $index + 1,
                ]);

            $step->refresh();

            $this->assertEquals($phase, $step->phase);
        }
    }

    public function test_store_step_accepts_phase(): void
    {
        $path = ActivationPath::factory()->create();

        $response = $this->actingAs($this->admin)->post(
            route('admin.activation.steps.store', $path),
            [
                'name' => 'Watch Demo Video',
                'description' => 'Observe the feature walkthrough.',
                'step_type' => StepType::TrainingCompleted->value,
                'target_value' => 1,
                'is_required' => true,
                'phase' => 'tell',
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('activation_steps', [
            'activation_path_id' => $path->id,
            'name' => 'Watch Demo Video',
            'phase' => 'tell',
        ]);
    }

    public function test_store_step_accepts_null_phase(): void
    {
        $path = ActivationPath::factory()->create();

        $response = $this->actingAs($this->admin)->post(
            route('admin.activation.steps.store', $path),
            [
                'name' => 'Generic Step',
                'description' => 'No phase assigned.',
                'step_type' => StepType::TrainingCompleted->value,
                'target_value' => 1,
                'is_required' => true,
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('activation_steps', [
            'activation_path_id' => $path->id,
            'name' => 'Generic Step',
            'phase' => null,
        ]);
    }

    public function test_update_step_changes_phase(): void
    {
        $path = ActivationPath::factory()->create();
        $step = ActivationStep::factory()
            ->withPhase(TrainingPhase::Tell)
            ->create(['activation_path_id' => $path->id]);

        $response = $this->actingAs($this->admin)->put(
            route('admin.activation.steps.update', $step),
            [
                'name' => $step->name,
                'description' => $step->description,
                'step_type' => $step->step_type->value,
                'target_value' => $step->criteria['target'] ?? 1,
                'is_required' => $step->is_required,
                'order' => $step->order,
                'phase' => 'do',
            ]
        );

        $response->assertRedirect();

        $step->refresh();
        $this->assertEquals(TrainingPhase::Do, $step->phase);
    }

    public function test_snapshot_includes_phase(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);
        ActivationStep::factory()
            ->withPhase(TrainingPhase::Show)
            ->create(['activation_path_id' => $path->id]);

        $service = app(ActivationService::class);
        $snapshot = $service->getSnapshot();

        $this->assertNotEmpty($snapshot);
        $this->assertEquals('show', $snapshot[0]['steps'][0]['phase']);
    }

    public function test_snapshot_phase_is_null_when_not_set(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);
        ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'phase' => null,
        ]);

        $service = app(ActivationService::class);
        $snapshot = $service->getSnapshot();

        $this->assertNotEmpty($snapshot);
        $this->assertNull($snapshot[0]['steps'][0]['phase']);
    }

    public function test_progression_page_shows_phase_timeline(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);

        ActivationStep::factory()->withPhase(TrainingPhase::Tell)->create([
            'activation_path_id' => $path->id,
            'order' => 1,
        ]);
        ActivationStep::factory()->withPhase(TrainingPhase::Show)->create([
            'activation_path_id' => $path->id,
            'order' => 2,
        ]);
        ActivationStep::factory()->withPhase(TrainingPhase::Do)->create([
            'activation_path_id' => $path->id,
            'order' => 3,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.activation.progression'));

        $response->assertOk();
        $response->assertSee('Tell');
        $response->assertSee('Show');
        $response->assertSee('Do');
    }
}
