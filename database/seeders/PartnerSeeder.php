<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\User;
use Database\Factories\ContactFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PartnerSeeder extends Seeder
{
    protected static array $geoRegionMap = [
        'Americas' => ['North America', 'Latin America', 'Canada'],
        'EMEA' => ['Northern Europe', 'Southern Europe', 'Middle East & Africa', 'UK & Ireland'],
        'APAC' => ['ANZ', 'Japan', 'Southeast Asia', 'India'],
    ];

    public function run(): void
    {
        $users = User::all();
        $existingNames = Partner::pluck('name')->toArray();

        // Use known partner names from ContactFactory, skipping any already seeded (e.g. by GunnarSeeder)
        $knownNames = array_keys(ContactFactory::KNOWN_PARTNERS);

        $namesToCreate = collect($knownNames)
            ->reject(fn (string $name) => in_array($name, $existingNames))
            ->values();

        foreach ($namesToCreate as $name) {
            $geography = array_rand(self::$geoRegionMap);
            $region = self::$geoRegionMap[$geography][array_rand(self::$geoRegionMap[$geography])];

            Partner::factory()->create([
                'name' => $name,
                'user_id' => $users->random()->id,
                'salesforce_id' => '00Q'.Str::upper(Str::random(15)),
                'geography' => $geography,
                'region' => $region,
            ]);
        }

        // Backfill any existing partners missing geography/region
        Partner::whereNull('geography')->orWhereNull('region')->each(function ($partner) {
            $geos = array_keys(self::$geoRegionMap);
            $geography = $geos[array_rand($geos)];
            $regions = self::$geoRegionMap[$geography];
            $region = $regions[array_rand($regions)];

            $partner->update([
                'geography' => $geography,
                'region' => $region,
            ]);
        });
    }
}
