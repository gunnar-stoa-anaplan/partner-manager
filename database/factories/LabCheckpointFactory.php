<?php

namespace Database\Factories;

use App\Models\Lab;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LabCheckpoint>
 */
class LabCheckpointFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['formula', 'structure', 'config', 'value', 'list']);

        return [
            'lab_id' => Lab::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.7)->sentence(),
            'sort_order' => fake()->numberBetween(0, 10),
            'checkpoint_type' => $type,
            'validation_config' => $this->configForType($type),
            'is_required' => true,
            'points' => fake()->randomElement([1, 2, 3, 5]),
            'hint' => fake()->optional(0.6)->sentence(),
        ];
    }

    public function formula(): static
    {
        return $this->state(fn () => [
            'checkpoint_type' => 'formula',
            'validation_config' => $this->configForType('formula'),
        ]);
    }

    public function structure(): static
    {
        return $this->state(fn () => [
            'checkpoint_type' => 'structure',
            'validation_config' => $this->configForType('structure'),
        ]);
    }

    public function list(): static
    {
        return $this->state(fn () => [
            'checkpoint_type' => 'list',
            'validation_config' => $this->configForType('list'),
        ]);
    }

    public function value(): static
    {
        return $this->state(fn () => [
            'checkpoint_type' => 'value',
            'validation_config' => $this->configForType('value'),
        ]);
    }

    public function config(): static
    {
        return $this->state(fn () => [
            'checkpoint_type' => 'config',
            'validation_config' => $this->configForType('config'),
        ]);
    }

    /** @return array<string, mixed> */
    private function configForType(string $type): array
    {
        return match ($type) {
            'formula' => [
                'module_name' => 'Revenue Planning',
                'line_item_name' => 'Total Revenue',
                'module_anaplan_id' => fake()->uuid(),
                'line_item_anaplan_id' => fake()->uuid(),
                'expected_formula' => 'SUM(Revenue Data.Amount)',
            ],
            'structure' => [
                'type' => 'module',
                'name' => 'Sales Reporting',
                'expected_line_items' => ['Total Sales', 'Net Revenue', 'Gross Margin'],
            ],
            'list' => [
                'list_anaplan_id' => fake()->uuid(),
                'list_name' => 'Regions',
                'expected_items' => ['North', 'South', 'East', 'West'],
            ],
            'value' => [
                'module_name' => 'Summary',
                'line_item_name' => 'Grand Total',
                'export_anaplan_id' => fake()->uuid(),
                'expected_value' => 1000,
                'tolerance' => 0.01,
            ],
            'config' => [
                'module_anaplan_id' => fake()->uuid(),
                'line_item_anaplan_id' => fake()->uuid(),
                'expected_format' => 'NUMBER',
                'expected_summary' => 'SUM',
            ],
        };
    }
}
