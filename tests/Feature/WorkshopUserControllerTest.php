<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkshopCheckAttempt;
use App\Models\WorkshopPage;
use App\Models\WorkshopPresence;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionParticipant;
use App\Models\WorkshopSurveyResponse;
use App\Models\WorkshopSurveyTemplate;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class WorkshopUserControllerTest extends TestCase
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

    public function test_index_lists_only_enrolled_contacts(): void
    {
        $session = WorkshopSession::factory()->create();
        $enrolled = Contact::factory()->create();
        $notEnrolled = Contact::factory()->create();
        WorkshopSessionParticipant::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $enrolled->id]);

        $response = $this->actingAs($this->coe)->get(route('workshops.users.index'));

        $response->assertOk();
        $response->assertSee($enrolled->email);
        $response->assertDontSee($notEnrolled->email);
    }

    public function test_granting_access_sets_portal_role_and_password(): void
    {
        $session = WorkshopSession::factory()->create();
        $contact = Contact::factory()->create(['portal_access' => false]);
        WorkshopSessionParticipant::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $contact->id]);
        $role = Role::where('slug', 'workshop_delivery_resource')->first();

        $response = $this->actingAs($this->coe)->post(route('workshops.users.store'), [
            'contact_id' => $contact->id,
            'role_id' => $role->id,
        ]);

        $response->assertRedirect(route('workshops.users.index'));
        $contact->refresh();
        $this->assertTrue($contact->portal_access);
        $this->assertSame($role->id, $contact->portal_role_id);
        $this->assertNotNull($contact->password);
    }

    public function test_reset_password_changes_the_stored_hash(): void
    {
        $contact = Contact::factory()->create(['portal_access' => true, 'password' => 'original']);
        $originalHash = $contact->password;

        $this->actingAs($this->coe)->post(route('workshops.users.reset-password', $contact));

        $this->assertNotSame($originalHash, $contact->fresh()->password);
    }

    public function test_disable_revokes_portal_access(): void
    {
        $contact = Contact::factory()->create(['portal_access' => true]);

        $this->actingAs($this->coe)->post(route('workshops.users.disable', $contact));

        $this->assertFalse($contact->fresh()->portal_access);
    }

    public function test_activity_merges_real_events_in_chronological_order(): void
    {
        $session = WorkshopSession::factory()->create();
        $contact = Contact::factory()->create();
        $page = WorkshopPage::factory()->create(['workshop_id' => $session->workshop_id, 'title' => 'Exercise 1']);
        $template = WorkshopSurveyTemplate::factory()->create(['workshop_id' => $session->workshop_id, 'kind' => 'post', 'passing_score' => 70]);

        WorkshopPresence::create(['workshop_session_id' => $session->id, 'workshop_page_id' => $page->id, 'author_contact_id' => $contact->id, 'last_seen_at' => now()->subDays(2)]);
        WorkshopCheckAttempt::factory()->create(['workshop_session_id' => $session->id, 'workshop_page_id' => $page->id, 'contact_id' => $contact->id, 'passed' => true, 'attempted_at' => now()->subDay()]);
        WorkshopSurveyResponse::factory()->create(['workshop_survey_template_id' => $template->id, 'workshop_session_id' => $session->id, 'contact_id' => $contact->id, 'passed' => true, 'submitted_at' => now()]);

        $response = $this->actingAs($this->coe)->get(route('workshops.users.activity', $contact));

        $response->assertOk();
        $response->assertSeeInOrder(['Submitted Post-Workshop survey', 'Check: Exercise 1', 'Viewed "Exercise 1"']);
    }
}
