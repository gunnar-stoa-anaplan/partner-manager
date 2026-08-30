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

    public function test_internal_user_can_mark_a_participant_complete_and_incomplete(): void
    {
        $session = WorkshopSession::factory()->create();
        $contact = Contact::factory()->create();
        $participant = $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($this->coe)->post(
            route('workshops.sessions.participants.complete', [$session->workshop, $session, $participant]),
        );

        $this->assertDatabaseHas('workshop_session_participants', [
            'id' => $participant->id,
            'completed_by' => $this->coe->id,
        ]);
        $this->assertNotNull($participant->fresh()->completed_at);

        $this->actingAs($this->coe)->post(
            route('workshops.sessions.participants.uncomplete', [$session->workshop, $session, $participant]),
        );

        $this->assertNull($participant->fresh()->completed_at);
    }

    public function test_participants_page_renders_grouped_by_partner_with_tenant_pool_banner(): void
    {
        $workshop = Workshop::factory()->create();
        $session = WorkshopSession::factory()->create(['workshop_id' => $workshop->id]);
        \App\Models\WorkshopCredential::factory()->create(['workshop_id' => $workshop->id, 'status' => 'available']);
        $partner = Partner::factory()->create(['name' => 'Acme Corp']);
        $contact = Contact::factory()->create(['partner_id' => $partner->id]);
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $response = $this->actingAs($this->coe)->get(route('workshops.sessions.participants.index', [$workshop, $session]));

        $response->assertOk();
        $response->assertSee('Acme Corp');
        $response->assertSee($contact->email);
        $response->assertSee('1 of 1 free');
    }

    public function test_reset_password_generates_a_new_password_for_an_enrolled_contact(): void
    {
        $session = WorkshopSession::factory()->create();
        $contact = Contact::factory()->create(['portal_access' => true, 'password' => 'original']);
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);
        $originalHash = $contact->password;

        $response = $this->actingAs($this->coe)->post(
            route('workshops.sessions.participants.reset-password', [$session->workshop, $session, $contact]),
        );

        $response->assertRedirect(route('workshops.sessions.participants.index', [$session->workshop, $session]));
        $this->assertNotSame($originalHash, $contact->fresh()->password);
    }

    public function test_invite_supports_name_and_bracket_email_format(): void
    {
        $session = WorkshopSession::factory()->create();
        $contact = Contact::factory()->create();

        $this->actingAs($this->coe)->post(
            route('workshops.sessions.participants.invite', [$session->workshop, $session]),
            ['emails' => "Jane Doe <{$contact->email}>"],
        );

        $this->assertDatabaseHas('workshop_session_participants', [
            'workshop_session_id' => $session->id,
            'contact_id' => $contact->id,
        ]);
    }

    public function test_rollup_report_computes_completion_stats(): void
    {
        $workshop = Workshop::factory()->create();
        $session = WorkshopSession::factory()->create(['workshop_id' => $workshop->id]);
        $partner = Partner::factory()->create();
        $completedContact = Contact::factory()->create(['partner_id' => $partner->id]);
        $incompleteContact = Contact::factory()->create(['partner_id' => $partner->id]);

        $session->participants()->create(['contact_id' => $completedContact->id, 'status' => 'active', 'enrolled_at' => now(), 'completed_at' => now()]);
        $session->participants()->create(['contact_id' => $incompleteContact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $response = $this->actingAs($this->coe)->get(route('workshops.rollup', $workshop));

        $response->assertOk();
        $response->assertSee('50%');
    }

    public function test_dashboard_renders(): void
    {
        Workshop::factory()->has(WorkshopSession::factory()->count(2), 'sessions')->create();

        $this->actingAs($this->coe)->get(route('workshops.dashboard'))->assertOk();
    }

    public function test_credentials_index_renders(): void
    {
        \App\Models\WorkshopCredential::factory()->create();

        $this->actingAs($this->coe)->get(route('workshops.credentials.index'))->assertOk();
        $this->actingAs($this->coe)->get(route('workshops.credentials.create'))->assertOk();
    }

    public function test_surveys_index_renders(): void
    {
        $workshop = \App\Models\Workshop::factory()->create();
        \App\Models\WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'pre']);

        $this->actingAs($this->coe)->get(route('workshops.surveys.index', $workshop))->assertOk();
    }

    public function test_calendar_page_renders(): void
    {
        $this->actingAs($this->coe)->get(route('workshops.calendar'))->assertOk();
    }

    public function test_calendar_events_returns_sessions_in_fullcalendar_shape(): void
    {
        $workshop = Workshop::factory()->create(['name' => 'Calendar Test Workshop']);
        $session = WorkshopSession::factory()->create([
            'workshop_id' => $workshop->id,
            'start_date' => '2027-03-10',
            'end_date' => '2027-03-12',
        ]);

        $response = $this->actingAs($this->coe)->getJson(route('workshops.calendar.events', ['start' => '2027-03-01', 'end' => '2027-03-31']));

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => (string) $session->id,
            'title' => 'Calendar Test Workshop — '.$session->name,
            'start' => '2027-03-10',
            'end' => '2027-03-13',
        ]);
    }

    public function test_calendar_events_excludes_sessions_outside_the_requested_range(): void
    {
        $inRange = WorkshopSession::factory()->create(['start_date' => '2027-05-10', 'end_date' => '2027-05-12']);
        $outOfRange = WorkshopSession::factory()->create(['start_date' => '2027-08-01', 'end_date' => '2027-08-03']);

        $response = $this->actingAs($this->coe)->getJson(route('workshops.calendar.events', ['start' => '2027-05-01', 'end' => '2027-05-31']));

        $response->assertOk();
        $response->assertJsonFragment(['id' => (string) $inRange->id]);
        $response->assertJsonMissing(['id' => (string) $outOfRange->id]);
    }
}
