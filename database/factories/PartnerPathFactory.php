<?php

namespace Database\Factories;

use App\Models\PartnerPath;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerPathFactory extends Factory
{
    protected $model = PartnerPath::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true).' Path',
            'description' => $this->faker->sentence(),
            'is_default' => false,
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
