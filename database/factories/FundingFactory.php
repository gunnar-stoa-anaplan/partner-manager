<?php

namespace Database\Factories;

use App\Models\Funding;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundingFactory extends Factory
{
    protected $model = Funding::class;

    public function definition(): array
    {
        return [
            'partner_id'  => Partner::inRandomOrder()->first()?->id ?? Partner::factory(),
            'name'        => $this->faker->randomElement(['MDF', 'SPF', 'Co-Marketing', 'Training Fund', 'Demo Fund']) . ' - ' . $this->faker->company,
            'amount'      => $this->faker->randomFloat(2, 5000, 100000),
            'status'      => $this->faker->randomElement(['Pending', 'Approved', 'Disbursed', 'Rejected']),
            'type'        => $this->faker->randomElement(['MDF', 'SPF', 'Co-Marketing', 'Training', 'Demo']),
            'description' => $this->faker->sentence,
            'date'        => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
