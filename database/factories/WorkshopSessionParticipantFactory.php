<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopSessionParticipant>
 */
class WorkshopSessionParticipantFactory extends Factory
{
    protected $model = WorkshopSessionParticipant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_session_id' => WorkshopSession::factory(),
            'contact_id' => Contact::factory(),
            'status' => 'invited',
            'enrolled_at' => now(),
        ];
    }
}
