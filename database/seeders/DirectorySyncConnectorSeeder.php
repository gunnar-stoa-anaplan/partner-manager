<?php

namespace Database\Seeders;

use App\Models\ConnectorProfile;
use App\Models\MappingTemplate;
use Illuminate\Database\Seeder;

class DirectorySyncConnectorSeeder extends Seeder
{
    public function run(): void
    {
        ConnectorProfile::updateOrCreate(
            ['slug' => 'directory-sync'],
            [
                'name' => 'Directory Sync',
                'description' => 'Sync team members from an external directory or HR system. Import users, email addresses, and reporting structure via CSV upload.',
                'icon' => 'users',
                'connection_type' => 'csv_upload',
                'direction' => 'import',
                'default_config' => [],
                'config_schema' => [],
                'template_slugs' => ['user-directory-import'],
                'sort_order' => 10,
            ],
        );

        MappingTemplate::updateOrCreate(
            ['slug' => 'user-directory-import', 'version' => '1.0'],
            [
                'name' => 'User Directory Import',
                'description' => 'Import team members from a directory CSV — syncs names, emails, and manager hierarchy.',
                'target_model' => 'User',
                'required_feature' => 'directory_sync_admin',
                'is_current' => true,
                'fields' => [
                    ['key' => 'external_id', 'source_field' => 'employee_id', 'target_field' => 'external_id', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Unique employee identifier from the directory'],
                    ['key' => 'name', 'source_field' => 'full_name', 'target_field' => 'name', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Employee full name'],
                    ['key' => 'email', 'source_field' => 'email_address', 'target_field' => 'email', 'transform' => 'lowercase', 'default_value' => null, 'is_required' => true, 'description' => 'Employee email address'],
                    ['key' => 'manager_external_id', 'source_field' => 'manager_employee_id', 'target_field' => 'manager_external_id', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Manager employee ID — resolved to manager_id after all users are imported'],
                ],
            ],
        );
    }
}
