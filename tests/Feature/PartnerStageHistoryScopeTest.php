<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\PartnerStageHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Increment 2: PartnerStageHistory is auto-scoped by partner visibility
 * (docs/authorization-spec.md, coverage inventory). An out-of-scope actor must
 * get zero rows; a privileged actor sees all.
 */
class PartnerStageHistoryScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scoped_to_visible_partners_for_a_plain_role(): void
    {
        $psm = User::factory()->psm()->create();
        $mine = Partner::factory()->create(['user_id' => $psm->id]);
        $other = Partner::factory()->create();

        PartnerStageHistory::factory()->create(['partner_id' => $mine->id]);
        PartnerStageHistory::factory()->create(['partner_id' => $other->id]);

        $this->actingAs($psm);
        $rows = PartnerStageHistory::all();

        $this->assertCount(1, $rows, 'PSM should see history only for partners in scope');
        $this->assertTrue($rows->every(fn ($r) => $r->partner_id === $mine->id));
    }

    public function test_out_of_scope_actor_sees_nothing(): void
    {
        $psm = User::factory()->psm()->create(); // owns no partners
        $other = Partner::factory()->create();
        PartnerStageHistory::factory()->create(['partner_id' => $other->id]);

        $this->actingAs($psm);

        $this->assertCount(0, PartnerStageHistory::all());
        $this->assertSame(0, PartnerStageHistory::count());
    }

    public function test_unrestricted_actor_sees_all(): void
    {
        $admin = User::factory()->superAdmin()->create();
        PartnerStageHistory::factory()->count(3)->create();

        $this->actingAs($admin);

        $this->assertCount(3, PartnerStageHistory::all());
    }
}
