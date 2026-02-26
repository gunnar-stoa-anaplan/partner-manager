<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\QbrSection;
use App\Models\QbrSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class QbrSessionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating QBR sessions and sections...');

        $partners = Partner::all();
        $users = User::all();
        $admin = User::whereRelation('role', 'slug', 'admin')->first();
        $sessionCount = 0;
        $sectionCount = 0;

        $quarters = ['Q1 FY27', 'Q2 FY27', 'Q3 FY27', 'Q4 FY27'];
        $ratings = ['strong', 'satisfactory', 'needs_improvement', 'at_risk'];

        foreach ($partners as $partner) {
            $psm = $users->firstWhere('id', $partner->user_id) ?? $admin;
            $contacts = $partner->contacts;

            // Each partner gets 1-3 QBR sessions (past/current quarters)
            $numSessions = fake()->numberBetween(1, 3);
            $selectedQuarters = fake()->randomElements($quarters, $numSessions);

            foreach ($selectedQuarters as $quarter) {
                $qNum = (int) substr($quarter, 1, 1);

                // Determine status based on quarter timing (approximate)
                $monthsFromNow = ($qNum - 2) * 3; // Q2 is roughly current
                $status = match (true) {
                    $qNum <= 1 => 'completed',
                    $qNum === 2 => fake()->randomElement(['completed', 'in_progress', 'scheduled']),
                    $qNum === 3 => fake()->randomElement(['planning', 'scheduled']),
                    default => 'planning',
                };

                $scheduledDate = fake()->dateTimeBetween('-4 months', '+3 months');
                $completedDate = $status === 'completed'
                    ? fake()->dateTimeBetween('-4 months', 'now')
                    : null;

                // Build attendees list
                $attendees = [
                    ['name' => $psm->name, 'role' => 'PSM', 'type' => 'internal'],
                ];
                if ($contacts->isNotEmpty()) {
                    $attendeeContacts = $contacts->random(min(fake()->numberBetween(1, 3), $contacts->count()));
                    foreach ($attendeeContacts as $contact) {
                        $attendees[] = [
                            'name' => $contact->name,
                            'role' => $contact->title ?? 'Partner Contact',
                            'type' => 'partner',
                        ];
                    }
                }

                $overallRating = in_array($status, ['completed', 'in_progress'])
                    ? fake()->randomElement($ratings)
                    : null;

                $session = QbrSession::create([
                    'partner_id' => $partner->id,
                    'fiscal_quarter' => $quarter,
                    'status' => $status,
                    'scheduled_date' => $scheduledDate,
                    'completed_date' => $completedDate,
                    'prepared_by' => $psm->id,
                    'attendees' => $attendees,
                    'overall_rating' => $overallRating,
                    'executive_summary' => in_array($status, ['completed', 'in_progress'])
                        ? $this->generateExecutiveSummary($partner->name, $overallRating)
                        : null,
                ]);
                $sessionCount++;

                // Generate sections
                $sectionTypes = [
                    'scorecard_review' => 0,
                    'pipeline_review' => 1,
                    'delivery_review' => 2,
                    'enablement_review' => 3,
                    'goals_review' => 4,
                    'path_review' => 5,
                    'task_review' => 6,
                    'action_review' => 7,
                    'open_discussion' => 8,
                ];

                foreach ($sectionTypes as $type => $order) {
                    $sectionRating = in_array($status, ['completed', 'in_progress'])
                        ? fake()->randomElement($ratings)
                        : null;

                    QbrSection::create([
                        'qbr_session_id' => $session->id,
                        'section_type' => $type,
                        'sort_order' => $order,
                        'auto_generated_data' => $this->generateSectionData($type, $partner),
                        'psm_notes' => in_array($status, ['completed', 'in_progress'])
                            ? fake()->paragraph(2)
                            : null,
                        'partner_feedback' => $status === 'completed' && fake()->boolean(60)
                            ? fake()->paragraph(1)
                            : null,
                        'rating' => $sectionRating,
                    ]);
                    $sectionCount++;
                }
            }
        }

        $this->command->info("Created {$sessionCount} QBR sessions, {$sectionCount} sections.");
    }

    private function generateExecutiveSummary(string $partnerName, ?string $rating): string
    {
        return match ($rating) {
            'strong' => "{$partnerName} continues to perform exceptionally well across all dimensions. Pipeline generation is ahead of plan, delivery quality remains high, and the team is actively pursuing certifications. Recommend advancing to next path stage.",
            'satisfactory' => "{$partnerName} is meeting expectations in most areas with some room for improvement. Pipeline is on track, though certification coverage could be expanded. Delivery metrics are solid. Continue current engagement cadence.",
            'needs_improvement' => "{$partnerName} is trending below plan in several key areas. Pipeline generation needs acceleration, and certification gaps need to be addressed. Created action plans to remediate. Increased engagement recommended.",
            'at_risk' => "{$partnerName} requires immediate attention. Significant gaps in pipeline, delivery, and engagement metrics. Escalated to management. Emergency action plan created with 30-day review checkpoint.",
            default => "QBR session for {$partnerName} — review in progress.",
        };
    }

    private function generateSectionData(string $type, $partner): array
    {
        return match ($type) {
            'scorecard_review' => [
                'current_score' => fake()->randomFloat(1, 30, 95),
                'previous_score' => fake()->randomFloat(1, 30, 95),
                'tier' => fake()->randomElement(['Platinum', 'Gold', 'Silver', 'Bronze']),
                'dimensions' => [
                    'certification' => fake()->randomFloat(1, 20, 100),
                    'pipeline' => fake()->randomFloat(1, 20, 100),
                    'delivery' => fake()->randomFloat(1, 20, 100),
                    'engagement' => fake()->randomFloat(1, 20, 100),
                    'growth' => fake()->randomFloat(1, 20, 100),
                    'self_sufficiency' => fake()->randomFloat(1, 20, 100),
                ],
            ],
            'pipeline_review' => [
                'total_pipeline' => fake()->randomFloat(0, 200000, 5000000),
                'sourced_pipeline' => fake()->randomFloat(0, 50000, 2000000),
                'influenced_pipeline' => fake()->randomFloat(0, 100000, 3000000),
                'win_rate' => fake()->randomFloat(1, 15, 55),
                'deals_in_flight' => fake()->numberBetween(3, 20),
                'avg_deal_size' => fake()->randomFloat(0, 75000, 400000),
            ],
            'delivery_review' => [
                'active_projects' => fake()->numberBetween(1, 8),
                'on_time_rate' => fake()->randomFloat(1, 60, 100),
                'csat_score' => fake()->randomFloat(1, 3.0, 5.0),
                'escalated_cases' => fake()->numberBetween(0, 3),
                'avg_resolution_days' => fake()->numberBetween(2, 15),
            ],
            'enablement_review' => [
                'total_certifications' => fake()->numberBetween(5, 30),
                'active_certifications' => fake()->numberBetween(3, 25),
                'expiring_30d' => fake()->numberBetween(0, 5),
                'blueprint_completion_rate' => fake()->randomFloat(1, 30, 90),
                'training_sessions_ytd' => fake()->numberBetween(2, 12),
            ],
            'goals_review' => [
                'sourced_target' => fake()->randomFloat(0, 500000, 3000000),
                'sourced_actual' => fake()->randomFloat(0, 100000, 2500000),
                'influenced_target' => fake()->randomFloat(0, 1000000, 8000000),
                'influenced_actual' => fake()->randomFloat(0, 300000, 7000000),
                'dimensions_on_track' => fake()->numberBetween(3, 8),
                'dimensions_total' => 8,
            ],
            'path_review' => [
                'current_stage' => fake()->randomElement(['Onboarded', 'Enabled', 'Performing', 'Accelerating', 'Strategic']),
                'days_in_stage' => fake()->numberBetween(14, 180),
                'gate_criteria_met' => fake()->numberBetween(2, 6),
                'gate_criteria_total' => fake()->numberBetween(4, 6),
                'gate_eligible' => fake()->boolean(40),
            ],
            'task_review' => [
                'total_tasks' => fake()->numberBetween(8, 25),
                'completed' => fake()->numberBetween(3, 15),
                'in_progress' => fake()->numberBetween(2, 8),
                'overdue' => fake()->numberBetween(0, 4),
                'blocked' => fake()->numberBetween(0, 2),
            ],
            'action_review' => [
                'total_plans' => fake()->numberBetween(2, 6),
                'open' => fake()->numberBetween(1, 4),
                'completed_this_quarter' => fake()->numberBetween(0, 3),
                'overdue' => fake()->numberBetween(0, 2),
            ],
            'open_discussion' => [
                'topics' => fake()->randomElements([
                    'Product roadmap alignment',
                    'Team expansion plans',
                    'New market opportunities',
                    'Competitive landscape',
                    'Executive sponsorship',
                    'Resource allocation',
                    'Technology adoption',
                ], 3),
            ],
            default => ['note' => 'Section data pending'],
        };
    }
}
