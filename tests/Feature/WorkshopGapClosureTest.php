<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\User;
use App\Models\WorkshopCredential;
use App\Models\WorkshopSession;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class WorkshopGapClosureTest extends TestCase
{
    use RefreshDatabase;

    private User $coe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        Feature::for(null)->activate('workshops');
        Feature::for(null)->activate('portal');

        $this->coe = User::factory()->coe()->create();
    }

    public function test_participant_progress_page_shows_their_own_assigned_credential(): void
    {
        $session = WorkshopSession::factory()->create();
        $credential = WorkshopCredential::factory()->create(['workshop_id' => $session->workshop_id]);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);
        $credential->assignments()->create([
            'workshop_session_id' => $session->id,
            'contact_id' => $contact->id,
            'assigned_at' => now(),
            'hold_until' => now()->addWeek(),
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.progress', $session));

        $response->assertOk();
        $response->assertSee($credential->username);
        $response->assertSee($credential->password_enc);
    }

    public function test_participant_progress_page_does_not_show_someone_elses_credential(): void
    {
        $session = WorkshopSession::factory()->create();
        $credential = WorkshopCredential::factory()->create(['workshop_id' => $session->workshop_id]);
        $otherContact = Contact::factory()->create();
        $credential->assignments()->create([
            'workshop_session_id' => $session->id,
            'contact_id' => $otherContact->id,
            'assigned_at' => now(),
        ]);

        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.progress', $session));

        $response->assertOk();
        $response->assertDontSee($credential->username);
    }

    public function test_portal_participant_can_view_activity_feed(): void
    {
        $session = WorkshopSession::factory()->create();
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Feed test question', 'status' => 'open']);
        $thread->replies()->create(['author_user_id' => $this->coe->id, 'is_monitor_reply' => true, 'body' => 'Feed test reply']);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.feed', $session));

        $response->assertOk();
        $response->assertSeeInOrder(['Feed test question', 'Feed test reply']);
    }

    public function test_non_participant_cannot_view_portal_feed(): void
    {
        $session = WorkshopSession::factory()->create();
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();

        $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.feed', $session))->assertForbidden();
    }

    public function test_internal_user_can_mark_a_thread_answered_without_replying(): void
    {
        $session = WorkshopSession::factory()->create();
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Q', 'status' => 'open']);

        $this->actingAs($this->coe)->post(route('workshops.sessions.threads.answer', [$session->workshop, $session, $thread]));

        $thread->refresh();
        $this->assertEquals('answered', $thread->status);
        $this->assertEquals(0, $thread->replies()->count());
    }

    public function test_monitor_can_mark_a_thread_answered_from_the_monitor_dashboard(): void
    {
        $session = WorkshopSession::factory()->create();
        $monitorUser = User::factory()->pam()->create();
        $session->monitors()->create(['user_id' => $monitorUser->id, 'assigned_at' => now()]);
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Q', 'status' => 'open']);

        $this->actingAs($monitorUser)->post(route('workshops.monitor.threads.answer', [$session, $thread]));

        $this->assertEquals('answered', $thread->fresh()->status);
    }

    public function test_reports_hub_page_renders_and_links_to_all_report_types(): void
    {
        $session = WorkshopSession::factory()->create();

        $response = $this->actingAs($this->coe)->get(route('workshops.reports.index', [$session->workshop, $session]));

        $response->assertOk();
        $response->assertSee('Executive Summary');
        $response->assertSee('Leaderboard');
        $response->assertSee('Slack Post');
    }

    public function test_all_partner_reports_can_be_downloaded_as_one_zip(): void
    {
        $session = WorkshopSession::factory()->create();
        $partnerA = Partner::factory()->create();
        $partnerB = Partner::factory()->create();
        $session->participants()->create(['contact_id' => Contact::factory()->create(['partner_id' => $partnerA->id])->id, 'status' => 'active', 'enrolled_at' => now()]);
        $session->participants()->create(['contact_id' => Contact::factory()->create(['partner_id' => $partnerB->id])->id, 'status' => 'active', 'enrolled_at' => now()]);

        $response = $this->actingAs($this->coe)->get(route('workshops.reports.partners-zip', [$session->workshop, $session]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/zip');
    }
}
