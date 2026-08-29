<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\WorkshopCheckAttempt;
use App\Models\WorkshopPage;
use App\Models\WorkshopSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopCheckAttempt>
 */
class WorkshopCheckAttemptFactory extends Factory
{
    protected $model = WorkshopCheckAttempt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'workshop_session_id' => WorkshopSession::factory(),
            'workshop_page_id' => WorkshopPage::factory(),
            'check_id' => 'check-1',
            'passed' => true,
            'attempted_at' => now(),
        ];
    }
}
