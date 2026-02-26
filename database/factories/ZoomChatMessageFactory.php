<?php

namespace Database\Factories;

use App\Ai\Agents\ProductLine\SupplyChainExpert;
use App\Enums\ZoomChatMessageStatus;
use App\Models\ZoomChatMessage;
use App\Models\ZoomChatResponder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ZoomChatMessage>
 */
class ZoomChatMessageFactory extends Factory
{
    protected $model = ZoomChatMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'zoom_chat_responder_id' => ZoomChatResponder::factory(),
            'zoom_message_id' => fake()->unique()->uuid(),
            'sender_name' => fake()->name(),
            'sender_email' => fake()->safeEmail(),
            'message_text' => '@bot '.fake()->sentence(),
            'agent_class' => SupplyChainExpert::class,
            'status' => ZoomChatMessageStatus::Received,
        ];
    }

    public function responded(): static
    {
        return $this->state([
            'status' => ZoomChatMessageStatus::Responded,
            'response_text' => fake()->paragraph(),
            'tokens_used' => fake()->numberBetween(50, 500),
            'processing_time_ms' => fake()->numberBetween(500, 5000),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'status' => ZoomChatMessageStatus::Failed,
            'error_message' => fake()->sentence(),
        ]);
    }
}
