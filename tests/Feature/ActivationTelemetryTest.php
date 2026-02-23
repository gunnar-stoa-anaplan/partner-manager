<?php

namespace Tests\Feature;

use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Models\StepProgress;
use App\Models\User;
use App\Services\ActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class ActivationTelemetryTest extends TestCase
{
    use RefreshDatabase;

    // ─── Snapshot Structure ────────────────────────────────────

    public function test_snapshot_returns_array_of_active_paths(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);
        ActivationStep::factory()->create(['activation_path_id' => $path->id]);

        // Inactive path should not appear
        ActivationPath::factory()->create(['is_active' => false]);

        $service = app(ActivationService::class);
        $snapshot = $service->getSnapshot();

        $this->assertCount(1, $snapshot);
        $this->assertArrayHasKey('feature', $snapshot[0]);
        $this->assertArrayHasKey('instance_active', $snapshot[0]);
        $this->assertArrayHasKey('assigned_count', $snapshot[0]);
        $this->assertArrayHasKey('completed_count', $snapshot[0]);
        $this->assertArrayHasKey('steps', $snapshot[0]);
    }

    public function test_snapshot_reports_correct_assigned_and_completed_counts(): void
    {
        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement_admin',
            'is_active' => true,
        ]);

        $step = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
        ]);

        $users = User::factory()->count(3)->create();

        // Assign all 3
        foreach ($users as $user) {
            $path->users()->attach($user->id, ['assigned_by' => $users[0]->id]);
        }

        // Complete 2 of them
        $path->users()->updateExistingPivot($users[0]->id, ['completed_at' => now()]);
        $path->users()->updateExistingPivot($users[1]->id, ['completed_at' => now()]);

        $service = app(ActivationService::class);
        $snapshot = $service->getSnapshot();

        $this->assertEquals('enablement_admin', $snapshot[0]['feature']);
        $this->assertEquals(3, $snapshot[0]['assigned_count']);
        $this->assertEquals(2, $snapshot[0]['completed_count']);
    }

    public function test_snapshot_reports_instance_active_flag(): void
    {
        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement_admin',
            'is_active' => true,
        ]);

        ActivationStep::factory()->create(['activation_path_id' => $path->id]);

        Feature::for(null)->activate('enablement_admin');

        $service = app(ActivationService::class);
        $snapshot = $service->getSnapshot();

        $this->assertTrue($snapshot[0]['instance_active']);
    }

    public function test_snapshot_reports_instance_inactive_flag(): void
    {
        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement_admin',
            'is_active' => true,
        ]);

        ActivationStep::factory()->create(['activation_path_id' => $path->id]);

        Feature::for(null)->deactivate('enablement_admin');

        $service = app(ActivationService::class);
        $snapshot = $service->getSnapshot();

        $this->assertFalse($snapshot[0]['instance_active']);
    }

    // ─── Step-Level Aggregates ─────────────────────────────────

    public function test_snapshot_includes_step_completion_counts(): void
    {
        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement_admin',
            'is_active' => true,
        ]);

        $step = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'name' => 'Watch Intro Video',
        ]);

        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            $path->users()->attach($user->id, ['assigned_by' => $users[0]->id]);
        }

        // 2 of 3 completed the step
        StepProgress::create([
            'activation_step_id' => $step->id,
            'user_id' => $users[0]->id,
            'current_value' => 1,
            'target_value' => 1,
            'completed' => true,
            'completed_at' => now(),
        ]);
        StepProgress::create([
            'activation_step_id' => $step->id,
            'user_id' => $users[1]->id,
            'current_value' => 1,
            'target_value' => 1,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $service = app(ActivationService::class);
        $snapshot = $service->getSnapshot();

        $this->assertCount(1, $snapshot[0]['steps']);
        $this->assertEquals('Watch Intro Video', $snapshot[0]['steps'][0]['name']);
        $this->assertEquals(2, $snapshot[0]['steps'][0]['completed_count']);
        $this->assertEquals(3, $snapshot[0]['steps'][0]['assigned_count']);
    }

    // ─── Empty State ───────────────────────────────────────────

    public function test_snapshot_returns_empty_array_when_no_paths(): void
    {
        $service = app(ActivationService::class);
        $snapshot = $service->getSnapshot();

        $this->assertEquals([], $snapshot);
    }

    public function test_snapshot_handles_path_with_no_assignments(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);
        ActivationStep::factory()->create(['activation_path_id' => $path->id]);

        $service = app(ActivationService::class);
        $snapshot = $service->getSnapshot();

        $this->assertEquals(0, $snapshot[0]['assigned_count']);
        $this->assertEquals(0, $snapshot[0]['completed_count']);
    }
}
