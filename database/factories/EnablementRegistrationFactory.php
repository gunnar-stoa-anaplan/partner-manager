<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\EnablementRegistration;
use App\Models\EnablementSession;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnablementRegistrationFactory extends Factory
{
    protected $model = EnablementRegistration::class;

    public function definition(): array
    {
        $nominatedAt = $this->faker->dateTimeBetween('-2 months', 'now');

        return [
            'enablement_session_id' => EnablementSession::factory(),
            'contact_id' => Contact::factory(),
            'partner_id' => Partner::factory(),
            'nominated_by' => User::inRandomOrder()->first()?->id ?? 1,
            'status' => 'nominated',
            'nominated_at' => $nominatedAt,
        ];
    }

    public function registered(): static
    {
        return $this->state(fn() => [
            'status' => 'registered',
            'registered_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn() => [
            'status' => 'confirmed',
            'registered_at' => $this->faker->dateTimeBetween('-2 months', '-1 month'),
            'confirmed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function attended(): static
    {
        return $this->state(fn() => [
            'status' => 'attended',
            'registered_at' => $this->faker->dateTimeBetween('-3 months', '-2 months'),
            'confirmed_at' => $this->faker->dateTimeBetween('-2 months', '-1 month'),
            'attended_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
