<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\User;
use App\Models\WorkshopSession;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class WorkshopReportTest extends TestCase
{
    use RefreshDatabase;

    private User $coe;

    private WorkshopSession $session;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        Feature::for(null)->activate('workshops');

        $this->coe = User::factory()->coe()->create();
        $this->partner = Partner::factory()->create();
        $this->session = WorkshopSession::factory()->create();

        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        $this->session->participants()->create([
            'contact_id' => $contact->id,
            'status' => 'active',
            'enrolled_at' => now(),
            'completed_at' => now(),
        ]);
    }

    public function test_exec_summary_renders_with_stats(): void
    {
        $response = $this->actingAs($this->coe)->get(route('workshops.reports.exec-summary', [$this->session->workshop, $this->session]));

        $response->assertOk();
        $response->assertSee($this->session->name);
    }

    public function test_slack_post_renders_formatted_text(): void
    {
        $response = $this->actingAs($this->coe)->get(route('workshops.reports.slack', [$this->session->workshop, $this->session]));

        $response->assertOk();
        $response->assertSee('just wrapped', false);
    }

    public function test_json_export_returns_expected_structure(): void
    {
        $response = $this->actingAs($this->coe)->getJson(route('workshops.reports.export-json', [$this->session->workshop, $this->session]));

        $response->assertOk();
        $response->assertJsonStructure(['workshop', 'session', 'totals', 'survey', 'discussion', 'by_partner', 'participants']);
        $response->assertJsonPath('totals.completed', 1);
    }

    public function test_partner_report_renders_and_is_scoped_to_that_partner(): void
    {
        $otherPartner = Partner::factory()->create();
        $otherContact = Contact::factory()->create(['partner_id' => $otherPartner->id]);
        $this->session->participants()->create(['contact_id' => $otherContact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $response = $this->actingAs($this->coe)->get(route('workshops.reports.partner', [$this->session->workshop, $this->session, $this->partner]));

        $response->assertOk();
        $response->assertSee($this->partner->name);
        $response->assertDontSee($otherContact->full_name);
    }

    public function test_partner_report_zip_downloads(): void
    {
        $response = $this->actingAs($this->coe)->get(route('workshops.reports.partner-zip', [$this->session->workshop, $this->session, $this->partner]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/zip');
    }

    public function test_session_leaderboard_renders(): void
    {
        $response = $this->actingAs($this->coe)->get(route('workshops.reports.leaderboard', [$this->session->workshop, $this->session]));

        $response->assertOk();
    }

    public function test_surveys_report_shows_likert_comparison_and_knowledge_pass_rate(): void
    {
        $workshop = $this->session->workshop;
        $pre = \App\Models\WorkshopSurveyTemplate::factory()->create([
            'workshop_id' => $workshop->id,
            'kind' => 'pre',
            'questions_json' => [['id' => 'q1', 'text' => 'How comfortable are you?', 'type' => 'likert']],
        ]);
        $post = \App\Models\WorkshopSurveyTemplate::factory()->create([
            'workshop_id' => $workshop->id,
            'kind' => 'post',
            'questions_json' => [['id' => 'q1', 'text' => 'How comfortable are you?', 'type' => 'likert']],
        ]);
        $knowledge = \App\Models\WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'knowledge']);

        $contactA = Contact::factory()->create(['partner_id' => $this->partner->id]);
        $contactB = Contact::factory()->create(['partner_id' => $this->partner->id]);
        $pre->responses()->create(['workshop_session_id' => $this->session->id, 'contact_id' => $contactA->id, 'responses_json' => ['q1' => 2], 'score' => 0, 'passed' => false, 'attempt_number' => 1, 'submitted_at' => now()]);
        $pre->responses()->create(['workshop_session_id' => $this->session->id, 'contact_id' => $contactB->id, 'responses_json' => ['q1' => 4], 'score' => 0, 'passed' => false, 'attempt_number' => 1, 'submitted_at' => now()]);
        $post->responses()->create(['workshop_session_id' => $this->session->id, 'contact_id' => $contactA->id, 'responses_json' => ['q1' => 5], 'score' => 0, 'passed' => false, 'attempt_number' => 1, 'submitted_at' => now()]);
        $knowledge->responses()->create(['workshop_session_id' => $this->session->id, 'contact_id' => $contactA->id, 'responses_json' => [], 'score' => 100, 'passed' => true, 'attempt_number' => 1, 'submitted_at' => now()]);
        $knowledge->responses()->create(['workshop_session_id' => $this->session->id, 'contact_id' => $contactB->id, 'responses_json' => [], 'score' => 0, 'passed' => false, 'attempt_number' => 1, 'submitted_at' => now()]);

        $response = $this->actingAs($this->coe)->get(route('workshops.reports.surveys', [$workshop, $this->session]));

        $response->assertOk();
        $response->assertSee('How comfortable are you?');
        $response->assertSee('50%'); // knowledge check pass rate: 1 of 2
        $response->assertSee('Very comfortable');
    }

    public function test_partners_report_ranks_by_all_three_surveys_done(): void
    {
        $workshop = $this->session->workshop;
        $pre = \App\Models\WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'pre']);
        $post = \App\Models\WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'post']);
        $knowledge = \App\Models\WorkshopSurveyTemplate::factory()->create(['workshop_id' => $workshop->id, 'kind' => 'knowledge']);

        $fullyDonePartner = Partner::factory()->create();
        $fullyDoneContact = Contact::factory()->create(['partner_id' => $fullyDonePartner->id]);
        $this->session->participants()->create(['contact_id' => $fullyDoneContact->id, 'status' => 'active', 'enrolled_at' => now()]);
        foreach ([$pre, $post, $knowledge] as $template) {
            $template->responses()->create([
                'workshop_session_id' => $this->session->id,
                'contact_id' => $fullyDoneContact->id,
                'responses_json' => [],
                'score' => 0,
                'passed' => false,
                'attempt_number' => 1,
                'submitted_at' => now(),
            ]);
        }

        // $this->partner already has one participant from setUp() with zero
        // survey responses, so it lands at 0% - a natural "not started" row.
        $response = $this->actingAs($this->coe)->get(route('workshops.reports.partners', [$workshop, $this->session]));

        $response->assertOk();
        $response->assertSeeInOrder([$fullyDonePartner->name, '100%', $this->partner->name, '0%']);
    }

    public function test_index_shows_all_three_done_rate_and_pre_post_delta(): void
    {
        $workshop = $this->session->workshop;
        $pre = \App\Models\WorkshopSurveyTemplate::factory()->create([
            'workshop_id' => $workshop->id,
            'kind' => 'pre',
            'questions_json' => [['id' => 'q1', 'text' => 'Comfort', 'type' => 'likert']],
        ]);
        $post = \App\Models\WorkshopSurveyTemplate::factory()->create([
            'workshop_id' => $workshop->id,
            'kind' => 'post',
            'questions_json' => [['id' => 'q1', 'text' => 'Comfort', 'type' => 'likert']],
        ]);

        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        $this->session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);
        $pre->responses()->create(['workshop_session_id' => $this->session->id, 'contact_id' => $contact->id, 'responses_json' => ['q1' => 2], 'score' => 0, 'passed' => false, 'attempt_number' => 1, 'submitted_at' => now()]);
        $post->responses()->create(['workshop_session_id' => $this->session->id, 'contact_id' => $contact->id, 'responses_json' => ['q1' => 5], 'score' => 0, 'passed' => false, 'attempt_number' => 1, 'submitted_at' => now()]);

        $response = $this->actingAs($this->coe)->get(route('workshops.reports.index', [$workshop, $this->session]));

        $response->assertOk();
        $response->assertSee('+3');
    }
}
