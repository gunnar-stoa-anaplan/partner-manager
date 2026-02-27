<?php

namespace Tests\Feature;

use App\Models\Blueprint;
use App\Models\Contact;
use App\Models\EnablementEvent;
use App\Models\EnablementRegistration;
use App\Models\EnablementSession;
use App\Models\Partner;
use App\Models\PartnerBlueprintAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class EnablementFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        Feature::for(null)->activate('enablement_admin');
        Feature::for(null)->activate('enablement_user');

        $this->admin = User::factory()->admin()->create();
        $this->psm = User::factory()->psm()->create();
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);
    }

    // ─── Enablement Dashboard ────────────────────────────────────

    public function test_admin_can_view_enablement_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.enablement-dashboard'));

        $response->assertOk();
        $response->assertViewIs('admin.enablement-dashboard');
        $response->assertViewHas([
            'totalBlueprints',
            'totalEvents',
            'totalSessions',
            'totalRegistrations',
            'avgCompletion',
            'blueprintByStatus',
            'completionByCategory',
            'topPartners',
            'sessionsByMonth',
            'registrationsByStatus',
            'eventsByType',
            'recentAssignments',
            'upcomingSessions',
        ]);
    }

    public function test_psm_cannot_view_enablement_dashboard(): void
    {
        $response = $this->actingAs($this->psm)->get(route('admin.enablement-dashboard'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_view_enablement_dashboard(): void
    {
        $response = $this->get(route('admin.enablement-dashboard'));

        $response->assertRedirect();
    }

    public function test_enablement_dashboard_shows_correct_kpi_counts(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        EnablementSession::factory()->count(3)->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.enablement-dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalEvents', 1);
        $response->assertViewHas('totalSessions', 3);
    }

    // ─── Enablement Event CRUD ───────────────────────────────────

    public function test_admin_can_view_event_index(): void
    {
        EnablementEvent::factory()->count(3)->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('enablement-events.index'));

        $response->assertOk();
        $response->assertViewIs('admin.enablement.index');
        $response->assertViewHas('events');
    }

    public function test_admin_can_view_create_event_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('enablement-events.create'));

        $response->assertOk();
        $response->assertViewIs('admin.enablement.create');
    }

    public function test_admin_can_store_enablement_event(): void
    {
        $response = $this->actingAs($this->admin)->post(route('enablement-events.store'), [
            'title' => 'IFP Hands-On Workshop',
            'type' => 'workshop',
            'category' => 'Delivery',
            'product_area' => 'IFP',
            'description' => 'A hands-on workshop for IFP.',
            'duration_hours' => 8,
            'max_participants' => 20,
            'status' => 'published',
        ]);

        $response->assertRedirect(route('enablement-events.index'));
        $this->assertDatabaseHas('enablement_events', [
            'title' => 'IFP Hands-On Workshop',
            'type' => 'workshop',
            'category' => 'Delivery',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_store_event_generates_slug(): void
    {
        $this->actingAs($this->admin)->post(route('enablement-events.store'), [
            'title' => 'Polaris Deep Dive',
            'type' => 'workshop',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('enablement_events', [
            'slug' => 'polaris-deep-dive',
        ]);
    }

    public function test_store_event_generates_unique_slug(): void
    {
        EnablementEvent::factory()->create([
            'title' => 'Polaris Deep Dive',
            'slug' => 'polaris-deep-dive',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)->post(route('enablement-events.store'), [
            'title' => 'Polaris Deep Dive',
            'type' => 'workshop',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('enablement_events', [
            'slug' => 'polaris-deep-dive-1',
        ]);
    }

    public function test_store_event_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('enablement-events.store'), []);

        $response->assertSessionHasErrors(['title', 'type', 'status']);
    }

    public function test_store_event_validates_type(): void
    {
        $response = $this->actingAs($this->admin)->post(route('enablement-events.store'), [
            'title' => 'Test',
            'type' => 'invalid_type',
            'status' => 'published',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_store_event_validates_status(): void
    {
        $response = $this->actingAs($this->admin)->post(route('enablement-events.store'), [
            'title' => 'Test',
            'type' => 'workshop',
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_admin_can_view_event_detail(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        EnablementSession::factory()->count(2)->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('enablement-events.show', $event));

        $response->assertOk();
        $response->assertViewIs('admin.enablement.show');
        $response->assertViewHas('event');
        $response->assertViewHas('sessions');
    }

    public function test_admin_can_view_edit_event_form(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('enablement-events.edit', $event));

        $response->assertOk();
        $response->assertViewIs('admin.enablement.edit');
        $response->assertViewHas('event');
    }

    public function test_admin_can_update_enablement_event(): void
    {
        $event = EnablementEvent::factory()->create([
            'title' => 'Original Title',
            'type' => 'workshop',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->put(route('enablement-events.update', $event), [
            'title' => 'Updated Workshop Title',
            'type' => 'bootcamp',
            'category' => 'Sales',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('enablement-events.show', $event->id));
        $this->assertDatabaseHas('enablement_events', [
            'id' => $event->id,
            'title' => 'Updated Workshop Title',
            'type' => 'bootcamp',
            'category' => 'Sales',
            'status' => 'published',
        ]);
    }

    public function test_admin_can_delete_enablement_event(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete(route('enablement-events.destroy', $event));

        $response->assertRedirect(route('enablement-events.index'));
        $this->assertSoftDeleted('enablement_events', ['id' => $event->id]);
    }

    public function test_psm_cannot_access_event_crud(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);

        $this->actingAs($this->psm)->get(route('enablement-events.index'))->assertForbidden();
        $this->actingAs($this->psm)->get(route('enablement-events.create'))->assertForbidden();
        $this->actingAs($this->psm)->post(route('enablement-events.store'), [
            'title' => 'Test',
            'type' => 'workshop',
            'status' => 'draft',
        ])->assertForbidden();
        $this->actingAs($this->psm)->get(route('enablement-events.edit', $event))->assertForbidden();
        $this->actingAs($this->psm)->put(route('enablement-events.update', $event), [
            'title' => 'Updated',
            'type' => 'workshop',
            'status' => 'draft',
        ])->assertForbidden();
        $this->actingAs($this->psm)->delete(route('enablement-events.destroy', $event))->assertForbidden();
    }

    // ─── Session CRUD ────────────────────────────────────────────

    public function test_admin_can_view_create_session_form(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('enablement-events.sessions.create', $event->id));

        $response->assertOk();
        $response->assertViewIs('admin.enablement.session-form');
    }

    public function test_admin_can_store_session(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->post(route('enablement-events.sessions.store', $event->id), [
            'start_at' => '2026-04-01 09:00:00',
            'end_at' => '2026-04-01 17:00:00',
            'all_day' => false,
            'timezone' => 'UTC',
            'location' => 'Virtual',
            'instructor' => 'Jane Doe',
            'geography' => 'NA',
            'region' => 'US West',
            'status' => 'scheduled',
        ]);

        $response->assertRedirect(route('enablement-events.show', $event->id));
        $this->assertDatabaseHas('enablement_sessions', [
            'enablement_event_id' => $event->id,
            'location' => 'Virtual',
            'instructor' => 'Jane Doe',
            'geography' => 'NA',
            'status' => 'scheduled',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_store_session_validates_start_date(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->post(route('enablement-events.sessions.store', $event->id), [
            'end_at' => '2026-04-01 17:00:00',
            'status' => 'scheduled',
        ]);

        $response->assertSessionHasErrors('start_at');
    }

    public function test_store_session_validates_end_after_start(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->post(route('enablement-events.sessions.store', $event->id), [
            'start_at' => '2026-04-01 17:00:00',
            'end_at' => '2026-04-01 09:00:00',
            'status' => 'scheduled',
        ]);

        $response->assertSessionHasErrors('end_at');
    }

    public function test_admin_can_edit_session(): void
    {
        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('enablement-sessions.edit', $session->id));

        $response->assertOk();
        $response->assertViewIs('admin.enablement.session-form');
    }

    public function test_admin_can_update_session(): void
    {
        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put(route('enablement-sessions.update', $session->id), [
            'start_at' => '2026-05-01 09:00:00',
            'end_at' => '2026-05-01 13:00:00',
            'all_day' => false,
            'location' => 'London, UK',
            'instructor' => 'Updated Instructor',
            'geography' => 'EMEA',
            'status' => 'scheduled',
        ]);

        $response->assertRedirect(route('enablement-events.show', $session->enablement_event_id));
        $this->assertDatabaseHas('enablement_sessions', [
            'id' => $session->id,
            'location' => 'London, UK',
            'instructor' => 'Updated Instructor',
            'geography' => 'EMEA',
        ]);
    }

    public function test_admin_can_delete_session(): void
    {
        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);
        $eventId = $session->enablement_event_id;

        $response = $this->actingAs($this->admin)->delete(route('enablement-sessions.destroy', $session->id));

        $response->assertRedirect(route('enablement-events.show', $eventId));
        $this->assertDatabaseMissing('enablement_sessions', ['id' => $session->id]);
    }

    // ─── Registration Management ─────────────────────────────────

    public function test_admin_can_view_session_registrations(): void
    {
        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        EnablementRegistration::factory()->create([
            'enablement_session_id' => $session->id,
            'contact_id' => $contact->id,
            'partner_id' => $this->partner->id,
            'nominated_by' => $this->psm->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('enablement-sessions.registrations', $session->id));

        $response->assertOk();
        $response->assertViewIs('admin.enablement.registrations');
        $response->assertViewHas('session');
    }

    public function test_admin_can_update_registration_to_registered(): void
    {
        $registration = $this->createNomination();

        $response = $this->actingAs($this->admin)->put(route('enablement-registrations.update', $registration->id), [
            'status' => 'registered',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('enablement_registrations', [
            'id' => $registration->id,
            'status' => 'registered',
        ]);
        $registration->refresh();
        $this->assertNotNull($registration->registered_at);
    }

    public function test_admin_can_update_registration_to_confirmed(): void
    {
        $registration = $this->createNomination();

        $this->actingAs($this->admin)->put(route('enablement-registrations.update', $registration->id), [
            'status' => 'confirmed',
        ]);

        $registration->refresh();
        $this->assertEquals('confirmed', $registration->status);
        $this->assertNotNull($registration->confirmed_at);
    }

    public function test_admin_can_update_registration_to_attended(): void
    {
        $registration = $this->createNomination();

        $this->actingAs($this->admin)->put(route('enablement-registrations.update', $registration->id), [
            'status' => 'attended',
        ]);

        $registration->refresh();
        $this->assertEquals('attended', $registration->status);
        $this->assertNotNull($registration->attended_at);
    }

    public function test_admin_can_update_registration_to_no_show(): void
    {
        $registration = $this->createNomination();

        $this->actingAs($this->admin)->put(route('enablement-registrations.update', $registration->id), [
            'status' => 'no_show',
        ]);

        $registration->refresh();
        $this->assertEquals('no_show', $registration->status);
    }

    public function test_admin_can_cancel_registration_with_reason(): void
    {
        $registration = $this->createNomination();

        $this->actingAs($this->admin)->put(route('enablement-registrations.update', $registration->id), [
            'status' => 'cancelled',
            'notes' => 'Scheduling conflict',
        ]);

        $registration->refresh();
        $this->assertEquals('cancelled', $registration->status);
        $this->assertEquals('Scheduling conflict', $registration->cancellation_reason);
    }

    public function test_update_registration_validates_status(): void
    {
        $registration = $this->createNomination();

        $response = $this->actingAs($this->admin)->put(route('enablement-registrations.update', $registration->id), [
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors('status');
    }

    // ─── Admin Calendar View ─────────────────────────────────────

    public function test_admin_can_view_admin_calendar(): void
    {
        $response = $this->actingAs($this->admin)->get(route('enablement-calendar'));

        $response->assertOk();
        $response->assertViewIs('admin.enablement.calendar');
    }

    // ─── API: Calendar Sessions Feed ─────────────────────────────

    public function test_admin_can_fetch_all_calendar_sessions(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        EnablementSession::factory()->count(3)->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHours(4),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('api.enablement.sessions', [
            'start' => now()->toDateString(),
            'end' => now()->addMonth()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertJsonCount(3);
        $response->assertJsonStructure([['id', 'title', 'start', 'end', 'extendedProps']]);
    }

    public function test_admin_sees_draft_event_sessions(): void
    {
        $draftEvent = EnablementEvent::factory()->create([
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
        EnablementSession::factory()->create([
            'enablement_event_id' => $draftEvent->id,
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHours(2),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('api.enablement.sessions', [
            'start' => now()->toDateString(),
            'end' => now()->addMonth()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertJsonCount(1);
    }

    public function test_psm_cannot_see_draft_event_sessions(): void
    {
        $draftEvent = EnablementEvent::factory()->create([
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
        EnablementSession::factory()->create([
            'enablement_event_id' => $draftEvent->id,
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHours(2),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->psm)->getJson(route('api.enablement.sessions', [
            'start' => now()->toDateString(),
            'end' => now()->addMonth()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertJsonCount(0);
    }

    public function test_calendar_sessions_can_filter_by_type(): void
    {
        $workshop = EnablementEvent::factory()->create([
            'type' => 'workshop',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $webinar = EnablementEvent::factory()->create([
            'type' => 'webinar',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        EnablementSession::factory()->create([
            'enablement_event_id' => $workshop->id,
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHours(2),
            'created_by' => $this->admin->id,
        ]);
        EnablementSession::factory()->create([
            'enablement_event_id' => $webinar->id,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(1),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('api.enablement.sessions', [
            'start' => now()->toDateString(),
            'end' => now()->addMonth()->toDateString(),
            'type' => 'workshop',
        ]));

        $response->assertOk();
        $response->assertJsonCount(1);
    }

    public function test_calendar_sessions_can_filter_by_geography(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHours(2),
            'geography' => 'NA',
            'created_by' => $this->admin->id,
        ]);
        EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(2),
            'geography' => 'EMEA',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('api.enablement.sessions', [
            'start' => now()->toDateString(),
            'end' => now()->addMonth()->toDateString(),
            'geography' => 'EMEA',
        ]));

        $response->assertOk();
        $response->assertJsonCount(1);
    }

    // ─── API: Session Detail ─────────────────────────────────────

    public function test_admin_can_view_session_detail_api(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('api.enablement.sessions.show', $session->id));

        $response->assertOk();
        $response->assertJsonStructure([
            'id', 'title', 'event_title', 'event_type', 'category',
            'start_at', 'end_at', 'location', 'instructor',
            'spots_remaining', 'registration_open', 'registrations',
        ]);
    }

    public function test_psm_cannot_view_draft_event_session_detail(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->psm)->getJson(route('api.enablement.sessions.show', $session->id));

        $response->assertForbidden();
    }

    public function test_psm_sees_only_own_partner_registrations(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        // PSM's partner registration
        $myContact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        EnablementRegistration::factory()->create([
            'enablement_session_id' => $session->id,
            'contact_id' => $myContact->id,
            'partner_id' => $this->partner->id,
            'nominated_by' => $this->psm->id,
        ]);

        // Other partner's registration
        $otherPartner = Partner::factory()->create();
        $otherContact = Contact::factory()->create(['partner_id' => $otherPartner->id]);
        EnablementRegistration::factory()->create([
            'enablement_session_id' => $session->id,
            'contact_id' => $otherContact->id,
            'partner_id' => $otherPartner->id,
            'nominated_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->psm)->getJson(route('api.enablement.sessions.show', $session->id));

        $response->assertOk();
        $registrations = $response->json('registrations');
        $this->assertCount(1, $registrations);
        $this->assertEquals($this->partner->id, $registrations[0]['partner_id']);
    }

    // ─── API: Admin Session CRUD ─────────────────────────────────

    public function test_admin_can_create_session_via_api(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->postJson(route('api.enablement.sessions.store'), [
            'enablement_event_id' => $event->id,
            'start_at' => '2026-04-01 09:00:00',
            'end_at' => '2026-04-01 17:00:00',
            'all_day' => false,
            'location' => 'Virtual',
            'geography' => 'NA',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure(['id', 'title', 'start', 'end']);
        $this->assertDatabaseHas('enablement_sessions', [
            'enablement_event_id' => $event->id,
            'location' => 'Virtual',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_psm_cannot_create_session_via_api(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->psm)->postJson(route('api.enablement.sessions.store'), [
            'enablement_event_id' => $event->id,
            'start_at' => '2026-04-01 09:00:00',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_update_session_via_api(): void
    {
        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->putJson(route('api.enablement.sessions.update', $session->id), [
            'start_at' => '2026-06-01 10:00:00',
            'end_at' => '2026-06-01 14:00:00',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('enablement_sessions', [
            'id' => $session->id,
            'start_at' => '2026-06-01 10:00:00',
        ]);
    }

    public function test_psm_cannot_update_session_via_api(): void
    {
        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->psm)->putJson(route('api.enablement.sessions.update', $session->id), [
            'start_at' => '2026-06-01 10:00:00',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_delete_session_via_api(): void
    {
        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->deleteJson(route('api.enablement.sessions.delete', $session->id));

        $response->assertOk();
        $this->assertDatabaseMissing('enablement_sessions', ['id' => $session->id]);
    }

    public function test_psm_cannot_delete_session_via_api(): void
    {
        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->psm)->deleteJson(route('api.enablement.sessions.delete', $session->id));

        $response->assertForbidden();
    }

    // ─── API: Partner Registration ───────────────────────────────

    public function test_psm_can_register_own_partner_for_session(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(4),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->psm)->postJson(route('api.enablement.register-partner', $session->id), [
            'partner_id' => $this->partner->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('partner_enablement_session', [
            'partner_id' => $this->partner->id,
            'enablement_session_id' => $session->id,
            'added_by' => $this->psm->id,
        ]);
    }

    public function test_psm_cannot_register_other_psm_partner(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(4),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->psm)->postJson(route('api.enablement.register-partner', $session->id), [
            'partner_id' => $otherPartner->id,
        ]);

        $response->assertForbidden();
    }

    public function test_cannot_register_partner_for_draft_event(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->psm)->postJson(route('api.enablement.register-partner', $session->id), [
            'partner_id' => $this->partner->id,
        ]);

        $response->assertUnprocessable();
    }

    public function test_cannot_register_partner_twice(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(4),
            'created_by' => $this->admin->id,
        ]);

        $this->partner->enablementSessions()->attach($session->id, ['added_by' => $this->psm->id]);

        $response = $this->actingAs($this->psm)->postJson(route('api.enablement.register-partner', $session->id), [
            'partner_id' => $this->partner->id,
        ]);

        $response->assertUnprocessable();
    }

    public function test_psm_can_unregister_own_partner(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        $this->partner->enablementSessions()->attach($session->id, ['added_by' => $this->psm->id]);

        // Also create a contact registration that should be cleaned up
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        EnablementRegistration::factory()->create([
            'enablement_session_id' => $session->id,
            'contact_id' => $contact->id,
            'partner_id' => $this->partner->id,
            'nominated_by' => $this->psm->id,
        ]);

        $response = $this->actingAs($this->psm)->deleteJson(route('api.enablement.unregister-partner', $session->id), [
            'partner_id' => $this->partner->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('partner_enablement_session', [
            'partner_id' => $this->partner->id,
            'enablement_session_id' => $session->id,
        ]);
        // Contact registrations should also be removed
        $this->assertDatabaseMissing('enablement_registrations', [
            'enablement_session_id' => $session->id,
            'partner_id' => $this->partner->id,
        ]);
    }

    public function test_psm_cannot_unregister_other_psm_partner(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->psm)->deleteJson(route('api.enablement.unregister-partner', $session->id), [
            'partner_id' => $otherPartner->id,
        ]);

        $response->assertForbidden();
    }

    // ─── API: Contact Nomination ─────────────────────────────────

    public function test_psm_can_nominate_contacts_for_session(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(4),
            'created_by' => $this->admin->id,
        ]);

        $contacts = Contact::factory()->count(3)->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->postJson(route('api.enablement.nominate', $session->id), [
            'contact_ids' => $contacts->pluck('id')->toArray(),
            'partner_id' => $this->partner->id,
        ]);

        $response->assertCreated();
        $response->assertJson(['nominations' => 3]);
        foreach ($contacts as $contact) {
            $this->assertDatabaseHas('enablement_registrations', [
                'enablement_session_id' => $session->id,
                'contact_id' => $contact->id,
                'partner_id' => $this->partner->id,
                'nominated_by' => $this->psm->id,
                'status' => 'nominated',
            ]);
        }
    }

    public function test_nominate_skips_already_registered_contacts(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(4),
            'created_by' => $this->admin->id,
        ]);

        $contact1 = Contact::factory()->create(['partner_id' => $this->partner->id]);
        $contact2 = Contact::factory()->create(['partner_id' => $this->partner->id]);

        // Pre-register contact1
        EnablementRegistration::factory()->create([
            'enablement_session_id' => $session->id,
            'contact_id' => $contact1->id,
            'partner_id' => $this->partner->id,
            'nominated_by' => $this->psm->id,
        ]);

        $response = $this->actingAs($this->psm)->postJson(route('api.enablement.nominate', $session->id), [
            'contact_ids' => [$contact1->id, $contact2->id],
            'partner_id' => $this->partner->id,
        ]);

        $response->assertCreated();
        $response->assertJson(['nominations' => 1]);
    }

    public function test_cannot_nominate_for_unpublished_event(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->postJson(route('api.enablement.nominate', $session->id), [
            'contact_ids' => [$contact->id],
            'partner_id' => $this->partner->id,
        ]);

        $response->assertUnprocessable();
    }

    public function test_cannot_nominate_when_registration_closed(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->subDay(),
            'end_at' => now()->subDay()->addHours(4),
            'created_by' => $this->admin->id,
        ]);

        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->postJson(route('api.enablement.nominate', $session->id), [
            'contact_ids' => [$contact->id],
            'partner_id' => $this->partner->id,
        ]);

        $response->assertUnprocessable();
    }

    public function test_psm_cannot_nominate_other_partner_contacts(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(4),
            'created_by' => $this->admin->id,
        ]);

        $contact = Contact::factory()->create(['partner_id' => $otherPartner->id]);

        $response = $this->actingAs($this->psm)->postJson(route('api.enablement.nominate', $session->id), [
            'contact_ids' => [$contact->id],
            'partner_id' => $otherPartner->id,
        ]);

        $response->assertForbidden();
    }

    // ─── API: Remove Nomination ──────────────────────────────────

    public function test_psm_can_remove_own_nomination(): void
    {
        $registration = $this->createNomination();

        $response = $this->actingAs($this->psm)->deleteJson(route('api.enablement.remove-nomination', $registration->id));

        $response->assertOk();
        $this->assertDatabaseMissing('enablement_registrations', ['id' => $registration->id]);
    }

    public function test_admin_can_remove_any_nomination(): void
    {
        $registration = $this->createNomination();

        $response = $this->actingAs($this->admin)->deleteJson(route('api.enablement.remove-nomination', $registration->id));

        $response->assertOk();
        $this->assertDatabaseMissing('enablement_registrations', ['id' => $registration->id]);
    }

    public function test_psm_cannot_remove_other_psm_nomination(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);
        $contact = Contact::factory()->create(['partner_id' => $otherPartner->id]);

        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);
        $registration = EnablementRegistration::factory()->create([
            'enablement_session_id' => $session->id,
            'contact_id' => $contact->id,
            'partner_id' => $otherPartner->id,
            'nominated_by' => $otherPsm->id,
        ]);

        $response = $this->actingAs($this->psm)->deleteJson(route('api.enablement.remove-nomination', $registration->id));

        $response->assertForbidden();
    }

    public function test_cannot_remove_confirmed_nomination(): void
    {
        $registration = $this->createNomination();
        $registration->markConfirmed();

        $response = $this->actingAs($this->psm)->deleteJson(route('api.enablement.remove-nomination', $registration->id));

        $response->assertUnprocessable();
    }

    public function test_cannot_remove_attended_nomination(): void
    {
        $registration = $this->createNomination();
        $registration->markAttended();

        $response = $this->actingAs($this->psm)->deleteJson(route('api.enablement.remove-nomination', $registration->id));

        $response->assertUnprocessable();
    }

    // ─── API: My Nominations ─────────────────────────────────────

    public function test_psm_can_view_own_nominations(): void
    {
        $this->createNomination();
        $this->createNomination();

        $response = $this->actingAs($this->psm)->getJson(route('api.enablement.my-nominations'));

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJsonStructure([['id', 'session_title', 'contact_name', 'status', 'can_remove']]);
    }

    public function test_psm_only_sees_own_nominations(): void
    {
        $this->createNomination();

        // Other PSM's nomination
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);
        $otherContact = Contact::factory()->create(['partner_id' => $otherPartner->id]);
        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);
        EnablementRegistration::factory()->create([
            'enablement_session_id' => $session->id,
            'contact_id' => $otherContact->id,
            'partner_id' => $otherPartner->id,
            'nominated_by' => $otherPsm->id,
        ]);

        $response = $this->actingAs($this->psm)->getJson(route('api.enablement.my-nominations'));

        $response->assertOk();
        $response->assertJsonCount(1);
    }

    // ─── API: Partner Sessions ───────────────────────────────────

    public function test_psm_can_view_own_partner_sessions(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHours(4),
            'created_by' => $this->admin->id,
        ]);

        $this->partner->enablementSessions()->attach($session->id, ['added_by' => $this->psm->id]);

        $response = $this->actingAs($this->psm)->getJson(route('api.enablement.partner-sessions', [
            'id' => $this->partner->id,
            'start' => now()->toDateString(),
            'end' => now()->addMonth()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertJsonCount(1);
    }

    public function test_psm_cannot_view_other_partner_sessions(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        $response = $this->actingAs($this->psm)->getJson(route('api.enablement.partner-sessions', [
            'id' => $otherPartner->id,
            'start' => now()->toDateString(),
            'end' => now()->addMonth()->toDateString(),
        ]));

        $response->assertForbidden();
    }

    // ─── Partner Enablement Tab ──────────────────────────────────

    public function test_psm_can_view_partner_enablement_tab(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);
        $assignment = PartnerBlueprintAssignment::factory()->create([
            'partner_id' => $this->partner->id,
            'blueprint_id' => $blueprint->id,
            'assigned_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->psm)->get(route('partners.tab', [
            'id' => $this->partner->id,
            'tab' => 'enablement',
        ]));

        $response->assertOk();
        $response->assertViewIs('partner.tabs.enablement');
        $response->assertViewHas('partner');
    }

    public function test_admin_can_view_any_partner_enablement_tab(): void
    {
        $response = $this->actingAs($this->admin)->get(route('partners.tab', [
            'id' => $this->partner->id,
            'tab' => 'enablement',
        ]));

        $response->assertOk();
    }

    public function test_psm_cannot_view_other_psm_partner_enablement_tab(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        $response = $this->actingAs($this->psm)->get(route('partners.tab', [
            'id' => $otherPartner->id,
            'tab' => 'enablement',
        ]));

        $response->assertForbidden();
    }

    // ─── PSM Enablement Calendar View ────────────────────────────

    public function test_authenticated_user_can_view_enablement_calendar_page(): void
    {
        $response = $this->actingAs($this->psm)->get(route('enablement.calendar'));

        $response->assertOk();
    }

    public function test_guest_cannot_view_enablement_calendar_page(): void
    {
        $response = $this->get(route('enablement.calendar'));

        $response->assertRedirect();
    }

    // ─── PSM Partner Enablement Page ─────────────────────────────

    public function test_psm_can_view_partner_enablement_page(): void
    {
        $response = $this->actingAs($this->psm)->get(route('partners.enablement', $this->partner->id));

        $response->assertOk();
        $response->assertViewIs('enablement.partner');
        $response->assertViewHas('partner');
    }

    public function test_psm_cannot_view_other_partner_enablement_page(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        $response = $this->actingAs($this->psm)->get(route('partners.enablement', $otherPartner->id));

        $response->assertForbidden();
    }

    public function test_admin_can_view_any_partner_enablement_page(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        $response = $this->actingAs($this->admin)->get(route('partners.enablement', $otherPartner->id));

        $response->assertOk();
    }

    // ─── Model: EnablementSession ────────────────────────────────

    public function test_session_registration_is_open_for_future_published_event(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(4),
            'status' => 'scheduled',
            'created_by' => $this->admin->id,
        ]);

        $this->assertTrue($session->isRegistrationOpen());
    }

    public function test_session_registration_closed_for_past_session(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->subDay(),
            'end_at' => now()->subDay()->addHours(4),
            'status' => 'scheduled',
            'created_by' => $this->admin->id,
        ]);

        $this->assertFalse($session->isRegistrationOpen());
    }

    public function test_session_registration_closed_for_cancelled_session(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(4),
            'status' => 'cancelled',
            'created_by' => $this->admin->id,
        ]);

        $this->assertFalse($session->isRegistrationOpen());
    }

    public function test_session_registration_closed_when_full(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(4),
            'max_participants' => 2,
            'status' => 'scheduled',
            'created_by' => $this->admin->id,
        ]);

        // Fill up spots
        $contacts = Contact::factory()->count(2)->create(['partner_id' => $this->partner->id]);
        foreach ($contacts as $contact) {
            EnablementRegistration::factory()->create([
                'enablement_session_id' => $session->id,
                'contact_id' => $contact->id,
                'partner_id' => $this->partner->id,
                'nominated_by' => $this->psm->id,
            ]);
        }

        $this->assertFalse($session->isRegistrationOpen());
    }

    public function test_session_registration_closed_past_deadline(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(4),
            'registration_deadline' => now()->subDay(),
            'status' => 'scheduled',
            'created_by' => $this->admin->id,
        ]);

        $this->assertFalse($session->isRegistrationOpen());
    }

    public function test_session_spots_remaining(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'max_participants' => 5,
            'created_by' => $this->admin->id,
        ]);

        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        EnablementRegistration::factory()->create([
            'enablement_session_id' => $session->id,
            'contact_id' => $contact->id,
            'partner_id' => $this->partner->id,
            'nominated_by' => $this->psm->id,
        ]);

        $this->assertEquals(4, $session->spotsRemaining());
    }

    public function test_session_spots_remaining_null_when_unlimited(): void
    {
        $event = EnablementEvent::factory()->create([
            'max_participants' => null,
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'max_participants' => null,
            'created_by' => $this->admin->id,
        ]);

        $this->assertNull($session->spotsRemaining());
    }

    public function test_cancelled_registrations_dont_count_for_capacity(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'max_participants' => 2,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(10)->addHours(4),
            'status' => 'scheduled',
            'created_by' => $this->admin->id,
        ]);

        $contact1 = Contact::factory()->create(['partner_id' => $this->partner->id]);
        $contact2 = Contact::factory()->create(['partner_id' => $this->partner->id]);
        EnablementRegistration::factory()->create([
            'enablement_session_id' => $session->id,
            'contact_id' => $contact1->id,
            'partner_id' => $this->partner->id,
            'nominated_by' => $this->psm->id,
            'status' => 'nominated',
        ]);
        EnablementRegistration::factory()->create([
            'enablement_session_id' => $session->id,
            'contact_id' => $contact2->id,
            'partner_id' => $this->partner->id,
            'nominated_by' => $this->psm->id,
            'status' => 'cancelled',
        ]);

        $this->assertEquals(1, $session->spotsRemaining());
        $this->assertTrue($session->isRegistrationOpen());
    }

    // ─── Model: EnablementRegistration ───────────────────────────

    public function test_registration_status_transitions(): void
    {
        $registration = $this->createNomination();

        $this->assertEquals('nominated', $registration->status);

        $registration->markRegistered();
        $this->assertEquals('registered', $registration->fresh()->status);
        $this->assertNotNull($registration->fresh()->registered_at);

        $registration->markConfirmed();
        $this->assertEquals('confirmed', $registration->fresh()->status);
        $this->assertNotNull($registration->fresh()->confirmed_at);

        $registration->markAttended();
        $this->assertEquals('attended', $registration->fresh()->status);
        $this->assertNotNull($registration->fresh()->attended_at);
    }

    public function test_registration_no_show(): void
    {
        $registration = $this->createNomination();

        $registration->markNoShow();
        $this->assertEquals('no_show', $registration->fresh()->status);
    }

    public function test_registration_cancel_with_reason(): void
    {
        $registration = $this->createNomination();

        $registration->cancel('Travel issues');
        $fresh = $registration->fresh();
        $this->assertEquals('cancelled', $fresh->status);
        $this->assertEquals('Travel issues', $fresh->cancellation_reason);
    }

    public function test_registration_nominated_at_auto_set(): void
    {
        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        $registration = EnablementRegistration::create([
            'enablement_session_id' => $session->id,
            'contact_id' => $contact->id,
            'partner_id' => $this->partner->id,
            'nominated_by' => $this->psm->id,
            'status' => 'nominated',
        ]);

        $this->assertNotNull($registration->nominated_at);
    }

    // ─── Model: EnablementEvent ──────────────────────────────────

    public function test_event_published_scope(): void
    {
        EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        EnablementEvent::factory()->create([
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
        EnablementEvent::factory()->create([
            'status' => 'archived',
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(1, EnablementEvent::published()->count());
    }

    public function test_event_active_scope(): void
    {
        $activeEvent = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        EnablementSession::factory()->create([
            'enablement_event_id' => $activeEvent->id,
            'start_at' => now()->addDays(5),
            'status' => 'scheduled',
            'created_by' => $this->admin->id,
        ]);

        $inactiveEvent = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        EnablementSession::factory()->create([
            'enablement_event_id' => $inactiveEvent->id,
            'start_at' => now()->subDays(30),
            'status' => 'completed',
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(1, EnablementEvent::active()->count());
    }

    public function test_event_total_registrations(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        $contacts = Contact::factory()->count(3)->create(['partner_id' => $this->partner->id]);
        foreach ($contacts as $contact) {
            EnablementRegistration::factory()->create([
                'enablement_session_id' => $session->id,
                'contact_id' => $contact->id,
                'partner_id' => $this->partner->id,
                'nominated_by' => $this->psm->id,
            ]);
        }

        $this->assertEquals(3, $event->totalRegistrations());
    }

    public function test_event_upcoming_sessions(): void
    {
        $event = EnablementEvent::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);

        EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(5),
            'status' => 'scheduled',
            'created_by' => $this->admin->id,
        ]);
        EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->subDays(5),
            'status' => 'completed',
            'created_by' => $this->admin->id,
        ]);
        EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(10),
            'status' => 'cancelled',
            'created_by' => $this->admin->id,
        ]);

        $this->assertCount(1, $event->upcomingSessions());
    }

    // ─── Session Calendar Array ──────────────────────────────────

    public function test_session_to_calendar_array(): void
    {
        $event = EnablementEvent::factory()->create([
            'type' => 'workshop',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => '2026-04-01 09:00:00',
            'all_day' => false,
            'status' => 'scheduled',
            'created_by' => $this->admin->id,
        ]);

        $session->load('event', 'registrations');
        $arr = $session->toCalendarArray();

        $this->assertEquals((string) $session->id, $arr['id']);
        $this->assertArrayHasKey('title', $arr);
        $this->assertArrayHasKey('start', $arr);
        $this->assertFalse($arr['allDay']);
        $this->assertEquals(EnablementEvent::TYPE_COLORS['workshop'], $arr['backgroundColor']);
        $this->assertArrayHasKey('extendedProps', $arr);
        $this->assertEquals('workshop', $arr['extendedProps']['type']);
    }

    // ─── Helper ──────────────────────────────────────────────────

    private function createNomination(): EnablementRegistration
    {
        $session = EnablementSession::factory()->create(['created_by' => $this->admin->id]);
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        return EnablementRegistration::factory()->create([
            'enablement_session_id' => $session->id,
            'contact_id' => $contact->id,
            'partner_id' => $this->partner->id,
            'nominated_by' => $this->psm->id,
        ]);
    }
}
