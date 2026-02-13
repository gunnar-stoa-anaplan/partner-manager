<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\ContactInteraction;
use App\Models\Partner;
use App\Models\Project;
use App\Models\User;
use Database\Factories\ContactFactory;
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

    /**
     * Fixed portal contacts created for every partner.
     * These are deterministic so login credentials are consistent across seeds.
     */
    private const PORTAL_CONTACTS = [
        [
            'first_name' => 'Alex',
            'last_name' => 'Morgan',
            'title' => 'Partner Practice Lead',
            'contact_type' => 'resource',
            'resource_role' => 'solution_architect',
            'portal_access' => true,
            'portal_role' => 'admin',
        ],
        [
            'first_name' => 'Jordan',
            'last_name' => 'Lee',
            'title' => 'Delivery Manager',
            'contact_type' => 'resource',
            'resource_role' => 'project_manager',
            'portal_access' => true,
            'portal_role' => 'delivery',
        ],
        [
            'first_name' => 'Taylor',
            'last_name' => 'Chen',
            'title' => 'Client Account Lead',
            'contact_type' => 'influencer',
            'influencer_role' => 'client_account_lead',
            'portal_access' => true,
            'portal_role' => 'sales',
        ],
    ];

    public function run(): void
    {
        $partners = Partner::all();
        $users = User::all();

        foreach ($partners as $partner) {
            $domain = ContactFactory::partnerDomain($partner->name);

            // 1. Create the 3 deterministic portal contacts
            $portalContacts = collect();
            foreach (self::PORTAL_CONTACTS as $template) {
                $first = strtolower($template['first_name']);
                $last = strtolower($template['last_name']);

                $portalContacts->push(Contact::create([
                    ...$template,
                    'partner_id' => $partner->id,
                    'email' => "{$first}.{$last}@{$domain}",
                    'password' => 'password',
                    'phone' => fake()->phoneNumber,
                    'avatar' => 'img/demo/avatars/avatar-'.fake()->numberBetween(1, 18).'.png',
                    'created_at' => fake()->dateTimeBetween('-6 months', '-1 week'),
                ]));
            }

            // 2. Create 7 more contacts via factory (1 resource, 2 influencers, 4 general)
            $factoryContacts = Contact::factory(7)->create([
                'partner_id' => $partner->id,
            ]);

            $factoryContacts->each(function ($contact, $index) {
                $backdatedAt = fake()->dateTimeBetween('-6 months', '-1 week');

                if ($index === 0) {
                    $contact->update([
                        'contact_type' => 'resource',
                        'resource_role' => fake()->randomElement(array_keys(Contact::RESOURCE_ROLES)),
                        'created_at' => $backdatedAt,
                    ]);
                } elseif ($index < 3) {
                    $contact->update([
                        'contact_type' => 'influencer',
                        'influencer_role' => fake()->randomElement(array_keys(Contact::INFLUENCER_ROLES)),
                        'created_at' => $backdatedAt,
                    ]);
                }
            });

            // 3. Merge all contacts and attach to projects
            $allContacts = $portalContacts->merge($factoryContacts);

            $allContacts->each(function ($contact) {
                $projectIds = Project::inRandomOrder()
                    ->limit(rand(1, 3))
                    ->pluck('id')
                    ->toArray();

                $contact->projects()->attach($projectIds);
            });

            // 4. Create interactions for influencer contacts
            $influencers = $allContacts->where('contact_type', 'influencer');
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
