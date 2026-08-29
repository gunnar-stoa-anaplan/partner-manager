<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopSurveyTemplate;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class WorkshopSurveyTest extends TestCase
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

    public function test_template_scores_single_and_multiple_choice_questions(): void
    {
        $template = WorkshopSurveyTemplate::factory()->create([
            'passing_score' => 50,
            'questions_json' => [
                ['id' => 'q1', 'text' => 'Single', 'type' => 'single', 'options' => ['A', 'B'], 'correct' => ['A']],
                ['id' => 'q2', 'text' => 'Multiple', 'type' => 'multiple', 'options' => ['A', 'B', 'C'], 'correct' => ['A', 'C']],
                ['id' => 'q3', 'text' => 'Open text - not scored', 'type' => 'text'],
            ],
        ]);

        $result = $template->score(['q1' => 'A', 'q2' => ['C', 'A'], 'q3' => 'anything']);

        $this->assertEquals(100, $result['score']);
        $this->assertTrue($result['passed']);

        $failing = $template->score(['q1' => 'B', 'q2' => ['B']]);
        $this->assertEquals(0, $failing['score']);
        $this->assertFalse($failing['passed']);
    }

    public function test_internal_user_can_create_a_survey_template(): void
    {
        $workshop = Workshop::factory()->create();

        $response = $this->actingAs($this->coe)->post(route('workshops.surveys.store'), [
            'workshop_id' => $workshop->id,
            'kind' => 'post',
            'passing_score' => 70,
            'questions_json' => json_encode([['id' => 'q1', 'text' => 'Q', 'type' => 'single', 'options' => ['A', 'B'], 'correct' => ['A']]]),
        ]);

        $response->assertRedirect(route('workshops.surveys.index'));
        $this->assertDatabaseHas('workshop_survey_templates', ['workshop_id' => $workshop->id, 'kind' => 'post']);
    }

    public function test_portal_participant_can_submit_a_survey_and_gets_scored(): void
    {
        $session = WorkshopSession::factory()->create();
        $template = WorkshopSurveyTemplate::factory()->create([
            'workshop_id' => $session->workshop_id,
            'passing_score' => 50,
            'questions_json' => [['id' => 'q1', 'text' => 'Q', 'type' => 'single', 'options' => ['A', 'B'], 'correct' => ['A']]],
        ]);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $response = $this->actingAs($contact, 'portal')->post(
            route('portal.my-workshops.surveys.submit', [$session, $template]),
            ['responses' => ['q1' => 'A']],
        );

        $response->assertRedirect(route('portal.my-workshops.show', $session));
        $this->assertDatabaseHas('workshop_survey_responses', [
            'workshop_survey_template_id' => $template->id,
            'contact_id' => $contact->id,
            'score' => 100,
            'passed' => 1,
        ]);
    }

    public function test_portal_survey_form_and_my_workshops_show_render(): void
    {
        $session = WorkshopSession::factory()->create();
        $template = WorkshopSurveyTemplate::factory()->create([
            'workshop_id' => $session->workshop_id,
            'questions_json' => [
                ['id' => 'q1', 'text' => 'Single choice', 'type' => 'single', 'options' => ['A', 'B'], 'correct' => ['A']],
                ['id' => 'q2', 'text' => 'Multiple choice', 'type' => 'multiple', 'options' => ['A', 'B'], 'correct' => ['A', 'B']],
                ['id' => 'q3', 'text' => 'Open text', 'type' => 'text'],
            ],
        ]);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.show', $session))->assertOk();
        $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.surveys.show', [$session, $template]))->assertOk();
    }

    public function test_a_non_participant_cannot_submit_a_survey_for_a_session(): void
    {
        $session = WorkshopSession::factory()->create();
        $template = WorkshopSurveyTemplate::factory()->create(['workshop_id' => $session->workshop_id]);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();

        $response = $this->actingAs($contact, 'portal')->post(
            route('portal.my-workshops.surveys.submit', [$session, $template]),
            ['responses' => ['q1' => 'A']],
        );

        $response->assertForbidden();
    }
}
