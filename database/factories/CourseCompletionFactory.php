<?php

namespace Database\Factories;

use App\Models\CourseCompletion;
use App\Models\Import;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseCompletion>
 */
class CourseCompletionFactory extends Factory
{
    protected $model = CourseCompletion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'resource_code' => $this->faker->unique()->safeEmail(),
            'person_name' => $this->faker->name(),
            'course_name' => $this->faker->randomElement([
                'Certified Model Builder',
                'Professional Solution Architect - Practice Exam',
                'Integrated Financial Planning (IFP) App 101 for Partners',
                'Operational Workforce Planning (OWP) Field Readiness Kit',
            ]),
            'completed_on' => $this->faker->dateTimeBetween('-2 years')->format('Y-m-d'),
            'partner_hq' => $this->faker->company(),
            'partner_region' => null,
            'import_id' => Import::factory(),
        ];
    }

    /**
     * Set a specific partner for the completion.
     */
    public function forPartner(int $partnerId): static
    {
        return $this->state(fn (array $attributes) => [
            'partner_id' => $partnerId,
        ]);
    }
}
