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

    public function test_checkpoints_include_pre_labs_post_and_knowledge_check_in_order(): void
    {
        $workshop = Workshop::factory()->create();
        WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'pre', 'questions_json' => [['id' => 'q1', 'text' => 'Feedback?', 'type' => 'text']]]);
        WorkshopPage::factory()->create(['workshop_id' => $workshop->id, 'audience' => 'participant', 'checks_json' => [['id' => 'c1', 'label' => 'Check 1']]]);
        WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'post', 'questions_json' => [['id' => 'q1', 'text' => 'Feedback?', 'type' => 'text']]]);
        WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'knowledge', 'passing_score' => 70]);

        $checkpoints = $this->service->checkpointsFor($workshop->fresh());

        $this->assertSame(['survey_pre', 'lab_'.WorkshopPage::first()->id.'_c1', 'survey_post', 'survey_knowledge'], array_column($checkpoints, 'key'));
        $this->assertSame('Post-Workshop Survey', $checkpoints[2]['label']);
        $this->assertSame('Knowledge Check', $checkpoints[3]['label']);
    }

    public function test_empty_templates_do_not_produce_checkpoints(): void
    {
        $workshop = Workshop::factory()->create();
        WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'pre', 'questions_json' => []]);

        $checkpoints = $this->service->checkpointsFor($workshop->fresh());

        $this->assertEmpty($checkpoints);
    }

    public function test_each_lab_check_is_its_own_independent_checkpoint(): void
    {
        $session = WorkshopSession::factory()->create();
        $contact = Contact::factory()->create();
        $page = WorkshopPage::factory()->create([
            'workshop_id' => $session->workshop_id,
            'checks_json' => [['id' => 'ex1-step1-renames', 'label' => 'One'], ['id' => 'ex1-step2-dimensions', 'label' => 'Two']],
        ]);
        WorkshopSessionParticipant::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $contact->id]);
        WorkshopCheckAttempt::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $contact->id, 'workshop_page_id' => $page->id, 'check_id' => 'ex1-step1-renames', 'passed' => true]);
        WorkshopCheckAttempt::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $contact->id, 'workshop_page_id' => $page->id, 'check_id' => 'ex1-step2-dimensions', 'passed' => false]);

        $checkpoints = $this->service->checkpointsFor($session->workshop);
        $this->assertSame('EX1: RENAMES', $checkpoints[0]['label']);
        $this->assertSame('EX1: DIMENSIONS', $checkpoints[1]['label']);

        $participants = $session->participants()->with('contact')->get();
        [$responses, $attempts] = $this->service->loadDataForSession($session->id);
        $rows = collect($this->service->rowsFor($participants, $checkpoints, $responses, $attempts))->keyBy(fn ($r) => $r['contact']->id);

        $this->assertSame('done', $rows[$contact->id]['statuses']['lab_'.$page->id.'_ex1-step1-renames']);
        $this->assertSame('failed', $rows[$contact->id]['statuses']['lab_'.$page->id.'_ex1-step2-dimensions']);
    }

    public function test_scored_survey_status_reflects_pass_fail_and_not_attempted(): void
    {
        $session = WorkshopSession::factory()->create();
        $template = WorkshopSurveyTemplate::factory()->create(['workshop_id' => $session->workshop_id, 'kind' => 'knowledge', 'passing_score' => 70]);
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

        $this->assertSame('done', $rows[$passer->id]['statuses']['survey_knowledge']);
        $this->assertSame('partial', $rows[$failer->id]['statuses']['survey_knowledge']);
        $this->assertSame('todo', $rows[$untouched->id]['statuses']['survey_knowledge']);
    }

    public function test_survey_status_is_failed_once_the_attempt_limit_is_used_up(): void
    {
        $session = WorkshopSession::factory()->create();
        $template = WorkshopSurveyTemplate::factory()->create(['workshop_id' => $session->workshop_id, 'kind' => 'knowledge', 'passing_score' => 70, 'max_attempts' => 1]);
        $contact = Contact::factory()->create();
        WorkshopSessionParticipant::factory()->create(['workshop_session_id' => $session->id, 'contact_id' => $contact->id]);
        WorkshopSurveyResponse::factory()->create(['workshop_survey_template_id' => $template->id, 'workshop_session_id' => $session->id, 'contact_id' => $contact->id, 'passed' => false, 'attempt_number' => 1]);

        $checkpoints = $this->service->checkpointsFor($session->workshop);
        $participants = $session->participants()->with('contact')->get();
        [$responses, $attempts] = $this->service->loadDataForSession($session->id);
        $rows = collect($this->service->rowsFor($participants, $checkpoints, $responses, $attempts))->keyBy(fn ($r) => $r['contact']->id);

        $this->assertSame('failed', $rows[$contact->id]['statuses']['survey_knowledge']);
    }
}
