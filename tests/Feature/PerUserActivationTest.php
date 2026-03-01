<?php

namespace Tests\Feature;

use App\Enums\TrainingPhase;
use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Models\StepProgress;
use App\Models\User;
use App\Services\ActivationService;
use App\Services\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;
use Tests\Traits\WithActivationSetup;

class PerUserActivationTest extends TestCase
{
    use RefreshDatabase, WithActivationSetup;

    private User $admin;

    private User $psm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->admin()->create();
        $this->psm = User::factory()->psm()->create();
    }

    // ─── No-Path Default: allow when no path exists ────────────

    public function test_user_allowed_when_no_activation_path_exists(): void
    {
        Feature::for(null)->activate('enablement');

        $registry = app(ModuleRegistry::class);

        $this->assertTrue($registry->isEnabled('enablement', $this->admin));
    }

    public function test_admin_allowed_when_pennant_active_and_no_path(): void
    {
        Feature::for(null)->activate('enablement');

        $response = $this->actingAs($this->admin)->get(route('blueprints.index'));

        $response->assertOk();
    }

    // ─── Pennant Off: block non-admin, allow admin ─────────────

    public function test_non_admin_blocked_when_pennant_inactive(): void
    {
        Feature::for(null)->deactivate('enablement');

        $registry = app(ModuleRegistry::class);

        $this->assertFalse($registry->isEnabled('enablement', $this->psm));
    }

    public function test_admin_bypasses_pennant_inactive(): void
    {
        Feature::for(null)->deactivate('enablement');

        $registry = app(ModuleRegistry::class);

        $this->assertTrue($registry->isEnabled('enablement', $this->admin));
    }

    public function test_route_returns_403_for_non_admin_when_pennant_inactive(): void
    {
        Feature::for(null)->deactivate('enablement');

        $response = $this->actingAs($this->psm)->get(route('blueprints.index'));

        $response->assertForbidden();
    }

    public function test_admin_route_allowed_when_pennant_inactive(): void
    {
        Feature::for(null)->deactivate('enablement');

        $response = $this->actingAs($this->admin)->get(route('blueprints.index'));

        $response->assertOk();
    }

    // ─── With Path: incomplete blocks, complete allows ─────────

    public function test_user_blocked_with_incomplete_activation_path(): void
    {
        Feature::for(null)->activate('enablement');

        $registry = app(ModuleRegistry::class);
        $registry->setTrainingMode('enablement', 'required');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement',
            'is_active' => true,
        ]);

        $step = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'criteria' => ['target' => 1],
        ]);

        // Assign non-admin user but don't complete
        $path->users()->attach($this->psm->id, [
            'assigned_by' => $this->admin->id,
            'assigned_at' => now(),
        ]);

        // Non-admin with incomplete path is blocked when training is required
        $this->assertFalse($registry->isEnabled('enablement', $this->psm));

        // Admin always passes
        $this->assertTrue($registry->isEnabled('enablement', $this->admin));
    }

    public function test_user_allowed_with_completed_activation_path(): void
    {
        $this->activateModuleForUser('enablement', $this->admin);

        $registry = app(ModuleRegistry::class);

        $this->assertTrue($registry->isEnabled('enablement', $this->admin));
    }

    public function test_non_admin_blocked_with_incomplete_tell(): void
    {
        Feature::for(null)->activate('enablement');
        app(ModuleRegistry::class)->setTrainingMode('enablement', 'required');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement',
            'is_active' => true,
        ]);

        ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'phase' => TrainingPhase::Tell,
        ]);

        $path->users()->attach($this->psm->id, ['assigned_by' => $this->admin->id]);

        // Non-admin assigned but Tell phase not completed — redirected to training
        $response = $this->actingAs($this->psm)->get(route('influencers.index'));

        $response->assertRedirect();
    }

    public function test_route_allowed_with_completed_path(): void
    {
        $this->activateModuleForUser('enablement', $this->admin);

        $response = $this->actingAs($this->admin)->get(route('blueprints.index'));

        $response->assertOk();
    }

    // ─── Admin bypass ──────────────────────────────────────────

    public function test_admin_bypasses_activation_gating(): void
    {
        Feature::for(null)->activate('enablement');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement',
            'is_active' => true,
        ]);

        ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'phase' => TrainingPhase::Tell,
        ]);

        // Admin has full visibility regardless of activation path status
        $response = $this->actingAs($this->admin)->get(route('blueprints.index'));

        $response->assertOk();
    }

    public function test_module_accessible_once_tell_phase_completed(): void
    {
        Feature::for(null)->activate('enablement');
        app(ModuleRegistry::class)->setTrainingMode('enablement', 'required');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement',
            'is_active' => true,
        ]);

        $tellStep = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'order' => 1,
            'is_required' => true,
            'phase' => TrainingPhase::Tell,
        ]);

        // Also add a Show step (should not block module access)
        ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'order' => 2,
            'is_required' => true,
            'phase' => TrainingPhase::Show,
        ]);

        $path->users()->attach($this->psm->id, ['assigned_by' => $this->admin->id]);

        // Before Tell — locked, redirected to training
        $response = $this->actingAs($this->psm)->get(route('influencers.index'));
        $response->assertRedirect();

        // Complete the Tell step
        StepProgress::factory()->create([
            'activation_step_id' => $tellStep->id,
            'user_id' => $this->psm->id,
            'current_value' => 1,
            'target_value' => 1,
            'completed' => true,
            'completed_at' => now(),
        ]);

        // Tell complete — non-admin now has module access for Show/Do training
        $response = $this->actingAs($this->psm)->get(route('influencers.index'));

        $response->assertOk();
    }

    // ─── Per-user isolation ────────────────────────────────────

    public function test_different_users_have_independent_access(): void
    {
        Feature::for(null)->activate('enablement');

        $registry = app(ModuleRegistry::class);
        $registry->setTrainingMode('enablement', 'required');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement',
            'is_active' => true,
        ]);

        ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
        ]);

        // Admin completes, PSM does not
        $path->users()->attach($this->admin->id, [
            'assigned_by' => $this->admin->id,
            'completed_at' => now(),
        ]);
        $path->users()->attach($this->psm->id, [
            'assigned_by' => $this->admin->id,
        ]);

        $this->assertTrue($registry->isEnabled('enablement', $this->admin));
        $this->assertFalse($registry->isEnabled('enablement', $this->psm));
    }

    // ─── ActivationService evaluate ────────────────────────────

    public function test_evaluate_marks_pivot_complete_when_all_steps_done(): void
    {
        Feature::for(null)->activate('enablement');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement',
            'is_active' => true,
        ]);

        $step = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'criteria' => ['target' => 1],
        ]);

        $path->users()->attach($this->admin->id, ['assigned_by' => $this->admin->id]);

        // Create completed progress
        StepProgress::create([
            'activation_step_id' => $step->id,
            'user_id' => $this->admin->id,
            'current_value' => 1,
            'target_value' => 1,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $service = app(ActivationService::class);
        $result = $service->evaluate($path, $this->admin);

        $this->assertTrue($result);
        $this->assertNotNull(
            $path->users()->where('user_id', $this->admin->id)->first()->pivot->completed_at
        );
    }

    public function test_evaluate_returns_false_when_step_incomplete(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);

        $step = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'criteria' => ['target' => 10],
        ]);

        $path->users()->attach($this->admin->id, ['assigned_by' => $this->admin->id]);

        StepProgress::create([
            'activation_step_id' => $step->id,
            'user_id' => $this->admin->id,
            'current_value' => 5,
            'target_value' => 10,
            'completed' => false,
        ]);

        $service = app(ActivationService::class);

        $this->assertFalse($service->evaluate($path, $this->admin));
    }

    // ─── ActivationService recordProgress ──────────────────────

    public function test_record_progress_creates_step_progress_for_user(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);
        $step = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'criteria' => ['target' => 1],
        ]);

        $path->users()->attach($this->admin->id, ['assigned_by' => $this->admin->id]);

        $service = app(ActivationService::class);
        $service->recordProgress($step, 1, $this->admin);

        $this->assertDatabaseHas('step_progress', [
            'activation_step_id' => $step->id,
            'user_id' => $this->admin->id,
            'completed' => true,
        ]);
    }

    public function test_record_progress_auto_evaluates_path(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);
        $step = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'criteria' => ['target' => 1],
        ]);

        $path->users()->attach($this->admin->id, ['assigned_by' => $this->admin->id]);

        $service = app(ActivationService::class);
        $service->recordProgress($step, 1, $this->admin);

        // Should auto-evaluate and mark the pivot completed
        $pivot = $path->users()->where('user_id', $this->admin->id)->first()->pivot;
        $this->assertNotNull($pivot->completed_at);
    }

    // ─── Config disabled: block non-admin, allow admin ─────────

    public function test_config_disabled_blocks_non_admin_even_with_pennant_and_path(): void
    {
        app(ModuleRegistry::class)->setModuleEnabled('enablement', false);
        Feature::for(null)->activate('enablement');

        $this->activateModuleForUser('enablement', $this->psm);

        $registry = app(ModuleRegistry::class);

        $this->assertFalse($registry->isEnabled('enablement', $this->psm));
    }

    public function test_admin_bypasses_config_disabled(): void
    {
        app(ModuleRegistry::class)->setModuleEnabled('enablement', false);

        $registry = app(ModuleRegistry::class);

        $this->assertTrue($registry->isEnabled('enablement', $this->admin));
    }
}
