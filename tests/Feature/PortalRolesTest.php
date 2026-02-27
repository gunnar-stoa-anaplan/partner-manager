<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PortalRolesTest extends TestCase
{
    use RefreshDatabase;

    private User $psm;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->psm = User::factory()->psm()->create();
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);
    }

    // ─── canAccess() ─────────────────────────────────────────────

    public function test_partner_admin_can_access_everything(): void
    {
        $contact = Contact::factory()->portalAdmin()->create(['partner_id' => $this->partner->id]);

        $this->assertTrue($contact->canAccess('goals'));
        $this->assertTrue($contact->canAccess('projects'));
        $this->assertTrue($contact->canAccess('deal_registrations'));
        $this->assertTrue($contact->canAccess('contacts'));
        $this->assertTrue($contact->canAccess('path'));
        $this->assertTrue($contact->canAccess('labs'));
        $this->assertTrue($contact->canAccess('certifications'));
        $this->assertTrue($contact->canAccess('calendar'));
        $this->assertTrue($contact->canAccess('launch_programs'));
        $this->assertTrue($contact->canAccess('cases'));
        $this->assertTrue($contact->canAccess('company_profile'));
    }

    public function test_sales_role_capabilities(): void
    {
        $contact = Contact::factory()->portalSales()->create(['partner_id' => $this->partner->id]);

        $this->assertTrue($contact->canAccess('deal_registrations'));
        $this->assertTrue($contact->canAccess('enablement'));
        $this->assertTrue($contact->canAccess('blueprints'));
        $this->assertFalse($contact->canAccess('projects'));
        $this->assertFalse($contact->canAccess('goals'));
        $this->assertFalse($contact->canAccess('contacts'));
        $this->assertFalse($contact->canAccess('path'));
        $this->assertFalse($contact->canAccess('company_profile'));
    }

    public function test_delivery_lead_capabilities(): void
    {
        $contact = Contact::factory()->portalDelivery()->create(['partner_id' => $this->partner->id]);

        $this->assertTrue($contact->canAccess('projects'));
        $this->assertTrue($contact->canAccess('cases'));
        $this->assertTrue($contact->canAccess('enablement'));
        $this->assertTrue($contact->canAccess('blueprints'));
        $this->assertTrue($contact->canAccess('labs'));
        $this->assertTrue($contact->canAccess('workspaces'));
        $this->assertTrue($contact->canAccess('path'));
        $this->assertFalse($contact->canAccess('deal_registrations'));
        $this->assertFalse($contact->canAccess('goals'));
        $this->assertFalse($contact->canAccess('contacts'));
        $this->assertFalse($contact->canAccess('company_profile'));
    }

    public function test_engagement_manager_capabilities(): void
    {
        $contact = Contact::factory()->portalRole('engagement_manager')->create(['partner_id' => $this->partner->id]);

        $this->assertTrue($contact->canAccess('projects'));
        $this->assertTrue($contact->canAccess('enablement'));
        $this->assertTrue($contact->canAccess('blueprints'));
        $this->assertTrue($contact->canAccess('path'));
        $this->assertTrue($contact->canAccess('goals'));
        $this->assertTrue($contact->canAccess('calendar'));
        $this->assertTrue($contact->canAccess('launch_programs'));
        $this->assertFalse($contact->canAccess('deal_registrations'));
        $this->assertFalse($contact->canAccess('contacts'));
    }

    public function test_technical_architect_capabilities(): void
    {
        $contact = Contact::factory()->portalRole('technical_architect')->create(['partner_id' => $this->partner->id]);

        $this->assertTrue($contact->canAccess('labs'));
        $this->assertTrue($contact->canAccess('workspaces'));
        $this->assertTrue($contact->canAccess('blueprints'));
        $this->assertTrue($contact->canAccess('enablement'));
        $this->assertFalse($contact->canAccess('projects'));
        $this->assertFalse($contact->canAccess('goals'));
        $this->assertFalse($contact->canAccess('deal_registrations'));
    }

    public function test_marketing_capabilities(): void
    {
        $contact = Contact::factory()->portalRole('marketing')->create(['partner_id' => $this->partner->id]);

        $this->assertTrue($contact->canAccess('enablement'));
        $this->assertTrue($contact->canAccess('blueprints'));
        $this->assertFalse($contact->canAccess('projects'));
        $this->assertFalse($contact->canAccess('deal_registrations'));
        $this->assertFalse($contact->canAccess('goals'));
        $this->assertFalse($contact->canAccess('path'));
    }

    public function test_unknown_role_has_no_capabilities(): void
    {
        $contact = Contact::factory()->create([
            'partner_id' => $this->partner->id,
            'portal_access' => true,
            'portal_role_id' => null,
        ]);

        $this->assertFalse($contact->canAccess('projects'));
        $this->assertFalse($contact->canAccess('goals'));
    }

    // ─── dashboardGroup() ────────────────────────────────────────

    public function test_dashboard_groups(): void
    {
        $admin = Contact::factory()->portalAdmin()->make();
        $engMgr = Contact::factory()->portalRole('engagement_manager')->make();
        $pracLead = Contact::factory()->portalRole('practice_lead')->make();
        $sales = Contact::factory()->portalSales()->make();
        $preSales = Contact::factory()->portalRole('pre_sales')->make();
        $marketing = Contact::factory()->portalRole('marketing')->make();
        $delLead = Contact::factory()->portalDelivery()->make();
        $delRes = Contact::factory()->portalRole('delivery_resource')->make();
        $techArch = Contact::factory()->portalRole('technical_architect')->make();

        $this->assertEquals('admin', $admin->dashboardGroup());
        $this->assertEquals('admin', $engMgr->dashboardGroup());
        $this->assertEquals('admin', $pracLead->dashboardGroup());
        $this->assertEquals('sales', $sales->dashboardGroup());
        $this->assertEquals('sales', $preSales->dashboardGroup());
        $this->assertEquals('sales', $marketing->dashboardGroup());
        $this->assertEquals('delivery', $delLead->dashboardGroup());
        $this->assertEquals('delivery', $delRes->dashboardGroup());
        $this->assertEquals('delivery', $techArch->dashboardGroup());
    }

    // ─── isPortalAdmin() ─────────────────────────────────────────

    public function test_is_portal_admin(): void
    {
        $admin = Contact::factory()->portalAdmin()->make();
        $sales = Contact::factory()->portalSales()->make();

        $this->assertTrue($admin->isPortalAdmin());
        $this->assertFalse($sales->isPortalAdmin());
    }

    // ─── PortalCan Middleware ────────────────────────────────────

    public function test_portal_can_middleware_allows_capable_role(): void
    {
        $contact = Contact::factory()->portalRole('engagement_manager')->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.goals.index'));

        $response->assertOk();
    }

    public function test_portal_can_middleware_blocks_incapable_role(): void
    {
        $contact = Contact::factory()->portalRole('marketing')->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.goals.index'));

        $response->assertForbidden();
    }

    public function test_portal_can_middleware_allows_partner_admin_wildcard(): void
    {
        $contact = Contact::factory()->portalAdmin()->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($contact, 'portal')->get(route('portal.goals.index'));

        $response->assertOk();
    }

    public function test_deal_registrations_accessible_by_sales_and_pre_sales(): void
    {
        $sales = Contact::factory()->portalSales()->create(['partner_id' => $this->partner->id]);
        $preSales = Contact::factory()->portalRole('pre_sales')->create(['partner_id' => $this->partner->id]);
        $marketing = Contact::factory()->portalRole('marketing')->create(['partner_id' => $this->partner->id]);

        $this->actingAs($sales, 'portal')->get(route('portal.deal-registrations.index'))->assertOk();
        $this->actingAs($preSales, 'portal')->get(route('portal.deal-registrations.index'))->assertOk();
        $this->actingAs($marketing, 'portal')->get(route('portal.deal-registrations.index'))->assertForbidden();
    }

    public function test_projects_accessible_by_delivery_roles(): void
    {
        $delLead = Contact::factory()->portalDelivery()->create(['partner_id' => $this->partner->id]);
        $delRes = Contact::factory()->portalRole('delivery_resource')->create(['partner_id' => $this->partner->id]);
        $sales = Contact::factory()->portalSales()->create(['partner_id' => $this->partner->id]);

        $this->actingAs($delLead, 'portal')->get(route('portal.projects.index'))->assertOk();
        $this->actingAs($delRes, 'portal')->get(route('portal.projects.index'))->assertOk();
        $this->actingAs($sales, 'portal')->get(route('portal.projects.index'))->assertForbidden();
    }

    // ─── Role Validation ─────────────────────────────────────────

    public function test_update_role_accepts_new_role_keys(): void
    {
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        foreach (['partner_admin', 'delivery_lead', 'engagement_manager', 'practice_lead', 'marketing', 'sales', 'pre_sales', 'technical_architect', 'delivery_resource'] as $role) {
            $response = $this->actingAs($this->psm)->patch(
                route('partners.portal-users.update-role', [$this->partner, $contact]),
                ['portal_role' => $role]
            );

            $response->assertRedirect();
            $this->assertEquals($role, $contact->fresh()->role?->slug);
        }
    }

    public function test_update_role_rejects_old_role_keys(): void
    {
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->patch(
            route('partners.portal-users.update-role', [$this->partner, $contact]),
            ['portal_role' => 'admin']
        );

        $response->assertSessionHasErrors('portal_role');
    }

    // ─── Contact Import ──────────────────────────────────────────

    public function test_import_creates_new_contacts_from_csv(): void
    {
        $csv = "First Name,Last Name,Email,Phone,Title,Portal Role\n";
        $csv .= "John,Smith,john.smith@test.com,+1-555-0101,CTO,delivery_lead\n";
        $csv .= "Jane,Doe,jane.doe@test.com,+1-555-0102,VP Sales,sales\n";

        $file = UploadedFile::fake()->createWithContent('contacts.csv', $csv);

        $response = $this->actingAs($this->psm)->post(
            route('partners.portal-users.import', $this->partner),
            ['file' => $file]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', [
            'partner_id' => $this->partner->id,
            'email' => 'john.smith@test.com',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'portal_role_id' => Role::where('slug', 'delivery_lead')->first()?->id,
        ]);
        $this->assertDatabaseHas('contacts', [
            'partner_id' => $this->partner->id,
            'email' => 'jane.doe@test.com',
            'portal_role_id' => Role::where('slug', 'sales')->first()?->id,
        ]);
    }

    public function test_import_updates_existing_contacts(): void
    {
        $existing = Contact::factory()->create([
            'partner_id' => $this->partner->id,
            'first_name' => 'Old',
            'last_name' => 'Name',
            'title' => 'Old Title',
        ]);
        // Factory afterMaking overrides email, so use the actual email
        $existingEmail = $existing->email;

        $csv = "First Name,Last Name,Email,Phone,Title,Portal Role\n";
        $csv .= "New,Name,{$existingEmail},,Updated Title,partner_admin\n";

        $file = UploadedFile::fake()->createWithContent('contacts.csv', $csv);

        $response = $this->actingAs($this->psm)->post(
            route('partners.portal-users.import', $this->partner),
            ['file' => $file]
        );

        $response->assertRedirect();
        $existing->refresh();
        $this->assertEquals('New', $existing->first_name);
        $this->assertEquals('Name', $existing->last_name);
        $this->assertEquals('Updated Title', $existing->title);
        $this->assertEquals('partner_admin', $existing->role?->slug);
    }

    public function test_import_skips_rows_with_invalid_email(): void
    {
        $csv = "First Name,Last Name,Email,Phone,Title,Portal Role\n";
        $csv .= "Valid,User,valid@test.com,,,sales\n";
        $csv .= "Invalid,User,not-an-email,,,sales\n";
        $csv .= "Empty,Email,,,,\n";

        $file = UploadedFile::fake()->createWithContent('contacts.csv', $csv);

        $response = $this->actingAs($this->psm)->post(
            route('partners.portal-users.import', $this->partner),
            ['file' => $file]
        );

        $response->assertRedirect();
        $this->assertStringContainsString('1 created', session('success'));
        $this->assertStringContainsString('2 skipped', session('success'));
    }

    public function test_import_rejects_non_spreadsheet_file(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->psm)->post(
            route('partners.portal-users.import', $this->partner),
            ['file' => $file]
        );

        $response->assertSessionHasErrors('file');
    }

    public function test_download_template(): void
    {
        $response = $this->actingAs($this->psm)->get(route('partners.portal-users.template'));

        $response->assertOk();
        $response->assertDownload('contact_import_template.csv');
    }

    public function test_import_requires_authorization(): void
    {
        $otherPsm = User::factory()->psm()->create();
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        $csv = "First Name,Last Name,Email\nTest,User,test@test.com\n";
        $file = UploadedFile::fake()->createWithContent('contacts.csv', $csv);

        $response = $this->actingAs($this->psm)->post(
            route('partners.portal-users.import', $otherPartner),
            ['file' => $file]
        );

        $response->assertForbidden();
    }
}
