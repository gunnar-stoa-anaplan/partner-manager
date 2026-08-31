<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => 'updated',
            'auditable_type' => 'App\\Models\\WorkshopPage',
            'auditable_id' => 1,
            'before' => ['title' => fake()->sentence(3)],
            'after' => ['title' => fake()->sentence(3)],
        ];
    }
}
