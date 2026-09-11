<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeeklyFocus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Increment 3: the second principal axis. User-owned records (no partner anchor)
 * are scoped by ScopedToOwner to the actor's user set — the actor plus their
 * reporting-hierarchy descendants (owner rolls up, DECISION 1). WeeklyFocus is
 * the first model on this mechanism.
 */
class OwnerScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_their_own_records(): void
    {
        $me = User::factory()->psm()->create();
        $other = User::factory()->psm()->create();

        $mine = WeeklyFocus::factory()->create(['user_id' => $me->id]);
        WeeklyFocus::factory()->create(['user_id' => $other->id]);

        $this->actingAs($me);
        $rows = WeeklyFocus::all();

        $this->assertCount(1, $rows);
        $this->assertSame($mine->id, $rows->first()->id);
    }

    public function test_manager_sees_reports_records_via_hierarchy_rollup(): void
    {
        $manager = User::factory()->manager()->create();
        $report = User::factory()->psm()->create(['manager_id' => $manager->id]);
        $outside = User::factory()->psm()->create();

        WeeklyFocus::factory()->create(['user_id' => $report->id]);
        WeeklyFocus::factory()->create(['user_id' => $outside->id]);

        $this->actingAs($manager);
        $rows = WeeklyFocus::all();

        $this->assertCount(1, $rows, 'manager sees their report’s records, not outsiders');
        $this->assertSame($report->id, $rows->first()->user_id);
    }

    public function test_out_of_scope_actor_sees_nothing(): void
    {
        $me = User::factory()->psm()->create();
        $other = User::factory()->psm()->create();
        WeeklyFocus::factory()->create(['user_id' => $other->id]);

        $this->actingAs($me);

        $this->assertSame(0, WeeklyFocus::count());
    }

    public function test_unrestricted_actor_sees_all(): void
    {
        $admin = User::factory()->superAdmin()->create();
        WeeklyFocus::factory()->count(3)->create();

        $this->actingAs($admin);

        $this->assertCount(3, WeeklyFocus::all());
    }
}
