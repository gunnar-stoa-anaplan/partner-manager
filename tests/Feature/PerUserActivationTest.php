<?php

namespace Tests\Feature;

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

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->psm = User::factory()->create(['role' => 'psm']);
    }

    // ─── No-Path Default: allow when no path exists ────────────

    public function test_user_allowed_when_no_activation_path_exists(): void
    {
        Feature::for(null)->activate('enablement_admin');

        $registry = app(ModuleRegistry::class);

        $this->assertTrue($registry->isEnabled('enablement_admin', $this->admin));
    }

    public function test_admin_allowed_when_pennant_active_and_no_path(): void
    {
        Feature::for(null)->activate('enablement_admin');

        $response = $this->actingAs($this->admin)->get(route('blueprints.index'));

        $response->assertOk();
    }

    // ─── Pennant Off: block even without a path ────────────────

    public function test_user_blocked_when_pennant_inactive(): void
    {
        Feature::for(null)->deactivate('enablement_admin');

        $registry = app(ModuleRegistry::class);

        $this->assertFalse($registry->isEnabled('enablement_admin', $this->admin));
    }

    public function test_route_returns_403_when_pennant_inactive(): void
    {
        Feature::for(null)->deactivate('enablement_admin');

        $response = $this->actingAs($this->admin)->get(route('blueprints.index'));

        $response->assertForbidden();
    }

    // ─── With Path: incomplete blocks, complete allows ─────────

    public function test_user_blocked_with_incomplete_activation_path(): void
    {
        Feature::for(null)->activate('enablement_admin');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement_admin',
            'is_active' => true,
        ]);

        $step = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'criteria' => ['target' => 1],
        ]);

        // Assign user but don't complete
        $path->users()->attach($this->admin->id, [
            'assigned_by' => $this->admin->id,
            'assigned_at' => now(),
        ]);

        $registry = app(ModuleRegistry::class);

        $this->assertFalse($registry->isEnabled('enablement_admin', $this->admin));
    }

    public function test_user_allowed_with_completed_activation_path(): void
    {
        $this->activateModuleForUser('enablement_admin', $this->admin);

        $registry = app(ModuleRegistry::class);

        $this->assertTrue($registry->isEnabled('enablement_admin', $this->admin));
    }

    public function test_route_blocked_with_incomplete_path(): void
    {
        Feature::for(null)->activate('enablement_admin');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement_admin',
            'is_active' => true,
        ]);

        ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
        ]);

        $path->users()->attach($this->admin->id, ['assigned_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('blueprints.index'));

        $response->assertForbidden();
    }

    public function test_route_allowed_with_completed_path(): void
    {
        $this->activateModuleForUser('enablement_admin', $this->admin);

        $response = $this->actingAs($this->admin)->get(route('blueprints.index'));

        $response->assertOk();
    }

    // ─── Admin bypass removed ──────────────────────────────────

    public function test_admin_blocked_when_path_exists_and_not_completed(): void
    {
        Feature::for(null)->activate('enablement_admin');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement_admin',
            'is_active' => true,
        ]);

        ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
        ]);

        // Admin is assigned but has not completed
        $path->users()->attach($this->admin->id, ['assigned_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('blueprints.index'));

        $response->assertForbidden();
    }

    // ─── Per-user isolation ────────────────────────────────────

    public function test_different_users_have_independent_access(): void
    {
        Feature::for(null)->activate('enablement_admin');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement_admin',
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

        $registry = app(ModuleRegistry::class);

        $this->assertTrue($registry->isEnabled('enablement_admin', $this->admin));
        $this->assertFalse($registry->isEnabled('enablement_admin', $this->psm));
    }

    // ─── ActivationService evaluate ────────────────────────────

    public function test_evaluate_marks_pivot_complete_when_all_steps_done(): void
    {
        Feature::for(null)->activate('enablement_admin');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement_admin',
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

    // ─── Config disabled overrides everything ──────────────────

    public function test_config_disabled_blocks_even_with_pennant_and_path(): void
    {
        config(['modules.enablement_admin.enabled' => false]);
        Feature::for(null)->activate('enablement_admin');

        $this->activateModuleForUser('enablement_admin', $this->admin);

        $registry = app(ModuleRegistry::class);

        $this->assertFalse($registry->isEnabled('enablement_admin', $this->admin));
    }
}
