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
        $response->assertSee('invited', false);
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
}
