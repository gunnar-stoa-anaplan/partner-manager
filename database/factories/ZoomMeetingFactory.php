<?php

namespace Database\Factories;

use App\Enums\ZoomMeetingStatus;
use App\Models\EnablementSession;
use App\Models\User;
use App\Models\ZoomMeeting;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZoomMeetingFactory extends Factory
{
    protected $model = ZoomMeeting::class;

    public function definition(): array
    {
        return [
            'enablement_session_id' => EnablementSession::factory(),
            'zoom_meeting_id' => (string) $this->faker->unique()->numberBetween(80000000000, 99999999999),
            'host_email' => $this->faker->safeEmail(),
            'join_url' => $this->faker->url(),
            'start_url' => $this->faker->url(),
            'password' => $this->faker->bothify('??####'),
            'topic' => $this->faker->sentence(4),
            'duration_minutes' => $this->faker->randomElement([60, 120, 180, 240]),
            'timezone' => 'UTC',
            'status' => ZoomMeetingStatus::Scheduled,
            'created_by' => User::factory(),
        ];
    }

    public function started(): static
    {
        return $this->state(fn () => [
            'status' => ZoomMeetingStatus::Started,
            'started_at' => now(),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn () => [
            'status' => ZoomMeetingStatus::Ended,
            'started_at' => now()->subHours(2),
            'ended_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => ZoomMeetingStatus::Cancelled,
        ]);
    }
}
