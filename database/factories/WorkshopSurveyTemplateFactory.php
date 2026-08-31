<?php

namespace Database\Factories;

use App\Models\Workshop;
use App\Models\WorkshopSurveyTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopSurveyTemplate>
 */
class WorkshopSurveyTemplateFactory extends Factory
{
    protected $model = WorkshopSurveyTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_id' => Workshop::factory(),
            'kind' => 'post',
            'intro' => fake()->sentence(),
            'questions_json' => [
                ['id' => 'q1', 'text' => fake()->sentence().'?', 'type' => 'single_select', 'options' => ['A', 'B', 'C'], 'correct' => ['A']],
            ],
            'passing_score' => 70,
            'published_at' => now(),
        ];
    }
}
