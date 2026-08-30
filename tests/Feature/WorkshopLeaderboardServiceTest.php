<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Workshop;
use App\Models\WorkshopCheckAttempt;
use App\Models\WorkshopPage;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionParticipant;
use App\Models\WorkshopSurveyResponse;
use App\Models\WorkshopSurveyTemplate;
use App\Services\WorkshopLeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopLeaderboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkshopLeaderboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WorkshopLeaderboardService::class);
    }

    public function test_checkpoints_include_labs_and_surveys_in_order(): void
    {
        $workshop = Workshop::factory()->create();
        WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'pre', 'questions_json' => [['id' => 'q1', 'text' => 'Feedback?', 'type' => 'text']]]);
        WorkshopPage::factory()->create(['workshop_id' => $workshop->id, 'audience' => 'participant', 'checks_json' => [['id' => 'c1', 'label' => 'Check 1']]]);
        WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'post', 'passing_score' => 70]);

        $checkpoints = $this->service->checkpointsFor($workshop->fresh());

        $this->assertSame(['survey_pre', 'lab_'.WorkshopPage::first()->id, 'survey_post'], array_column($checkpoints, 'key'));
        $this->assertSame('Knowledge Check', $checkpoints[2]['label']);
    }

    public function test_unscored_post_survey_is_labeled_post_workshop_survey(): void
    {
        $workshop = Workshop::factory()->create();
        WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'post', 'questions_json' => [['id' => 'q1', 'text' => 'Feedback?', 'type' => 'text']]]);

        $checkpoints = $this->service->checkpointsFor($workshop->fresh());

        $this->assertSame('Post-Workshop Survey', $checkpoints[0]['label']);
    }

    public function test_lab_status_is_done_only_once_every_check_on_the_page_is_passed(): void
    {
        $session = WorkshopSession::factory()->create();
        $contact = Contact::factory()->create();
        $page = WorkshopPage::factory()->create([
            'workshop_id' => $session->workshop_id,
            'checks_json' => [['id' => 'c1', 'label' => 'One'], ['id' => 'c2', 'label' => 'Two']],
        ]);
        WorkshopSessionParticipant::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $contact->id]);
        WorkshopCheckAttempt::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $contact->id, 'workshop_page_id' => $page->id, 'check_id' => 'c1', 'passed' => true]);

        $checkpoints = $this->service->checkpointsFor($session->workshop);
        $participants = $session->participants()->with('contact')->get();
        [$responses, $attempts] = $this->service->loadDataForSession($session->id);
        $rows = collect($this->service->rowsFor($participants, $checkpoints, $responses, $attempts))->keyBy(fn ($r) => $r['contact']->id);

        $this->assertSame('partial', $rows[$contact->id]['statuses']['lab_'.$page->id]);

        WorkshopCheckAttempt::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $contact->id, 'workshop_page_id' => $page->id, 'check_id' => 'c2', 'passed' => true]);
        [$responses, $attempts] = $this->service->loadDataForSession($session->id);
        $rows = collect($this->service->rowsFor($participants, $checkpoints, $responses, $attempts))->keyBy(fn ($r) => $r['contact']->id);

        $this->assertSame('done', $rows[$contact->id]['statuses']['lab_'.$page->id]);
    }

    public function test_scored_survey_status_reflects_pass_fail_and_not_attempted(): void
    {
        $session = WorkshopSession::factory()->create();
        $template = WorkshopSurveyTemplate::factory()->create(['workshop_id' => $session->workshop_id, 'kind' => 'post', 'passing_score' => 70]);
        $passer = Contact::factory()->create();
        $failer = Contact::factory()->create();
        $untouched = Contact::factory()->create();
        foreach ([$passer, $failer, $untouched] as $contact) {
            WorkshopSessionParticipant::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $contact->id]);
        }
        WorkshopSurveyResponse::factory()->create(['workshop_survey_template_id' => $template->id, 'workshop_session_id' => $session->id, 'contact_id' => $passer->id, 'passed' => true]);
        WorkshopSurveyResponse::factory()->create(['workshop_survey_template_id' => $template->id, 'workshop_session_id' => $session->id, 'contact_id' => $failer->id, 'passed' => false]);

        $checkpoints = $this->service->checkpointsFor($session->workshop);
        $participants = $session->participants()->with('contact')->get();
        [$responses, $attempts] = $this->service->loadDataForSession($session->id);
        $rows = collect($this->service->rowsFor($participants, $checkpoints, $responses, $attempts))->keyBy(fn ($r) => $r['contact']->id);

        $this->assertSame('done', $rows[$passer->id]['statuses']['survey_post']);
        $this->assertSame('partial', $rows[$failer->id]['statuses']['survey_post']);
        $this->assertSame('todo', $rows[$untouched->id]['statuses']['survey_post']);
    }
}
