<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partner;
use App\Models\User;
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

        Partner::factory()
            ->count(10)
            ->make()
            ->each(function ($partner) use ($users) {
                $partner->user_id = $users->random()->id;
                $partner->salesforce_id = '00Q' . Str::upper(Str::random(15));
                $partner->save();
            });

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