<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\LaunchProgramParticipant;
use App\Models\LaunchProgramPartner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaunchProgramParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'launch_program_partner_id' => LaunchProgramPartner::factory(),
            'contact_id' => Contact::factory(),
            'role' => fake()->randomElement(array_keys(LaunchProgramParticipant::ROLES)),
            'assigned_by' => User::factory(),
        ];
    }
}
