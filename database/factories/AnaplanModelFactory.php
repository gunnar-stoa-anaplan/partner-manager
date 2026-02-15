<?php

namespace Database\Factories;

use App\Models\AnaplanModel;
use App\Models\AnaplanWorkspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnaplanModel>
 */
class AnaplanModelFactory extends Factory
{
    protected $model = AnaplanModel::class;

    protected static array $modelNames = [
        'Revenue Planning', 'Headcount Plan', 'Demand Forecasting',
        'Supply Chain Model', 'Financial Consolidation', 'Workforce Planning',
        'Sales Forecasting', 'Expense Management', 'Budget Model',
        'Cash Flow Forecasting', 'Territory Planning', 'Quota Management',
        'Capacity Planning', 'Scenario Analysis', 'Driver-Based Planning',
    ];

    protected static array $categories = [
        'Finance', 'Sales', 'Supply Chain', 'HR', 'Operations', 'General',
    ];

    public function definition(): array
    {
        return [
            'anaplan_id' => $this->faker->uuid(),
            'name' => $this->faker->randomElement(self::$modelNames),
            'anaplan_workspace_id' => AnaplanWorkspace::factory(),
            'active_state' => $this->faker->randomElement(['PRODUCTION', 'UNLOCKED']),
            'model_url' => 'https://us1a.app.anaplan.com/'.$this->faker->uuid(),
            'category' => $this->faker->randomElement(self::$categories),
            'memory_usage' => $this->faker->numberBetween(50_000_000, 2_000_000_000),
            'created_in_anaplan_at' => $this->faker->dateTimeBetween('-2 years', '-3 months'),
            'last_modified_in_anaplan_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'last_modified_by_guid' => $this->faker->uuid(),
            'last_saved_serial_number' => $this->faker->numberBetween(100, 9999),
            'module_count' => $this->faker->numberBetween(3, 15),
            'list_count' => $this->faker->numberBetween(2, 10),
            'last_synced_at' => $this->faker->dateTimeBetween('-2 hours', 'now'),
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'active_state' => 'ARCHIVED',
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn () => [
            'active_state' => 'LOCKED',
        ]);
    }
}
