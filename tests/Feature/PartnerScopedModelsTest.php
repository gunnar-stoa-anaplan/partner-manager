<?php

namespace Tests\Feature;

use App\Models\ActionPlan;
use App\Models\LabParticipant;
use App\Models\Partner;
use App\Models\PartnerMarketplaceEntry;
use App\Models\PartnerSolution;
use App\Models\ResourceStageHistory;
use App\Models\ResourceTask;
use App\Models\User;
use App\Models\WeeklyFocusItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Increment 2: partner-bearing models newly brought under the ScopedToActor
 * row-level scope. Each must hide rows for out-of-scope actors and show all to
 * a privileged actor. See docs/authorization-spec.md and authorization-coverage.md.
 *
 * @param  class-string<Model>  $model
 */
class PartnerScopedModelsTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{class-string<Model>}> */
    public static function scopedModels(): array
    {
        return [
            'ResourceStageHistory' => [ResourceStageHistory::class],
            'PartnerMarketplaceEntry' => [PartnerMarketplaceEntry::class],
            'PartnerSolution' => [PartnerSolution::class],
            'ResourceTask' => [ResourceTask::class],
            'WeeklyFocusItem' => [WeeklyFocusItem::class],
            'ActionPlan' => [ActionPlan::class],
            'PartnerPathEnrollment' => [\App\Models\PartnerPathEnrollment::class],
            'ResourcePathEnrollment' => [\App\Models\ResourcePathEnrollment::class],
            'PartnerBlueprintAssignment' => [\App\Models\PartnerBlueprintAssignment::class],
            'EnablementRegistration' => [\App\Models\EnablementRegistration::class],
            'EnablementGoal' => [\App\Models\EnablementGoal::class],
            'PartnerPlanCommit' => [\App\Models\PartnerPlanCommit::class],
            // Benchmark / snapshot tables. These also feed population-wide
            // aggregates that must bypass the scope — see
            // BenchmarkPopulationReadTest for that guarantee.
            'PartnerScorecard' => [\App\Models\PartnerScorecard::class],
            'PartnerHealthSnapshot' => [\App\Models\PartnerHealthSnapshot::class],
            'OpportunitySnapshot' => [\App\Models\OpportunitySnapshot::class],
            'ResourceSnapshot' => [\App\Models\ResourceSnapshot::class],
            'PartnerAssignedGoal' => [\App\Models\PartnerAssignedGoal::class],
            // LabParticipant is covered by its own method below — its factory
            // chain (Lab -> EnablementEvent) needs an admin user to exist first
            // (pre-existing EnablementEventFactory quirk), which the generic
            // provider setup does not create.
        ];
    }

    /** @dataProvider scopedModels */
    public function test_scoped_to_visible_partners(string $model): void
    {
        $psm = User::factory()->psm()->create();
        $mine = Partner::factory()->create(['user_id' => $psm->id]);
        $other = Partner::factory()->create();

        $model::factory()->create(['partner_id' => $mine->id]);
        $model::factory()->create(['partner_id' => $other->id]);

        $this->actingAs($psm);
        $rows = $model::all();

        $this->assertCount(1, $rows, "$model should be scoped to the actor's partners");
        $this->assertTrue($rows->every(fn ($r) => $r->partner_id === $mine->id));
    }

    /** @dataProvider scopedModels */
    public function test_out_of_scope_actor_sees_nothing(string $model): void
    {
        $psm = User::factory()->psm()->create();
        $other = Partner::factory()->create();
        $model::factory()->create(['partner_id' => $other->id]);

        $this->actingAs($psm);

        $this->assertSame(0, $model::count(), "$model must leak nothing to an out-of-scope actor");
    }

    /** @dataProvider scopedModels */
    public function test_unrestricted_actor_sees_all(string $model): void
    {
        $admin = User::factory()->superAdmin()->create();
        $model::factory()->count(3)->create();

        $this->actingAs($admin);

        $this->assertCount(3, $model::all());
    }

    public function test_lab_participant_is_scoped(): void
    {
        // EnablementEventFactory (reached via LabParticipant -> Lab) resolves
        // created_by from an existing admin user; ensure one exists so the
        // factory chain is valid. (Pre-existing factory quirk, not scoping.)
        $admin = User::factory()->admin()->create();

        $psm = User::factory()->psm()->create();
        $mine = Partner::factory()->create(['user_id' => $psm->id]);
        $other = Partner::factory()->create();

        LabParticipant::factory()->create(['partner_id' => $mine->id]);
        LabParticipant::factory()->create(['partner_id' => $other->id]);

        $this->actingAs($psm);
        $rows = LabParticipant::all();
        $this->assertCount(1, $rows, 'LabParticipant must be scoped to the actor partners');
        $this->assertTrue($rows->every(fn ($r) => $r->partner_id === $mine->id));

        $this->actingAs($admin);
        $this->assertCount(2, LabParticipant::all());
    }
}
