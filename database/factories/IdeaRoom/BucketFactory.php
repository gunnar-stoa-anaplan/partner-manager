<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\Bucket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bucket>
 */
class BucketFactory extends Factory
{
    protected $model = Bucket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->word(),
            'color' => fake()->hexColor(),
            'icon' => null,
            'sort_order' => 0,
            'is_default' => false,
        ];
    }
}
