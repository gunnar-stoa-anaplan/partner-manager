<?php

namespace Database\Factories;

use App\Models\ContentAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentAsset>
 */
class ContentAssetFactory extends Factory
{
    protected $model = ContentAsset::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(array_keys(ContentAsset::TYPES));

        return [
            'name' => $this->faker->sentence(3),
            'type' => $type,
            'lob' => $this->faker->randomElement(ContentAsset::LOBS),
            'application' => $this->faker->randomElement(['IFP', 'Territory and Quota Planning', 'Demand Planning', 'Compensation']),
            'persona' => $this->faker->randomElement(ContentAsset::PERSONAS),
            'url' => 'https://example.com/'.$this->faker->slug(),
            'owner_name' => $this->faker->name(),
            'secondary_owner_name' => null,
            'status' => 'available',
            'core_app' => $this->faker->randomElement(['yes', 'no', 'na']),
            'launch_year' => $this->faker->randomElement(['pre-2025', '2025', '2026']),
            'is_priority_need' => false,
            'is_launching_in_progress' => false,
            'validated_at' => null,
            'notes' => null,
            'metadata' => null,
        ];
    }
}
