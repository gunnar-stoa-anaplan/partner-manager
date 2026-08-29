<?php

namespace Database\Factories;

use App\Models\Workshop;
use App\Models\WorkshopToolkitAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopToolkitAsset>
 */
class WorkshopToolkitAssetFactory extends Factory
{
    protected $model = WorkshopToolkitAsset::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_id' => Workshop::factory(),
            'title' => fake()->sentence(3),
            'file_path' => 'workshop-toolkits/1/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
        ];
    }
}
