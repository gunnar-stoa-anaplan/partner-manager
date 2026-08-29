<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use App\Models\WorkshopSession;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class WorkshopDiscussionTest extends TestCase
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

    public function test_participant_can_post_a_question_and_facilitator_can_reply(): void
    {
        $session = WorkshopSession::factory()->create();
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($contact, 'portal')->post(route('portal.my-workshops.threads.store', $session), [
            'body' => 'How do I configure the golden model?',
        ]);

        $thread = $session->threads()->first();
        $this->assertNotNull($thread);
        $this->assertEquals($contact->id, $thread->author_contact_id);

        $this->actingAs($this->coe)->post(route('workshops.sessions.threads.reply', [$session->workshop, $session, $thread]), [
            'body' => 'Start with the SYS module.',
        ]);

        $thread->refresh();
        $this->assertEquals('answered', $thread->status);
        $this->assertEquals(1, $thread->replies()->count());
        $this->assertTrue($thread->replies()->first()->is_monitor_reply);
    }

    public function test_facilitator_can_hide_a_thread(): void
    {
        $session = WorkshopSession::factory()->create();
        $thread = $session->threads()->create(['kind' => 'question', 'body' => 'Test', 'status' => 'open']);

        $this->actingAs($this->coe)->post(route('workshops.sessions.threads.hide', [$session->workshop, $session, $thread]));

        $this->assertEquals('hidden', $thread->fresh()->status);
    }

    public function test_a_contact_not_in_the_session_cannot_post_a_question(): void
    {
        $session = WorkshopSession::factory()->create();
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();

        $response = $this->actingAs($contact, 'portal')->post(route('portal.my-workshops.threads.store', $session), [
            'body' => 'Sneaky question',
        ]);

        $response->assertForbidden();
    }
}
