<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->psm = User::factory()->create(['role' => 'psm']);

        Storage::fake('public');
    }

    // ─── Access Control ──────────────────────────────────────────

    public function test_admin_can_view_branding_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.branding'));

        $response->assertOk();
        $response->assertSee('Branding');
        $response->assertSee('Identity');
        $response->assertSee('Default Theme');
    }

    public function test_non_admin_cannot_view_branding_page(): void
    {
        $response = $this->actingAs($this->psm)->get(route('admin.branding'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_view_branding_page(): void
    {
        $response = $this->get(route('admin.branding'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_update_branding(): void
    {
        $response = $this->actingAs($this->psm)->put(route('admin.branding.update'), [
            'branding_app_name' => 'Test',
        ]);

        $response->assertForbidden();
    }

    // ─── App Name ────────────────────────────────────────────────

    public function test_admin_can_set_app_name(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.branding.update'), [
            'branding_app_name' => 'Acme Partner Hub',
        ]);

        $response->assertRedirect(route('admin.branding'));
        $response->assertSessionHas('success');
        $this->assertEquals('Acme Partner Hub', AppSetting::get('branding_app_name'));
    }

    public function test_admin_can_clear_app_name(): void
    {
        AppSetting::set('branding_app_name', 'Old Name');

        $response = $this->actingAs($this->admin)->put(route('admin.branding.update'), [
            'branding_app_name' => '',
        ]);

        $response->assertRedirect(route('admin.branding'));
        $this->assertEquals('', AppSetting::get('branding_app_name'));
    }

    // ─── Theme Selection ─────────────────────────────────────────

    public function test_admin_can_set_default_theme(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.branding.update'), [
            'branding_theme' => 'earth',
        ]);

        $response->assertRedirect(route('admin.branding'));
        $response->assertSessionHas('success');
        $this->assertEquals('earth', AppSetting::get('branding_theme'));
    }

    public function test_admin_can_clear_default_theme(): void
    {
        AppSetting::set('branding_theme', 'earth');

        $response = $this->actingAs($this->admin)->put(route('admin.branding.update'), [
            'branding_theme' => '',
        ]);

        $response->assertRedirect(route('admin.branding'));
        $this->assertEquals('', AppSetting::get('branding_theme'));
    }

    public function test_invalid_theme_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.branding.update'), [
            'branding_theme' => 'nonexistent-theme',
        ]);

        $response->assertSessionHasErrors('branding_theme');
    }

    // ─── Logo Upload ─────────────────────────────────────────────

    public function test_admin_can_upload_logo(): void
    {
        $file = UploadedFile::fake()->image('logo.png', 200, 100);

        $response = $this->actingAs($this->admin)->put(route('admin.branding.update'), [
            'branding_logo' => $file,
        ]);

        $response->assertRedirect(route('admin.branding'));
        $response->assertSessionHas('success');

        $logoPath = AppSetting::get('branding_logo_path');
        $this->assertNotEmpty($logoPath);
        Storage::disk('public')->assertExists($logoPath);
    }

    public function test_uploading_new_logo_deletes_old_one(): void
    {
        Storage::disk('public')->put('branding/old-logo.png', 'old');
        AppSetting::set('branding_logo_path', 'branding/old-logo.png');

        $file = UploadedFile::fake()->image('new-logo.png', 200, 100);

        $this->actingAs($this->admin)->put(route('admin.branding.update'), [
            'branding_logo' => $file,
        ]);

        Storage::disk('public')->assertMissing('branding/old-logo.png');

        $newPath = AppSetting::get('branding_logo_path');
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_admin_can_remove_logo(): void
    {
        Storage::disk('public')->put('branding/logo.png', 'content');
        AppSetting::set('branding_logo_path', 'branding/logo.png');

        $response = $this->actingAs($this->admin)->put(route('admin.branding.update'), [
            'remove_logo' => '1',
        ]);

        $response->assertRedirect(route('admin.branding'));
        Storage::disk('public')->assertMissing('branding/logo.png');
        $this->assertEquals('', AppSetting::get('branding_logo_path'));
    }

    // ─── Favicon Upload ──────────────────────────────────────────

    public function test_admin_can_upload_favicon(): void
    {
        $file = UploadedFile::fake()->image('favicon.png', 32, 32);

        $response = $this->actingAs($this->admin)->put(route('admin.branding.update'), [
            'branding_favicon' => $file,
        ]);

        $response->assertRedirect(route('admin.branding'));

        $faviconPath = AppSetting::get('branding_favicon_path');
        $this->assertNotEmpty($faviconPath);
        Storage::disk('public')->assertExists($faviconPath);
    }

    public function test_admin_can_remove_favicon(): void
    {
        Storage::disk('public')->put('branding/favicon.png', 'content');
        AppSetting::set('branding_favicon_path', 'branding/favicon.png');

        $response = $this->actingAs($this->admin)->put(route('admin.branding.update'), [
            'remove_favicon' => '1',
        ]);

        $response->assertRedirect(route('admin.branding'));
        Storage::disk('public')->assertMissing('branding/favicon.png');
        $this->assertEquals('', AppSetting::get('branding_favicon_path'));
    }

    // ─── Layout Integration ──────────────────────────────────────

    public function test_custom_app_name_appears_in_page_title(): void
    {
        AppSetting::set('branding_app_name', 'Acme Hub');

        $response = $this->actingAs($this->admin)->get(route('admin.branding'));

        $response->assertOk();
        $response->assertSee('Acme Hub', false);
    }

    public function test_default_app_name_used_when_not_set(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.branding'));

        $response->assertOk();
        $response->assertSee('Partner Manager', false);
    }

    public function test_default_theme_css_injected_when_set(): void
    {
        AppSetting::set('branding_theme', 'earth');

        $response = $this->actingAs($this->admin)->get(route('admin.branding'));

        $response->assertOk();
        $response->assertSee('/css/themes/earth.css', false);
    }

    public function test_no_theme_css_injected_when_not_set(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.branding'));

        $response->assertOk();
        $response->assertDontSee('default-theme-style', false);
    }

    public function test_page_shows_all_available_themes(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.branding'));

        $response->assertOk();
        $response->assertSee('Default');
        $response->assertSee('Earth');
        $response->assertSee('Aurora');
        $response->assertSee('Nebula');
    }
}
