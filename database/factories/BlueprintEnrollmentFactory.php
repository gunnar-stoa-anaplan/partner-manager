<?php

namespace Database\Factories;

use App\Models\BlueprintEnrollment;
use App\Models\Contact;
use App\Models\PartnerBlueprintAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlueprintEnrollment>
 */
class BlueprintEnrollmentFactory extends Factory
{
    protected $model = BlueprintEnrollment::class;

    public function definition(): array
    {
        return [
            'partner_blueprint_assignment_id' => PartnerBlueprintAssignment::factory(),
            'contact_id' => Contact::factory(),
            'enrolled_by' => User::factory(),
            'status' => 'enrolled',
            'completed_at' => null,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
