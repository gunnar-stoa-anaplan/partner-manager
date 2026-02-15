<?php

namespace Database\Factories;

use App\Models\AnaplanList;
use App\Models\AnaplanModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnaplanList>
 */
class AnaplanListFactory extends Factory
{
    protected $model = AnaplanList::class;

    protected static array $listNames = [
        'Accounts', 'Cost Centers', 'Products', 'Time Periods',
        'Departments', 'Entities', 'Versions', 'Scenarios',
        'Employees', 'Projects', 'Regions', 'Customers',
        'GL Accounts', 'Business Units', 'Channels',
    ];

    public function definition(): array
    {
        return [
            'anaplan_id' => $this->faker->uuid(),
            'name' => $this->faker->randomElement(self::$listNames),
            'anaplan_model_id' => AnaplanModel::factory(),
            'item_count' => $this->faker->numberBetween(10, 5000),
            'has_selective_access' => $this->faker->boolean(20),
        ];
    }
}
