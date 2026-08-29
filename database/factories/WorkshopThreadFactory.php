<?php

namespace Database\Factories;

use App\Models\WorkshopSession;
use App\Models\WorkshopThread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopThread>
 */
class WorkshopThreadFactory extends Factory
{
    protected $model = WorkshopThread::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_session_id' => WorkshopSession::factory(),
            'kind' => 'question',
            'body' => fake()->sentence(),
            'status' => 'open',
        ];
    }
}
