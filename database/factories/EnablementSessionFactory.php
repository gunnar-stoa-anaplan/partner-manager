<?php

namespace Database\Factories;

use App\Models\EnablementEvent;
use App\Models\EnablementSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnablementSessionFactory extends Factory
{
    protected $model = EnablementSession::class;

    public function definition(): array
    {
        $startAt = $this->faker->dateTimeBetween('now', '+6 months');
        $durationHours = $this->faker->randomElement([1, 2, 4, 8]);
        $endAt = (clone $startAt)->modify("+{$durationHours} hours");

        return [
            'enablement_event_id' => EnablementEvent::factory(),
            'title_override' => null,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'all_day' => $durationHours >= 8,
            'timezone' => $this->faker->randomElement(['UTC', 'America/New_York', 'America/Chicago', 'America/Los_Angeles', 'Europe/London', 'Europe/Berlin', 'Asia/Tokyo', 'Asia/Singapore']),
            'location' => $this->faker->randomElement([
                'Virtual', 'Virtual', 'Virtual',
                'San Francisco, CA', 'New York, NY', 'London, UK',
                'Singapore', 'Tokyo, Japan', 'Sydney, Australia',
                'Minneapolis, MN', 'Chicago, IL', 'Paris, France',
                'Frankfurt, Germany', 'Bangalore, India',
            ]),
            'meeting_url' => $this->faker->optional(0.6)->url(),
            'instructor' => $this->faker->name(),
            'geography' => $this->faker->randomElement(['NA', 'EMEA', 'APJ', 'LATAM']),
            'region' => $this->faker->randomElement(['US West', 'US East', 'UK/I', 'DACH', 'Nordics', 'ANZ', 'Japan', 'SEA', 'India', 'Brazil']),
            'max_participants' => null,
            'status' => 'scheduled',
            'notes' => null,
            'registration_deadline' => null,
            'created_by' => User::where('role', 'admin')->first()?->id ?? 1,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn() => [
            'status' => 'completed',
            'start_at' => $this->faker->dateTimeBetween('-3 months', '-1 week'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn() => ['status' => 'cancelled']);
    }
}
