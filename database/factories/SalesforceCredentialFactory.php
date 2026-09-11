<?php

namespace Database\Factories;

use App\Models\SalesforceCredential;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesforceCredential>
 */
class SalesforceCredentialFactory extends Factory
{
    protected $model = SalesforceCredential::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => fake()->uuid(),
            'username' => fake()->unique()->safeEmail(),
            'client_secret_encrypted' => 'secret-'.fake()->uuid(),
        ];
    }
}
