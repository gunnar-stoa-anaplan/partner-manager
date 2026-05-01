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

        $personas = ['delivery', 'sales', 'pre-sales'];

        $owner = User::whereRelation('role', 'slug', 'admin')->first()
            ?? User::whereRelation('role', 'slug', 'super_admin')->first()
            ?? User::first();

        foreach ($personas as $persona) {
            $journeyKey = "{$productKey}-{$persona}";
            $personaLabel = ucwords(str_replace('-', ' ', $persona));
            $journey = Journey::firstOrCreate(
                ['journey_key' => $journeyKey],
                [
                    'name' => "{$context['product_name']} — {$personaLabel}",
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
