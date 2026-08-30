<?php

namespace Tests\Feature;

use App\Ai\Agents\WorkshopReplySuggester;
use App\Models\Contact;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopPage;
use App\Models\WorkshopPresence;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionMonitor;
use App\Models\WorkshopThread;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class WorkshopDiscussionEnhancementsTest extends TestCase
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

    public function test_internal_user_can_toggle_a_reaction_on_a_thread(): void
    {
        $session = WorkshopSession::factory()->create();
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Q', 'status' => 'open']);

        $this->actingAs($this->coe)->post(route('workshops.sessions.threads.react', [$session->workshop, $session, $thread]));
        $this->assertTrue($thread->fresh()->hasReactionFrom($this->coe->id, null));

        $this->actingAs($this->coe)->post(route('workshops.sessions.threads.react', [$session->workshop, $session, $thread]));
        $this->assertFalse($thread->fresh()->hasReactionFrom($this->coe->id, null));
    }

    public function test_portal_participant_can_toggle_a_reaction(): void
    {
        $session = WorkshopSession::factory()->create();
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Q', 'status' => 'open']);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($contact, 'portal')->post(route('portal.my-workshops.threads.react', [$session, $thread]));
        $this->assertTrue($thread->fresh()->hasReactionFrom(null, $contact->id));
    }

    public function test_presence_heartbeat_and_active_count(): void
    {
        $session = WorkshopSession::factory()->create();

        $this->actingAs($this->coe)->get(route('workshops.sessions.show', [$session->workshop, $session]));

        $this->assertDatabaseHas('workshop_presence', [
            'workshop_session_id' => $session->id,
            'author_user_id' => $this->coe->id,
        ]);
        $this->assertEquals(1, WorkshopPresence::activeCountForSession($session->id));
    }

    public function test_viewing_a_thread_marks_it_read(): void
    {
        $session = WorkshopSession::factory()->create();
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Q', 'status' => 'open']);

        $this->actingAs($this->coe)->get(route('workshops.sessions.show', [$session->workshop, $session]));

        $this->assertDatabaseHas('workshop_read_state', [
            'workshop_thread_id' => $thread->id,
            'author_user_id' => $this->coe->id,
        ]);
    }

    public function test_internal_user_can_post_a_thread_with_an_attachment(): void
    {
        Storage::fake('local');
        $session = WorkshopSession::factory()->create();

        $this->actingAs($this->coe)->post(route('workshops.sessions.threads.store', [$session->workshop, $session]), [
            'body' => 'Announcement with a file',
            'attachment' => UploadedFile::fake()->create('notes.pdf', 50, 'application/pdf'),
        ]);

        $thread = WorkshopThread::firstWhere('body', 'Announcement with a file');
        $this->assertNotNull($thread);
        $this->assertEquals(1, $thread->uploads()->count());

        $this->actingAs($this->coe)->get(route('workshops.uploads.download', $thread->uploads()->first()))->assertOk();
    }

    public function test_portal_participant_can_reply_with_an_attachment_and_download_it(): void
    {
        Storage::fake('local');
        $session = WorkshopSession::factory()->create();
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Q', 'status' => 'open']);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($contact, 'portal')->post(route('portal.my-workshops.threads.reply', [$session, $thread]), [
            'body' => 'Here is a screenshot',
            'attachment' => UploadedFile::fake()->image('screenshot.png'),
        ]);

        $upload = $thread->replies()->first()->uploads()->first();
        $this->assertNotNull($upload);

        $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.uploads.download', [$session, $upload]))->assertOk();
    }

    public function test_page_scoped_thread_only_shows_on_that_page(): void
    {
        $session = WorkshopSession::factory()->create();
        $pageA = WorkshopPage::factory()->create(['workshop_id' => $session->workshop_id]);
        $pageB = WorkshopPage::factory()->create(['workshop_id' => $session->workshop_id]);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($contact, 'portal')->post(route('portal.my-workshops.threads.store', $session), [
            'body' => 'Question about page A',
            'workshop_page_id' => $pageA->id,
        ]);

        $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.pages.show', [$session, $pageA]))
            ->assertSee('Question about page A');
        $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.pages.show', [$session, $pageB]))
            ->assertDontSee('Question about page A');
    }

    public function test_cohort_feed_renders_chronologically(): void
    {
        $session = WorkshopSession::factory()->create();
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Feed question', 'status' => 'open']);
        $thread->replies()->create(['author_user_id' => $this->coe->id, 'is_monitor_reply' => true, 'body' => 'Feed reply']);

        $response = $this->actingAs($this->coe)->get(route('workshops.sessions.feed', [$session->workshop, $session]));

        $response->assertOk();
        $response->assertSeeInOrder(['Feed question', 'Feed reply']);
    }

    public function test_monitor_dashboard_accessible_to_assigned_monitor_without_broad_role(): void
    {
        $session = WorkshopSession::factory()->create();
        $plainUser = User::factory()->pam()->create(); // no workshops permission at all
        $session->monitors()->create(['user_id' => $plainUser->id, 'assigned_at' => now()]);

        $this->actingAs($plainUser)->get(route('workshops.monitor.show', $session))->assertOk();
    }

    public function test_monitor_dashboard_forbidden_to_unrelated_internal_user(): void
    {
        $session = WorkshopSession::factory()->create();
        $unrelatedUser = User::factory()->pam()->create();

        $this->actingAs($unrelatedUser)->get(route('workshops.monitor.show', $session))->assertForbidden();
    }

    public function test_ai_suggest_reply_returns_a_suggestion(): void
    {
        WorkshopReplySuggester::fake(['Great question! Start with the SYS module.']);

        $session = WorkshopSession::factory()->create();
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'How do I start?', 'status' => 'open']);

        $response = $this->actingAs($this->coe)->getJson(route('workshops.sessions.threads.suggest-reply', [$session->workshop, $session, $thread]));

        $response->assertOk();
        $response->assertJsonStructure(['suggestion']);
    }

    public function test_a_session_monitor_can_view_a_facilitator_only_page_for_their_session(): void
    {
        $monitorUser = User::factory()->pam()->create(); // no workshops permission at all
        $workshop = Workshop::factory()->create();
        $session = WorkshopSession::factory()->create(['workshop_id' => $workshop->id]);
        WorkshopSessionMonitor::factory()->create(['workshop_session_id' => $session->id, 'user_id' => $monitorUser->id]);
        $page = WorkshopPage::factory()->create(['workshop_id' => $workshop->id, 'audience' => 'facilitator']);

        $response = $this->actingAs($monitorUser)->get(route('workshops.monitor.pages.show', [$session, $page]));

        $response->assertOk();
        $response->assertSee($page->title);
    }

    public function test_a_participant_facing_page_is_not_reachable_through_the_monitor_facilitator_route(): void
    {
        $monitorUser = User::factory()->pam()->create();
        $workshop = Workshop::factory()->create();
        $session = WorkshopSession::factory()->create(['workshop_id' => $workshop->id]);
        WorkshopSessionMonitor::factory()->create(['workshop_session_id' => $session->id, 'user_id' => $monitorUser->id]);
        $page = WorkshopPage::factory()->create(['workshop_id' => $workshop->id, 'audience' => 'participant']);

        $response = $this->actingAs($monitorUser)->get(route('workshops.monitor.pages.show', [$session, $page]));

        $response->assertNotFound();
    }

    public function test_a_facilitator_page_from_a_different_workshop_is_not_reachable_via_the_monitor_route(): void
    {
        $monitorUser = User::factory()->pam()->create();
        $session = WorkshopSession::factory()->create();
        WorkshopSessionMonitor::factory()->create(['workshop_session_id' => $session->id, 'user_id' => $monitorUser->id]);
        $otherWorkshopPage = WorkshopPage::factory()->create(['audience' => 'facilitator']);

        $response = $this->actingAs($monitorUser)->get(route('workshops.monitor.pages.show', [$session, $otherWorkshopPage]));

        $response->assertNotFound();
    }

    public function test_rollup_report_shows_discussion_metrics(): void
    {
        $session = WorkshopSession::factory()->create();
        $session->threads()->create(['kind' => 'question', 'body' => 'Open one', 'status' => 'open']);
        $session->threads()->create(['kind' => 'question', 'body' => 'Answered one', 'status' => 'answered', 'answered_at' => now()]);

        $response = $this->actingAs($this->coe)->get(route('workshops.rollup', $session->workshop));

        $response->assertOk();
        $response->assertSee('Total Questions');
    }
}
