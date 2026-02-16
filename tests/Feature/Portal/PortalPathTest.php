<?php

namespace Tests\Feature\Portal;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\PartnerPath;
use App\Models\PartnerPathEnrollment;
use App\Models\PartnerPathStage;
use App\Models\PartnerStageHistory;
use App\Models\PartnerTask;
use App\Models\User;
use App\Services\PartnerHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalPathTest extends TestCase
{
    use RefreshDatabase;

    private User $psm;

    private Partner $partner;

    private PartnerPath $path;

    /** @var \Illuminate\Support\Collection<int, PartnerPathStage> */
    private $stages;

    protected function setUp(): void
    {
        parent::setUp();

        $this->psm = User::factory()->create(['role' => 'psm']);
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);

        $this->path = PartnerPath::factory()->create();
        $this->stages = collect();

        foreach (['Onboarding', 'Enabled', 'Scaled', 'Premier'] as $i => $name) {
            $this->stages->push(PartnerPathStage::factory()->create([
                'partner_path_id' => $this->path->id,
                'stage_number' => $i + 1,
                'name' => $name,
                'color' => '#'.dechex(rand(0x100000, 0xFFFFFF)),
                'sort_order' => $i + 1,
            ]));
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function createPortalContact(string $role = 'admin', ?Partner $partner = null): Contact
    {
        $partner = $partner ?? $this->partner;

        return Contact::factory()->{'portal'.ucfirst($role)}()->create([
            'partner_id' => $partner->id,
        ]);
    }

    private function createEnrollment(int $stageIndex = 1, int $daysInStage = 15): PartnerPathEnrollment
    {
        return PartnerPathEnrollment::factory()->create([
            'partner_id' => $this->partner->id,
            'partner_path_id' => $this->path->id,
            'current_stage_id' => $this->stages[$stageIndex]->id,
            'enrolled_at' => now()->subDays(60),
            'stage_entered_at' => now()->subDays($daysInStage),
            'status' => 'active',
        ]);
    }

    // ─── Portal Path Page ─────────────────────────────────────────

    public function test_portal_admin_can_view_path_page_with_enrollment(): void
    {
        $contact = $this->createPortalContact('admin');
        $enrollment = $this->createEnrollment(stageIndex: 1, daysInStage: 20);

        PartnerTask::factory()->create([
            'partner_id' => $this->partner->id,
            'partner_path_enrollment_id' => $enrollment->id,
            'stage_id' => $this->stages[1]->id,
            'requirement_level' => 'required',
            'status' => 'completed',
        ]);
        PartnerTask::factory()->create([
            'partner_id' => $this->partner->id,
            'partner_path_enrollment_id' => $enrollment->id,
            'stage_id' => $this->stages[1]->id,
            'requirement_level' => 'required',
            'status' => 'not_started',
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.path.index'));

        $response->assertOk();
        $response->assertViewIs('portal.path.index');
        $response->assertViewHas('enrollment');
        $response->assertViewHas('stages');
        $response->assertViewHas('currentStageNumber', 2);
        $response->assertViewHas('totalStages', 4);
        $response->assertViewHas('daysInStage', 20);
        $response->assertViewHas('requiredTotal', 2);
        $response->assertViewHas('requiredCompleted', 1);
        $response->assertSee('Enabled');
        $response->assertSee('Stage Progress');
    }

    public function test_portal_admin_sees_empty_state_without_enrollment(): void
    {
        $contact = $this->createPortalContact('admin');

        $response = $this->actingAs($contact, 'portal')->get(route('portal.path.index'));

        $response->assertOk();
        $response->assertViewHas('enrollment', null);
        $response->assertSee('No Active Partner Path');
    }

    public function test_portal_sales_role_cannot_access_path_page(): void
    {
        $contact = $this->createPortalContact('sales');

        $response = $this->actingAs($contact, 'portal')->get(route('portal.path.index'));

        $response->assertForbidden();
    }

    public function test_portal_marketing_role_cannot_access_path_page(): void
    {
        $contact = Contact::factory()->portalRole('marketing')->create([
            'partner_id' => $this->partner->id,
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.path.index'));

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get(route('portal.path.index'));

        $response->assertRedirect();
    }

    public function test_overdue_tasks_are_counted_correctly(): void
    {
        $contact = $this->createPortalContact('admin');
        $enrollment = $this->createEnrollment(stageIndex: 0, daysInStage: 30);

        // 2 overdue tasks
        PartnerTask::factory()->overdue()->create([
            'partner_id' => $this->partner->id,
            'partner_path_enrollment_id' => $enrollment->id,
            'stage_id' => $this->stages[0]->id,
        ]);
        PartnerTask::factory()->overdue()->create([
            'partner_id' => $this->partner->id,
            'partner_path_enrollment_id' => $enrollment->id,
            'stage_id' => $this->stages[0]->id,
        ]);
        // 1 completed (not overdue even with past due date)
        PartnerTask::factory()->completed()->create([
            'partner_id' => $this->partner->id,
            'partner_path_enrollment_id' => $enrollment->id,
            'stage_id' => $this->stages[0]->id,
            'due_date' => now()->subDays(3),
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.path.index'));

        $response->assertOk();
        $response->assertViewHas('overdueCount', 2);
    }

    public function test_stage_history_is_displayed(): void
    {
        $contact = $this->createPortalContact('admin');
        $this->createEnrollment(stageIndex: 1, daysInStage: 10);

        PartnerStageHistory::factory()->create([
            'partner_id' => $this->partner->id,
            'partner_path_id' => $this->path->id,
            'from_stage_id' => $this->stages[0]->id,
            'to_stage_id' => $this->stages[1]->id,
            'transitioned_at' => now()->subDays(10),
            'approved_by' => $this->psm->id,
            'notes' => 'All gates met',
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.path.index'));

        $response->assertOk();
        $response->assertSee('Stage History');
        $response->assertSee('All gates met');
    }

    public function test_required_completion_percentage_computed(): void
    {
        $contact = $this->createPortalContact('admin');
        $enrollment = $this->createEnrollment(stageIndex: 0, daysInStage: 10);

        // 3 required: 2 completed, 1 not
        PartnerTask::factory()->completed()->create([
            'partner_id' => $this->partner->id,
            'partner_path_enrollment_id' => $enrollment->id,
            'stage_id' => $this->stages[0]->id,
            'requirement_level' => 'required',
        ]);
        PartnerTask::factory()->completed()->create([
            'partner_id' => $this->partner->id,
            'partner_path_enrollment_id' => $enrollment->id,
            'stage_id' => $this->stages[0]->id,
            'requirement_level' => 'required',
        ]);
        PartnerTask::factory()->create([
            'partner_id' => $this->partner->id,
            'partner_path_enrollment_id' => $enrollment->id,
            'stage_id' => $this->stages[0]->id,
            'requirement_level' => 'required',
            'status' => 'in_progress',
        ]);
        // 1 optional completed — should not affect required stats
        PartnerTask::factory()->optional()->completed()->create([
            'partner_id' => $this->partner->id,
            'partner_path_enrollment_id' => $enrollment->id,
            'stage_id' => $this->stages[0]->id,
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.path.index'));

        $response->assertOk();
        $response->assertViewHas('requiredTotal', 3);
        $response->assertViewHas('requiredCompleted', 2);
        $response->assertViewHas('requiredCompletionPct', 67);
    }

    // ─── Health Service Risk Signals ──────────────────────────────

    public function test_health_signal_no_active_enrollment(): void
    {
        $service = app(PartnerHealthService::class);
        $result = $service->computeForPartner($this->partner);

        $this->assertContains('No active partner path enrollment', $result['signals']);
    }

    public function test_health_signal_stalled_in_stage(): void
    {
        $this->createEnrollment(stageIndex: 1, daysInStage: 120);

        $service = app(PartnerHealthService::class);
        $result = $service->computeForPartner($this->partner);

        $stalledSignal = collect($result['signals'])->first(fn ($s) => str_contains($s, 'days without advancement'));
        $this->assertNotNull($stalledSignal);
        $this->assertStringContainsString('Enabled', $stalledSignal);
    }

    public function test_health_signal_overdue_required_tasks(): void
    {
        $enrollment = $this->createEnrollment(stageIndex: 0, daysInStage: 30);

        PartnerTask::factory()->overdue()->count(3)->create([
            'partner_id' => $this->partner->id,
            'partner_path_enrollment_id' => $enrollment->id,
            'stage_id' => $this->stages[0]->id,
            'requirement_level' => 'required',
        ]);

        $service = app(PartnerHealthService::class);
        $result = $service->computeForPartner($this->partner);

        $overdueSignal = collect($result['signals'])->first(fn ($s) => str_contains($s, 'overdue required path task'));
        $this->assertNotNull($overdueSignal);
        $this->assertStringContainsString('3', $overdueSignal);
    }

    public function test_health_signal_not_generated_for_healthy_enrollment(): void
    {
        $this->createEnrollment(stageIndex: 0, daysInStage: 10);

        $service = app(PartnerHealthService::class);
        $result = $service->computeForPartner($this->partner);

        $this->assertNotContains('No active partner path enrollment', $result['signals']);
        $this->assertEmpty(collect($result['signals'])->filter(fn ($s) => str_contains($s, 'days without advancement')));
        $this->assertEmpty(collect($result['signals'])->filter(fn ($s) => str_contains($s, 'overdue required path task')));
    }
}
