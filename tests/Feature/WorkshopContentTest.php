<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopPage;
use App\Models\WorkshopSession;
use App\Models\WorkshopToolkitAsset;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class WorkshopContentTest extends TestCase
{
    use RefreshDatabase;

    private User $coe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        Feature::for(null)->activate('workshops');
        Feature::for(null)->activate('portal');

        $this->coe = User::factory()->coe()->create();
    }

    public function test_internal_user_can_create_a_content_page(): void
    {
        $workshop = Workshop::factory()->create();

        $response = $this->actingAs($this->coe)->post(route('workshops.pages.store', $workshop), [
            'slug' => 'overview',
            'title' => 'Overview',
            'audience' => 'participant',
            'body' => 'Welcome to the workshop.',
            'checks_json' => json_encode([['id' => 'check-1', 'label' => 'Read the overview']]),
        ]);

        $response->assertRedirect(route('workshops.pages.index', $workshop));
        $this->assertDatabaseHas('workshop_pages', ['workshop_id' => $workshop->id, 'slug' => 'overview']);
    }

    public function test_participant_can_view_a_page_and_submit_a_check(): void
    {
        $session = WorkshopSession::factory()->create();
        $page = WorkshopPage::factory()->create([
            'workshop_id' => $session->workshop_id,
            'checks_json' => [['id' => 'check-1', 'label' => 'Did the thing']],
        ]);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.pages.show', [$session, $page]))->assertOk();

        $this->actingAs($contact, 'portal')->post(route('portal.my-workshops.pages.check', [$session, $page]), [
            'check_id' => 'check-1',
            'passed' => 1,
        ]);

        $this->assertDatabaseHas('workshop_check_attempts', [
            'contact_id' => $contact->id,
            'workshop_page_id' => $page->id,
            'check_id' => 'check-1',
            'passed' => 1,
        ]);

        $completion = $page->fresh()->completionFor($contact->id, $session->id);
        $this->assertEquals(1, $completion['passed']);
        $this->assertTrue($completion['complete']);
    }

    public function test_internal_user_can_view_a_content_page(): void
    {
        $workshop = Workshop::factory()->create();
        $page = WorkshopPage::factory()->create(['workshop_id' => $workshop->id, 'title' => 'Overview Page']);

        $response = $this->actingAs($this->coe)->get(route('workshops.pages.show', [$workshop, $page]));

        $response->assertOk();
        $response->assertSee('Overview Page');
    }

    /**
     * Regression test: page content is real curriculum HTML (headings,
     * callouts, tables) and must render as markup, not as escaped text.
     * A previous version of the portal view ran the body through
     * nl2br(e(...)), which silently turned real HTML into literal tags.
     */
    public function test_page_body_html_renders_unescaped_for_participants_and_admins(): void
    {
        $session = WorkshopSession::factory()->create();
        $page = WorkshopPage::factory()->create([
            'workshop_id' => $session->workshop_id,
            'body' => '<h2>Welcome</h2><p>Read the <strong>callout</strong> below.</p><div class="callout-tip">Tip</div>',
        ]);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $portalResponse = $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.pages.show', [$session, $page]));
        $portalResponse->assertOk();
        $portalResponse->assertSee('<h2>Welcome</h2>', false);
        $portalResponse->assertSee('<strong>callout</strong>', false);
        $portalResponse->assertDontSee('&lt;h2&gt;');

        $adminResponse = $this->actingAs($this->coe)->get(route('workshops.pages.show', [$session->workshop, $page]));
        $adminResponse->assertOk();
        $adminResponse->assertSee('<h2>Welcome</h2>', false);
        $adminResponse->assertDontSee('&lt;h2&gt;');
    }

    public function test_progress_view_renders_and_reports_json(): void
    {
        $session = WorkshopSession::factory()->create();
        WorkshopPage::factory()->create(['workshop_id' => $session->workshop_id]);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.progress', $session))->assertOk();
        $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.progress.json', $session))
            ->assertOk()
            ->assertJsonStructure(['session', 'pages']);
    }

    public function test_non_participant_cannot_view_a_page(): void
    {
        $session = WorkshopSession::factory()->create();
        $page = WorkshopPage::factory()->create(['workshop_id' => $session->workshop_id]);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();

        $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.pages.show', [$session, $page]))->assertForbidden();
    }

    public function test_admin_can_upload_and_download_toolkit_asset_and_participant_can_download(): void
    {
        Storage::fake('local');
        $workshop = Workshop::factory()->create();
        $session = WorkshopSession::factory()->create(['workshop_id' => $workshop->id]);
        $contact = Contact::factory()->portalRole('workshop_delivery_resource')->create();
        $session->participants()->create(['contact_id' => $contact->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($this->coe)->post(route('workshops.toolkit.store', $workshop), [
            'title' => 'Setup Guide',
            'file' => UploadedFile::fake()->create('setup.pdf', 100, 'application/pdf'),
        ]);

        $asset = WorkshopToolkitAsset::firstWhere('title', 'Setup Guide');
        $this->assertNotNull($asset);

        $this->actingAs($this->coe)->get(route('workshops.toolkit.download', [$workshop, $asset]))->assertOk();
        $this->actingAs($contact, 'portal')->get(route('portal.my-workshops.toolkit.download', [$session, $asset]))->assertOk();
    }
}
