<?php

namespace Database\Factories;

use App\Models\SavedFilter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedFilter>
 */
class SavedFilterFactory extends Factory
{
    protected $model = SavedFilter::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'context' => 'state-of-business',
            'criteria' => ['region' => 'AMER'],
        ];
    }
}
