<?php

namespace Database\Factories;

use App\Models\Workshop;
use App\Models\WorkshopPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopPage>
 */
class WorkshopPageFactory extends Factory
{
    protected $model = WorkshopPage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_id' => Workshop::factory(),
            'slug' => fake()->unique()->slug(),
            'title' => fake()->sentence(3),
            'body' => fake()->paragraphs(3, true),
            'sort_order' => 0,
            'checks_json' => [['id' => 'check-1', 'label' => 'Completed the exercise']],
        ];
    }
}
