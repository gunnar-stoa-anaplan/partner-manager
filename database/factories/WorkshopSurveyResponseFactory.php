<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\WorkshopSession;
use App\Models\WorkshopSurveyResponse;
use App\Models\WorkshopSurveyTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopSurveyResponse>
 */
class WorkshopSurveyResponseFactory extends Factory
{
    protected $model = WorkshopSurveyResponse::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_survey_template_id' => WorkshopSurveyTemplate::factory(),
            'workshop_session_id' => WorkshopSession::factory(),
            'contact_id' => Contact::factory(),
            'responses_json' => ['q1' => 'A'],
            'score' => 100,
            'passed' => true,
            'attempt_number' => 1,
            'submitted_at' => now(),
        ];
    }
}
