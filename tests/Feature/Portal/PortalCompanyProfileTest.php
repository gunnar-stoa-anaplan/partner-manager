<?php

namespace Tests\Feature\Portal;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\PartnerMarketplaceEntry;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalCompanyProfileTest extends TestCase
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

    private function createPortalContact(string $role = 'admin'): Contact
    {
        return Contact::factory()->{'portal'.ucfirst($role)}()->create([
            'partner_id' => $this->partner->id,
        ]);
    }

    // ─── Access Control ───────────────────────────────────────────

    public function test_partner_admin_can_view_company_profile(): void
    {
        $contact = $this->createPortalContact('admin');

        $this->actingAs($contact, 'portal')
            ->get(route('portal.company-profile.edit'))
            ->assertOk()
            ->assertViewIs('portal.company-profile.edit');
    }

    public function test_non_admin_role_cannot_view_company_profile(): void
    {
        $contact = $this->createPortalContact('sales');

        $this->actingAs($contact, 'portal')
            ->get(route('portal.company-profile.edit'))
            ->assertForbidden();
    }

    public function test_unauthenticated_user_redirected_from_company_profile(): void
    {
        $this->get(route('portal.company-profile.edit'))
            ->assertRedirect(route('portal.login'));
    }

    // ─── Profile Update ───────────────────────────────────────────

    public function test_partner_admin_can_update_profile_fields(): void
    {
        $contact = $this->createPortalContact('admin');

        $this->actingAs($contact, 'portal')
            ->put(route('portal.company-profile.update'), [
                'phone' => '(415) 742-8199',
                'sales_territory' => 'West Coast',
                'mailing_street' => '50 Hawthorne Street',
                'mailing_city' => 'San Francisco',
                'mailing_state' => 'California',
                'mailing_postal_code' => '94105',
                'mailing_country' => 'United States of America',
                'headline' => 'Leading Connected Planning Partner',
                'company_description' => 'We deliver enterprise planning solutions.',
                'partner_locator_emails' => 'partners@example.com;list@example.com',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('partners', [
            'id' => $this->partner->id,
            'phone' => '(415) 742-8199',
            'mailing_city' => 'San Francisco',
            'headline' => 'Leading Connected Planning Partner',
            'partner_locator_emails' => 'partners@example.com;list@example.com',
        ]);
    }

    public function test_partner_admin_can_upload_logo(): void
    {
        Storage::fake('public');
        $contact = $this->createPortalContact('admin');

        $this->actingAs($contact, 'portal')
            ->put(route('portal.company-profile.update'), [
                'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
            ])
            ->assertRedirect();

        $this->partner->refresh();
        $this->assertNotNull($this->partner->logo_path);
        Storage::disk('public')->assertExists($this->partner->logo_path);
    }

    public function test_partner_admin_can_remove_logo(): void
    {
        Storage::fake('public');
        $contact = $this->createPortalContact('admin');

        // Upload first
        $path = UploadedFile::fake()->image('logo.png')->store('partner-logos', 'public');
        $this->partner->update(['logo_path' => $path]);

        $this->actingAs($contact, 'portal')
            ->put(route('portal.company-profile.update'), [
                'remove_logo' => '1',
            ])
            ->assertRedirect();

        $this->partner->refresh();
        $this->assertNull($this->partner->logo_path);
    }

    public function test_profile_update_validates_logo_file_type(): void
    {
        $contact = $this->createPortalContact('admin');

        $this->actingAs($contact, 'portal')
            ->put(route('portal.company-profile.update'), [
                'logo' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('logo');
    }

    public function test_profile_update_only_affects_own_partner(): void
    {
        $contact = $this->createPortalContact('admin');
        $otherPartner = Partner::factory()->create(['phone' => '555-0000']);

        $this->actingAs($contact, 'portal')
            ->put(route('portal.company-profile.update'), [
                'phone' => '555-1234',
            ])
            ->assertRedirect();

        $this->assertEquals('555-1234', $this->partner->fresh()->phone);
        $this->assertEquals('555-0000', $otherPartner->fresh()->phone);
    }

    // ─── Marketplace Entries — Create ─────────────────────────────

    public function test_partner_admin_can_create_marketplace_entry(): void
    {
        $contact = $this->createPortalContact('admin');

        $this->actingAs($contact, 'portal')
            ->post(route('portal.company-profile.entries.store'), [
                'category' => 'consumer_solutions',
                'title' => 'Our Consumer Solution',
                'link' => 'https://example.com/solution',
                'description' => 'A great consumer solution.',
                'sort_order' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('partner_marketplace_entries', [
            'partner_id' => $this->partner->id,
            'category' => 'consumer_solutions',
            'title' => 'Our Consumer Solution',
            'link' => 'https://example.com/solution',
            'sort_order' => 1,
        ]);
    }

    public function test_create_entry_validates_required_fields(): void
    {
        $contact = $this->createPortalContact('admin');

        $this->actingAs($contact, 'portal')
            ->post(route('portal.company-profile.entries.store'), [])
            ->assertSessionHasErrors(['category', 'title', 'link', 'description']);
    }

    public function test_create_entry_validates_category_must_be_predefined(): void
    {
        $contact = $this->createPortalContact('admin');

        $this->actingAs($contact, 'portal')
            ->post(route('portal.company-profile.entries.store'), [
                'category' => 'invalid_category',
                'title' => 'Test',
                'link' => 'https://example.com',
                'description' => 'Test description.',
            ])
            ->assertSessionHasErrors('category');
    }

    public function test_create_entry_validates_link_is_url(): void
    {
        $contact = $this->createPortalContact('admin');

        $this->actingAs($contact, 'portal')
            ->post(route('portal.company-profile.entries.store'), [
                'category' => 'consumer_solutions',
                'title' => 'Test',
                'link' => 'not-a-url',
                'description' => 'Test description.',
            ])
            ->assertSessionHasErrors('link');
    }

    // ─── Marketplace Entries — Update ─────────────────────────────

    public function test_partner_admin_can_update_marketplace_entry(): void
    {
        $contact = $this->createPortalContact('admin');
        $entry = PartnerMarketplaceEntry::factory()->create([
            'partner_id' => $this->partner->id,
            'title' => 'Old Title',
        ]);

        $this->actingAs($contact, 'portal')
            ->put(route('portal.company-profile.entries.update', $entry), [
                'category' => $entry->category,
                'title' => 'Updated Title',
                'link' => $entry->link,
                'description' => $entry->description,
                'sort_order' => 5,
            ])
            ->assertRedirect();

        $this->assertEquals('Updated Title', $entry->fresh()->title);
        $this->assertEquals(5, $entry->fresh()->sort_order);
    }

    public function test_cannot_update_entry_from_another_partner(): void
    {
        $contact = $this->createPortalContact('admin');
        $otherPartner = Partner::factory()->create();
        $entry = PartnerMarketplaceEntry::factory()->create(['partner_id' => $otherPartner->id]);

        $this->actingAs($contact, 'portal')
            ->put(route('portal.company-profile.entries.update', $entry), [
                'category' => $entry->category,
                'title' => 'Hijacked',
                'link' => $entry->link,
                'description' => $entry->description,
            ])
            // Row-level scope (ScopedToActor) hides another partner's entry, so
            // route-model binding 404s before the controller's ownership check —
            // the fail-closed, no-existence-leak outcome the spec prefers.
            ->assertNotFound();
    }

    // ─── Marketplace Entries — Delete ─────────────────────────────

    public function test_partner_admin_can_delete_marketplace_entry(): void
    {
        $contact = $this->createPortalContact('admin');
        $entry = PartnerMarketplaceEntry::factory()->create(['partner_id' => $this->partner->id]);

        $this->actingAs($contact, 'portal')
            ->delete(route('portal.company-profile.entries.destroy', $entry))
            ->assertRedirect();

        $this->assertDatabaseMissing('partner_marketplace_entries', ['id' => $entry->id]);
    }

    public function test_cannot_delete_entry_from_another_partner(): void
    {
        $contact = $this->createPortalContact('admin');
        $otherPartner = Partner::factory()->create();
        $entry = PartnerMarketplaceEntry::factory()->create(['partner_id' => $otherPartner->id]);

        $this->actingAs($contact, 'portal')
            ->delete(route('portal.company-profile.entries.destroy', $entry))
            // See update test above: scope 404s another partner's entry.
            ->assertNotFound();
    }

    // ─── Cascade Delete ───────────────────────────────────────────

    public function test_deleting_partner_cascades_marketplace_entries(): void
    {
        $entry = PartnerMarketplaceEntry::factory()->create(['partner_id' => $this->partner->id]);

        $this->partner->delete();

        $this->assertDatabaseMissing('partner_marketplace_entries', ['id' => $entry->id]);
    }

    // ─── Marketplace Status — Auto Submit ─────────────────────────

    public function test_saving_profile_with_headline_sets_status_to_submitted(): void
    {
        $contact = $this->createPortalContact('admin');
        $this->partner->update(['marketplace_status' => 'draft']);

        $this->actingAs($contact, 'portal')
            ->put(route('portal.company-profile.update'), [
                'headline' => 'Leading Connected Planning Partner',
            ])
            ->assertRedirect();

        $this->assertEquals('submitted', $this->partner->fresh()->marketplace_status);
    }

    public function test_saving_profile_without_headline_stays_draft(): void
    {
        $contact = $this->createPortalContact('admin');
        $this->partner->update(['marketplace_status' => 'draft']);

        $this->actingAs($contact, 'portal')
            ->put(route('portal.company-profile.update'), [
                'phone' => '555-1234',
            ])
            ->assertRedirect();

        $this->assertEquals('draft', $this->partner->fresh()->marketplace_status);
    }

    public function test_rejected_profile_resubmits_on_update(): void
    {
        $contact = $this->createPortalContact('admin');
        $this->partner->update([
            'marketplace_status' => 'rejected',
            'marketplace_rejection_reason' => 'Needs more detail.',
            'headline' => 'Old Headline',
        ]);

        $this->actingAs($contact, 'portal')
            ->put(route('portal.company-profile.update'), [
                'headline' => 'Updated Headline',
                'company_description' => 'Better description now.',
            ])
            ->assertRedirect();

        $partner = $this->partner->fresh();
        $this->assertEquals('submitted', $partner->marketplace_status);
        $this->assertNull($partner->marketplace_rejection_reason);
    }

    public function test_approved_profile_stays_approved_on_update(): void
    {
        $contact = $this->createPortalContact('admin');
        $this->partner->update([
            'marketplace_status' => 'approved',
            'headline' => 'Approved Headline',
        ]);

        $this->actingAs($contact, 'portal')
            ->put(route('portal.company-profile.update'), [
                'phone' => '555-9999',
            ])
            ->assertRedirect();

        $this->assertEquals('approved', $this->partner->fresh()->marketplace_status);
    }

    public function test_status_banner_shows_on_edit_page(): void
    {
        $contact = $this->createPortalContact('admin');
        $this->partner->update(['marketplace_status' => 'submitted']);

        $this->actingAs($contact, 'portal')
            ->get(route('portal.company-profile.edit'))
            ->assertOk()
            ->assertSee('under review');
    }
}
