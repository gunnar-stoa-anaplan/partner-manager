<?php

namespace Database\Seeders;

use App\Models\Journey;
use App\Models\User;
use App\Services\JourneyAssetInitializer;
use Illuminate\Database\Seeder;

class JourneySeeder extends Seeder
{
    /**
     * Seed the three IFP journeys (Delivery, Sales, Pre-Sales) — instantiated
     * from the universal templates in resources/journey-templates/. Idempotent.
     * Each journey is assigned to the first admin user (falling back to the
     * first user if none exists).
     */
    public function run(JourneyAssetInitializer $initializer): void
    {
        $productKey = 'ifp';
        $context = [
            'product_id' => $productKey,
            'product_name' => 'Integrated Financial Planning',
            'product_short' => 'IFP',
            'adjacent_product' => 'FCR',
            'adjacent_journey_id' => 'fcr',
            'terminal_outcome' => 'Finance Expert designation -> FCR pathway',
        ];

        $personaDescriptions = [
            'delivery' => 'Per-application Delivery journey for Integrated Financial Planning. Covers OOTB IFP application understanding, the 10 methodology phases, hands-on configuration in a delivery sandbox, and progressive co-delivery to real clients. Earns Finance Delivery Expert designation.',
            'sales' => 'Per-application Sales journey for Integrated Financial Planning. Covers IFP value messaging, use cases, client references, competitive positioning, and end-to-end mock + real-customer pitches. Earns Finance Sales Expert designation.',
            'pre-sales' => 'Per-application Pre-Sales journey for Integrated Financial Planning. Covers IFP demo environment + script mastery, light-config patterns for client orgs/hierarchies/products, technical battlecards, and progressive customer demo engagements. Earns Finance Pre-Sales Expert designation.',
        ];

        $owner = User::whereRelation('role', 'slug', 'admin')->first()
            ?? User::whereRelation('role', 'slug', 'super_admin')->first()
            ?? User::first();

        foreach ($personaDescriptions as $persona => $description) {
            $journeyKey = "{$productKey}-{$persona}";
            $personaLabel = ucwords(str_replace('-', ' ', $persona));
            $journey = Journey::query()->updateOrCreate(
                ['journey_key' => $journeyKey],
                [
                    'name' => "{$context['product_name']} — {$personaLabel}",
                    'description' => $description,
                    'product_area' => $productKey,
                    'persona' => $persona,
                    'status' => 'planned',
                ]
            );

            $initializer->initialize($journey, $context);

            if ($owner) {
                $journey->assignments()->firstOrCreate(['user_id' => $owner->id]);
            }
        }
    }
}
