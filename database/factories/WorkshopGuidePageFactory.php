<?php

namespace Database\Factories;

use App\Models\WorkshopGuide;
use App\Models\WorkshopGuidePage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopGuidePage>
 */
class WorkshopGuidePageFactory extends Factory
{
    protected $model = WorkshopGuidePage::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'workshop_guide_id' => WorkshopGuide::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'section' => fake()->randomElement(['Getting Started', 'Labs', 'Resources']),
            'sort_order' => fake()->numberBetween(0, 20),
            'lab_id' => null,
        ];
    }
}
