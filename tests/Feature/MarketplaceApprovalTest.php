<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\PartnerPath;
use App\Models\PartnerPathEnrollment;
use App\Models\PartnerPathStage;
use App\Models\PartnerTask;
use App\Models\TaskTemplate;
use App\Models\User;
use App\Services\TaskAutoVerificationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class MarketplaceApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        Feature::for(null)->activate('deal_registration');
        Feature::for(null)->activate('partner_management');

        $this->admin = User::factory()->admin()->create();
        $this->psm = User::factory()->psm()->create();
        $this->partner = Partner::factory()->create([
            'user_id' => $this->psm->id,
            'marketplace_status' => 'submitted',
            'headline' => 'Great Partner',
            'company_description' => 'We deliver solutions.',
        ]);
    }

    // ─── Approve ─────────────────────────────────────────────────

    public function test_admin_can_approve_marketplace_profile(): void
    {
        $this->actingAs($this->admin)
            ->put(route('partners.marketplace-review', $this->partner), [
                'action' => 'approve',
            ])
            ->assertRedirect();

        $partner = $this->partner->fresh();
        $this->assertEquals('approved', $partner->marketplace_status);
        $this->assertEquals($this->admin->id, $partner->marketplace_reviewed_by);
        $this->assertNotNull($partner->marketplace_reviewed_at);
        $this->assertNull($partner->marketplace_rejection_reason);
    }

    public function test_psm_can_approve_marketplace_profile(): void
    {
        $this->actingAs($this->psm)
            ->put(route('partners.marketplace-review', $this->partner), [
                'action' => 'approve',
            ])
            ->assertRedirect();

        $this->assertEquals('approved', $this->partner->fresh()->marketplace_status);
    }

    // ─── Reject ──────────────────────────────────────────────────

    public function test_admin_can_reject_marketplace_profile(): void
    {
        $this->actingAs($this->admin)
            ->put(route('partners.marketplace-review', $this->partner), [
                'action' => 'reject',
                'rejection_reason' => 'Please add more detail to your company description.',
            ])
            ->assertRedirect();

        $partner = $this->partner->fresh();
        $this->assertEquals('rejected', $partner->marketplace_status);
        $this->assertEquals('Please add more detail to your company description.', $partner->marketplace_rejection_reason);
        $this->assertEquals($this->admin->id, $partner->marketplace_reviewed_by);
        $this->assertNotNull($partner->marketplace_reviewed_at);
    }

    public function test_rejection_requires_reason(): void
    {
        $this->actingAs($this->admin)
            ->put(route('partners.marketplace-review', $this->partner), [
                'action' => 'reject',
            ])
            ->assertSessionHasErrors('rejection_reason');
    }

    public function test_action_must_be_approve_or_reject(): void
    {
        $this->actingAs($this->admin)
            ->put(route('partners.marketplace-review', $this->partner), [
                'action' => 'invalid',
            ])
            ->assertSessionHasErrors('action');
    }

    // ─── Approval clears previous rejection ──────────────────────

    public function test_approving_previously_rejected_clears_rejection_reason(): void
    {
        $this->partner->update([
            'marketplace_status' => 'submitted',
            'marketplace_rejection_reason' => 'Old rejection reason.',
        ]);

        $this->actingAs($this->admin)
            ->put(route('partners.marketplace-review', $this->partner), [
                'action' => 'approve',
            ])
            ->assertRedirect();

        $partner = $this->partner->fresh();
        $this->assertEquals('approved', $partner->marketplace_status);
        $this->assertNull($partner->marketplace_rejection_reason);
    }

    // ─── Task Auto-Completion ──────────────────────────────────

    public function test_approving_marketplace_auto_completes_partner_task(): void
    {
        $path = PartnerPath::factory()->create(['is_default' => true]);
        $stage = PartnerPathStage::factory()->create(['partner_path_id' => $path->id, 'stage_number' => 1]);
        $enrollment = PartnerPathEnrollment::factory()->create([
            'partner_id' => $this->partner->id,
            'partner_path_id' => $path->id,
            'current_stage_id' => $stage->id,
        ]);
        $template = TaskTemplate::create([
            'partner_path_id' => $path->id,
            'stage_id' => $stage->id,
            'category' => 'account',
            'requirement_level' => 'required',
            'title' => 'Complete Marketplace Profile',
            'description' => 'Submit marketplace profile for approval.',
            'effort_size' => 'medium',
            'default_owner' => 'joint',
            'auto_verify_rule' => "partner.marketplace_status = 'approved'",
            'evidence_type' => 'system_verified',
            'sort_order' => 0,
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $task = PartnerTask::create([
            'partner_id' => $this->partner->id,
            'task_template_id' => $template->id,
            'title' => $template->title,
            'description' => $template->description,
            'category' => 'account',
            'requirement_level' => 'required',
            'status' => 'not_started',
            'priority' => 'medium',
            'effort_size' => 'medium',
            'owner' => 'joint',
            'assigned_to_user_id' => $this->psm->id,
            'stage_id' => $stage->id,
            'partner_path_enrollment_id' => $enrollment->id,
        ]);

        $this->actingAs($this->admin)
            ->put(route('partners.marketplace-review', $this->partner), [
                'action' => 'approve',
            ])
            ->assertRedirect();

        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertTrue($task->auto_verified);
        $this->assertNotNull($task->auto_verified_at);
        $this->assertNotNull($task->completed_at);
    }

    public function test_rejecting_marketplace_does_not_complete_partner_task(): void
    {
        $path = PartnerPath::factory()->create(['is_default' => true]);
        $stage = PartnerPathStage::factory()->create(['partner_path_id' => $path->id, 'stage_number' => 1]);
        $enrollment = PartnerPathEnrollment::factory()->create([
            'partner_id' => $this->partner->id,
            'partner_path_id' => $path->id,
            'current_stage_id' => $stage->id,
        ]);
        $template = TaskTemplate::create([
            'partner_path_id' => $path->id,
            'stage_id' => $stage->id,
            'category' => 'account',
            'requirement_level' => 'required',
            'title' => 'Complete Marketplace Profile',
            'description' => 'Submit marketplace profile for approval.',
            'effort_size' => 'medium',
            'default_owner' => 'joint',
            'auto_verify_rule' => "partner.marketplace_status = 'approved'",
            'evidence_type' => 'system_verified',
            'sort_order' => 0,
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $task = PartnerTask::create([
            'partner_id' => $this->partner->id,
            'task_template_id' => $template->id,
            'title' => $template->title,
            'description' => $template->description,
            'category' => 'account',
            'requirement_level' => 'required',
            'status' => 'not_started',
            'priority' => 'medium',
            'effort_size' => 'medium',
            'owner' => 'joint',
            'assigned_to_user_id' => $this->psm->id,
            'stage_id' => $stage->id,
            'partner_path_enrollment_id' => $enrollment->id,
        ]);

        $this->actingAs($this->admin)
            ->put(route('partners.marketplace-review', $this->partner), [
                'action' => 'reject',
                'rejection_reason' => 'Needs more detail.',
            ])
            ->assertRedirect();

        $task->refresh();
        $this->assertEquals('not_started', $task->status);
        $this->assertFalse($task->auto_verified);
    }

    // ─── TaskAutoVerificationService Unit Tests ─────────────────

    public function test_evaluate_partner_field_equality_rule(): void
    {
        $service = app(TaskAutoVerificationService::class);
        $partner = Partner::factory()->create(['marketplace_status' => 'approved']);

        $this->assertTrue($service->evaluate($partner, "partner.marketplace_status = 'approved'"));
        $this->assertFalse($service->evaluate($partner, "partner.marketplace_status = 'submitted'"));
    }

    public function test_verify_for_partner_only_processes_that_partner(): void
    {
        $service = app(TaskAutoVerificationService::class);

        $path = PartnerPath::factory()->create(['is_default' => true]);
        $stage = PartnerPathStage::factory()->create(['partner_path_id' => $path->id, 'stage_number' => 1]);

        $template = TaskTemplate::create([
            'partner_path_id' => $path->id,
            'stage_id' => $stage->id,
            'category' => 'account',
            'requirement_level' => 'required',
            'title' => 'Complete Marketplace Profile',
            'description' => 'Test',
            'effort_size' => 'medium',
            'default_owner' => 'joint',
            'auto_verify_rule' => "partner.marketplace_status = 'approved'",
            'evidence_type' => 'system_verified',
            'sort_order' => 0,
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        // Partner A: approved — task should complete
        $partnerA = Partner::factory()->create(['marketplace_status' => 'approved']);
        $enrollmentA = PartnerPathEnrollment::factory()->create([
            'partner_id' => $partnerA->id,
            'partner_path_id' => $path->id,
            'current_stage_id' => $stage->id,
        ]);
        $taskA = PartnerTask::create([
            'partner_id' => $partnerA->id,
            'task_template_id' => $template->id,
            'title' => $template->title,
            'description' => $template->description,
            'category' => 'account',
            'requirement_level' => 'required',
            'status' => 'not_started',
            'priority' => 'medium',
            'effort_size' => 'medium',
            'owner' => 'joint',
            'assigned_to_user_id' => $this->psm->id,
            'stage_id' => $stage->id,
            'partner_path_enrollment_id' => $enrollmentA->id,
        ]);

        // Partner B: draft — task should NOT be touched
        $partnerB = Partner::factory()->create(['marketplace_status' => 'draft']);
        $enrollmentB = PartnerPathEnrollment::factory()->create([
            'partner_id' => $partnerB->id,
            'partner_path_id' => $path->id,
            'current_stage_id' => $stage->id,
        ]);
        $taskB = PartnerTask::create([
            'partner_id' => $partnerB->id,
            'task_template_id' => $template->id,
            'title' => $template->title,
            'description' => $template->description,
            'category' => 'account',
            'requirement_level' => 'required',
            'status' => 'not_started',
            'priority' => 'medium',
            'effort_size' => 'medium',
            'owner' => 'joint',
            'assigned_to_user_id' => $this->psm->id,
            'stage_id' => $stage->id,
            'partner_path_enrollment_id' => $enrollmentB->id,
        ]);

        $verified = $service->verifyForPartner($partnerA);

        $this->assertEquals(1, $verified);
        $this->assertEquals('completed', $taskA->fresh()->status);
        $this->assertEquals('not_started', $taskB->fresh()->status);
    }

    // ─── Show Page ──────────────────────────────────────────────

    public function test_marketplace_submission_show_page_loads(): void
    {
        $this->actingAs($this->admin)
            ->get(route('engage.marketplace-submissions.show', $this->partner))
            ->assertOk()
            ->assertSeeText($this->partner->name)
            ->assertSeeText('Great Partner');
    }

    public function test_show_page_displays_review_actions_when_submitted(): void
    {
        $this->actingAs($this->admin)
            ->get(route('engage.marketplace-submissions.show', $this->partner))
            ->assertOk()
            ->assertSeeText('Review Actions')
            ->assertSeeText('Approve Profile');
    }

    public function test_show_page_hides_review_actions_when_approved(): void
    {
        $this->partner->update(['marketplace_status' => 'approved']);

        $this->actingAs($this->admin)
            ->get(route('engage.marketplace-submissions.show', $this->partner))
            ->assertOk()
            ->assertDontSeeText('Review Actions');
    }

    // ─── Unauthenticated ─────────────────────────────────────────

    public function test_unauthenticated_user_cannot_review_marketplace(): void
    {
        $this->put(route('partners.marketplace-review', $this->partner), [
            'action' => 'approve',
        ])->assertRedirect(route('login'));
    }
}
