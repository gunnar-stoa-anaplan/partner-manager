<?php

namespace Database\Factories;

use App\Models\DealRegistration;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DealRegistration>
 */
class DealRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['draft', 'submitted', 'under_review', 'approved', 'rejected', 'expired']);
        $submittedAt = fake()->dateTimeBetween('-6 months', 'now');
        $isReviewed = in_array($status, ['approved', 'rejected']);

        return [
            'partner_id' => Partner::inRandomOrder()->value('id') ?? Partner::factory(),
            'deal_name' => fake()->company().' — '.fake()->randomElement([
                'Platform Migration',
                'Supply Chain Implementation',
                'Finance Planning',
                'Sales Performance',
                'Workforce Planning',
                'Enterprise Rollout',
                'Digital Transformation',
            ]),
            'customer_name' => fake()->company(),
            'customer_contact_email' => fake()->safeEmail(),
            'estimated_value' => fake()->randomElement([25000, 50000, 75000, 100000, 150000, 250000, 500000]),
            'expected_close_date' => fake()->dateTimeBetween('+1 month', '+12 months'),
            'product_area' => fake()->randomElement(array_keys(DealRegistration::PRODUCT_AREAS)),
            'description' => fake()->paragraph(),
            'status' => $status,
            'rejection_reason' => $status === 'rejected' ? fake()->sentence() : null,
            'reviewed_by' => $isReviewed ? (User::whereRelation('role', 'slug', 'admin')->inRandomOrder()->value('id') ?? 1) : null,
            'reviewed_at' => $isReviewed ? fake()->dateTimeBetween($submittedAt, 'now') : null,
            'submitted_at' => $status !== 'draft' ? $submittedAt : null,
            'expires_at' => $status !== 'draft' ? now()->addDays(fake()->numberBetween(-10, 90)) : null,
        ];
    }
}
