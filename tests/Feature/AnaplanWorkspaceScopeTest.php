<?php

namespace Tests\Feature;

use App\Models\AnaplanWorkspace;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Increment 2: AnaplanWorkspace is row-level scoped, but its system-wide
 * provisioning path (linkToPartners) must bypass the scope so it still works
 * regardless of the acting user. See docs/authorization-spec.md.
 */
class AnaplanWorkspaceScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scoped_to_visible_partners(): void
    {
        $psm = User::factory()->psm()->create();
        $mine = Partner::factory()->create(['user_id' => $psm->id]);
        $other = Partner::factory()->create();

        AnaplanWorkspace::factory()->create(['partner_id' => $mine->id]);
        AnaplanWorkspace::factory()->create(['partner_id' => $other->id]);

        $this->actingAs($psm);
        $rows = AnaplanWorkspace::all();

        $this->assertCount(1, $rows);
        $this->assertTrue($rows->every(fn ($w) => $w->partner_id === $mine->id));
    }

    public function test_out_of_scope_actor_sees_nothing(): void
    {
        $psm = User::factory()->psm()->create();
        AnaplanWorkspace::factory()->create(['partner_id' => Partner::factory()->create()->id]);

        $this->actingAs($psm);

        $this->assertSame(0, AnaplanWorkspace::count());
    }

    public function test_unrestricted_actor_sees_all(): void
    {
        $admin = User::factory()->superAdmin()->create();
        AnaplanWorkspace::factory()->count(3)->create();

        $this->actingAs($admin);

        $this->assertCount(3, AnaplanWorkspace::all());
    }

    public function test_link_to_partners_runs_system_wide_under_a_restricted_actor(): void
    {
        // A partner OUT of the acting PSM's scope, and an unassigned workspace
        // whose name maps to it.
        $acme = Partner::factory()->create(['name' => 'Acme']);
        $workspace = AnaplanWorkspace::factory()->unassigned()->create([
            'name' => 'IFP Applications - Acme',
        ]);

        $psm = User::factory()->psm()->create(); // sees none of the above
        $this->actingAs($psm);

        $linked = AnaplanWorkspace::linkToPartners();

        $this->assertSame(1, $linked, 'provisioning must run system-wide despite the restricted actor');
        $this->assertSame(
            $acme->id,
            AnaplanWorkspace::withoutGlobalScope('actor_visibility')->find($workspace->id)->partner_id
        );
    }
}
