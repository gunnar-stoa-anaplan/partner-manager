<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\PsmObjective;
use App\Models\User;
use App\Models\WeeklyFocus;
use App\Models\WeeklyFocusItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class PrioritiesFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    private User $manager;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        Feature::for(null)->activate('partner_management');

        $this->admin = User::factory()->admin()->create();
        $this->manager = User::factory()->manager()->create();
        $this->psm = User::factory()->psm()->create(['manager_id' => $this->manager->id]);
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);
        $this->psm->psmPartners()->attach($this->partner->id, ['role' => 'psm']);
    }

    // ─── Portfolio Priorities ────────────────────────────────────

    public function test_psm_can_view_portfolio_priorities(): void
    {
        $response = $this->actingAs($this->psm)->get(route('priorities.index'));

        $response->assertOk();
        $response->assertSee($this->partner->name);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('priorities.index'));

        $response->assertRedirect(route('login'));
    }

    // ─── Weekly Focus ────────────────────────────────────────────

    public function test_psm_can_view_weekly_focus(): void
    {
        $response = $this->actingAs($this->psm)->get(route('priorities.weekly'));

        $response->assertOk();
        $response->assertSee('Weekly Focus');
    }

    public function test_weekly_focus_creates_focus_for_current_week(): void
    {
        $this->actingAs($this->psm)->get(route('priorities.weekly'));

        $this->assertDatabaseHas('weekly_focuses', [
            'user_id' => $this->psm->id,
            'week_start' => WeeklyFocus::currentWeekStart()->toDateString(),
        ]);
    }

    // ─── Objectives ──────────────────────────────────────────────

    public function test_psm_can_create_metric_tracked_objective(): void
    {
        $response = $this->actingAs($this->psm)->post(route('priorities.objectives.store'), [
            'partner_id' => $this->partner->id,
            'title' => 'Test Objective',
            'category' => 'enablement',
            'metric_type' => 'manual',
            'target_value' => 10,
            'target_date' => now()->addWeeks(4)->toDateString(),
        ]);

        $response->assertRedirect(route('priorities.weekly'));
        $this->assertDatabaseHas('psm_objectives', [
            'user_id' => $this->psm->id,
            'partner_id' => $this->partner->id,
            'title' => 'Test Objective',
            'status' => 'active',
        ]);
    }

    public function test_psm_can_update_objective_status(): void
    {
        $objective = PsmObjective::factory()->create([
            'user_id' => $this->psm->id,
            'partner_id' => $this->partner->id,
        ]);

        $response = $this->actingAs($this->psm)->patch(route('priorities.objectives.update', $objective), [
            'status' => 'cancelled',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('psm_objectives', [
            'id' => $objective->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_objective_validation_requires_future_date(): void
    {
        $response = $this->actingAs($this->psm)->post(route('priorities.objectives.store'), [
            'partner_id' => $this->partner->id,
            'title' => 'Test',
            'category' => 'enablement',
            'metric_type' => 'manual',
            'target_value' => 10,
            'target_date' => now()->subDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors('target_date');
    }

    // ─── Action Items ────────────────────────────────────────────

    public function test_psm_can_create_action_item(): void
    {
        $this->actingAs($this->psm)->get(route('priorities.weekly'));

        $response = $this->actingAs($this->psm)->post(route('priorities.items.store'), [
            'partner_id' => $this->partner->id,
            'description' => 'Schedule QBR with partner',
        ]);

        $response->assertRedirect(route('priorities.weekly'));
        $this->assertDatabaseHas('weekly_focus_items', [
            'description' => 'Schedule QBR with partner',
            'status' => 'planned',
        ]);
    }

    public function test_psm_can_update_action_item_status(): void
    {
        $focus = WeeklyFocus::factory()->create(['user_id' => $this->psm->id]);
        $item = WeeklyFocusItem::factory()->create([
            'weekly_focus_id' => $focus->id,
            'partner_id' => $this->partner->id,
        ]);

        $response = $this->actingAs($this->psm)->patch(route('priorities.items.update', $item), [
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('weekly_focus_items', [
            'id' => $item->id,
            'status' => 'completed',
        ]);
    }

    public function test_psm_can_delete_action_item(): void
    {
        $focus = WeeklyFocus::factory()->create(['user_id' => $this->psm->id]);
        $item = WeeklyFocusItem::factory()->create([
            'weekly_focus_id' => $focus->id,
            'partner_id' => $this->partner->id,
        ]);

        $response = $this->actingAs($this->psm)->delete(route('priorities.items.destroy', $item));

        $response->assertRedirect();
        $this->assertDatabaseMissing('weekly_focus_items', ['id' => $item->id]);
    }

    // ─── Carry Over ──────────────────────────────────────────────

    public function test_psm_can_carry_over_incomplete_action_items(): void
    {
        $lastWeek = WeeklyFocus::currentWeekStart()->subWeek();
        $lastFocus = WeeklyFocus::factory()->forWeek($lastWeek)->create(['user_id' => $this->psm->id]);
        WeeklyFocusItem::factory()->create([
            'weekly_focus_id' => $lastFocus->id,
            'partner_id' => $this->partner->id,
            'description' => 'Carried item',
            'status' => 'planned',
        ]);
        WeeklyFocusItem::factory()->completed()->create([
            'weekly_focus_id' => $lastFocus->id,
            'partner_id' => $this->partner->id,
            'description' => 'Done item',
        ]);

        $response = $this->actingAs($this->psm)->post(route('priorities.carry-over'));

        $response->assertRedirect(route('priorities.weekly'));

        $currentFocus = WeeklyFocus::forUser($this->psm->id)
            ->forWeek(WeeklyFocus::currentWeekStart())
            ->first();

        $this->assertNotNull($currentFocus);
        $this->assertEquals(1, $currentFocus->items()->count());
        $this->assertEquals('Carried item', $currentFocus->items->first()->description);
    }

    // ─── Team View ───────────────────────────────────────────────

    public function test_psm_cannot_access_team_view_without_reports(): void
    {
        $response = $this->actingAs($this->psm)->get(route('priorities.team'));

        $response->assertForbidden();
    }

    public function test_manager_can_view_team_overview(): void
    {
        $response = $this->actingAs($this->manager)->get(route('priorities.team'));

        $response->assertOk();
        $response->assertSee($this->psm->name);
    }

    public function test_manager_can_drill_down_to_direct_report(): void
    {
        $response = $this->actingAs($this->manager)->get(route('priorities.team.member', $this->psm));

        $response->assertOk();
        $response->assertSee($this->psm->name);
    }

    public function test_manager_cannot_view_non_report_psm(): void
    {
        $otherPsm = User::factory()->psm()->create();

        $response = $this->actingAs($this->manager)->get(route('priorities.team.member', $otherPsm));

        $response->assertForbidden();
    }

    // ─── Manager Review ──────────────────────────────────────────

    public function test_manager_can_review_weekly_focus(): void
    {
        $focus = WeeklyFocus::factory()->create(['user_id' => $this->psm->id]);

        $response = $this->actingAs($this->manager)->post(route('priorities.review', $focus), [
            'manager_notes' => 'Great progress this week!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('weekly_focuses', [
            'id' => $focus->id,
            'manager_notes' => 'Great progress this week!',
            'reviewed_by' => $this->manager->id,
        ]);
        $this->assertNotNull($focus->fresh()->reviewed_at);
    }

    // ─── Manager Suggest ─────────────────────────────────────────

    public function test_manager_can_suggest_objective_for_psm(): void
    {
        $response = $this->actingAs($this->manager)->post(route('priorities.suggest', $this->psm), [
            'partner_id' => $this->partner->id,
            'title' => 'Suggested Objective',
            'category' => 'pipeline',
            'metric_type' => 'manual',
            'target_value' => 5,
            'target_date' => now()->addWeeks(3)->toDateString(),
        ]);

        $response->assertRedirect(route('priorities.team.member', $this->psm));
        $this->assertDatabaseHas('psm_objectives', [
            'user_id' => $this->psm->id,
            'title' => 'Suggested Objective',
            'suggested_by' => $this->manager->id,
        ]);
    }

    public function test_manager_cannot_suggest_for_non_report(): void
    {
        $otherPsm = User::factory()->psm()->create();

        $response = $this->actingAs($this->manager)->post(route('priorities.suggest', $otherPsm), [
            'partner_id' => $this->partner->id,
            'title' => 'Blocked Suggestion',
            'category' => 'pipeline',
            'metric_type' => 'manual',
            'target_value' => 5,
            'target_date' => now()->addWeeks(3)->toDateString(),
        ]);

        $response->assertForbidden();
    }

    // ─── Authorization ───────────────────────────────────────────

    public function test_items_scoped_to_user(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherFocus = WeeklyFocus::factory()->create(['user_id' => $otherPsm->id]);
        $otherItem = WeeklyFocusItem::factory()->create([
            'weekly_focus_id' => $otherFocus->id,
            'partner_id' => $this->partner->id,
        ]);

        $response = $this->actingAs($this->psm)->patch(route('priorities.items.update', $otherItem), [
            'status' => 'completed',
        ]);

        $response->assertForbidden();
    }

    public function test_objectives_scoped_to_user(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherObjective = PsmObjective::factory()->create([
            'user_id' => $otherPsm->id,
            'partner_id' => $this->partner->id,
        ]);

        $response = $this->actingAs($this->psm)->patch(route('priorities.objectives.update', $otherObjective), [
            'status' => 'cancelled',
        ]);

        $response->assertForbidden();
    }

    // ─── Timeline ────────────────────────────────────────────────

    public function test_psm_can_view_timeline(): void
    {
        $response = $this->actingAs($this->psm)->get(route('priorities.timeline'));

        $response->assertOk();
        $response->assertSee('Progress Timeline');
    }
}
