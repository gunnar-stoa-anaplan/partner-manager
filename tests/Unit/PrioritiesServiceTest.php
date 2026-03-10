<?php

namespace Tests\Unit;

use App\Models\Partner;
use App\Models\PartnerHealthSnapshot;
use App\Models\PsmObjective;
use App\Models\User;
use App\Models\WeeklyFocus;
use App\Models\WeeklyFocusItem;
use App\Services\PrioritiesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrioritiesServiceTest extends TestCase
{
    use RefreshDatabase;

    private PrioritiesService $service;

    private User $psm;

    private User $manager;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->service = app(PrioritiesService::class);
        $this->manager = User::factory()->manager()->create();
        $this->psm = User::factory()->psm()->create(['manager_id' => $this->manager->id]);
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);
        $this->psm->psmPartners()->attach($this->partner->id, ['role' => 'psm']);
    }

    public function test_portfolio_summary_returns_correct_partners(): void
    {
        $summary = $this->service->portfolioSummary($this->psm);

        $this->assertCount(1, $summary);
        $this->assertEquals($this->partner->id, $summary->first()['partner']->id);
    }

    public function test_suggested_objectives_from_health_signals(): void
    {
        PartnerHealthSnapshot::create([
            'partner_id' => $this->partner->id,
            'score' => 25.0,
            'risk_level' => 'critical',
            'signals' => [
                'Low enablement dimension — blueprint completions below threshold',
                'Pipeline declined significantly over last 90 days',
            ],
            'dimensions' => [
                'scorecard' => 30.0,
                'pipeline_trend' => 20.0,
                'goal_attainment' => 40.0,
                'enablement' => 15.0,
                'contact_freshness' => 50.0,
                'delivery_csat' => 60.0,
            ],
            'snapshot_date' => now(),
        ]);

        $suggestions = $this->service->suggestedObjectives($this->psm);

        $this->assertNotEmpty($suggestions['urgent']);
        $this->assertGreaterThanOrEqual(1, count($suggestions['urgent']));
    }

    public function test_team_overview_returns_direct_reports_only(): void
    {
        $otherPsm = User::factory()->psm()->create();

        $overview = $this->service->teamOverview($this->manager);

        $reportIds = $overview->pluck('user.id')->toArray();
        $this->assertContains($this->psm->id, $reportIds);
        $this->assertNotContains($otherPsm->id, $reportIds);
    }

    public function test_carry_over_copies_only_incomplete_action_items(): void
    {
        $lastWeek = WeeklyFocus::currentWeekStart()->subWeek();
        $lastFocus = WeeklyFocus::factory()->forWeek($lastWeek)->create(['user_id' => $this->psm->id]);

        WeeklyFocusItem::factory()->create([
            'weekly_focus_id' => $lastFocus->id,
            'partner_id' => $this->partner->id,
            'description' => 'Incomplete task',
            'status' => 'planned',
        ]);
        WeeklyFocusItem::factory()->completed()->create([
            'weekly_focus_id' => $lastFocus->id,
            'partner_id' => $this->partner->id,
            'description' => 'Done task',
        ]);
        WeeklyFocusItem::factory()->dropped()->create([
            'weekly_focus_id' => $lastFocus->id,
            'partner_id' => $this->partner->id,
            'description' => 'Dropped task',
        ]);

        $count = $this->service->carryOverActionItems($this->psm);

        $this->assertEquals(1, $count);

        $currentFocus = WeeklyFocus::forUser($this->psm->id)
            ->forWeek(WeeklyFocus::currentWeekStart())
            ->first();

        $this->assertNotNull($currentFocus);
        $this->assertEquals(1, $currentFocus->items()->count());
        $this->assertEquals('Incomplete task', $currentFocus->items->first()->description);
    }

    public function test_weekly_view_includes_active_objectives(): void
    {
        PsmObjective::factory()->create([
            'user_id' => $this->psm->id,
            'partner_id' => $this->partner->id,
            'status' => 'active',
            'metric_type' => 'manual',
        ]);
        PsmObjective::factory()->completed()->create([
            'user_id' => $this->psm->id,
            'partner_id' => $this->partner->id,
        ]);

        $view = $this->service->weeklyView($this->psm);

        $this->assertCount(1, $view['objectives']);
        $this->assertInstanceOf(WeeklyFocus::class, $view['focus']);
    }

    public function test_get_or_create_weekly_focus(): void
    {
        $focus = $this->service->getOrCreateWeeklyFocus($this->psm);

        $this->assertInstanceOf(WeeklyFocus::class, $focus);
        $this->assertEquals($this->psm->id, $focus->user_id);
        $this->assertEquals(WeeklyFocus::currentWeekStart()->toDateString(), $focus->week_start->toDateString());

        // Calling again returns the same record
        $focus2 = $this->service->getOrCreateWeeklyFocus($this->psm);
        $this->assertEquals($focus->id, $focus2->id);
    }

    public function test_timeline_returns_correct_weeks(): void
    {
        $timeline = $this->service->timeline($this->psm, 4);

        $this->assertCount(4, $timeline);
        $this->assertArrayHasKey('week_start', $timeline->first());
        $this->assertArrayHasKey('objectives_completed', $timeline->first());
        $this->assertArrayHasKey('items_completed', $timeline->first());
    }
}
