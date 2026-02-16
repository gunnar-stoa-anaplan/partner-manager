<?php

namespace Database\Factories;

use App\Models\BlueprintContentItem;
use App\Models\BlueprintEnrollment;
use App\Models\BlueprintProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlueprintProgress>
 */
class BlueprintProgressFactory extends Factory
{
    protected $model = BlueprintProgress::class;

    public function definition(): array
    {
        return [
            'blueprint_enrollment_id' => BlueprintEnrollment::factory(),
            'blueprint_content_item_id' => BlueprintContentItem::factory(),
            'completed' => false,
            'completed_at' => null,
            'notes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
            'completed_at' => now(),
        ]);
    }
}
