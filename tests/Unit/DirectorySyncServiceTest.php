<?php

namespace Tests\Unit;

use App\Models\FieldMapping;
use App\Models\IntegrationConnection;
use App\Models\IntegrationJob;
use App\Models\User;
use App\Services\DirectorySyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorySyncServiceTest extends TestCase
{
    use RefreshDatabase;

    private DirectorySyncService $service;

    private IntegrationConnection $connection;

    private IntegrationJob $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DirectorySyncService;

        $this->connection = IntegrationConnection::create([
            'name' => 'Test Sync',
            'type' => 'csv_upload',
            'direction' => 'import',
            'status' => 'active',
            'applied_templates' => ['user-directory-import'],
        ]);

        $mappings = [
            ['name' => 'Employee ID', 'source_field' => 'employee_id', 'target_model' => 'User', 'target_field' => 'external_id', 'transform' => 'trim'],
            ['name' => 'Full Name', 'source_field' => 'full_name', 'target_model' => 'User', 'target_field' => 'name', 'transform' => 'trim'],
            ['name' => 'Email', 'source_field' => 'email_address', 'target_model' => 'User', 'target_field' => 'email', 'transform' => 'lowercase'],
            ['name' => 'Manager ID', 'source_field' => 'manager_employee_id', 'target_model' => 'User', 'target_field' => 'manager_external_id', 'transform' => 'trim'],
        ];

        foreach ($mappings as $mapping) {
            FieldMapping::create(array_merge($mapping, [
                'connection_id' => $this->connection->id,
                'sort_order' => 0,
            ]));
        }

        $this->job = IntegrationJob::create([
            'connection_id' => $this->connection->id,
            'direction' => 'import',
            'status' => 'pending',
        ]);
    }

    private function createCsvFile(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($path, $content);

        return $path;
    }

    public function test_creates_new_users_from_csv(): void
    {
        $csv = "employee_id,full_name,email_address,manager_employee_id\nEMP001,Jane Doe,jane@example.com,\nEMP002,John Smith,john@example.com,\n";
        $path = $this->createCsvFile($csv);

        $result = $this->service->syncFromCsv($this->connection, $path, $this->job);

        $this->assertEquals(2, $result->created);
        $this->assertEquals(0, $result->updated);
        $this->assertEquals(0, $result->skipped);

        $this->assertDatabaseHas('users', [
            'external_id' => 'EMP001',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'directory_source' => 'csv',
        ]);

        $this->assertDatabaseHas('users', [
            'external_id' => 'EMP002',
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'directory_source' => 'csv',
        ]);

        unlink($path);
    }

    public function test_updates_existing_synced_users(): void
    {
        User::factory()->create([
            'external_id' => 'EMP001',
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'directory_source' => 'csv',
        ]);

        $csv = "employee_id,full_name,email_address,manager_employee_id\nEMP001,New Name,new@example.com,\n";
        $path = $this->createCsvFile($csv);

        $result = $this->service->syncFromCsv($this->connection, $path, $this->job);

        $this->assertEquals(0, $result->created);
        $this->assertEquals(1, $result->updated);

        $this->assertDatabaseHas('users', [
            'external_id' => 'EMP001',
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        unlink($path);
    }

    public function test_skips_locally_managed_users_matched_by_email(): void
    {
        User::factory()->create([
            'email' => 'local@example.com',
            'directory_source' => null,
            'external_id' => null,
        ]);

        $csv = "employee_id,full_name,email_address,manager_employee_id\nEMP001,Local User,local@example.com,\n";
        $path = $this->createCsvFile($csv);

        $result = $this->service->syncFromCsv($this->connection, $path, $this->job);

        $this->assertEquals(0, $result->created);
        $this->assertEquals(0, $result->updated);
        $this->assertEquals(1, $result->skipped);

        unlink($path);
    }

    public function test_resolves_manager_relationships(): void
    {
        $csv = "employee_id,full_name,email_address,manager_employee_id\nEMP001,Manager,manager@example.com,\nEMP002,Report,report@example.com,EMP001\n";
        $path = $this->createCsvFile($csv);

        $result = $this->service->syncFromCsv($this->connection, $path, $this->job);

        $this->assertEquals(2, $result->created);

        $manager = User::where('external_id', 'EMP001')->first();
        $report = User::where('external_id', 'EMP002')->first();

        $this->assertNotNull($manager);
        $this->assertNotNull($report);
        $this->assertEquals($manager->id, $report->manager_id);

        unlink($path);
    }

    public function test_assigns_default_role_to_new_users(): void
    {
        config(['team.directory.default_role' => 'psm']);

        $csv = "employee_id,full_name,email_address,manager_employee_id\nEMP001,Jane Doe,jane@example.com,\n";
        $path = $this->createCsvFile($csv);

        $this->service->syncFromCsv($this->connection, $path, $this->job);

        $this->assertDatabaseHas('users', [
            'external_id' => 'EMP001',
            'role' => 'psm',
        ]);

        unlink($path);
    }

    public function test_skips_rows_missing_required_fields(): void
    {
        $csv = "employee_id,full_name,email_address,manager_employee_id\n,Jane Doe,jane@example.com,\n";
        $path = $this->createCsvFile($csv);

        $result = $this->service->syncFromCsv($this->connection, $path, $this->job);

        $this->assertEquals(0, $result->created);
        $this->assertEquals(1, $result->skipped);
        $this->assertTrue($result->hasErrors());

        unlink($path);
    }

    public function test_applies_email_lowercase_transform(): void
    {
        $csv = "employee_id,full_name,email_address,manager_employee_id\nEMP001,Jane Doe,JANE@EXAMPLE.COM,\n";
        $path = $this->createCsvFile($csv);

        $this->service->syncFromCsv($this->connection, $path, $this->job);

        $this->assertDatabaseHas('users', [
            'external_id' => 'EMP001',
            'email' => 'jane@example.com',
        ]);

        unlink($path);
    }

    public function test_updates_integration_job_on_completion(): void
    {
        $csv = "employee_id,full_name,email_address,manager_employee_id\nEMP001,Jane Doe,jane@example.com,\n";
        $path = $this->createCsvFile($csv);

        $this->service->syncFromCsv($this->connection, $path, $this->job);

        $this->job->refresh();

        $this->assertEquals('completed', $this->job->status);
        $this->assertEquals(1, $this->job->total_rows);
        $this->assertEquals(1, $this->job->rows_processed);
        $this->assertNotNull($this->job->completed_at);

        unlink($path);
    }

    public function test_records_unresolvable_manager_as_error(): void
    {
        $csv = "employee_id,full_name,email_address,manager_employee_id\nEMP001,Jane Doe,jane@example.com,NONEXISTENT\n";
        $path = $this->createCsvFile($csv);

        $result = $this->service->syncFromCsv($this->connection, $path, $this->job);

        $this->assertEquals(1, $result->created);
        $this->assertTrue($result->hasErrors());
        $this->assertStringContainsString('NONEXISTENT', $result->errors[0]);

        unlink($path);
    }

    public function test_sets_last_synced_at_timestamp(): void
    {
        $csv = "employee_id,full_name,email_address,manager_employee_id\nEMP001,Jane Doe,jane@example.com,\n";
        $path = $this->createCsvFile($csv);

        $this->service->syncFromCsv($this->connection, $path, $this->job);

        $user = User::where('external_id', 'EMP001')->first();

        $this->assertNotNull($user->last_synced_at);

        unlink($path);
    }
}
