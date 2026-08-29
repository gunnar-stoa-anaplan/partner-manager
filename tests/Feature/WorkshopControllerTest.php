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

class WorkshopControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $coe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        Feature::for(null)->activate('workshops');

        $this->coe = User::factory()->coe()->create();
    }

    public function test_internal_user_can_create_a_workshop(): void
    {
        $response = $this->actingAs($this->coe)->post(route('workshops.store'), [
            'slug' => 'anaplan-way-ifp',
            'name' => 'Anaplan Way for IFP',
            'short_description' => 'Delivery workshop',
            'status' => 'active',
        ]);

        $workshop = Workshop::firstWhere('slug', 'anaplan-way-ifp');
        $response->assertRedirect(route('workshops.show', $workshop));
        $this->assertDatabaseHas('workshops', [
            'slug' => 'anaplan-way-ifp',
            'created_by' => $this->coe->id,
        ]);
    }

    public function test_internal_user_can_schedule_a_session(): void
    {
        $workshop = Workshop::factory()->create();

        $response = $this->actingAs($this->coe)->post(route('workshops.sessions.store', $workshop), [
            'slug' => 'q3-2026-cohort',
            'name' => 'Q3 2026 Cohort',
            'start_date' => now()->addWeek()->format('Y-m-d'),
            'end_date' => now()->addWeek()->addDays(4)->format('Y-m-d'),
            'status' => 'upcoming',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('workshop_sessions', [
            'workshop_id' => $workshop->id,
            'slug' => 'q3-2026-cohort',
        ]);
    }

    public function test_inviting_by_email_only_matches_existing_contacts(): void
    {
        $session = WorkshopSession::factory()->create();
        $partner = Partner::factory()->create();
        $contact = Contact::factory()->create(['partner_id' => $partner->id]);

        $response = $this->actingAs($this->coe)->post(
            route('workshops.sessions.participants.invite', [$session->workshop, $session]),
            ['emails' => "{$contact->email}\nunknown@nowhere.com"],
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('workshop_session_participants', [
            'workshop_session_id' => $session->id,
            'contact_id' => $contact->id,
            'status' => 'invited',
            'enrolled_by_user_id' => $this->coe->id,
        ]);
        $this->assertDatabaseCount('workshop_session_participants', 1);
    }

    public function test_reinviting_an_already_enrolled_contact_does_not_duplicate(): void
    {
        $session = WorkshopSession::factory()->create();
        $contact = Contact::factory()->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($this->coe)->post(
            route('workshops.sessions.participants.invite', [$session->workshop, $session]),
            ['emails' => $contact->email],
        );

        $this->assertDatabaseCount('workshop_session_participants', 1);
    }
}
