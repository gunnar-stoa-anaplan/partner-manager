<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'company' => $this->faker->company,
            'title' => $this->faker->jobTitle,
            'source' => $this->faker->randomElement(['website', 'partner', 'webcast', 'linkedin', 'referral', 'other']),
            'status' => 'unassigned',
            'partner_id' => null,
            'notes' => $this->faker->sentence,
        ];
    }
}