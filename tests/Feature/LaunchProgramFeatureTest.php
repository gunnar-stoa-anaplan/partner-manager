<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\LaunchProgram;
use App\Models\LaunchProgramMilestone;
use App\Models\LaunchProgramMilestoneCompletion;
use App\Models\LaunchProgramParticipant;
use App\Models\LaunchProgramPartner;
use App\Models\Partner;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaunchProgramFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->psm = User::factory()->create(['role' => 'psm']);
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function createProgram(string $status = 'draft', ?User $creator = null): LaunchProgram
    {
        return LaunchProgram::factory()->{$status}()->create([
            'created_by' => $creator?->id ?? $this->admin->id,
        ]);
    }

    private function createEnrollment(LaunchProgram $program, ?Partner $partner = null, string $status = 'nominated'): LaunchProgramPartner
    {
        return LaunchProgramPartner::factory()->create([
            'launch_program_id' => $program->id,
            'partner_id' => ($partner ?? $this->partner)->id,
            'status' => $status,
            'nominated_by' => $this->psm->id,
        ]);
    }

    private function createPortalContact(string $role = 'admin', ?Partner $partner = null): Contact
    {
        $partner = $partner ?? $this->partner;

        return Contact::factory()->{'portal'.ucfirst($role)}()->create([
            'partner_id' => $partner->id,
        ]);
    }

    private function validProgramData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Launch Program',
            'description' => 'A test program.',
            'status' => 'draft',
            'starts_at' => now()->addDays(7)->toDateString(),
            'ends_at' => now()->addMonths(6)->toDateString(),
        ], $overrides);
    }

    // ─── Admin CRUD ───────────────────────────────────────────────

    public function test_admin_can_view_program_index(): void
    {
        LaunchProgram::factory()->count(3)->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.launch-programs.index'));

        $response->assertOk();
        $response->assertViewIs('admin.launch-programs.index');
        $response->assertViewHas('programs');
    }

    public function test_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.launch-programs.create'));

        $response->assertOk();
        $response->assertViewIs('admin.launch-programs.create');
        $response->assertViewHas('products');
    }

    public function test_admin_can_store_program(): void
    {
        $product = Product::factory()->create();
        $data = $this->validProgramData(['product_id' => $product->id]);

        $response = $this->actingAs($this->admin)->post(route('admin.launch-programs.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('launch_programs', ['name' => 'Test Launch Program']);
    }

    public function test_store_generates_slug(): void
    {
        $data = $this->validProgramData(['name' => 'Polaris Partner Launch']);

        $this->actingAs($this->admin)->post(route('admin.launch-programs.store'), $data);

        $this->assertDatabaseHas('launch_programs', ['slug' => 'polaris-partner-launch']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.launch-programs.store'), []);

        $response->assertSessionHasErrors(['name', 'status']);
    }

    public function test_store_validates_invalid_status(): void
    {
        $data = $this->validProgramData(['status' => 'invalid']);

        $response = $this->actingAs($this->admin)->post(route('admin.launch-programs.store'), $data);

        $response->assertSessionHasErrors('status');
    }

    public function test_admin_can_view_program_show(): void
    {
        $program = $this->createProgram('active');

        $response = $this->actingAs($this->admin)->get(route('admin.launch-programs.show', $program));

        $response->assertOk();
        $response->assertViewIs('admin.launch-programs.show');
        $response->assertViewHas('program');
    }

    public function test_admin_can_view_edit_form(): void
    {
        $program = $this->createProgram();

        $response = $this->actingAs($this->admin)->get(route('admin.launch-programs.edit', $program));

        $response->assertOk();
        $response->assertViewIs('admin.launch-programs.edit');
    }

    public function test_admin_can_update_program(): void
    {
        $program = $this->createProgram();

        $response = $this->actingAs($this->admin)->put(route('admin.launch-programs.update', $program), $this->validProgramData([
            'name' => 'Updated Name',
            'status' => 'accepting_nominations',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('launch_programs', ['id' => $program->id, 'name' => 'Updated Name']);
    }

    public function test_admin_can_delete_draft_program(): void
    {
        $program = $this->createProgram('draft');

        $response = $this->actingAs($this->admin)->delete(route('admin.launch-programs.destroy', $program));

        $response->assertRedirect(route('admin.launch-programs.index'));
        $this->assertDatabaseMissing('launch_programs', ['id' => $program->id]);
    }

    public function test_admin_cannot_delete_non_draft_program(): void
    {
        $program = $this->createProgram('active');

        $response = $this->actingAs($this->admin)->delete(route('admin.launch-programs.destroy', $program));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('launch_programs', ['id' => $program->id]);
    }

    public function test_psm_cannot_access_admin_crud(): void
    {
        $response = $this->actingAs($this->psm)->get(route('admin.launch-programs.index'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_admin_crud(): void
    {
        $response = $this->get(route('admin.launch-programs.index'));

        $response->assertRedirect();
    }

    // ─── Milestone Management ─────────────────────────────────────

    public function test_admin_can_add_milestone(): void
    {
        $program = $this->createProgram();

        $response = $this->actingAs($this->admin)->post(route('admin.launch-programs.milestones.store', $program), [
            'title' => 'Complete Training',
            'type' => 'task',
            'is_required' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('launch_program_milestones', [
            'launch_program_id' => $program->id,
            'title' => 'Complete Training',
        ]);
    }

    public function test_milestone_auto_increments_sort_order(): void
    {
        $program = $this->createProgram();
        LaunchProgramMilestone::factory()->create([
            'launch_program_id' => $program->id,
            'sort_order' => 3,
        ]);

        $this->actingAs($this->admin)->post(route('admin.launch-programs.milestones.store', $program), [
            'title' => 'Second Milestone',
            'type' => 'document',
            'is_required' => false,
        ]);

        $this->assertDatabaseHas('launch_program_milestones', [
            'title' => 'Second Milestone',
            'sort_order' => 4,
        ]);
    }

    public function test_admin_can_update_milestone(): void
    {
        $milestone = LaunchProgramMilestone::factory()->create([
            'launch_program_id' => $this->createProgram()->id,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.launch-program-milestones.update', $milestone), [
            'title' => 'Updated Title',
            'type' => 'enablement',
            'is_required' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('launch_program_milestones', ['id' => $milestone->id, 'title' => 'Updated Title']);
    }

    public function test_admin_can_delete_milestone(): void
    {
        $milestone = LaunchProgramMilestone::factory()->create([
            'launch_program_id' => $this->createProgram()->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.launch-program-milestones.destroy', $milestone));

        $response->assertRedirect();
        $this->assertDatabaseMissing('launch_program_milestones', ['id' => $milestone->id]);
    }

    public function test_milestone_validates_required_fields(): void
    {
        $program = $this->createProgram();

        $response = $this->actingAs($this->admin)->post(route('admin.launch-programs.milestones.store', $program), []);

        $response->assertSessionHasErrors(['title', 'type']);
    }

    public function test_milestone_validates_invalid_type(): void
    {
        $program = $this->createProgram();

        $response = $this->actingAs($this->admin)->post(route('admin.launch-programs.milestones.store', $program), [
            'title' => 'Test',
            'type' => 'invalid_type',
        ]);

        $response->assertSessionHasErrors('type');
    }

    // ─── Partner Enrollment Status ────────────────────────────────

    public function test_admin_can_update_partner_status(): void
    {
        $program = $this->createProgram('active');
        $enrollment = $this->createEnrollment($program);

        $response = $this->actingAs($this->admin)->put(route('admin.launch-program-partners.update', $enrollment), [
            'status' => 'accepted',
        ]);

        $response->assertRedirect();
        $enrollment->refresh();
        $this->assertEquals('accepted', $enrollment->status);
        $this->assertNotNull($enrollment->accepted_at);
    }

    public function test_update_partner_status_validates_invalid_status(): void
    {
        $enrollment = $this->createEnrollment($this->createProgram());

        $response = $this->actingAs($this->admin)->put(route('admin.launch-program-partners.update', $enrollment), [
            'status' => 'bogus',
        ]);

        $response->assertSessionHasErrors('status');
    }

    // ─── PSM Nomination ───────────────────────────────────────────

    public function test_psm_can_nominate_own_partner(): void
    {
        $program = $this->createProgram('acceptingNominations');

        $response = $this->actingAs($this->psm)->post(route('partners.launch-programs.nominate', [
            'partner' => $this->partner,
            'launchProgram' => $program,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('launch_program_partners', [
            'launch_program_id' => $program->id,
            'partner_id' => $this->partner->id,
            'status' => 'nominated',
        ]);
    }

    public function test_psm_cannot_nominate_for_non_accepting_program(): void
    {
        $program = $this->createProgram('active');

        $response = $this->actingAs($this->psm)->post(route('partners.launch-programs.nominate', [
            'partner' => $this->partner,
            'launchProgram' => $program,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('launch_program_partners', [
            'launch_program_id' => $program->id,
            'partner_id' => $this->partner->id,
        ]);
    }

    public function test_psm_cannot_nominate_already_enrolled_partner(): void
    {
        $program = $this->createProgram('acceptingNominations');
        $this->createEnrollment($program);

        $response = $this->actingAs($this->psm)->post(route('partners.launch-programs.nominate', [
            'partner' => $this->partner,
            'launchProgram' => $program,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_psm_cannot_nominate_other_psms_partner(): void
    {
        $otherPsm = User::factory()->create(['role' => 'psm']);
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);
        $program = $this->createProgram('acceptingNominations');

        $response = $this->actingAs($this->psm)->post(route('partners.launch-programs.nominate', [
            'partner' => $otherPartner,
            'launchProgram' => $program,
        ]));

        $response->assertForbidden();
    }

    public function test_admin_can_nominate_any_partner(): void
    {
        $program = $this->createProgram('acceptingNominations');

        $response = $this->actingAs($this->admin)->post(route('partners.launch-programs.nominate', [
            'partner' => $this->partner,
            'launchProgram' => $program,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    // ─── PSM Participants ─────────────────────────────────────────

    public function test_psm_can_add_participant(): void
    {
        $program = $this->createProgram('active');
        $enrollment = $this->createEnrollment($program, null, 'active');
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->post(route('launch-program-partners.participants.store', $enrollment), [
            'contact_id' => $contact->id,
            'role' => 'technical_lead',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('launch_program_participants', [
            'launch_program_partner_id' => $enrollment->id,
            'contact_id' => $contact->id,
            'role' => 'technical_lead',
        ]);
    }

    public function test_add_participant_validates_required_fields(): void
    {
        $enrollment = $this->createEnrollment($this->createProgram());

        $response = $this->actingAs($this->psm)->post(route('launch-program-partners.participants.store', $enrollment), []);

        $response->assertSessionHasErrors(['contact_id', 'role']);
    }

    public function test_psm_can_remove_participant(): void
    {
        $enrollment = $this->createEnrollment($this->createProgram('active'), null, 'active');
        $participant = LaunchProgramParticipant::factory()->create([
            'launch_program_partner_id' => $enrollment->id,
            'contact_id' => Contact::factory()->create(['partner_id' => $this->partner->id])->id,
            'assigned_by' => $this->psm->id,
        ]);

        $response = $this->actingAs($this->psm)->delete(route('launch-program-participants.destroy', $participant));

        $response->assertRedirect();
        $this->assertDatabaseMissing('launch_program_participants', ['id' => $participant->id]);
    }

    // ─── Partner Tab ──────────────────────────────────────────────

    public function test_psm_can_view_launch_programs_tab(): void
    {
        $response = $this->actingAs($this->psm)->get(route('partners.tab', [
            'id' => $this->partner->id,
            'tab' => 'launch-programs',
        ]));

        $response->assertOk();
        $response->assertViewIs('partner.tabs.launch-programs');
        $response->assertViewHas(['enrolledPrograms', 'availablePrograms', 'contacts']);
    }

    public function test_admin_can_view_any_partners_launch_programs_tab(): void
    {
        $response = $this->actingAs($this->admin)->get(route('partners.tab', [
            'id' => $this->partner->id,
            'tab' => 'launch-programs',
        ]));

        $response->assertOk();
    }

    public function test_psm_cannot_view_other_psms_partner_tab(): void
    {
        $otherPsm = User::factory()->create(['role' => 'psm']);
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        $response = $this->actingAs($this->psm)->get(route('partners.tab', [
            'id' => $otherPartner->id,
            'tab' => 'launch-programs',
        ]));

        $response->assertForbidden();
    }

    public function test_tab_shows_enrolled_and_available_programs(): void
    {
        $enrolledProgram = $this->createProgram('active');
        $this->createEnrollment($enrolledProgram);

        $availableProgram = $this->createProgram('acceptingNominations');

        $response = $this->actingAs($this->psm)->get(route('partners.tab', [
            'id' => $this->partner->id,
            'tab' => 'launch-programs',
        ]));

        $response->assertOk();
        $enrolledPrograms = $response->viewData('enrolledPrograms');
        $availablePrograms = $response->viewData('availablePrograms');

        $this->assertTrue($enrolledPrograms->contains('launch_program_id', $enrolledProgram->id));
        $this->assertTrue($availablePrograms->contains('id', $availableProgram->id));
    }

    public function test_tab_excludes_already_enrolled_from_available(): void
    {
        $program = $this->createProgram('acceptingNominations');
        $this->createEnrollment($program);

        $response = $this->actingAs($this->psm)->get(route('partners.tab', [
            'id' => $this->partner->id,
            'tab' => 'launch-programs',
        ]));

        $availablePrograms = $response->viewData('availablePrograms');
        $this->assertFalse($availablePrograms->contains('id', $program->id));
    }

    // ─── Portal Launch Programs ───────────────────────────────────

    public function test_portal_admin_can_view_launch_program_index(): void
    {
        $contact = $this->createPortalContact('admin');
        $program = $this->createProgram('active');
        $this->createEnrollment($program);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.launch-programs.index'));

        $response->assertOk();
        $response->assertViewIs('portal.launch-programs.index');
        $response->assertViewHas('enrollments');
    }

    public function test_portal_admin_can_view_enrollment_detail(): void
    {
        $contact = $this->createPortalContact('admin');
        $program = $this->createProgram('active');
        $enrollment = $this->createEnrollment($program);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.launch-programs.show', $enrollment));

        $response->assertOk();
        $response->assertViewIs('portal.launch-programs.show');
        $response->assertViewHas(['program', 'enrollment', 'milestones']);
    }

    public function test_portal_contact_cannot_view_other_partners_enrollment(): void
    {
        $otherPartner = Partner::factory()->create();
        $contact = $this->createPortalContact('admin');
        $program = $this->createProgram('active');
        $enrollment = $this->createEnrollment($program, $otherPartner);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.launch-programs.show', $enrollment));

        $response->assertForbidden();
    }

    public function test_portal_participant_can_complete_milestone(): void
    {
        $contact = $this->createPortalContact('admin');
        $program = $this->createProgram('active');
        $enrollment = $this->createEnrollment($program, null, 'active');
        $milestone = LaunchProgramMilestone::factory()->required()->create([
            'launch_program_id' => $program->id,
        ]);
        $participant = LaunchProgramParticipant::factory()->create([
            'launch_program_partner_id' => $enrollment->id,
            'contact_id' => $contact->id,
        ]);

        $response = $this->actingAs($contact, 'portal')->post(route('portal.launch-programs.complete-milestone', [
            'programPartner' => $enrollment,
            'milestone' => $milestone,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('launch_program_milestone_completions', [
            'launch_program_participant_id' => $participant->id,
            'launch_program_milestone_id' => $milestone->id,
        ]);
    }

    public function test_milestone_completion_is_idempotent(): void
    {
        $contact = $this->createPortalContact('admin');
        $program = $this->createProgram('active');
        $enrollment = $this->createEnrollment($program, null, 'active');
        $milestone = LaunchProgramMilestone::factory()->required()->create([
            'launch_program_id' => $program->id,
        ]);
        $participant = LaunchProgramParticipant::factory()->create([
            'launch_program_partner_id' => $enrollment->id,
            'contact_id' => $contact->id,
        ]);

        // Complete once
        $this->actingAs($contact, 'portal')->post(route('portal.launch-programs.complete-milestone', [
            'programPartner' => $enrollment,
            'milestone' => $milestone,
        ]));

        // Complete again — should not duplicate
        $this->actingAs($contact, 'portal')->post(route('portal.launch-programs.complete-milestone', [
            'programPartner' => $enrollment,
            'milestone' => $milestone,
        ]));

        $this->assertEquals(1, LaunchProgramMilestoneCompletion::where('launch_program_participant_id', $participant->id)
            ->where('launch_program_milestone_id', $milestone->id)
            ->count());
    }

    public function test_non_participant_cannot_complete_milestone(): void
    {
        $contact = $this->createPortalContact('admin');
        $program = $this->createProgram('active');
        $enrollment = $this->createEnrollment($program, null, 'active');
        $milestone = LaunchProgramMilestone::factory()->create([
            'launch_program_id' => $program->id,
        ]);

        $response = $this->actingAs($contact, 'portal')->post(route('portal.launch-programs.complete-milestone', [
            'programPartner' => $enrollment,
            'milestone' => $milestone,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_portal_admin_can_submit_feedback(): void
    {
        $contact = $this->createPortalContact('admin');
        $program = $this->createProgram('active');
        $enrollment = $this->createEnrollment($program, null, 'active');

        $response = $this->actingAs($contact, 'portal')->post(route('portal.launch-programs.submit-feedback', $enrollment), [
            'type' => 'product',
            'rating' => 4,
            'body' => 'Great program experience.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('launch_program_feedback', [
            'launch_program_partner_id' => $enrollment->id,
            'contact_id' => $contact->id,
            'type' => 'product',
            'body' => 'Great program experience.',
        ]);
    }

    public function test_feedback_validates_body_required(): void
    {
        $contact = $this->createPortalContact('admin');
        $enrollment = $this->createEnrollment($this->createProgram('active'), null, 'active');

        $response = $this->actingAs($contact, 'portal')->post(route('portal.launch-programs.submit-feedback', $enrollment), [
            'type' => 'general',
        ]);

        $response->assertSessionHasErrors('body');
    }

    public function test_feedback_validates_invalid_type(): void
    {
        $contact = $this->createPortalContact('admin');
        $enrollment = $this->createEnrollment($this->createProgram('active'), null, 'active');

        $response = $this->actingAs($contact, 'portal')->post(route('portal.launch-programs.submit-feedback', $enrollment), [
            'type' => 'invalid',
            'body' => 'Some text.',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_portal_sales_cannot_access_launch_programs(): void
    {
        $contact = $this->createPortalContact('sales');

        $response = $this->actingAs($contact, 'portal')->get(route('portal.launch-programs.index'));

        $response->assertForbidden();
    }

    // ─── Model: LaunchProgram ─────────────────────────────────────

    public function test_model_is_accepting_nominations(): void
    {
        $program = $this->createProgram('acceptingNominations');

        $this->assertTrue($program->isAcceptingNominations());
        $this->assertFalse($program->isActive());
    }

    public function test_model_is_active(): void
    {
        $program = $this->createProgram('active');

        $this->assertTrue($program->isActive());
        $this->assertFalse($program->isAcceptingNominations());
    }

    public function test_model_auto_generates_slug(): void
    {
        $program = LaunchProgram::factory()->create([
            'name' => 'My Amazing Program',
            'slug' => null,
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals('my-amazing-program', $program->slug);
    }

    public function test_model_partner_count_excludes_withdrawn(): void
    {
        $program = $this->createProgram('active');
        $this->createEnrollment($program, null, 'active');

        $otherPartner = Partner::factory()->create();
        $this->createEnrollment($program, $otherPartner, 'withdrawn');

        $this->assertEquals(1, $program->partnerCount());
    }

    public function test_model_completion_percentage_with_no_partners(): void
    {
        $program = $this->createProgram('active');

        $this->assertEquals(0, $program->completionPercentage());
    }

    // ─── Model: LaunchProgramPartner ──────────────────────────────

    public function test_partner_completion_100_when_no_required_milestones(): void
    {
        $program = $this->createProgram('active');
        LaunchProgramMilestone::factory()->optional()->create([
            'launch_program_id' => $program->id,
        ]);
        $enrollment = $this->createEnrollment($program, null, 'active');

        $this->assertEquals(100, $enrollment->completionPercentage());
    }

    public function test_partner_completion_0_when_no_participants(): void
    {
        $program = $this->createProgram('active');
        LaunchProgramMilestone::factory()->required()->create([
            'launch_program_id' => $program->id,
        ]);
        $enrollment = $this->createEnrollment($program, null, 'active');

        $this->assertEquals(0, $enrollment->completionPercentage());
    }

    public function test_partner_completion_calculates_from_required_milestones(): void
    {
        $program = $this->createProgram('active');
        $requiredMilestone = LaunchProgramMilestone::factory()->required()->create([
            'launch_program_id' => $program->id,
        ]);
        LaunchProgramMilestone::factory()->required()->create([
            'launch_program_id' => $program->id,
        ]);
        $enrollment = $this->createEnrollment($program, null, 'active');
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        $participant = LaunchProgramParticipant::factory()->create([
            'launch_program_partner_id' => $enrollment->id,
            'contact_id' => $contact->id,
        ]);

        LaunchProgramMilestoneCompletion::factory()->create([
            'launch_program_participant_id' => $participant->id,
            'launch_program_milestone_id' => $requiredMilestone->id,
        ]);

        // 1 of 2 required milestones = 50%
        $this->assertEquals(50, $enrollment->completionPercentage());
    }

    // ─── Model: LaunchProgramParticipant ──────────────────────────

    public function test_participant_completion_percentage(): void
    {
        $program = $this->createProgram('active');
        $m1 = LaunchProgramMilestone::factory()->required()->create(['launch_program_id' => $program->id]);
        LaunchProgramMilestone::factory()->required()->create(['launch_program_id' => $program->id]);
        $enrollment = $this->createEnrollment($program, null, 'active');
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        $participant = LaunchProgramParticipant::factory()->create([
            'launch_program_partner_id' => $enrollment->id,
            'contact_id' => $contact->id,
        ]);

        LaunchProgramMilestoneCompletion::factory()->create([
            'launch_program_participant_id' => $participant->id,
            'launch_program_milestone_id' => $m1->id,
        ]);

        $this->assertEquals(50, $participant->completionPercentage());
    }

    public function test_participant_completed_milestone_ids(): void
    {
        $program = $this->createProgram('active');
        $m1 = LaunchProgramMilestone::factory()->create(['launch_program_id' => $program->id]);
        $m2 = LaunchProgramMilestone::factory()->create(['launch_program_id' => $program->id]);
        $enrollment = $this->createEnrollment($program, null, 'active');
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        $participant = LaunchProgramParticipant::factory()->create([
            'launch_program_partner_id' => $enrollment->id,
            'contact_id' => $contact->id,
        ]);

        LaunchProgramMilestoneCompletion::factory()->create([
            'launch_program_participant_id' => $participant->id,
            'launch_program_milestone_id' => $m1->id,
        ]);
        LaunchProgramMilestoneCompletion::factory()->create([
            'launch_program_participant_id' => $participant->id,
            'launch_program_milestone_id' => $m2->id,
        ]);

        $ids = $participant->completedMilestoneIds();
        $this->assertCount(2, $ids);
        $this->assertContains($m1->id, $ids);
        $this->assertContains($m2->id, $ids);
    }
}
