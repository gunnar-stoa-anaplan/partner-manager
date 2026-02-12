<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\PartnerEvent;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerEventSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating partner events...');

        $partners = Partner::all();
        $users = User::all();
        $count = 0;

        $eventTemplates = [
            'qbr' => ['title' => 'Quarterly Business Review — {quarter}', 'duration_hours' => 2],
            'check_in' => ['title' => 'Bi-Weekly Check-In', 'duration_hours' => 1],
            'training' => ['title' => '{topic} Training Session', 'duration_hours' => 4],
            'workshop' => ['title' => '{topic} Workshop', 'duration_hours' => 8],
            'demo' => ['title' => 'Solution Demo: {topic}', 'duration_hours' => 1],
            'joint_sales_call' => ['title' => 'Joint Sales Call — {account}', 'duration_hours' => 1],
            'kickoff' => ['title' => 'Project Kickoff — {account}', 'duration_hours' => 2],
            'escalation_review' => ['title' => 'Escalation Review: {topic}', 'duration_hours' => 1],
        ];

        $topics = ['IFP Implementation', 'SCM Best Practices', 'Polaris Migration', 'SPM Configuration', 'Pipeline Acceleration', 'Certification Planning', 'Delivery Quality'];
        $quarters = ['Q1 FY27', 'Q2 FY27', 'Q3 FY27', 'Q4 FY27'];

        foreach ($partners as $partner) {
            $psm = $users->firstWhere('id', $partner->user_id) ?? $users->random();
            $contacts = $partner->contacts;
            $eventCount = fake()->numberBetween(5, 12);

            for ($i = 0; $i < $eventCount; $i++) {
                $type = fake()->randomElement(array_keys($eventTemplates));
                $template = $eventTemplates[$type];

                $title = str_replace(
                    ['{quarter}', '{topic}', '{account}'],
                    [fake()->randomElement($quarters), fake()->randomElement($topics), fake()->company()],
                    $template['title']
                );

                $startAt = fake()->dateTimeBetween('-6 months', '+3 months');
                $isPast = $startAt < now();
                $endAt = (clone $startAt)->modify("+{$template['duration_hours']} hours");

                $event = PartnerEvent::create([
                    'partner_id' => $partner->id,
                    'created_by' => $psm->id,
                    'title' => $title,
                    'type' => $type,
                    'status' => $isPast ? 'completed' : 'scheduled',
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'all_day' => $template['duration_hours'] >= 8,
                    'location' => fake()->randomElement(['Virtual', 'Partner Office', 'Anaplan HQ', 'Customer Site']),
                    'description' => fake()->sentence(8),
                    'outcome' => $isPast ? fake()->sentence(6) : null,
                    'next_steps' => $isPast ? fake()->sentence(8) : null,
                ]);

                // Attach contacts to the event
                if ($contacts->isNotEmpty()) {
                    $attendeeCount = min(fake()->numberBetween(1, 4), $contacts->count());
                    $attendees = $contacts->random($attendeeCount);
                    foreach ($attendees as $contact) {
                        $event->participants()->syncWithoutDetaching([$contact->id]);
                    }
                }

                $count++;
            }
        }

        $this->command->info("Created {$count} partner events.");
    }
}
