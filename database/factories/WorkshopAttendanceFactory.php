<?php

namespace Database\Factories;

use App\Models\WorkshopAttendance;
use App\Models\ZoomMeeting;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkshopAttendanceFactory extends Factory
{
    protected $model = WorkshopAttendance::class;

    public function definition(): array
    {
        $joinTime = $this->faker->dateTimeBetween('-2 hours', '-1 hour');
        $leaveTime = (clone $joinTime)->modify('+'.rand(30, 120).' minutes');

        return [
            'zoom_meeting_id' => ZoomMeeting::factory(),
            'participant_email' => $this->faker->safeEmail(),
            'participant_name' => $this->faker->name(),
            'zoom_user_id' => $this->faker->uuid(),
            'join_time' => $joinTime,
            'leave_time' => $leaveTime,
            'duration_seconds' => $leaveTime->getTimestamp() - $joinTime->getTimestamp(),
            'source' => 'webhook',
        ];
    }

    public function fromReport(): static
    {
        return $this->state(fn () => [
            'source' => 'report',
            'attentiveness_score' => $this->faker->numberBetween(50, 100),
        ]);
    }
}
