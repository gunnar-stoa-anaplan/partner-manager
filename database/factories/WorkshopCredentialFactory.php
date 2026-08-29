<?php

namespace Database\Factories;

use App\Models\Workshop;
use App\Models\WorkshopCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopCredential>
 */
class WorkshopCredentialFactory extends Factory
{
    protected $model = WorkshopCredential::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_id' => Workshop::factory(),
            'email' => fake()->unique()->safeEmail(),
            'username' => fake()->unique()->userName(),
            'password_enc' => fake()->password(),
            'status' => 'available',
        ];
    }
}
