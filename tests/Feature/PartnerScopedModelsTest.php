<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\PartnerMarketplaceEntry;
use App\Models\ResourceStageHistory;
use App\Models\User;
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
}
