<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionMonitor;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class WorkshopArchiveAndLockTest extends TestCase
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

    public function test_internal_user_can_archive_a_workshop(): void
    {
        $workshop = Workshop::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->coe)->post(route('workshops.archive', $workshop));

        $response->assertRedirect(route('workshops.index'));
        $workshop->refresh();
        $this->assertSame('archived', $workshop->status);
        $this->assertNotNull($workshop->archived_at);
    }

    public function test_archiving_an_already_archived_workshop_does_not_reset_the_timestamp(): void
    {
        $workshop = Workshop::factory()->create(['status' => 'archived', 'archived_at' => now()->subWeek()]);
        $originalTimestamp = $workshop->archived_at;

        $this->actingAs($this->coe)->post(route('workshops.archive', $workshop));

        $this->assertTrue($workshop->fresh()->archived_at->equalTo($originalTimestamp));
    }

    public function test_internal_user_can_archive_a_session(): void
    {
        $session = WorkshopSession::factory()->create(['status' => 'upcoming']);

        $response = $this->actingAs($this->coe)->post(route('workshops.sessions.archive', [$session->workshop, $session]));

        $response->assertRedirect(route('workshops.show', $session->workshop));
        $session->refresh();
        $this->assertSame('archived', $session->status);
        $this->assertNotNull($session->archived_at);
    }

    public function test_an_archived_session_is_locked(): void
    {
        $session = WorkshopSession::factory()->create(['status' => 'archived']);

        $this->assertTrue($session->isLocked());
    }

    public function test_a_session_well_past_its_end_date_is_locked_even_without_archiving(): void
    {
        $session = WorkshopSession::factory()->create([
            'status' => 'completed',
            'start_date' => now()->subDays(40),
            'end_date' => now()->subDays(36),
        ]);

        $this->assertTrue($session->isLocked());
    }

    public function test_a_recently_ended_session_is_not_yet_locked(): void
    {
        $session = WorkshopSession::factory()->create([
            'status' => 'completed',
            'start_date' => now()->subDays(5),
            'end_date' => now()->subDays(2),
        ]);

        $this->assertFalse($session->isLocked());
    }

    public function test_a_future_session_is_not_locked(): void
    {
        $session = WorkshopSession::factory()->create();

        $this->assertFalse($session->isLocked());
    }

    public function test_internal_user_cannot_post_a_thread_in_a_locked_session(): void
    {
        $session = WorkshopSession::factory()->create(['status' => 'archived']);

        $response = $this->actingAs($this->coe)->post(route('workshops.sessions.threads.store', [$session->workshop, $session]), [
            'body' => 'Should be blocked',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('workshop_threads', ['body' => 'Should be blocked']);
    }

    public function test_internal_user_cannot_reply_in_a_locked_session(): void
    {
        $session = WorkshopSession::factory()->create(['status' => 'archived']);
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Q', 'status' => 'open']);

        $response = $this->actingAs($this->coe)->post(route('workshops.sessions.threads.reply', [$session->workshop, $session, $thread]), [
            'body' => 'Should be blocked',
        ]);

        $response->assertForbidden();
    }

    public function test_internal_user_cannot_mark_answered_in_a_locked_session(): void
    {
        $session = WorkshopSession::factory()->create(['status' => 'archived']);
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Q', 'status' => 'open']);

        $response = $this->actingAs($this->coe)->post(route('workshops.sessions.threads.answer', [$session->workshop, $session, $thread]));

        $response->assertForbidden();
        $this->assertSame('open', $thread->fresh()->status);
    }

    public function test_monitor_cannot_mark_answered_in_a_locked_session(): void
    {
        $session = WorkshopSession::factory()->create(['status' => 'archived']);
        $monitorUser = User::factory()->pam()->create();
        WorkshopSessionMonitor::factory()->create(['workshop_session_id' => $session->id, 'user_id' => $monitorUser->id]);
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Q', 'status' => 'open']);

        $response = $this->actingAs($monitorUser)->post(route('workshops.monitor.threads.answer', [$session, $thread]));

        $response->assertForbidden();
    }

    public function test_portal_participant_cannot_post_a_thread_in_a_locked_session(): void
    {
        $session = WorkshopSession::factory()->create(['status' => 'archived']);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $response = $this->actingAs($contact, 'portal')->post(route('portal.my-workshops.threads.store', $session), [
            'body' => 'Should be blocked',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('workshop_threads', ['body' => 'Should be blocked']);
    }

    public function test_portal_participant_cannot_reply_in_a_locked_session(): void
    {
        $session = WorkshopSession::factory()->create(['status' => 'archived']);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Q', 'status' => 'open']);

        $response = $this->actingAs($contact, 'portal')->post(route('portal.my-workshops.threads.reply', [$session, $thread]), [
            'body' => 'Should be blocked',
        ]);

        $response->assertForbidden();
    }

    public function test_portal_participant_cannot_react_in_a_locked_session(): void
    {
        $session = WorkshopSession::factory()->create(['status' => 'archived']);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Q', 'status' => 'open']);

        $response = $this->actingAs($contact, 'portal')->post(route('portal.my-workshops.threads.react', [$session, $thread]));

        $response->assertForbidden();
    }
}
