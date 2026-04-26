<?php

namespace Tests\Feature;

use App\Models\Journey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JourneyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    private User $anotherPsm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->psm = User::factory()->psm()->create();
        $this->anotherPsm = User::factory()->psm()->create();
    }

    // ─── Index — PSM scoping ─────────────────────────────────────────────────

    /**
     * PSM can see journeys assigned to them.
     */
    public function test_psm_can_see_their_assigned_journeys(): void
    {
        $assigned = Journey::factory()->create(['name' => 'Assigned Journey']);
        $unassigned = Journey::factory()->create(['name' => 'Unassigned Journey']);

        $assigned->assignments()->create(['user_id' => $this->psm->id]);

        $response = $this->actingAs($this->psm)->get(route('journeys.index'));

        $response->assertOk();
        $response->assertViewIs('journeys.index');
        $response->assertViewHas('journeys', fn ($journeys) => $journeys->contains('id', $assigned->id));
        $response->assertViewHas('journeys', fn ($journeys) => ! $journeys->contains('id', $unassigned->id));
    }

    /**
     * PSM does not see journeys assigned only to another PSM.
     */
    public function test_psm_cannot_see_journeys_assigned_to_other_psms(): void
    {
        $otherJourney = Journey::factory()->create(['name' => 'Other PSM Journey']);
        $otherJourney->assignments()->create(['user_id' => $this->anotherPsm->id]);

        $response = $this->actingAs($this->psm)->get(route('journeys.index'));

        $response->assertOk();
        $response->assertViewHas('journeys', fn ($journeys) => ! $journeys->contains('id', $otherJourney->id));
    }

    /**
     * Admin can see all journeys regardless of assignment.
     */
    public function test_admin_can_see_all_journeys(): void
    {
        $journeyA = Journey::factory()->create(['name' => 'Journey A']);
        $journeyB = Journey::factory()->create(['name' => 'Journey B']);

        // Only assign A to a PSM — admin should still see both
        $journeyA->assignments()->create(['user_id' => $this->psm->id]);

        $response = $this->actingAs($this->admin)->get(route('journeys.index'));

        $response->assertOk();
        $response->assertViewIs('journeys.index');
        $response->assertViewHas('journeys', fn ($journeys) => $journeys->contains('id', $journeyA->id));
        $response->assertViewHas('journeys', fn ($journeys) => $journeys->contains('id', $journeyB->id));
    }

    // ─── Show — access control ────────────────────────────────────────────────

    /**
     * PSM can view the detail page for an assigned journey.
     */
    public function test_psm_can_view_assigned_journey_detail(): void
    {
        $journey = Journey::factory()->create();
        $journey->assignments()->create(['user_id' => $this->psm->id]);

        $response = $this->actingAs($this->psm)->get(route('journeys.show', $journey));

        $response->assertOk();
        $response->assertViewIs('journeys.show');
        $response->assertViewHas('journey', fn ($j) => $j->id === $journey->id);
    }

    /**
     * PSM receives 403 when attempting to view a journey they are not assigned to.
     */
    public function test_psm_receives_403_for_unassigned_journey_detail(): void
    {
        $journey = Journey::factory()->create();
        // Assign to another PSM, not $this->psm
        $journey->assignments()->create(['user_id' => $this->anotherPsm->id]);

        $response = $this->actingAs($this->psm)->get(route('journeys.show', $journey));

        $response->assertForbidden();
    }

    /**
     * Admin can view any journey's detail page.
     */
    public function test_admin_can_view_any_journey_detail(): void
    {
        $journey = Journey::factory()->create();
        // No assignment — admin should still have access

        $response = $this->actingAs($this->admin)->get(route('journeys.show', $journey));

        $response->assertOk();
        $response->assertViewIs('journeys.show');
    }

    // ─── Sub-pages — access mirrors show ─────────────────────────────────────

    /**
     * PSM can access participants page for an assigned journey.
     */
    public function test_psm_can_access_participants_for_assigned_journey(): void
    {
        $journey = Journey::factory()->create();
        $journey->assignments()->create(['user_id' => $this->psm->id]);

        $response = $this->actingAs($this->psm)->get(route('journeys.participants', $journey));

        $response->assertOk();
        $response->assertViewIs('journeys.participants');
    }

    /**
     * PSM receives 403 on participants page for an unassigned journey.
     */
    public function test_psm_receives_403_for_unassigned_journey_participants(): void
    {
        $journey = Journey::factory()->create();

        $response = $this->actingAs($this->psm)->get(route('journeys.participants', $journey));

        $response->assertForbidden();
    }

    /**
     * PSM can access pending actions for an assigned journey.
     */
    public function test_psm_can_access_pending_actions_for_assigned_journey(): void
    {
        $journey = Journey::factory()->create();
        $journey->assignments()->create(['user_id' => $this->psm->id]);

        $response = $this->actingAs($this->psm)->get(route('journeys.pending', $journey));

        $response->assertOk();
        $response->assertViewIs('journeys.pending');
    }

    /**
     * PSM can access activity log for an assigned journey.
     */
    public function test_psm_can_access_activity_log_for_assigned_journey(): void
    {
        $journey = Journey::factory()->create();
        $journey->assignments()->create(['user_id' => $this->psm->id]);

        $response = $this->actingAs($this->psm)->get(route('journeys.activity', $journey));

        $response->assertOk();
        $response->assertViewIs('journeys.activity');
    }

    // ─── Unauthenticated ─────────────────────────────────────────────────────

    /**
     * Unauthenticated users are redirected to login.
     */
    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get(route('journeys.index'));

        $response->assertRedirect(route('login'));
    }
}
