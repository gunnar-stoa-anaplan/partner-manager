<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Partner;
use App\Models\PartnerAssignedGoal;
use App\Models\PartnerHealthSnapshot;
use App\Models\PartnerScorecard;
use App\Models\User;
use App\Services\BenchmarkService;
use App\Services\SignalAggregateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The snapshot/benchmark tables carry ScopedToActor row-level visibility, but a
 * handful of reads are *definitionally* population-wide (portfolio distributions,
 * cohort averages, benchmark percentiles). Those reads bypass the scope with
 * ->withoutGlobalScope('actor_visibility') so the statistic is computed over the
 * full population regardless of who is acting — while ad-hoc reads of the same
 * models stay fail-closed. This test pins both halves of that contract.
 *
 * See docs/authorization-coverage.md §10.
 */
class BenchmarkPopulationReadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A restricted PSM sees only their own partner's snapshot through the scope,
     * yet the portfolio aggregate still spans every partner.
     */
    public function test_portfolio_aggregate_spans_full_population_for_restricted_actor(): void
    {
        AppSetting::set('signal_sharing_enabled', '1');

        $psm = User::factory()->psm()->create();
        $mine = Partner::factory()->create(['user_id' => $psm->id]);
        $others = Partner::factory()->count(4)->create();

        $day = now()->startOfDay();
        foreach ([$mine, ...$others] as $i => $partner) {
            PartnerHealthSnapshot::factory()->create([
                'partner_id' => $partner->id,
                'risk_level' => 'healthy',
                'score' => 80,
                'snapshot_date' => $day->copy()->subDays($i),
            ]);
        }

        $this->actingAs($psm);

        // Fail-closed: the scoped model hides the four out-of-scope partners.
        $this->assertSame(1, PartnerHealthSnapshot::count());

        // Population-wide: the portfolio distribution counts all five partners.
        $aggregate = app(SignalAggregateService::class)->buildAggregate();

        $this->assertNotNull($aggregate);
        $distribution = $aggregate['health_distribution'];
        $this->assertSame(5, array_sum($distribution));
        $this->assertSame(5, $distribution['healthy']);
    }

    /**
     * The benchmark helper is asked for exactly the partner ids it is given, and
     * must return scorecard/goal-derived data for all of them even when the actor
     * could not otherwise see those rows.
     */
    public function test_benchmark_service_reads_across_supplied_population(): void
    {
        $psm = User::factory()->psm()->create();
        $mine = Partner::factory()->create(['user_id' => $psm->id]);
        $others = Partner::factory()->count(3)->create();
        $all = collect([$mine, ...$others]);

        foreach ($all as $partner) {
            PartnerScorecard::factory()->create([
                'partner_id' => $partner->id,
                'delivery_score' => 60,
                'engagement_score' => 55,
            ]);
            PartnerAssignedGoal::factory()->create([
                'partner_id' => $partner->id,
                'target_value' => 100,
                'actual_value' => 50,
            ]);
        }

        $this->actingAs($psm);

        // Feed the full population explicitly (as an admin/portfolio view would).
        $benchmarks = app(BenchmarkService::class)->computeAll($all->pluck('id'));

        $this->assertCount(4, $benchmarks, 'benchmark must cover every supplied partner, not just the actor-visible one');
        foreach ($all as $partner) {
            $this->assertArrayHasKey($partner->id, $benchmarks);
        }
    }
}
