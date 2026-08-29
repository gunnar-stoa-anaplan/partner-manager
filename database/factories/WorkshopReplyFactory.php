<?php

namespace Database\Factories;

use App\Models\WorkshopReply;
use App\Models\WorkshopThread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopReply>
 */
class WorkshopReplyFactory extends Factory
{
    protected $model = WorkshopReply::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_thread_id' => WorkshopThread::factory(),
            'body' => fake()->sentence(),
        ];
    }
}
