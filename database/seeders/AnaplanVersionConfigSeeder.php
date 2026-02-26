<?php

namespace Database\Seeders;

use App\Models\AnaplanVersionConfig;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class AnaplanVersionConfigSeeder extends Seeder
{
    /**
     * Seed version configs and the notification template.
     */
    public function run(): void
    {
        $currentVersions = [
            'Finance' => 'V2.0',
            'Supply Chain' => 'V2.0',
            'HR & Workforce' => 'V2.0',
            'Sales & Marketing' => 'V2.0',
        ];

        foreach ($currentVersions as $area => $version) {
            AnaplanVersionConfig::updateOrCreate(
                ['product_area' => $area],
                ['current_version' => $version]
            );
        }

        // Remove old IFP config if it exists
        AnaplanVersionConfig::where('product_area', 'IFP')->delete();

        // Seed the notification template for version alerts
        $adminUser = \App\Models\User::whereRelation('role', 'slug', 'admin')->first();
        NotificationTemplate::updateOrCreate(
            ['event_type' => 'anaplan_version_outdated'],
            [
                'channel' => 'both',
                'subject' => 'Anaplan Version Update Available for {{{partner_name}}}',
                'body' => '{{{partner_name}}} is running {{{latest_version}}} of {{{product_area}}}. The current version is {{{current_version}}}. Please coordinate with the partner to plan an upgrade.',
                'is_active' => true,
                'created_by' => $adminUser?->id ?? 1,
                'updated_by' => $adminUser?->id ?? 1,
            ]
        );
    }
}
