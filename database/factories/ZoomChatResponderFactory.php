<?php

namespace Database\Factories;

use App\Ai\Agents\ProductLine\SupplyChainExpert;
use App\Models\User;
use App\Models\ZoomChatResponder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ZoomChatResponder>
 */
class ZoomChatResponderFactory extends Factory
{
    protected $model = ZoomChatResponder::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'zoom_meeting_id' => (string) fake()->unique()->numberBetween(80000000000, 99999999999),
            'agent_class' => SupplyChainExpert::class,
            'display_name' => 'AI Assistant',
            'trigger_prefix' => '@bot',
            'is_active' => true,
            'max_response_tokens' => 512,
            'rate_limit_per_minute' => 10,
            'created_by' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withAgent(string $agentClass): static
    {
        return $this->state(['agent_class' => $agentClass]);
    }
}
