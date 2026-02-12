<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\ContactInteraction;
use App\Models\Partner;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    private const INTERACTION_TYPES = [
        'meeting', 'call', 'email', 'event', 'linkedin', 'workshop', 'qbr',
    ];

    private const INTERACTION_TITLES = [
        'meeting' => ['Quarterly business review', 'Pipeline alignment call', 'Strategy session', 'Kickoff meeting'],
        'call' => ['Follow-up call', 'Deal review', 'Partner check-in', 'Escalation discussion'],
        'email' => ['Opportunity update', 'Resource request', 'Event invitation', 'Partnership recap'],
        'event' => ['Partner Summit', 'Product launch event', 'Regional meetup', 'Webinar attendee'],
        'linkedin' => ['LinkedIn engagement', 'Content sharing', 'Connection made', 'Post interaction'],
        'workshop' => ['Technical workshop', 'Sales enablement session', 'Product deep dive', 'Use case workshop'],
        'qbr' => ['Q1 QBR', 'Q2 QBR', 'Q3 QBR', 'Q4 QBR'],
    ];

    public function run(): void
    {
        $partners = Partner::all();
        $users = User::all();

        foreach ($partners as $partner) {
            // Create 10 contacts per partner with distributed contact_types
            $contacts = Contact::factory(10)->create([
                'partner_id' => $partner->id,
            ]);

            // Assign contact types: 3 resources, 3 influencers, 4 general
            // Stagger created_at across past 6 months so trend charts show growth
            $contacts->each(function ($contact, $index) {
                $backdatedAt = fake()->dateTimeBetween('-6 months', '-1 week');

                if ($index < 3) {
                    $contact->update([
                        'contact_type' => 'resource',
                        'resource_role' => fake()->randomElement(array_keys(Contact::RESOURCE_ROLES)),
                        'created_at' => $backdatedAt,
                    ]);
                } elseif ($index < 6) {
                    $contact->update([
                        'contact_type' => 'influencer',
                        'influencer_role' => fake()->randomElement(array_keys(Contact::INFLUENCER_ROLES)),
                        'created_at' => $backdatedAt,
                    ]);
                }
            });

            // Attach contacts to random projects
            $contacts->each(function ($contact) {
                $projectIds = Project::inRandomOrder()
                    ->limit(rand(1, 3))
                    ->pluck('id')
                    ->toArray();

                $contact->projects()->attach($projectIds);
            });

            // Create interactions for influencer contacts
            $influencers = $contacts->where('contact_type', 'influencer');
            $psm = $users->firstWhere('id', $partner->user_id) ?? $users->first();

            foreach ($influencers as $influencer) {
                $interactionCount = fake()->numberBetween(3, 8);

                for ($i = 0; $i < $interactionCount; $i++) {
                    $type = fake()->randomElement(self::INTERACTION_TYPES);
                    $titles = self::INTERACTION_TITLES[$type];
                    $interactionDate = fake()->dateTimeBetween('-6 months', 'now');

                    ContactInteraction::create([
                        'contact_id' => $influencer->id,
                        'partner_id' => $partner->id,
                        'logged_by' => $psm->id,
                        'interaction_type' => $type,
                        'title' => fake()->randomElement($titles),
                        'description' => fake()->sentence(10),
                        'interaction_date' => $interactionDate,
                        'outcome' => fake()->boolean(70) ? fake()->sentence(8) : null,
                        'next_steps' => fake()->boolean(60) ? fake()->sentence(6) : null,
                    ]);
                }
            }
        }
    }
}
