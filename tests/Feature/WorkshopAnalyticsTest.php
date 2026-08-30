<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopSurveyResponse;
use App\Models\WorkshopSurveyTemplate;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class WorkshopAnalyticsTest extends TestCase
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

    public function test_workshops_analytics_aggregates_across_every_workshop_by_default(): void
    {
        $workshopA = Workshop::factory()->create();
        $workshopB = Workshop::factory()->create();
        $sessionA = WorkshopSession::factory()->create(['workshop_id' => $workshopA->id]);
        $sessionB = WorkshopSession::factory()->create(['workshop_id' => $workshopB->id]);
        $sessionA->participants()->create(['contact_id' => Contact::factory()->create()->id, 'status' => 'active', 'enrolled_at' => now()]);
        $sessionB->participants()->create(['contact_id' => Contact::factory()->create()->id, 'status' => 'active', 'enrolled_at' => now()]);

        $response = $this->actingAs($this->coe)->get(route('workshops.analytics.workshops'));

        $response->assertOk();
        $response->assertSee($sessionA->name);
        $response->assertSee($sessionB->name);
    }

    public function test_workshops_analytics_filters_to_a_single_workshop(): void
    {
        $workshopA = Workshop::factory()->create();
        $workshopB = Workshop::factory()->create();
        $sessionA = WorkshopSession::factory()->create(['workshop_id' => $workshopA->id]);
        $sessionB = WorkshopSession::factory()->create(['workshop_id' => $workshopB->id]);

        $response = $this->actingAs($this->coe)->get(route('workshops.analytics.workshops', ['workshop_id' => $workshopA->id]));

        $response->assertOk();
        $response->assertSee($sessionA->name);
        $response->assertDontSee($sessionB->name);
    }

    public function test_sessions_analytics_computes_pre_post_delta_per_session_using_its_own_workshop_templates(): void
    {
        $workshop = Workshop::factory()->create();
        $session = WorkshopSession::factory()->create(['workshop_id' => $workshop->id]);
        $pre = WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'pre', 'questions_json' => [['id' => 'q1', 'text' => 'Comfort', 'type' => 'likert']]]);
        $post = WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'post', 'questions_json' => [['id' => 'q1', 'text' => 'Comfort', 'type' => 'likert']]]);
        $contact = Contact::factory()->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);
        WorkshopSurveyResponse::factory()->create(['workshop_survey_template_id' => $pre->id, 'workshop_session_id' => $session->id, 'contact_id' => $contact->id, 'responses_json' => ['q1' => 2]]);
        WorkshopSurveyResponse::factory()->create(['workshop_survey_template_id' => $post->id, 'workshop_session_id' => $session->id, 'contact_id' => $contact->id, 'responses_json' => ['q1' => 5]]);

        $response = $this->actingAs($this->coe)->get(route('workshops.analytics.sessions', ['workshop_id' => $workshop->id]));

        $response->assertOk();
        $response->assertSee('+3');
    }

    public function test_participation_analytics_ranks_partners_by_participant_volume(): void
    {
        $bigPartner = Partner::factory()->create(['name' => 'Big Partner']);
        $smallPartner = Partner::factory()->create(['name' => 'Small Partner']);
        $session = WorkshopSession::factory()->create();

        foreach (range(1, 3) as $i) {
            $contact = Contact::factory()->create(['partner_id' => $bigPartner->id]);
            $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now(), 'completed_at' => now()]);
        }
        $soloContact = Contact::factory()->create(['partner_id' => $smallPartner->id]);
        $session->participants()->create(['contact_id' => $soloContact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $response = $this->actingAs($this->coe)->get(route('workshops.analytics.participation'));

        $response->assertOk();
        $response->assertSeeInOrder(['Big Partner', '100%', 'Small Partner', '0%']);
    }
}
