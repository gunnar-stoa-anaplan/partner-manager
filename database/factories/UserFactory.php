<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Assign an internal role by slug (creates the role if it doesn't exist).
     */
    public function withRole(string $slug, array $attributes = []): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(
                ['slug' => $slug, 'type' => 'internal'],
                array_merge(['display_name' => ucwords(str_replace('_', ' ', $slug))], $attributes)
            )->id,
        ]);
    }

    public function admin(): static
    {
        return $this->withRole('admin', [
            'display_name' => 'Administrator',
            'bypass_permissions' => true,
            'is_protected' => true,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->withRole('super_admin', [
            'display_name' => 'Super Admin',
            'bypass_permissions' => true,
            'is_protected' => true,
        ]);
    }

    public function psm(): static
    {
        return $this->withRole('psm', ['display_name' => 'Partner Success Manager']);
    }

    public function pam(): static
    {
        return $this->withRole('pam', ['display_name' => 'Partner Account Manager']);
    }

    public function coe(): static
    {
        return $this->withRole('coe', ['display_name' => 'Center of Excellence']);
    }

    public function manager(): static
    {
        return $this->withRole('manager', ['display_name' => 'Manager']);
    }

    public function integration(): static
    {
        return $this->withRole('integration', ['display_name' => 'Integration']);
    }

    public function journeyOwner(): static
    {
        return $this->withRole('journey_owner', ['display_name' => 'Journey Owner']);
    }
}
