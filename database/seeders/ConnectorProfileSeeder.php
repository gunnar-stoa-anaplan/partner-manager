<?php

namespace Database\Seeders;

use App\Models\ConnectorProfile;
use Illuminate\Database\Seeder;

class ConnectorProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'slug' => 'salesforce-crm',
                'name' => 'Salesforce CRM',
                'description' => 'Import opportunities, contacts, and accounts directly from Salesforce. Requires a Salesforce Connected App with API access.',
                'icon' => 'cloud',
                'connection_type' => 'salesforce',
                'direction' => 'both',
                'default_config' => [],
                'config_schema' => [
                    ['key' => 'instance_url', 'label' => 'Instance URL', 'type' => 'url', 'placeholder' => 'https://yourorg.my.salesforce.com', 'required' => true],
                    ['key' => 'client_id', 'label' => 'Client ID', 'type' => 'text', 'required' => true],
                    ['key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password', 'required' => true],
                ],
                'template_slugs' => [
                    'partner-import',
                    'contact-import',
                    'account-import',
                    'lead-import',
                    'opportunity-import',
                    'deal-reg-import',
                ],
                'sort_order' => 1,
            ],
            [
                'slug' => 'csv-upload',
                'name' => 'CSV File Upload',
                'description' => 'Upload CSV or Excel files manually for batch imports. Ideal for periodic data refreshes from spreadsheets or reports.',
                'icon' => 'file-text',
                'connection_type' => 'csv_upload',
                'direction' => 'import',
                'default_config' => [],
                'config_schema' => [],
                'template_slugs' => [
                    'partner-import',
                    'contact-import',
                    'opportunity-import',
                    'project-import',
                ],
                'sort_order' => 2,
            ],
            [
                'slug' => 'rest-inbound',
                'name' => 'REST API (Inbound)',
                'description' => 'Receive data from external systems via REST API. Configure the source endpoint to push data into Partner Manager.',
                'icon' => 'download',
                'connection_type' => 'rest_api',
                'direction' => 'import',
                'default_config' => [],
                'config_schema' => [
                    ['key' => 'base_url', 'label' => 'Source Base URL', 'type' => 'url', 'placeholder' => 'https://api.example.com/v1', 'required' => true],
                    ['key' => 'auth_type', 'label' => 'Authentication', 'type' => 'select', 'options' => ['none' => 'None', 'bearer' => 'Bearer Token', 'basic' => 'Basic Auth', 'api_key' => 'API Key'], 'required' => false],
                    ['key' => 'auth_value', 'label' => 'Auth Value', 'type' => 'password', 'placeholder' => 'Token or credentials', 'required' => false],
                ],
                'template_slugs' => [
                    'partner-import',
                    'contact-import',
                    'opportunity-import',
                ],
                'sort_order' => 3,
            ],
            [
                'slug' => 'rest-outbound',
                'name' => 'REST API (Outbound)',
                'description' => 'Push partner data to external systems via REST API. Configure the destination endpoint to receive data from Partner Manager.',
                'icon' => 'upload',
                'connection_type' => 'rest_api',
                'direction' => 'export',
                'default_config' => [],
                'config_schema' => [
                    ['key' => 'base_url', 'label' => 'Destination Base URL', 'type' => 'url', 'placeholder' => 'https://api.example.com/v1', 'required' => true],
                    ['key' => 'auth_type', 'label' => 'Authentication', 'type' => 'select', 'options' => ['none' => 'None', 'bearer' => 'Bearer Token', 'basic' => 'Basic Auth', 'api_key' => 'API Key'], 'required' => false],
                    ['key' => 'auth_value', 'label' => 'Auth Value', 'type' => 'password', 'placeholder' => 'Token or credentials', 'required' => false],
                ],
                'template_slugs' => [],
                'sort_order' => 4,
            ],
        ];

        foreach ($profiles as $profile) {
            ConnectorProfile::updateOrCreate(
                ['slug' => $profile['slug']],
                $profile,
            );
        }
    }
}
