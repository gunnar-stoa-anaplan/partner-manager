<?php

namespace Tests\Unit;

use App\Services\VersionCheckService;
use Tests\TestCase;

class VersionCheckServiceTest extends TestCase
{
    private VersionCheckService $service;

    private string $statusPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statusPath = sys_get_temp_dir().'/pm-version-test-'.uniqid();
        mkdir($this->statusPath, 0755, true);
        config(['version.agent_status_path' => $this->statusPath]);
        config(['version.current' => '2.1.0']);

        $this->service = new VersionCheckService;
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->statusPath.'/*'));
        if (is_dir($this->statusPath)) {
            rmdir($this->statusPath);
        }

        parent::tearDown();
    }

    // ─── currentVersion ──────────────────────────────────────────

    public function test_returns_configured_version(): void
    {
        $this->assertEquals('2.1.0', $this->service->currentVersion());
    }

    public function test_returns_default_version_when_not_configured(): void
    {
        config(['version.current' => null]);

        $service = new VersionCheckService;

        $this->assertEquals('1.0.0', $service->currentVersion());
    }

    // ─── getAvailableUpdate ──────────────────────────────────────

    public function test_returns_null_when_no_status_file(): void
    {
        $this->assertNull($this->service->getAvailableUpdate());
    }

    public function test_returns_null_for_malformed_json(): void
    {
        file_put_contents($this->statusPath.'/version-check.json', 'bad json');

        $this->assertNull($this->service->getAvailableUpdate());
    }

    public function test_returns_null_when_version_field_missing(): void
    {
        $this->writeStatus(['type' => 'update', 'summary' => 'Something']);

        $this->assertNull($this->service->getAvailableUpdate());
    }

    public function test_returns_null_when_version_is_same(): void
    {
        $this->writeStatus(['version' => '2.1.0', 'type' => 'patch']);

        $this->assertNull($this->service->getAvailableUpdate());
    }

    public function test_returns_null_when_version_is_older(): void
    {
        $this->writeStatus(['version' => '2.0.0', 'type' => 'patch']);

        $this->assertNull($this->service->getAvailableUpdate());
    }

    public function test_returns_update_data_when_newer_version(): void
    {
        $data = [
            'version' => '2.2.0',
            'type' => 'update',
            'released' => '2026-02-16',
            'summary' => 'New features',
            'changelog' => ['Feature A', 'Fix B'],
        ];

        $this->writeStatus($data);

        $result = $this->service->getAvailableUpdate();

        $this->assertNotNull($result);
        $this->assertEquals('2.2.0', $result['version']);
        $this->assertEquals('update', $result['type']);
        $this->assertCount(2, $result['changelog']);
    }

    public function test_returns_update_for_major_version_jump(): void
    {
        $this->writeStatus(['version' => '3.0.0', 'type' => 'upgrade']);

        $result = $this->service->getAvailableUpdate();

        $this->assertNotNull($result);
        $this->assertEquals('3.0.0', $result['version']);
    }

    // ─── isUpdateAccepted ────────────────────────────────────────

    public function test_not_accepted_when_file_missing(): void
    {
        $this->assertFalse($this->service->isUpdateAccepted());
    }

    public function test_accepted_when_file_exists(): void
    {
        file_put_contents($this->statusPath.'/update-accepted.json', json_encode(['version' => '2.2.0']));

        $this->assertTrue($this->service->isUpdateAccepted());
    }

    // ─── acceptUpdate ────────────────────────────────────────────

    public function test_writes_acceptance_file(): void
    {
        $result = $this->service->acceptUpdate('2.2.0', 42);

        $this->assertTrue($result);
        $this->assertFileExists($this->statusPath.'/update-accepted.json');

        $data = json_decode(file_get_contents($this->statusPath.'/update-accepted.json'), true);
        $this->assertEquals('2.2.0', $data['version']);
        $this->assertEquals(42, $data['accepted_by_user_id']);
        $this->assertArrayHasKey('accepted_at', $data);
    }

    public function test_returns_false_when_directory_missing(): void
    {
        config(['version.agent_status_path' => '/nonexistent/path/that/does/not/exist']);

        $service = new VersionCheckService;
        $result = $service->acceptUpdate('2.2.0', 1);

        $this->assertFalse($result);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $data
     */
    private function writeStatus(array $data): void
    {
        file_put_contents(
            $this->statusPath.'/version-check.json',
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }
}
