<?php

namespace Tests\Feature;

use App\Models\EnablementGoal;
use App\Models\Partner;
use App\Models\ResourceSnapshot;
use App\Models\User;
use App\Services\EnablementGoalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class EnablementGoalsTest extends TestCase
{
    use RefreshDatabase;

    private User $psm;

    private User $otherPsm;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        Feature::for(null)->activate('partner_management');

        $this->psm = User::factory()->psm()->create();
        $this->otherPsm = User::factory()->psm()->create();
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);

        // Seed resource snapshots for actuals computation
        $today = now()->toDateString();
        ResourceSnapshot::create(['partner_id' => $this->partner->id, 'snapshot_date' => $today, 'program' => 'FCR', 'role' => 'Sales', 'total_count' => 10, 'started_count' => 5, 'completed_count' => 5, 'unique_people_count' => 10]);
        ResourceSnapshot::create(['partner_id' => $this->partner->id, 'snapshot_date' => $today, 'program' => 'IFP', 'role' => 'Sales', 'total_count' => 8, 'started_count' => 3, 'completed_count' => 5, 'unique_people_count' => 8]);
        ResourceSnapshot::create(['partner_id' => $this->partner->id, 'snapshot_date' => $today, 'program' => 'FCR', 'role' => 'Pre-Sales', 'total_count' => 5, 'started_count' => 3, 'completed_count' => 2, 'unique_people_count' => 5]);
        ResourceSnapshot::create(['partner_id' => $this->partner->id, 'snapshot_date' => $today, 'program' => 'I&D', 'role' => 'Delivery', 'total_count' => 20, 'started_count' => 10, 'completed_count' => 10, 'unique_people_count' => 20]);
        ResourceSnapshot::create(['partner_id' => $this->partner->id, 'snapshot_date' => $today, 'program' => 'IFP', 'role' => 'Delivery', 'total_count' => 15, 'started_count' => 8, 'completed_count' => 7, 'unique_people_count' => 15]);
        ResourceSnapshot::create(['partner_id' => $this->partner->id, 'snapshot_date' => $today, 'program' => 'FCR', 'role' => 'Delivery', 'total_count' => 12, 'started_count' => 6, 'completed_count' => 6, 'unique_people_count' => 12]);
    }

    // ─── Service Tests ────────────────────────────────────────────

    public function test_actuals_computes_aggregate_and_line_level(): void
    {
        $service = app(EnablementGoalService::class);
        $actuals = $service->actuals($this->partner->id);

        // Aggregate groups: sum completed_count across all programs for the role
        $this->assertEquals(10, $actuals['sales']); // FCR 5 + IFP 5
        $this->assertEquals(2, $actuals['pre_sales']); // FCR 2

        // Delivery-Innovation lines (all null programs → 0)
        $this->assertEquals(0, $actuals['delinno_polaris']);
        $this->assertEquals(0, $actuals['delinno_ado']);
        $this->assertEquals(0, $actuals['delinno_forecaster']);
        $this->assertEquals(0, $actuals['delinno_comodeler']);
        $this->assertEquals(0, $actuals['delinno_agentstudio']);

        // Delivery-Apps lines (completed_count matched by program)
        $this->assertEquals(6, $actuals['delapp_fcr']); // FCR completed: 6
        $this->assertEquals(7, $actuals['delapp_ifp']); // IFP completed: 7
        $this->assertEquals(10, $actuals['delapp_id']); // I&D completed: 10
        $this->assertEquals(0, $actuals['delapp_owp']); // not seeded
    }

    public function test_goals_for_partners_includes_variance(): void
    {
        EnablementGoal::create([
            'partner_id' => $this->partner->id,
            'user_id' => $this->psm->id,
            'category' => 'sales',
            'target' => 25,
            'baseline' => 5,
        ]);

        $service = app(EnablementGoalService::class);
        $result = $service->goalsForPartners(collect([$this->partner->id]), $this->psm->id);

        // Result is partner_id → group_key → lines (aggregate has single entry keyed by group)
        $sales = $result[$this->partner->id]['sales']['sales'];
        $this->assertEquals(25, $sales['target']);
        $this->assertEquals(5, $sales['baseline']);
        $this->assertEquals(10, $sales['actual']); // FCR 5 + IFP 5 completed
        $this->assertEquals(-15, $sales['variance']); // 10 - 25 = -15
    }

    public function test_goals_for_partners_line_level(): void
    {
        EnablementGoal::create([
            'partner_id' => $this->partner->id,
            'user_id' => $this->psm->id,
            'category' => 'delapp_fcr',
            'target' => 20,
            'baseline' => 8,
        ]);

        $service = app(EnablementGoalService::class);
        $result = $service->goalsForPartners(collect([$this->partner->id]), $this->psm->id);

        $delappFcr = $result[$this->partner->id]['delivery_apps']['delapp_fcr'];
        $this->assertEquals(20, $delappFcr['target']);
        $this->assertEquals(8, $delappFcr['baseline']);
        $this->assertEquals(6, $delappFcr['actual']); // completed_count: 6
        $this->assertEquals(-14, $delappFcr['variance']); // 6 - 20 = -14
    }

    // ─── Upsert Endpoint ──────────────────────────────────────────

    public function test_psm_can_create_aggregate_goal(): void
    {
        $response = $this->actingAs($this->psm)->postJson(route('state-of-business.goals.upsert'), [
            'partner_id' => $this->partner->id,
            'category' => 'sales',
            'target' => 30,
            'baseline' => 15,
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['target' => 30, 'baseline' => 15, 'actual' => 10]);

        $this->assertDatabaseHas('enablement_goals', [
            'partner_id' => $this->partner->id,
            'user_id' => $this->psm->id,
            'category' => 'sales',
            'target' => 30,
        ]);
    }

    public function test_psm_can_create_line_level_goal(): void
    {
        $response = $this->actingAs($this->psm)->postJson(route('state-of-business.goals.upsert'), [
            'partner_id' => $this->partner->id,
            'category' => 'delapp_ifp',
            'target' => 25,
            'baseline' => 10,
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['target' => 25, 'baseline' => 10, 'actual' => 7]);
    }

    public function test_psm_can_update_existing_goal(): void
    {
        $goal = EnablementGoal::create([
            'partner_id' => $this->partner->id,
            'user_id' => $this->psm->id,
            'category' => 'pre_sales',
            'target' => 10,
            'baseline' => 3,
        ]);

        $response = $this->actingAs($this->psm)->postJson(route('state-of-business.goals.upsert'), [
            'partner_id' => $this->partner->id,
            'category' => 'pre_sales',
            'target' => 15,
            'baseline' => 3,
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['target' => 15]);

        $this->assertDatabaseCount('enablement_goals', 1);
        $this->assertEquals(15, $goal->fresh()->target);
    }

    public function test_upsert_validates_invalid_category(): void
    {
        $response = $this->actingAs($this->psm)->postJson(route('state-of-business.goals.upsert'), [
            'partner_id' => $this->partner->id,
            'category' => 'nonexistent',
            'target' => 10,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('category');
    }

    public function test_upsert_validates_required_fields(): void
    {
        $response = $this->actingAs($this->psm)->postJson(route('state-of-business.goals.upsert'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['partner_id', 'category', 'target']);
    }

    // ─── Delete Endpoint ──────────────────────────────────────────

    public function test_psm_can_delete_own_goal(): void
    {
        $goal = EnablementGoal::create([
            'partner_id' => $this->partner->id,
            'user_id' => $this->psm->id,
            'category' => 'delapp_ifp',
            'target' => 20,
            'baseline' => 10,
        ]);

        $response = $this->actingAs($this->psm)->deleteJson(route('state-of-business.goals.destroy', $goal));

        $response->assertOk();
        $this->assertDatabaseMissing('enablement_goals', ['id' => $goal->id]);
    }

    public function test_psm_cannot_delete_another_psms_goal(): void
    {
        $goal = EnablementGoal::create([
            'partner_id' => $this->partner->id,
            'user_id' => $this->otherPsm->id,
            'category' => 'delapp_ifp',
            'target' => 20,
            'baseline' => 10,
        ]);

        $response = $this->actingAs($this->psm)->deleteJson(route('state-of-business.goals.destroy', $goal));

        $response->assertForbidden();
        $this->assertDatabaseHas('enablement_goals', ['id' => $goal->id]);
    }
}
