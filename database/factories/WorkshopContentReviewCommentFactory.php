<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkshopContentReviewComment;
use App\Models\WorkshopPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopContentReviewComment>
 */
class WorkshopContentReviewCommentFactory extends Factory
{
    protected $model = WorkshopContentReviewComment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_page_id' => WorkshopPage::factory(),
            'workshop_survey_template_id' => null,
            'author_user_id' => User::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
