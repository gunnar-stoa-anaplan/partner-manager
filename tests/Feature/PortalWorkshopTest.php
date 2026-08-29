<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class PortalWorkshopTest extends TestCase
{
    use RefreshDatabase;

    private Partner $partnerA;

    private Partner $partnerB;

    private WorkshopSession $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        Feature::for(null)->activate('workshops');
        Feature::for(null)->activate('portal');

        $this->partnerA = Partner::factory()->create();
        $this->partnerB = Partner::factory()->create();

        $workshop = Workshop::factory()->create(['created_by' => User::factory()->coe()->create()->id]);
        $this->session = WorkshopSession::factory()->create(['workshop_id' => $workshop->id]);
    }

    public function test_practice_lead_can_view_and_invite_within_own_partner(): void
    {
        $lead = Contact::factory()->portalRole('practice_lead')->create(['partner_id' => $this->partnerA->id]);
        $this->session->participants()->create(['contact_id' => $lead->id, 'status' => 'active', 'enrolled_at' => now()]);

        $resource = Contact::factory()->create(['partner_id' => $this->partnerA->id]);

        $this->actingAs($lead, 'portal')
            ->get(route('portal.workshops.index'))
            ->assertOk();

        $response = $this->actingAs($lead, 'portal')->post(route('portal.workshops.invite', $this->session), [
            'emails' => $resource->email,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('workshop_session_participants', [
            'workshop_session_id' => $this->session->id,
            'contact_id' => $resource->id,
            'enrolled_by_contact_id' => $lead->id,
        ]);
    }

    public function test_practice_lead_cannot_invite_a_contact_from_another_partner(): void
    {
        $lead = Contact::factory()->portalRole('practice_lead')->create(['partner_id' => $this->partnerA->id]);
        $this->session->participants()->create(['contact_id' => $lead->id, 'status' => 'active', 'enrolled_at' => now()]);

        $otherPartnerContact = Contact::factory()->create(['partner_id' => $this->partnerB->id]);

        $this->actingAs($lead, 'portal')->post(route('portal.workshops.invite', $this->session), [
            'emails' => $otherPartnerContact->email,
        ]);

        $this->assertDatabaseMissing('workshop_session_participants', [
            'contact_id' => $otherPartnerContact->id,
        ]);
    }

    public function test_delivery_resource_cannot_invite_but_can_view_own_sessions(): void
    {
        $resource = Contact::factory()->portalRole('delivery_resource')->create(['partner_id' => $this->partnerA->id]);
        $this->session->participants()->create(['contact_id' => $resource->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($resource, 'portal')
            ->get(route('portal.my-workshops.index'))
            ->assertOk();

        $response = $this->actingAs($resource, 'portal')->post(route('portal.workshops.invite', $this->session), [
            'emails' => 'anyone@partnera.com',
        ]);

        $response->assertForbidden();
    }

    public function test_practice_lead_cannot_view_a_session_their_partner_is_not_part_of(): void
    {
        $lead = Contact::factory()->portalRole('practice_lead')->create(['partner_id' => $this->partnerA->id]);

        $this->actingAs($lead, 'portal')
            ->get(route('portal.workshops.show', $this->session))
            ->assertForbidden();
    }
}
