<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionParticipant;
use App\Models\WorkshopSurveyResponse;
use App\Models\WorkshopSurveyTemplate;
use App\Services\WorkshopSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkshopSummaryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WorkshopSummaryService::class);
    }

    public function test_summary_excludes_archived_sessions(): void
    {
        $workshop = Workshop::factory()->create();
        WorkshopSession::factory()->create(['workshop_id' => $workshop->id, 'status' => 'upcoming']);
        WorkshopSession::factory()->create(['workshop_id' => $workshop->id, 'status' => 'archived']);

        $summary = $this->service->build($workshop->fresh());

        $this->assertSame(1, $summary['sessions_count']);
    }

    public function test_pre_and_post_averages_come_from_the_rating_question(): void
    {
        $workshop = Workshop::factory()->create();
        $session = WorkshopSession::factory()->create(['workshop_id' => $workshop->id]);
        $pre = WorkshopSurveyTemplate::factory()->create([
            'workshop_id' => $workshop->id,
            'kind' => 'pre',
            'questions_json' => [['id' => 'confidence', 'text' => 'Rate yourself', 'type' => 'rating']],
        ]);
        $post = WorkshopSurveyTemplate::factory()->create([
            'workshop_id' => $workshop->id,
            'kind' => 'post',
            'questions_json' => [['id' => 'confidence', 'text' => 'Rate yourself', 'type' => 'rating']],
        ]);

        $contactA = Contact::factory()->create();
        $contactB = Contact::factory()->create();
        foreach ([$contactA, $contactB] as $contact) {
            WorkshopSessionParticipant::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $contact->id]);
        }
        WorkshopSurveyResponse::factory()->create(['workshop_survey_template_id' => $pre->id, 'workshop_session_id' => $session->id, 'contact_id' => $contactA->id, 'responses_json' => ['confidence' => 2]]);
        WorkshopSurveyResponse::factory()->create(['workshop_survey_template_id' => $pre->id, 'workshop_session_id' => $session->id, 'contact_id' => $contactB->id, 'responses_json' => ['confidence' => 4]]);
        WorkshopSurveyResponse::factory()->create(['workshop_survey_template_id' => $post->id, 'workshop_session_id' => $session->id, 'contact_id' => $contactA->id, 'responses_json' => ['confidence' => 5]]);
        WorkshopSurveyResponse::factory()->create(['workshop_survey_template_id' => $post->id, 'workshop_session_id' => $session->id, 'contact_id' => $contactB->id, 'responses_json' => ['confidence' => 5]]);

        $summary = $this->service->build($workshop->fresh());

        $this->assertSame(3.0, $summary['pre_avg']);
        $this->assertSame(5.0, $summary['post_avg']);
        $this->assertSame(2.0, $summary['per_session'][0]['delta']);
    }

    public function test_top_partners_are_ranked_by_participant_count(): void
    {
        $workshop = Workshop::factory()->create();
        $session = WorkshopSession::factory()->create(['workshop_id' => $workshop->id]);
        $bigPartner = Partner::factory()->create(['name' => 'Big Partner']);
        $smallPartner = Partner::factory()->create(['name' => 'Small Partner']);

        foreach (range(1, 3) as $i) {
            $contact = Contact::factory()->create(['partner_id' => $bigPartner->id]);
            WorkshopSessionParticipant::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $contact->id]);
        }
        $soloContact = Contact::factory()->create(['partner_id' => $smallPartner->id]);
        WorkshopSessionParticipant::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $soloContact->id]);

        $summary = $this->service->build($workshop->fresh());

        $this->assertSame('Big Partner', $summary['top_partners'][0]['name']);
        $this->assertSame(3, $summary['top_partners'][0]['count']);
        $this->assertSame(2, $summary['partner_firms_count']);
    }
}
