<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerMarketplaceEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartnerMarketplaceEntry>
 */
class PartnerMarketplaceEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'category' => $this->faker->randomElement(array_keys(PartnerMarketplaceEntry::CATEGORIES)),
            'title' => $this->faker->sentence(4),
            'link' => $this->faker->url(),
            'description' => $this->faker->paragraph(),
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
