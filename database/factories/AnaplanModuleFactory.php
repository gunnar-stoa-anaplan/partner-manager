<?php

namespace Database\Factories;

use App\Models\AnaplanModel;
use App\Models\AnaplanModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnaplanModule>
 */
class AnaplanModuleFactory extends Factory
{
    protected $model = AnaplanModule::class;

    protected static array $moduleNames = [
        'Revenue Inputs', 'P&L Summary', 'Assumptions',
        'Data Hub', 'Reporting Module', 'FX Rates',
        'Employee Master', 'Cost Allocation', 'Dashboard Data',
        'Driver Inputs', 'Actuals Import', 'Variance Analysis',
        'KPI Scorecard', 'Scenario Comparison', 'Time Settings',
    ];

    public function definition(): array
    {
        return [
            'anaplan_id' => $this->faker->uuid(),
            'name' => $this->faker->randomElement(self::$moduleNames),
            'anaplan_model_id' => AnaplanModel::factory(),
        ];
    }
}
