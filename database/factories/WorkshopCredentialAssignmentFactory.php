<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\WorkshopCredential;
use App\Models\WorkshopCredentialAssignment;
use App\Models\WorkshopSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopCredentialAssignment>
 */
class WorkshopCredentialAssignmentFactory extends Factory
{
    protected $model = WorkshopCredentialAssignment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_credential_id' => WorkshopCredential::factory(),
            'workshop_session_id' => WorkshopSession::factory(),
            'contact_id' => Contact::factory(),
            'assigned_at' => now(),
            'hold_until' => now()->addDays(5),
        ];
    }
}
