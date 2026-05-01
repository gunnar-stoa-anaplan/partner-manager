<?php

namespace Database\Seeders;

use App\Models\Journey;
use App\Services\JourneyAssetInitializer;
use Illuminate\Database\Seeder;

class FoundationalJourneySeeder extends Seeder
{
    /**
     * Seed the four foundational (horizontal) journeys that sit upstream of
     * every per-application journey. These are completed once per resource
     * regardless of which applications they later work with.
     *
     * Persona convention here:
     *   - 'all'       → universal, every persona
     *   - 'technical' → Delivery + Pre-Sales (technical roles)
     *   - 'sales' / 'pre-sales' → role-specific
     */
    public function run(JourneyAssetInitializer $initializer): void
    {
        $foundationals = [
            [
                'journey_key' => 'anaplan-overview',
                'name' => 'Anaplan Overview',
                'description' => 'Universal entry-point journey for every partner resource. Covers Anaplan as a company, the platform\'s purpose, and how the Connected Planning vision applies across customer engagements. Required prerequisite for every other journey.',
                'product_area' => 'foundation',
                'persona' => 'all',
                'status' => 'planned',
            ],
            [
                'journey_key' => 'anaplan-platform',
                'name' => 'Anaplan Platform',
                'description' => 'Deep platform fluency for technical roles — model architecture, lists, hierarchies, modules, formulas, and integrations. Required by Delivery (to configure models) and Pre-Sales (to drive demo environments). Sales does not require this journey.',
                'product_area' => 'foundation',
                'persona' => 'technical',
                'status' => 'planned',
            ],
            [
                'journey_key' => 'connected-planning-sales-foundations',
                'name' => 'Connected Planning Sales Foundations',
                'description' => 'Sales-foundation journey covering value-messaging fundamentals, Connected Planning enterprise narrative, and selling skills that apply across all Anaplan applications. Required prerequisite for every per-application Sales journey (e.g., IFP Sales, FCR Sales).',
                'product_area' => 'foundation',
                'persona' => 'sales',
                'status' => 'planned',
            ],
            [
                'journey_key' => 'connected-planning-pre-sales-foundations',
                'name' => 'Connected Planning Pre-Sales Foundations',
                'description' => 'Pre-sales foundation journey covering general discovery methodology, demo orchestration craft, and competitive battlecard fundamentals. Centerpiece is the one-day instructor-led Pre-Sales Workshop. Required prerequisite for every per-application Pre-Sales journey.',
                'product_area' => 'foundation',
                'persona' => 'pre-sales',
                'status' => 'planned',
            ],
        ];

        foreach ($foundationals as $f) {
            Journey::query()->updateOrCreate(
                ['journey_key' => $f['journey_key']],
                $f,
            );
        }

        $assetSeedTargets = [
            'anaplan-overview',
            'anaplan-platform',
            'connected-planning-sales-foundations',
            'connected-planning-pre-sales-foundations',
        ];
        foreach ($assetSeedTargets as $slug) {
            $journey = Journey::query()->where('journey_key', $slug)->first();
            if ($journey !== null) {
                $initializer->initialize($journey, [], $slug);
            }
        }
    }
}
