<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    private string $statusPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->admin()->create();
        $this->psm = User::factory()->psm()->create();

        $this->statusPath = sys_get_temp_dir().'/pm-status-test-'.uniqid();
        mkdir($this->statusPath, 0755, true);
        config(['version.agent_status_path' => $this->statusPath]);
        config(['version.current' => '2.1.0']);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->statusPath.'/*'));
        if (is_dir($this->statusPath)) {
            rmdir($this->statusPath);
        }

        parent::tearDown();
    }

    // ─── Access Control ──────────────────────────────────────────

    public function test_admin_can_view_system_status(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.system-status'));

        $response->assertOk();
        $response->assertSee('System Status');
    }

    public function test_non_admin_cannot_view_system_status(): void
    {
        $response = $this->actingAs($this->psm)->get(route('admin.system-status'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_view_system_status(): void
    {
        $response = $this->get(route('admin.system-status'));

        $response->assertRedirect(route('login'));
    }

    // ─── View Data ───────────────────────────────────────────────

    public function test_shows_current_version(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.system-status'));

        $response->assertOk();
        $response->assertSee('v2.1.0');
    }

    public function test_shows_service_health_cards(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.system-status'));

        $response->assertOk();
        $response->assertSee('Database');
        $response->assertSee('Cache');
        $response->assertSee('Queue');
        $response->assertSee('Mail');
    }

    public function test_shows_database_as_healthy(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.system-status'));

        $response->assertOk();
        $response->assertSee('Healthy');
    }

    public function test_shows_runtime_info(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.system-status'));

        $response->assertOk();
        $response->assertSee('PHP Version');
        $response->assertSee(PHP_VERSION);
    }

    // ─── Agent Status ────────────────────────────────────────────

    public function test_shows_not_connected_when_no_agent_file(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.system-status'));

        $response->assertOk();
        $response->assertSee('Not Connected');
        $response->assertSee('Agent Not Connected');
    }

    public function test_shows_connected_when_agent_recently_heartbeated(): void
    {
        $this->writeAgentStatus([
            'agent_version' => '1.2.0',
            'instance_id' => 'inst_abc123',
            'last_heartbeat' => now()->toIso8601String(),
            'uptime_seconds' => 86400,
            'containers' => [
                ['name' => 'partner-manager-app-1', 'image' => 'partner-manager:2.1.0', 'state' => 'running', 'status' => 'Up 3 days'],
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.system-status'));

        $response->assertOk();
        $response->assertSee('Connected');
        $response->assertSee('inst_abc123');
        $response->assertSee('partner-manager-app-1');
        $response->assertSee('partner-manager:2.1.0');
    }

    public function test_shows_stale_when_agent_heartbeat_is_old(): void
    {
        $this->writeAgentStatus([
            'agent_version' => '1.2.0',
            'instance_id' => 'inst_abc123',
            'last_heartbeat' => now()->subMinutes(5)->toIso8601String(),
            'uptime_seconds' => 86400,
            'containers' => [],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.system-status'));

        $response->assertOk();
        $response->assertSee('Stale');
    }

    // ─── Updates Section ─────────────────────────────────────────

    public function test_shows_up_to_date_when_no_update(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.system-status'));

        $response->assertOk();
        $response->assertSee('Up to Date');
    }

    public function test_shows_available_update(): void
    {
        file_put_contents($this->statusPath.'/version-check.json', json_encode([
            'version' => '2.2.0',
            'type' => 'update',
            'released' => '2026-02-16',
            'summary' => 'New scorecard templates',
        ]));

        $response = $this->actingAs($this->admin)->get(route('admin.system-status'));

        $response->assertOk();
        $response->assertSee('2.2.0 Available');
        $response->assertSee('New scorecard templates');
        $response->assertSee('Review Update');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $data
     */
    private function writeAgentStatus(array $data): void
    {
        file_put_contents(
            $this->statusPath.'/agent-status.json',
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }
}
