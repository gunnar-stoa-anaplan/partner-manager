<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\LaunchProgramFeedback;
use App\Models\LaunchProgramPartner;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaunchProgramFeedbackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'launch_program_partner_id' => LaunchProgramPartner::factory(),
            'contact_id' => Contact::factory(),
            'type' => fake()->randomElement(array_keys(LaunchProgramFeedback::TYPES)),
            'rating' => fake()->optional(0.8)->numberBetween(1, 5),
            'body' => fake()->paragraph(),
        ];
    }
}
