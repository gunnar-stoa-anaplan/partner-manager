<?php

namespace Tests\Feature;

use App\Models\Blueprint;
use App\Models\BlueprintContentItem;
use App\Models\BlueprintEnrollment;
use App\Models\BlueprintOutcome;
use App\Models\BlueprintProgress;
use App\Models\Contact;
use App\Models\Partner;
use App\Models\PartnerBlueprintAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithActivationSetup;

class BlueprintFeatureTest extends TestCase
{
    use RefreshDatabase, WithActivationSetup;

    private User $admin;

    private User $psm;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->admin()->create();
        $this->psm = User::factory()->psm()->create();
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);
        $this->activateModuleForUser('enablement', $this->admin);
        $this->activateModuleForUser('enablement', $this->psm);
    }

    // ─── Admin CRUD ─────────────────────────────────────────────

    public function test_admin_can_view_blueprint_index(): void
    {
        Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('blueprints.index'));

        $response->assertOk();
        $response->assertViewIs('admin.blueprints.index');
        $response->assertViewHas('blueprints');
    }

    public function test_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('blueprints.create'));

        $response->assertOk();
        $response->assertViewIs('admin.blueprints.create');
    }

    public function test_admin_can_store_a_blueprint(): void
    {
        $response = $this->actingAs($this->admin)->post(route('blueprints.store'), [
            'name' => 'IFP Application Enablement',
            'category' => 'application',
            'product_area' => 'Finance',
            'description' => 'IFP enablement blueprint for partners.',
            'version' => 'V1.0',
            'status' => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('blueprints', [
            'name' => 'IFP Application Enablement',
            'category' => 'application',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_store_delivery_category_blueprint(): void
    {
        $response = $this->actingAs($this->admin)->post(route('blueprints.store'), [
            'name' => 'Delivery Excellence',
            'category' => 'delivery',
            'product_area' => 'Platform',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('blueprints', [
            'name' => 'Delivery Excellence',
            'category' => 'delivery',
        ]);
    }

    public function test_admin_can_view_blueprint_detail(): void
    {
        $blueprint = Blueprint::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('blueprints.show', $blueprint));

        $response->assertOk();
        $response->assertViewIs('admin.blueprints.show');
    }

    public function test_admin_can_edit_a_blueprint(): void
    {
        $blueprint = Blueprint::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('blueprints.edit', $blueprint));

        $response->assertOk();
        $response->assertViewIs('admin.blueprints.edit');
    }

    public function test_admin_can_update_a_blueprint(): void
    {
        $blueprint = Blueprint::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put(route('blueprints.update', $blueprint), [
            'name' => 'Updated Name',
            'category' => 'product',
            'product_area' => 'Sales',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('blueprints.show', $blueprint));
        $this->assertDatabaseHas('blueprints', [
            'id' => $blueprint->id,
            'name' => 'Updated Name',
            'category' => 'product',
            'status' => 'published',
        ]);
    }

    public function test_admin_can_delete_a_blueprint(): void
    {
        $blueprint = Blueprint::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete(route('blueprints.destroy', $blueprint));

        $response->assertRedirect(route('blueprints.index'));
        $this->assertSoftDeleted('blueprints', ['id' => $blueprint->id]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('blueprints.store'), []);

        $response->assertSessionHasErrors(['name', 'category', 'status']);
    }

    public function test_store_rejects_invalid_category(): void
    {
        $response = $this->actingAs($this->admin)->post(route('blueprints.store'), [
            'name' => 'Test',
            'category' => 'invalid',
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('category');
    }

    // ─── Outcome Management ─────────────────────────────────────

    public function test_admin_can_add_outcome(): void
    {
        $blueprint = Blueprint::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->post(route('blueprints.outcomes.store', $blueprint), [
            'title' => 'Partners understand positioning',
            'description' => 'Foundational knowledge about product positioning.',
            'sort_order' => 1,
        ]);

        $response->assertRedirect(route('blueprints.edit', $blueprint));
        $this->assertDatabaseHas('blueprint_outcomes', [
            'blueprint_id' => $blueprint->id,
            'title' => 'Partners understand positioning',
            'sort_order' => 1,
        ]);
    }

    public function test_admin_can_delete_outcome(): void
    {
        $outcome = BlueprintOutcome::factory()->create([
            'blueprint_id' => Blueprint::factory()->create(['created_by' => $this->admin->id]),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('blueprint-outcomes.destroy', $outcome));

        $response->assertRedirect();
        $this->assertDatabaseMissing('blueprint_outcomes', ['id' => $outcome->id]);
    }

    // ─── Content Item Management ────────────────────────────────

    public function test_admin_can_add_content_item(): void
    {
        $blueprint = Blueprint::factory()->create(['created_by' => $this->admin->id]);
        $outcome = BlueprintOutcome::factory()->create(['blueprint_id' => $blueprint->id]);

        $response = $this->actingAs($this->admin)->post(route('blueprint-outcomes.items.store', $outcome), [
            'title' => 'Academy Course: IFP Basics',
            'url' => 'https://academy.example.com/ifp-basics',
            'duration_minutes' => 30,
            'duration_display' => '30 min',
            'source' => 'Academy',
            'gtm_applicable' => 'required',
            'pre_sales_applicable' => 'optional',
            'delivery_applicable' => 'na',
        ]);

        $response->assertRedirect(route('blueprints.edit', $blueprint));
        $this->assertDatabaseHas('blueprint_content_items', [
            'blueprint_outcome_id' => $outcome->id,
            'title' => 'Academy Course: IFP Basics',
            'gtm_applicable' => 'required',
            'delivery_applicable' => 'na',
        ]);
    }

    public function test_admin_can_update_content_item(): void
    {
        $blueprint = Blueprint::factory()->create(['created_by' => $this->admin->id]);
        $outcome = BlueprintOutcome::factory()->create(['blueprint_id' => $blueprint->id]);
        $item = BlueprintContentItem::factory()->create(['blueprint_outcome_id' => $outcome->id]);

        $response = $this->actingAs($this->admin)->put(route('blueprint-content-items.update', $item), [
            'title' => 'Updated Title',
            'gtm_applicable' => 'na',
            'pre_sales_applicable' => 'required',
            'delivery_applicable' => 'required',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('blueprint_content_items', [
            'id' => $item->id,
            'title' => 'Updated Title',
            'pre_sales_applicable' => 'required',
        ]);
    }

    public function test_admin_can_delete_content_item(): void
    {
        $blueprint = Blueprint::factory()->create(['created_by' => $this->admin->id]);
        $outcome = BlueprintOutcome::factory()->create(['blueprint_id' => $blueprint->id]);
        $item = BlueprintContentItem::factory()->create(['blueprint_outcome_id' => $outcome->id]);

        $response = $this->actingAs($this->admin)->delete(route('blueprint-content-items.destroy', $item));

        $response->assertRedirect();
        $this->assertDatabaseMissing('blueprint_content_items', ['id' => $item->id]);
    }

    // ─── PSM Catalog & Assignment ───────────────────────────────

    public function test_psm_can_view_catalog(): void
    {
        Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->psm)->get(route('blueprints.catalog'));

        $response->assertOk();
        $response->assertViewIs('blueprints.catalog');
    }

    public function test_psm_can_view_blueprint_detail(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->psm)->get(route('blueprints.detail', $blueprint));

        $response->assertOk();
        $response->assertViewIs('blueprints.show');
    }

    public function test_psm_can_assign_partner_to_blueprint(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->psm)->post(route('blueprints.assign', $blueprint), [
            'partner_id' => $this->partner->id,
            'role' => 'gtm',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('partner_blueprint_assignments', [
            'partner_id' => $this->partner->id,
            'blueprint_id' => $blueprint->id,
            'role' => 'gtm',
            'assigned_by' => $this->psm->id,
        ]);
    }

    public function test_duplicate_assignment_is_rejected(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);
        PartnerBlueprintAssignment::factory()->create([
            'partner_id' => $this->partner->id,
            'blueprint_id' => $blueprint->id,
            'role' => 'gtm',
            'assigned_by' => $this->psm->id,
        ]);

        $response = $this->actingAs($this->psm)->post(route('blueprints.assign', $blueprint), [
            'partner_id' => $this->partner->id,
            'role' => 'gtm',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_psm_can_remove_assignment(): void
    {
        $assignment = PartnerBlueprintAssignment::factory()->create([
            'partner_id' => $this->partner->id,
            'assigned_by' => $this->psm->id,
        ]);

        $response = $this->actingAs($this->psm)->delete(route('blueprint-assignments.destroy', $assignment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('partner_blueprint_assignments', ['id' => $assignment->id]);
    }

    // ─── Enrollment ─────────────────────────────────────────────

    public function test_psm_can_enroll_contacts(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);
        $assignment = PartnerBlueprintAssignment::factory()->create([
            'partner_id' => $this->partner->id,
            'blueprint_id' => $blueprint->id,
            'assigned_by' => $this->psm->id,
        ]);
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->post(route('blueprint-assignments.enroll', $assignment), [
            'contact_ids' => [$contact->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('blueprint_enrollments', [
            'partner_blueprint_assignment_id' => $assignment->id,
            'contact_id' => $contact->id,
            'enrolled_by' => $this->psm->id,
        ]);
    }

    public function test_duplicate_enrollment_is_skipped(): void
    {
        $assignment = PartnerBlueprintAssignment::factory()->create([
            'partner_id' => $this->partner->id,
            'assigned_by' => $this->psm->id,
        ]);
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        BlueprintEnrollment::factory()->create([
            'partner_blueprint_assignment_id' => $assignment->id,
            'contact_id' => $contact->id,
            'enrolled_by' => $this->psm->id,
        ]);

        $response = $this->actingAs($this->psm)->post(route('blueprint-assignments.enroll', $assignment), [
            'contact_ids' => [$contact->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('blueprint_enrollments', 1);
    }

    public function test_psm_can_remove_enrollment(): void
    {
        $enrollment = BlueprintEnrollment::factory()->create([
            'enrolled_by' => $this->psm->id,
        ]);

        $response = $this->actingAs($this->psm)->delete(route('blueprint-enrollments.destroy', $enrollment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('blueprint_enrollments', ['id' => $enrollment->id]);
    }

    // ─── Progress Tracking ──────────────────────────────────────

    public function test_progress_toggle_marks_item_complete(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);
        $outcome = BlueprintOutcome::factory()->create(['blueprint_id' => $blueprint->id]);
        $item = BlueprintContentItem::factory()->requiredForAll()->create(['blueprint_outcome_id' => $outcome->id]);
        $assignment = PartnerBlueprintAssignment::factory()->gtm()->create([
            'partner_id' => $this->partner->id,
            'blueprint_id' => $blueprint->id,
            'assigned_by' => $this->psm->id,
        ]);
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        $enrollment = BlueprintEnrollment::factory()->create([
            'partner_blueprint_assignment_id' => $assignment->id,
            'contact_id' => $contact->id,
            'enrolled_by' => $this->psm->id,
        ]);

        $response = $this->actingAs($this->psm)->postJson(route('api.blueprint-progress.toggle'), [
            'enrollment_id' => $enrollment->id,
            'content_item_id' => $item->id,
        ]);

        $response->assertOk();
        $response->assertJson(['completed' => true]);
        $this->assertDatabaseHas('blueprint_progress', [
            'blueprint_enrollment_id' => $enrollment->id,
            'blueprint_content_item_id' => $item->id,
            'completed' => true,
        ]);
    }

    public function test_progress_toggle_can_uncomplete_item(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);
        $outcome = BlueprintOutcome::factory()->create(['blueprint_id' => $blueprint->id]);
        $item = BlueprintContentItem::factory()->requiredForAll()->create(['blueprint_outcome_id' => $outcome->id]);
        $assignment = PartnerBlueprintAssignment::factory()->gtm()->create([
            'partner_id' => $this->partner->id,
            'blueprint_id' => $blueprint->id,
            'assigned_by' => $this->psm->id,
        ]);
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        $enrollment = BlueprintEnrollment::factory()->create([
            'partner_blueprint_assignment_id' => $assignment->id,
            'contact_id' => $contact->id,
            'enrolled_by' => $this->psm->id,
        ]);
        BlueprintProgress::factory()->completed()->create([
            'blueprint_enrollment_id' => $enrollment->id,
            'blueprint_content_item_id' => $item->id,
        ]);

        $response = $this->actingAs($this->psm)->postJson(route('api.blueprint-progress.toggle'), [
            'enrollment_id' => $enrollment->id,
            'content_item_id' => $item->id,
        ]);

        $response->assertOk();
        $response->assertJson(['completed' => false]);
    }

    // ─── PSM Progress Views ─────────────────────────────────────

    public function test_psm_can_view_assignment_progress(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);
        $assignment = PartnerBlueprintAssignment::factory()->create([
            'partner_id' => $this->partner->id,
            'blueprint_id' => $blueprint->id,
            'assigned_by' => $this->psm->id,
        ]);

        $response = $this->actingAs($this->psm)->get(route('blueprint-assignments.show', $assignment));

        $response->assertOk();
        $response->assertViewIs('blueprints.partner-progress');
    }

    public function test_psm_can_view_contact_progress(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);
        $outcome = BlueprintOutcome::factory()->create(['blueprint_id' => $blueprint->id]);
        BlueprintContentItem::factory()->requiredForAll()->create(['blueprint_outcome_id' => $outcome->id]);
        $assignment = PartnerBlueprintAssignment::factory()->gtm()->create([
            'partner_id' => $this->partner->id,
            'blueprint_id' => $blueprint->id,
            'assigned_by' => $this->psm->id,
        ]);
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);
        $enrollment = BlueprintEnrollment::factory()->create([
            'partner_blueprint_assignment_id' => $assignment->id,
            'contact_id' => $contact->id,
            'enrolled_by' => $this->psm->id,
        ]);

        $response = $this->actingAs($this->psm)->get(route('blueprint-enrollments.show', $enrollment));

        $response->assertOk();
        $response->assertViewIs('blueprints.contact-progress');
    }

    // ─── Non-Admin Access Control ───────────────────────────────

    public function test_non_admin_cannot_access_admin_crud(): void
    {
        $response = $this->actingAs($this->psm)->get(route('blueprints.index'));

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_store_blueprint(): void
    {
        $response = $this->actingAs($this->psm)->post(route('blueprints.store'), [
            'name' => 'Test',
            'category' => 'application',
            'status' => 'draft',
        ]);

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_delete_blueprint(): void
    {
        $blueprint = Blueprint::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->psm)->delete(route('blueprints.destroy', $blueprint));

        $response->assertForbidden();
    }

    // ─── Unauthenticated Access ─────────────────────────────────

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('blueprints.catalog'));

        $response->assertRedirect(route('login'));
    }

    // ─── Slug Auto-Generation ───────────────────────────────────

    public function test_slug_is_auto_generated(): void
    {
        $blueprint = Blueprint::factory()->create([
            'name' => 'IFP Application Enablement',
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals('ifp-application-enablement', $blueprint->slug);
    }

    public function test_duplicate_slug_gets_suffix(): void
    {
        Blueprint::factory()->create([
            'name' => 'IFP Basics',
            'created_by' => $this->admin->id,
        ]);

        $second = Blueprint::factory()->create([
            'name' => 'IFP Basics',
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals('ifp-basics-1', $second->slug);
    }

    // ─── Owner Workflow ─────────────────────────────────────────

    public function test_admin_can_assign_owner_to_blueprint(): void
    {
        $owner = User::factory()->psm()->create();

        $response = $this->actingAs($this->admin)->post(route('blueprints.store'), [
            'name' => 'Owned Blueprint',
            'category' => 'application',
            'status' => 'draft',
            'owner_id' => $owner->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('blueprints', [
            'name' => 'Owned Blueprint',
            'owner_id' => $owner->id,
        ]);
    }

    public function test_owner_can_edit_their_blueprint(): void
    {
        $owner = User::factory()->psm()->create();
        $this->activateModuleForUser('enablement', $owner);
        $blueprint = Blueprint::factory()->withOwner($owner)->create([
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($owner)->get(route('blueprints.owner.edit', $blueprint));

        $response->assertOk();
        $response->assertViewIs('admin.blueprints.edit');
        $response->assertViewHas('ownerMode', true);
    }

    public function test_owner_can_update_their_blueprint(): void
    {
        $owner = User::factory()->psm()->create();
        $this->activateModuleForUser('enablement', $owner);
        $blueprint = Blueprint::factory()->withOwner($owner)->create([
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($owner)->put(route('blueprints.owner.update', $blueprint), [
            'name' => 'Owner Updated Name',
            'category' => 'application',
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('blueprints.owner.edit', $blueprint));
        $this->assertDatabaseHas('blueprints', [
            'id' => $blueprint->id,
            'name' => 'Owner Updated Name',
        ]);
    }

    public function test_non_owner_cannot_edit_blueprint(): void
    {
        $owner = User::factory()->psm()->create();
        $otherUser = User::factory()->psm()->create();
        $blueprint = Blueprint::factory()->withOwner($owner)->create([
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($otherUser)->get(route('blueprints.owner.edit', $blueprint));

        $response->assertForbidden();
    }

    public function test_owner_can_submit_for_publishing(): void
    {
        $owner = User::factory()->psm()->create();
        $this->activateModuleForUser('enablement', $owner);
        $blueprint = Blueprint::factory()->withOwner($owner)->create([
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($owner)->post(route('blueprints.owner.submit', $blueprint));

        $response->assertRedirect();
        $this->assertDatabaseHas('blueprints', [
            'id' => $blueprint->id,
            'status' => 'submitted',
        ]);
    }

    public function test_non_owner_cannot_submit_for_publishing(): void
    {
        $owner = User::factory()->psm()->create();
        $otherUser = User::factory()->psm()->create();
        $blueprint = Blueprint::factory()->withOwner($owner)->create([
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($otherUser)->post(route('blueprints.owner.submit', $blueprint));

        $response->assertForbidden();
    }

    public function test_owner_cannot_set_published_status(): void
    {
        $owner = User::factory()->psm()->create();
        $this->activateModuleForUser('enablement', $owner);
        $blueprint = Blueprint::factory()->withOwner($owner)->create([
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($owner)->put(route('blueprints.owner.update', $blueprint), [
            'name' => $blueprint->name,
            'category' => $blueprint->category,
            'status' => 'published',
        ]);

        $response->assertSessionHasErrors('status');
    }

    // ─── Duplicate ──────────────────────────────────────────────

    public function test_admin_can_duplicate_blueprint(): void
    {
        $blueprint = Blueprint::factory()->published()->create([
            'name' => 'Original Blueprint',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('blueprints.duplicate', $blueprint));

        $response->assertRedirect();
        $this->assertDatabaseHas('blueprints', [
            'name' => 'Original Blueprint - Copy',
            'status' => 'draft',
            'created_by' => $this->admin->id,
            'owner_id' => null,
        ]);
    }

    public function test_duplicate_deep_copies_outcomes_and_items(): void
    {
        $blueprint = Blueprint::factory()->create(['created_by' => $this->admin->id]);
        $outcome = BlueprintOutcome::factory()->create([
            'blueprint_id' => $blueprint->id,
            'title' => 'Original Outcome',
        ]);
        BlueprintContentItem::factory()->requiredForAll()->create([
            'blueprint_outcome_id' => $outcome->id,
            'title' => 'Original Item',
        ]);

        $this->actingAs($this->admin)->post(route('blueprints.duplicate', $blueprint));

        $copy = Blueprint::where('name', $blueprint->name.' - Copy')->first();
        $this->assertNotNull($copy);
        $this->assertEquals(1, $copy->outcomes()->count());
        $this->assertEquals('Original Outcome', $copy->outcomes->first()->title);
        $this->assertEquals(1, $copy->outcomes->first()->contentItems()->count());
        $this->assertEquals('Original Item', $copy->outcomes->first()->contentItems->first()->title);
    }

    // ─── Status Date Tracking ───────────────────────────────────

    public function test_creating_draft_sets_drafted_at(): void
    {
        $blueprint = Blueprint::factory()->create([
            'created_by' => $this->admin->id,
            'status' => 'draft',
        ]);

        $this->assertNotNull($blueprint->drafted_at);
    }

    public function test_publishing_sets_published_at(): void
    {
        $blueprint = Blueprint::factory()->create(['created_by' => $this->admin->id]);

        $this->actingAs($this->admin)->put(route('blueprints.update', $blueprint), [
            'name' => $blueprint->name,
            'category' => $blueprint->category,
            'status' => 'published',
        ]);

        $blueprint->refresh();
        $this->assertNotNull($blueprint->published_at);
    }

    public function test_archiving_sets_archived_at(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);

        $this->actingAs($this->admin)->put(route('blueprints.update', $blueprint), [
            'name' => $blueprint->name,
            'category' => $blueprint->category,
            'status' => 'archived',
        ]);

        $blueprint->refresh();
        $this->assertNotNull($blueprint->archived_at);
    }

    public function test_submitted_status_is_valid(): void
    {
        $response = $this->actingAs($this->admin)->post(route('blueprints.store'), [
            'name' => 'Submitted Blueprint',
            'category' => 'application',
            'status' => 'submitted',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('blueprints', [
            'name' => 'Submitted Blueprint',
            'status' => 'submitted',
        ]);
    }

    // ─── Enablement Dashboard ───────────────────────────────────

    public function test_admin_can_view_enablement_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.enablement-dashboard'));

        $response->assertOk();
        $response->assertViewIs('admin.enablement-dashboard');
    }

    public function test_non_admin_cannot_view_enablement_dashboard(): void
    {
        $response = $this->actingAs($this->psm)->get(route('admin.enablement-dashboard'));

        $response->assertForbidden();
    }
}
