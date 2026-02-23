<?php

namespace Tests\Feature;

use App\Enums\TrainingPhase;
use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Models\StepProgress;
use App\Models\User;
use App\Services\ActivationAggregateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;
use Tests\Traits\WithActivationSetup;

class ActivationAggregateTest extends TestCase
{
    use RefreshDatabase, WithActivationSetup;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // ─── Aggregate Structure ───────────────────────────────────

    public function test_aggregate_returns_required_top_level_keys(): void
    {
        ActivationPath::factory()->create(['is_active' => true]);

        $service = app(ActivationAggregateService::class);
        $aggregate = $service->buildAggregate();

        $this->assertArrayHasKey('paths', $aggregate);
        $this->assertArrayHasKey('summary', $aggregate);
        $this->assertArrayHasKey('collected_at', $aggregate);
    }

    public function test_aggregate_path_contains_enriched_fields(): void
    {
        Feature::for(null)->activate('test_feature');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);

        ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'phase' => TrainingPhase::Tell,
        ]);

        $service = app(ActivationAggregateService::class);
        $aggregate = $service->buildAggregate();

        $pathData = $aggregate['paths'][0];

        $this->assertArrayHasKey('feature', $pathData);
        $this->assertArrayHasKey('completion_rate', $pathData);
        $this->assertArrayHasKey('avg_days_to_complete', $pathData);
        $this->assertArrayHasKey('median_days_to_complete', $pathData);
        $this->assertArrayHasKey('phases', $pathData);
        $this->assertArrayHasKey('velocity', $pathData);
        $this->assertArrayHasKey('steps', $pathData);
        $this->assertEquals('test_feature', $pathData['feature']);
        $this->assertTrue($pathData['instance_active']);
    }

    // ─── Completion Rate ────────────────────────────────────────

    public function test_aggregate_calculates_correct_completion_rate(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);
        ActivationStep::factory()->create(['activation_path_id' => $path->id]);

        $users = User::factory()->count(4)->create();
        foreach ($users as $user) {
            $path->users()->attach($user->id, ['assigned_by' => $this->admin->id, 'assigned_at' => now()]);
        }

        // Complete 1 of 4
        $path->users()->updateExistingPivot($users[0]->id, ['completed_at' => now()]);

        $service = app(ActivationAggregateService::class);
        $aggregate = $service->buildAggregate();

        $this->assertEquals(0.25, $aggregate['paths'][0]['completion_rate']);
    }

    // ─── Timing / Velocity ──────────────────────────────────────

    public function test_aggregate_computes_avg_and_median_days_to_complete(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);
        ActivationStep::factory()->create(['activation_path_id' => $path->id]);

        $users = User::factory()->count(3)->create();

        // Assign and complete with different durations
        $path->users()->attach($users[0]->id, [
            'assigned_by' => $this->admin->id,
            'assigned_at' => now()->subDays(10),
            'completed_at' => now(),
        ]);
        $path->users()->attach($users[1]->id, [
            'assigned_by' => $this->admin->id,
            'assigned_at' => now()->subDays(20),
            'completed_at' => now(),
        ]);
        $path->users()->attach($users[2]->id, [
            'assigned_by' => $this->admin->id,
            'assigned_at' => now()->subDays(30),
            'completed_at' => now(),
        ]);

        $service = app(ActivationAggregateService::class);
        $aggregate = $service->buildAggregate();

        $pathData = $aggregate['paths'][0];

        // avg of 10, 20, 30 = 20
        $this->assertEquals(20.0, $pathData['avg_days_to_complete']);
        // median of 10, 20, 30 = 20
        $this->assertEquals(20.0, $pathData['median_days_to_complete']);
    }

    public function test_aggregate_returns_null_velocity_when_no_completions(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);
        ActivationStep::factory()->create(['activation_path_id' => $path->id]);

        $path->users()->attach($this->admin->id, [
            'assigned_by' => $this->admin->id,
            'assigned_at' => now(),
        ]);

        $service = app(ActivationAggregateService::class);
        $aggregate = $service->buildAggregate();

        $this->assertNull($aggregate['paths'][0]['avg_days_to_complete']);
        $this->assertNull($aggregate['paths'][0]['median_days_to_complete']);
    }

    // ─── Phase Metrics ──────────────────────────────────────────

    public function test_aggregate_includes_phase_breakdown(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);

        $tellStep = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'order' => 1,
            'phase' => TrainingPhase::Tell,
        ]);

        $showStep = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'order' => 2,
            'phase' => TrainingPhase::Show,
        ]);

        $doStep = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'order' => 3,
            'phase' => TrainingPhase::Do,
        ]);

        $user = User::factory()->create();
        $path->users()->attach($user->id, ['assigned_by' => $this->admin->id, 'assigned_at' => now()]);

        StepProgress::factory()->create([
            'activation_step_id' => $tellStep->id,
            'user_id' => $user->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $service = app(ActivationAggregateService::class);
        $aggregate = $service->buildAggregate();

        $phases = $aggregate['paths'][0]['phases'];

        $this->assertArrayHasKey('tell', $phases);
        $this->assertArrayHasKey('show', $phases);
        $this->assertArrayHasKey('do', $phases);
        $this->assertEquals(1, $phases['tell']['total_steps']);
        $this->assertEquals(1, $phases['tell']['completed_users']);
        $this->assertEquals(0, $phases['show']['completed_users']);
    }

    // ─── Recent Velocity ────────────────────────────────────────

    public function test_aggregate_counts_recent_completions(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);
        ActivationStep::factory()->create(['activation_path_id' => $path->id]);

        $users = User::factory()->count(3)->create();

        // 1 completed 3 days ago (within 7d and 30d)
        $path->users()->attach($users[0]->id, [
            'assigned_by' => $this->admin->id,
            'assigned_at' => now()->subDays(10),
            'completed_at' => now()->subDays(3),
        ]);

        // 1 completed 15 days ago (within 30d only)
        $path->users()->attach($users[1]->id, [
            'assigned_by' => $this->admin->id,
            'assigned_at' => now()->subDays(20),
            'completed_at' => now()->subDays(15),
        ]);

        // 1 completed 60 days ago (outside both windows)
        $path->users()->attach($users[2]->id, [
            'assigned_by' => $this->admin->id,
            'assigned_at' => now()->subDays(90),
            'completed_at' => now()->subDays(60),
        ]);

        $service = app(ActivationAggregateService::class);
        $aggregate = $service->buildAggregate();

        $velocity = $aggregate['paths'][0]['velocity'];

        $this->assertEquals(1, $velocity['completions_last_7d']);
        $this->assertEquals(2, $velocity['completions_last_30d']);
    }

    // ─── Summary ────────────────────────────────────────────────

    public function test_summary_includes_required_keys(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);
        ActivationStep::factory()->create(['activation_path_id' => $path->id]);

        $service = app(ActivationAggregateService::class);
        $aggregate = $service->buildAggregate();

        $summary = $aggregate['summary'];

        $this->assertArrayHasKey('total_paths', $summary);
        $this->assertArrayHasKey('active_paths', $summary);
        $this->assertArrayHasKey('overall_completion_rate', $summary);
        $this->assertArrayHasKey('avg_velocity_days', $summary);
        $this->assertArrayHasKey('users_in_training', $summary);
        $this->assertArrayHasKey('features_fully_activated', $summary);
        $this->assertArrayHasKey('avg_phase_days', $summary);
        $this->assertArrayHasKey('bottleneck_steps', $summary);
    }

    public function test_summary_counts_users_in_training(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);
        ActivationStep::factory()->create(['activation_path_id' => $path->id]);

        $users = User::factory()->count(3)->create();
        foreach ($users as $user) {
            $path->users()->attach($user->id, ['assigned_by' => $this->admin->id, 'assigned_at' => now()]);
        }

        // Complete 1 user
        $path->users()->updateExistingPivot($users[0]->id, ['completed_at' => now()]);

        $service = app(ActivationAggregateService::class);
        $aggregate = $service->buildAggregate();

        // 2 users still in training (no completed_at)
        $this->assertEquals(2, $aggregate['summary']['users_in_training']);
    }

    public function test_bottleneck_steps_ordered_by_lowest_completion_rate(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);

        $easyStep = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'order' => 1,
            'name' => 'Easy Step',
            'is_required' => true,
            'phase' => TrainingPhase::Tell,
        ]);

        $hardStep = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'order' => 2,
            'name' => 'Hard Step',
            'is_required' => true,
            'phase' => TrainingPhase::Do,
        ]);

        $users = User::factory()->count(4)->create();
        foreach ($users as $user) {
            $path->users()->attach($user->id, ['assigned_by' => $this->admin->id, 'assigned_at' => now()]);
        }

        // All 4 complete the easy step
        foreach ($users as $user) {
            StepProgress::factory()->create([
                'activation_step_id' => $easyStep->id,
                'user_id' => $user->id,
                'completed' => true,
                'completed_at' => now(),
            ]);
        }

        // Only 1 completes the hard step
        StepProgress::factory()->create([
            'activation_step_id' => $hardStep->id,
            'user_id' => $users[0]->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $service = app(ActivationAggregateService::class);
        $aggregate = $service->buildAggregate();

        $bottlenecks = $aggregate['summary']['bottleneck_steps'];

        $this->assertNotEmpty($bottlenecks);
        $this->assertEquals('Hard Step', $bottlenecks[0]['step']);
        $this->assertEquals(0.25, $bottlenecks[0]['completion_rate']);
    }

    // ─── Empty State ────────────────────────────────────────────

    public function test_aggregate_handles_no_active_paths(): void
    {
        $service = app(ActivationAggregateService::class);
        $aggregate = $service->buildAggregate();

        $this->assertEquals([], $aggregate['paths']);
        $this->assertEquals(0, $aggregate['summary']['total_paths']);
        $this->assertEquals(0, $aggregate['summary']['overall_completion_rate']);
    }

    // ─── API Endpoint ───────────────────────────────────────────

    public function test_internal_activation_aggregate_endpoint_returns_json(): void
    {
        ActivationPath::factory()->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/internal/activation-aggregate');

        $response->assertOk();
        $response->assertJsonStructure([
            'paths',
            'summary',
            'collected_at',
        ]);
    }

    public function test_store_benchmarks_endpoint_stores_data(): void
    {
        $response = $this->postJson('/api/v1/internal/activation-benchmarks', [
            'cohort_size' => 8,
            'metrics' => [
                'overall_completion_rate' => ['p25' => 10, 'median' => 25, 'p75' => 40, 'mean' => 28],
            ],
            'computed_date' => '2026-02-23',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('app_settings', [
            'key' => 'activation_benchmarks',
        ]);
    }

    // ─── Benchmark View ─────────────────────────────────────────

    public function test_benchmark_view_loads_for_admin(): void
    {
        $this->activateModuleForUser('feature_activation_coe', $this->admin);

        $response = $this->actingAs($this->admin)->get(route('admin.activation.benchmarks'));

        $response->assertOk();
        $response->assertViewIs('admin.activation.benchmarks');
    }

    // ─── Step Completion Rate ───────────────────────────────────

    public function test_step_completion_rate_calculated_correctly(): void
    {
        $path = ActivationPath::factory()->create(['is_active' => true]);

        $step = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'phase' => TrainingPhase::Tell,
        ]);

        $users = User::factory()->count(4)->create();
        foreach ($users as $user) {
            $path->users()->attach($user->id, ['assigned_by' => $this->admin->id, 'assigned_at' => now()]);
        }

        // 3 of 4 complete the step
        for ($i = 0; $i < 3; $i++) {
            StepProgress::factory()->create([
                'activation_step_id' => $step->id,
                'user_id' => $users[$i]->id,
                'completed' => true,
                'completed_at' => now(),
            ]);
        }

        $service = app(ActivationAggregateService::class);
        $aggregate = $service->buildAggregate();

        $this->assertEquals(0.75, $aggregate['paths'][0]['steps'][0]['completion_rate']);
    }
}
