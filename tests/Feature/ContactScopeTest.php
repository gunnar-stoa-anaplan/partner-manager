<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contact is the portal auth model, so it uses a LOCAL scope (scopeVisibleToActor)
 * applied at internal read sites rather than the ScopedToActor global scope
 * (which would recurse through the portal guard during authentication). The scope
 * honors owner + assignment + hierarchy, and closes direct-lookup IDOR.
 */
class ContactScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_visible_to_actor_honors_owned_and_assigned_partners(): void
    {
        $psm = User::factory()->psm()->create();
        $owned = Partner::factory()->create(['user_id' => $psm->id]);
        $assigned = Partner::factory()->create();
        \DB::table('user_partner')->insert(['user_id' => $psm->id, 'partner_id' => $assigned->id]);
        $other = Partner::factory()->create();

        $ownedContact = Contact::factory()->create(['partner_id' => $owned->id]);
        $assignedContact = Contact::factory()->create(['partner_id' => $assigned->id]);
        Contact::factory()->create(['partner_id' => $other->id]);

        $this->actingAs($psm);
        $visible = Contact::visibleToActor()->pluck('id')->all();

        sort($visible);
        $expected = [$ownedContact->id, $assignedContact->id];
        sort($expected);
        $this->assertSame($expected, $visible, 'PSM sees owned + assigned partners contacts, not others');
    }

    public function test_out_of_scope_direct_lookup_is_not_found(): void
    {
        $psm = User::factory()->psm()->create();
        $otherContact = Contact::factory()->create(['partner_id' => Partner::factory()->create()->id]);

        $this->actingAs($psm);

        $this->assertNull(Contact::visibleToActor()->find($otherContact->id));
        $this->expectException(ModelNotFoundException::class);
        Contact::visibleToActor()->findOrFail($otherContact->id);
    }

    public function test_unrestricted_actor_sees_all_contacts(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Contact::factory()->count(3)->create();

        $this->actingAs($admin);

        $this->assertCount(3, Contact::visibleToActor()->get());
    }
}
