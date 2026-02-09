<?php

namespace Database\Factories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    protected static array $geoRegionMap = [
        'Americas' => ['North America', 'Latin America', 'Canada'],
        'EMEA' => ['Northern Europe', 'Southern Europe', 'Middle East & Africa', 'UK & Ireland'],
        'APAC' => ['ANZ', 'Japan', 'Southeast Asia', 'India'],
    ];

    public function definition(): array
    {
        $geography = $this->faker->randomElement(array_keys(self::$geoRegionMap));
        $region = $this->faker->randomElement(self::$geoRegionMap[$geography]);

        return [
            'name' => $this->faker->company,
            'user_id' => null, // initially unassigned
            'geography' => $geography,
            'region' => $region,
        ];
    }
}