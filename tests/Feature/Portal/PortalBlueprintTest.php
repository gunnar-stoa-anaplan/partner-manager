<?php

namespace Tests\Feature\Portal;

use App\Models\Blueprint;
use App\Models\BlueprintEnrollment;
use App\Models\BlueprintOutcome;
use App\Models\Contact;
use App\Models\Partner;
use App\Models\PartnerBlueprintAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalBlueprintTest extends TestCase
{
    use RefreshDatabase;

    private User $psm;

    private Partner $partner;

    private Blueprint $blueprint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->psm = User::factory()->create(['role' => 'psm']);
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);

        $this->blueprint = Blueprint::factory()->published()->create();
        BlueprintOutcome::factory()->create(['blueprint_id' => $this->blueprint->id]);
    }

    private function createPortalContact(string $role = 'admin'): Contact
    {
        return Contact::factory()->{'portal'.ucfirst($role)}()->create([
            'partner_id' => $this->partner->id,
        ]);
    }

    private function createAssignmentWithEnrollment(Contact $contact): BlueprintEnrollment
    {
        $assignment = PartnerBlueprintAssignment::factory()->create([
            'partner_id' => $this->partner->id,
            'blueprint_id' => $this->blueprint->id,
            'status' => 'active',
        ]);

        return BlueprintEnrollment::factory()->create([
            'partner_blueprint_assignment_id' => $assignment->id,
            'contact_id' => $contact->id,
        ]);
    }

    // ─── Blueprint Index ──────────────────────────────────────────

    public function test_portal_admin_can_view_blueprints_index(): void
    {
        $contact = $this->createPortalContact('admin');

        PartnerBlueprintAssignment::factory()->create([
            'partner_id' => $this->partner->id,
            'blueprint_id' => $this->blueprint->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.blueprints.index'));

        $response->assertOk();
        $response->assertViewIs('portal.blueprints.index');
        $response->assertViewHas('assignments');
        $response->assertSee($this->partner->name);
    }

    public function test_delivery_lead_can_view_blueprints(): void
    {
        $contact = Contact::factory()->portalRole('delivery_lead')->create([
            'partner_id' => $this->partner->id,
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.blueprints.index'));

        $response->assertOk();
    }

    public function test_technical_architect_can_view_blueprints(): void
    {
        $contact = Contact::factory()->portalRole('technical_architect')->create([
            'partner_id' => $this->partner->id,
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.blueprints.index'));

        $response->assertOk();
    }

    public function test_index_shows_only_active_assignments(): void
    {
        $contact = $this->createPortalContact('admin');

        $active = PartnerBlueprintAssignment::factory()->create([
            'partner_id' => $this->partner->id,
            'blueprint_id' => $this->blueprint->id,
            'status' => 'active',
        ]);

        $paused = PartnerBlueprintAssignment::factory()->paused()->create([
            'partner_id' => $this->partner->id,
            'blueprint_id' => Blueprint::factory()->published()->create()->id,
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.blueprints.index'));

        $assignments = $response->viewData('assignments');
        $this->assertCount(1, $assignments);
        $this->assertEquals($active->id, $assignments->first()->id);
    }

    public function test_index_shows_contacts_own_enrollments(): void
    {
        $contact = $this->createPortalContact('admin');
        $enrollment = $this->createAssignmentWithEnrollment($contact);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.blueprints.index'));

        $response->assertOk();
        $myEnrollments = $response->viewData('myEnrollments');
        $this->assertCount(1, $myEnrollments);
        $this->assertEquals($enrollment->id, $myEnrollments->first()->id);
    }

    public function test_index_does_not_show_other_contacts_enrollments(): void
    {
        $contact = $this->createPortalContact('admin');
        $otherContact = Contact::factory()->portalRole('delivery_lead')->create([
            'partner_id' => $this->partner->id,
        ]);

        $this->createAssignmentWithEnrollment($otherContact);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.blueprints.index'));

        $myEnrollments = $response->viewData('myEnrollments');
        $this->assertCount(0, $myEnrollments);
    }

    public function test_unauthenticated_user_cannot_view_blueprints(): void
    {
        $response = $this->get(route('portal.blueprints.index'));

        $response->assertRedirect();
    }

    // ─── Blueprint Show (Enrollment Detail) ───────────────────────

    public function test_portal_admin_can_view_own_enrollment(): void
    {
        $contact = $this->createPortalContact('admin');
        $enrollment = $this->createAssignmentWithEnrollment($contact);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.blueprints.show', $enrollment));

        $response->assertOk();
        $response->assertViewIs('portal.blueprints.show');
        $response->assertSee($this->blueprint->name);
        $response->assertSee('Overall Completion');
    }

    public function test_portal_admin_can_view_other_contacts_enrollment(): void
    {
        $admin = $this->createPortalContact('admin');
        $deliveryContact = Contact::factory()->portalRole('delivery_lead')->create([
            'partner_id' => $this->partner->id,
        ]);

        $enrollment = $this->createAssignmentWithEnrollment($deliveryContact);

        $response = $this->actingAs($admin, 'portal')->get(route('portal.blueprints.show', $enrollment));

        $response->assertOk();
    }

    public function test_non_admin_cannot_view_other_contacts_enrollment(): void
    {
        $deliveryContact = Contact::factory()->portalRole('delivery_lead')->create([
            'partner_id' => $this->partner->id,
        ]);
        $otherContact = Contact::factory()->portalRole('delivery_resource')->create([
            'partner_id' => $this->partner->id,
        ]);

        $enrollment = $this->createAssignmentWithEnrollment($otherContact);

        $response = $this->actingAs($deliveryContact, 'portal')->get(route('portal.blueprints.show', $enrollment));

        $response->assertForbidden();
    }

    public function test_contact_cannot_view_enrollment_from_another_partner(): void
    {
        $contact = $this->createPortalContact('admin');

        $otherPartner = Partner::factory()->create(['user_id' => $this->psm->id]);
        $otherContact = Contact::factory()->portalAdmin()->create([
            'partner_id' => $otherPartner->id,
        ]);

        $assignment = PartnerBlueprintAssignment::factory()->create([
            'partner_id' => $otherPartner->id,
            'blueprint_id' => $this->blueprint->id,
            'status' => 'active',
        ]);

        $enrollment = BlueprintEnrollment::factory()->create([
            'partner_blueprint_assignment_id' => $assignment->id,
            'contact_id' => $otherContact->id,
        ]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.blueprints.show', $enrollment));

        $response->assertForbidden();
    }

    public function test_non_admin_can_view_own_enrollment(): void
    {
        $contact = Contact::factory()->portalRole('delivery_resource')->create([
            'partner_id' => $this->partner->id,
        ]);

        $enrollment = $this->createAssignmentWithEnrollment($contact);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.blueprints.show', $enrollment));

        $response->assertOk();
        $response->assertSee($this->blueprint->name);
    }
}
