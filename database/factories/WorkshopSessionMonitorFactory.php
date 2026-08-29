<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionMonitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopSessionMonitor>
 */
class WorkshopSessionMonitorFactory extends Factory
{
    protected $model = WorkshopSessionMonitor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_session_id' => WorkshopSession::factory(),
            'user_id' => User::factory(),
            'assigned_at' => now(),
        ];
    }
}
