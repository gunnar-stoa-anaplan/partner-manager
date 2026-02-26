<?php

namespace Tests\Feature;

use App\Jobs\ProcessDirectorySync;
use App\Models\FieldMapping;
use App\Models\IntegrationConnection;
use App\Models\IntegrationJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class DirectorySyncTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $coe;

    private User $psm;

    private IntegrationConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->coe = User::factory()->create(['role' => 'coe']);
        $this->psm = User::factory()->create(['role' => 'psm']);

        Feature::for(null)->activate('directory_sync_admin');

        $this->connection = IntegrationConnection::create([
            'name' => 'Test Directory Sync',
            'type' => 'csv_upload',
            'direction' => 'import',
            'status' => 'active',
            'applied_templates' => ['user-directory-import'],
        ]);

        // Create field mappings for the connection
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
    }

    public function test_admin_can_access_directory_sync_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('directory-sync.index'))
            ->assertOk();
    }

    public function test_coe_can_access_directory_sync_index(): void
    {
        $this->actingAs($this->coe)
            ->get(route('directory-sync.index'))
            ->assertOk();
    }

    public function test_psm_cannot_access_directory_sync_index(): void
    {
        $this->actingAs($this->psm)
            ->get(route('directory-sync.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('directory-sync.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_bypasses_feature_flag(): void
    {
        // Admins and CoE bypass all feature gating by design (ModuleRegistry::isEnabled)
        Feature::for(null)->deactivate('directory_sync_admin');

        $this->actingAs($this->admin)
            ->get(route('directory-sync.index'))
            ->assertOk();
    }

    public function test_admin_can_upload_csv(): void
    {
        Bus::fake();

        $csv = "employee_id,full_name,email_address,manager_employee_id\nEMP001,Jane Doe,jane@example.com,\n";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('directory-sync.upload'), [
                'connection_id' => $this->connection->id,
                'csv_file' => $file,
            ])
            ->assertRedirect(route('directory-sync.index'))
            ->assertSessionHas('success');

        Bus::assertDispatched(ProcessDirectorySync::class);

        $this->assertDatabaseHas('integration_jobs', [
            'connection_id' => $this->connection->id,
            'direction' => 'import',
        ]);
    }

    public function test_upload_requires_csv_file(): void
    {
        $this->actingAs($this->admin)
            ->post(route('directory-sync.upload'), [
                'connection_id' => $this->connection->id,
            ])
            ->assertSessionHasErrors('csv_file');
    }

    public function test_upload_requires_valid_connection(): void
    {
        $csv = "employee_id,full_name,email_address\nEMP001,Jane Doe,jane@example.com\n";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('directory-sync.upload'), [
                'connection_id' => 99999,
                'csv_file' => $file,
            ])
            ->assertSessionHasErrors('connection_id');
    }

    public function test_synced_user_fields_are_protected_on_update(): void
    {
        $syncedUser = User::factory()->create([
            'role' => 'psm',
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'directory_source' => 'csv',
            'external_id' => 'EMP001',
        ]);

        $this->actingAs($this->admin)
            ->put(route('users.update', $syncedUser), [
                'name' => 'Changed Name',
                'email' => 'changed@example.com',
                'role' => 'manager',
                'manager_id' => null,
            ]);

        $syncedUser->refresh();

        // Synced fields should NOT be changed
        $this->assertEquals('Original Name', $syncedUser->name);
        $this->assertEquals('original@example.com', $syncedUser->email);

        // Local fields should be changed
        $this->assertEquals('manager', $syncedUser->role);
    }

    public function test_local_user_fields_are_not_protected(): void
    {
        $localUser = User::factory()->create([
            'role' => 'psm',
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'directory_source' => null,
        ]);

        $this->actingAs($this->admin)
            ->put(route('users.update', $localUser), [
                'name' => 'Changed Name',
                'email' => 'changed@example.com',
                'role' => 'manager',
                'manager_id' => null,
            ]);

        $localUser->refresh();

        $this->assertEquals('Changed Name', $localUser->name);
        $this->assertEquals('changed@example.com', $localUser->email);
        $this->assertEquals('manager', $localUser->role);
    }

    public function test_user_model_is_synced_from_directory(): void
    {
        $synced = User::factory()->create(['directory_source' => 'csv']);
        $local = User::factory()->create(['directory_source' => null]);

        $this->assertTrue($synced->isSyncedFromDirectory());
        $this->assertFalse($local->isSyncedFromDirectory());
    }

    public function test_user_model_field_read_only(): void
    {
        $synced = User::factory()->create(['directory_source' => 'csv']);
        $local = User::factory()->create(['directory_source' => null]);

        $this->assertTrue($synced->isFieldReadOnly('name'));
        $this->assertTrue($synced->isFieldReadOnly('email'));
        $this->assertTrue($synced->isFieldReadOnly('manager_id'));
        $this->assertFalse($synced->isFieldReadOnly('role'));
        $this->assertFalse($synced->isFieldReadOnly('avatar'));

        $this->assertFalse($local->isFieldReadOnly('name'));
        $this->assertFalse($local->isFieldReadOnly('email'));
    }

    public function test_index_shows_sync_history(): void
    {
        IntegrationJob::create([
            'connection_id' => $this->connection->id,
            'direction' => 'import',
            'status' => 'completed',
            'total_rows' => 5,
            'rows_processed' => 4,
            'rows_failed' => 1,
            'summary' => ['created' => 3, 'updated' => 1, 'skipped' => 1],
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('directory-sync.index'))
            ->assertOk()
            ->assertSee('Test Directory Sync')
            ->assertSee('Completed');
    }

    public function test_edit_form_shows_sync_badge_for_directory_user(): void
    {
        $syncedUser = User::factory()->create([
            'role' => 'psm',
            'directory_source' => 'csv',
            'external_id' => 'EMP001',
            'last_synced_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('users.edit', $syncedUser))
            ->assertOk()
            ->assertSee('Synced from Csv')
            ->assertSee('This field is managed by directory sync');
    }

    public function test_edit_form_does_not_show_sync_badge_for_local_user(): void
    {
        $localUser = User::factory()->create([
            'role' => 'psm',
            'directory_source' => null,
        ]);

        $this->actingAs($this->admin)
            ->get(route('users.edit', $localUser))
            ->assertOk()
            ->assertDontSee('Synced from')
            ->assertDontSee('This field is managed by directory sync');
    }
}
