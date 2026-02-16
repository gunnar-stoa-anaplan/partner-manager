<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    private string $statusPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->psm = User::factory()->create(['role' => 'psm']);

        $this->statusPath = sys_get_temp_dir().'/pm-agent-test-'.uniqid();
        mkdir($this->statusPath, 0755, true);
        config(['version.agent_status_path' => $this->statusPath]);
        config(['version.current' => '2.1.0']);
    }

    protected function tearDown(): void
    {
        // Clean up temp files
        array_map('unlink', glob($this->statusPath.'/*'));
        if (is_dir($this->statusPath)) {
            rmdir($this->statusPath);
        }

        parent::tearDown();
    }

    // ─── Access Control ──────────────────────────────────────────

    public function test_non_admin_cannot_view_update_page(): void
    {
        $response = $this->actingAs($this->psm)->get(route('admin.update'));

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_accept_update(): void
    {
        $response = $this->actingAs($this->psm)->post(route('admin.update.accept'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_view_update_page(): void
    {
        $response = $this->get(route('admin.update'));

        $response->assertRedirect(route('login'));
    }

    // ─── No Update Available ─────────────────────────────────────

    public function test_redirects_to_dashboard_when_no_update_available(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.update'));

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('info');
    }

    public function test_redirects_when_no_version_check_file_exists(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.update'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_redirects_when_version_check_file_has_same_version(): void
    {
        $this->writeVersionCheck(['version' => '2.1.0', 'type' => 'patch', 'released' => '2026-02-16', 'summary' => 'No change']);

        $response = $this->actingAs($this->admin)->get(route('admin.update'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_redirects_when_version_check_file_has_older_version(): void
    {
        $this->writeVersionCheck(['version' => '2.0.0', 'type' => 'patch', 'released' => '2026-02-16', 'summary' => 'Old']);

        $response = $this->actingAs($this->admin)->get(route('admin.update'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    // ─── Update Available ────────────────────────────────────────

    public function test_shows_update_page_when_newer_version_available(): void
    {
        $this->writeVersionCheck([
            'version' => '2.2.0',
            'type' => 'update',
            'released' => '2026-02-16',
            'summary' => 'New scorecard templates',
            'changelog' => ['Added scorecard export', 'Fixed date formatting'],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.update'));

        $response->assertOk();
        $response->assertViewHas('currentVersion', '2.1.0');
        $response->assertViewHas('update');
        $response->assertViewHas('isAccepted', false);
        $response->assertSee('2.2.0');
        $response->assertSee('New scorecard templates');
    }

    // ─── Accept Update ───────────────────────────────────────────

    public function test_admin_can_accept_update(): void
    {
        $this->writeVersionCheck([
            'version' => '2.2.0',
            'type' => 'update',
            'released' => '2026-02-16',
            'summary' => 'New features',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.update.accept'));

        $response->assertRedirect(route('admin.update'));
        $response->assertSessionHas('success');

        $this->assertFileExists($this->statusPath.'/update-accepted.json');

        $accepted = json_decode(file_get_contents($this->statusPath.'/update-accepted.json'), true);
        $this->assertEquals('2.2.0', $accepted['version']);
        $this->assertEquals($this->admin->id, $accepted['accepted_by_user_id']);
    }

    public function test_cannot_accept_when_no_update_available(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.update.accept'));

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('info');
    }

    public function test_cannot_accept_when_already_accepted(): void
    {
        $this->writeVersionCheck([
            'version' => '2.2.0',
            'type' => 'update',
            'released' => '2026-02-16',
            'summary' => 'New features',
        ]);

        file_put_contents($this->statusPath.'/update-accepted.json', json_encode(['version' => '2.2.0']));

        $response = $this->actingAs($this->admin)->post(route('admin.update.accept'));

        $response->assertRedirect(route('admin.update'));
        $response->assertSessionHas('info');
    }

    public function test_shows_accepted_state_on_update_page(): void
    {
        $this->writeVersionCheck([
            'version' => '2.2.0',
            'type' => 'update',
            'released' => '2026-02-16',
            'summary' => 'New features',
        ]);

        file_put_contents($this->statusPath.'/update-accepted.json', json_encode(['version' => '2.2.0']));

        $response = $this->actingAs($this->admin)->get(route('admin.update'));

        $response->assertOk();
        $response->assertViewHas('isAccepted', true);
        $response->assertSee('Update Accepted');
    }

    // ─── Invalid Data ────────────────────────────────────────────

    public function test_handles_malformed_version_check_file(): void
    {
        file_put_contents($this->statusPath.'/version-check.json', 'not valid json');

        $response = $this->actingAs($this->admin)->get(route('admin.update'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_handles_version_check_missing_version_field(): void
    {
        file_put_contents($this->statusPath.'/version-check.json', json_encode(['type' => 'update']));

        $response = $this->actingAs($this->admin)->get(route('admin.update'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    // ─── Banner ──────────────────────────────────────────────────

    public function test_update_banner_visible_to_admin_when_update_available(): void
    {
        $this->writeVersionCheck([
            'version' => '2.2.0',
            'type' => 'update',
            'released' => '2026-02-16',
            'summary' => 'New features available',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Version 2.2.0 is available');
        $response->assertSee('Review Update');
    }

    public function test_update_banner_not_visible_to_non_admin(): void
    {
        $this->writeVersionCheck([
            'version' => '2.2.0',
            'type' => 'update',
            'released' => '2026-02-16',
            'summary' => 'New features',
        ]);

        $response = $this->actingAs($this->psm)->get('/dashboard');

        $response->assertDontSee('Version 2.2.0 is available');
    }

    public function test_no_banner_when_no_update_available(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertDontSee('Review Update');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $data
     */
    private function writeVersionCheck(array $data): void
    {
        file_put_contents(
            $this->statusPath.'/version-check.json',
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }
}
