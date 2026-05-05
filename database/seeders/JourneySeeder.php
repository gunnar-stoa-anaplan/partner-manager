<?php

namespace Database\Seeders;

use App\Models\Journey;
use App\Models\User;
use Illuminate\Database\Seeder;

class JourneySeeder extends Seeder
{
    /**
     * Seed the journeys table with the IFP Delivery Journey and assign it
     * to the first admin user (falling back to the first user if none exist).
     */
    /**
     * @var list<array{journey_key: string, name: string, product_area: string, persona: string, status: string}>
     */
    private const JOURNEYS = [
        ['journey_key' => 'ifp-delivery-finance',     'name' => 'IFP Delivery Journey',          'product_area' => 'Integrated Financial Planning', 'persona' => 'Delivery',  'status' => 'pilot'],
        ['journey_key' => 'sales-foundations',        'name' => 'Sales Foundations',             'product_area' => 'Anaplan Platform',              'persona' => 'Sales',     'status' => 'active'],
        ['journey_key' => 'pre-sales-foundations',    'name' => 'Pre-Sales Foundations',         'product_area' => 'Anaplan Platform',              'persona' => 'Pre-Sales', 'status' => 'active'],
        ['journey_key' => 'anaplan-overview',         'name' => 'Anaplan Overview',              'product_area' => 'Anaplan Platform',              'persona' => 'All',       'status' => 'active'],
        ['journey_key' => 'anaplan-platform-found',   'name' => 'Anaplan Platform Foundations',  'product_area' => 'Anaplan Platform',              'persona' => 'Delivery',  'status' => 'active'],
        ['journey_key' => 'rpm-territory-quota',      'name' => 'RPM Territory & Quota Planning', 'product_area' => 'RPM',                          'persona' => 'Sales',     'status' => 'planned'],
        ['journey_key' => 'scm-demand-planning',      'name' => 'SCM Demand Planning',           'product_area' => 'SCM',                           'persona' => 'Delivery',  'status' => 'planned'],
    ];

    public function run(): void
    {
        $created = [];
        foreach (self::JOURNEYS as $row) {
            $created[] = Journey::firstOrCreate(
                ['journey_key' => $row['journey_key']],
                $row + ['db_path' => null, 'config_path' => null],
            );
        }

        // Assign every seeded journey to the first admin (legacy behavior).
        $admin = User::whereRelation('role', 'slug', 'admin')->first()
            ?? User::whereRelation('role', 'slug', 'super_admin')->first()
            ?? User::first();

        if ($admin) {
            foreach ($created as $journey) {
                $journey->assignments()->firstOrCreate(['user_id' => $admin->id]);
            }
        }
    }
}
