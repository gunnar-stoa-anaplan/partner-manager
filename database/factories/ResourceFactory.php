<?php

namespace Database\Factories;

use App\Models\Import;
use App\Models\Partner;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'name' => $this->faker->name(),
            'role' => $this->faker->randomElement(['Delivery', 'Pre-Sales', 'Sales']),
            'program' => $this->faker->randomElement(['IFP', 'I&D', 'SPM', 'OWP']),
            'status' => $this->faker->randomElement(['Started', 'Completed']),
            'import_id' => Import::factory(),
        ];
    }

    /**
     * Set a specific partner for the resource.
     */
    public function forPartner($partnerId): static
    {
        return $this->state(fn (array $attributes) => [
            'partner_id' => $partnerId,
        ]);
    }

    /**
     * Set a specific import for the resource.
     */
    public function forImport($importId): static
    {
        return $this->state(fn (array $attributes) => [
            'import_id' => $importId,
        ]);
    }

    /**
     * Mark resource as completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Completed',
        ]);
    }

    /**
     * Mark resource as started.
     */
    public function started(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Started',
        ]);
    }

    /**
     * Set a specific program.
     */
    public function program(string $program): static
    {
        return $this->state(fn (array $attributes) => [
            'program' => $program,
        ]);
    }
}
