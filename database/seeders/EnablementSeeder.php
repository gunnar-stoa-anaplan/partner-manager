<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\EnablementEvent;
use App\Models\EnablementRegistration;
use App\Models\EnablementSession;
use App\Models\Partner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EnablementSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('role', 'admin')->first();
        $adminId = $adminUser?->id ?? 1;

        $allUsers = User::all();
        $partners = Partner::with('contacts')->get();

        // Define a curated catalog of enablement events across all product areas and types
        $eventDefinitions = [
            // ─── IFP ─────────────────────────────────────────────
            ['title' => 'IFP Fundamentals Training', 'type' => 'training', 'category' => 'Technical', 'product_area' => 'IFP', 'duration_hours' => 16, 'max_participants' => 20, 'target_audience' => 'Model Builders', 'description' => 'A comprehensive introduction to Integrated Financial Planning covering core concepts, model architecture, and foundational building techniques.', 'objectives' => 'Understand IFP data model structure. Build basic planning models. Navigate the IFP workspace.', 'prerequisites' => 'Basic understanding of financial planning concepts.'],
            ['title' => 'IFP Advanced Modeling Workshop', 'type' => 'workshop', 'category' => 'Technical', 'product_area' => 'IFP', 'duration_hours' => 8, 'max_participants' => 15, 'target_audience' => 'Solution Architects', 'description' => 'Advanced hands-on workshop focusing on complex modeling patterns, optimization strategies, and large-scale deployment best practices.', 'objectives' => 'Master advanced formula techniques. Optimize model performance. Design scalable solutions.'],
            ['title' => 'IFP Certification Prep', 'type' => 'certification_exam', 'category' => 'Technical', 'product_area' => 'IFP', 'duration_hours' => 4, 'max_participants' => 30, 'target_audience' => 'Model Builders', 'description' => 'Preparation session covering all certification exam topics with practice questions and review of key concepts.'],
            ['title' => 'IFP Customer Success Stories', 'type' => 'webinar', 'category' => 'Sales', 'product_area' => 'IFP', 'duration_hours' => 1, 'max_participants' => null, 'target_audience' => 'Sales Engineers', 'description' => 'Learn from successful IFP deployments across industries. Real customer case studies and lessons learned.'],
            ['title' => 'IFP Delivery Readiness Bootcamp', 'type' => 'bootcamp', 'category' => 'Implementation', 'product_area' => 'IFP', 'duration_hours' => 40, 'max_participants' => 12, 'target_audience' => 'Implementation Consultants', 'description' => 'Intensive 5-day bootcamp preparing delivery teams for their first IFP implementation.', 'prerequisites' => 'IFP Fundamentals Training completion.'],

            // ─── SCM ─────────────────────────────────────────────
            ['title' => 'SCM Platform Overview', 'type' => 'training', 'category' => 'Technical', 'product_area' => 'SCM', 'duration_hours' => 8, 'max_participants' => 25, 'target_audience' => 'Solution Architects', 'description' => 'End-to-end overview of the Supply Chain Management platform covering demand planning, supply planning, and S&OP processes.'],
            ['title' => 'SCM Solution Design Workshop', 'type' => 'workshop', 'category' => 'Solution Architecture', 'product_area' => 'SCM', 'duration_hours' => 8, 'max_participants' => 15, 'target_audience' => 'Solution Architects', 'description' => 'Collaborative workshop on designing SCM solutions for complex supply chain scenarios.'],
            ['title' => 'SCM New Features Overview', 'type' => 'webinar', 'category' => 'Technical', 'product_area' => 'SCM', 'duration_hours' => 1, 'max_participants' => null, 'target_audience' => 'Technical Architects', 'description' => 'Quarterly update on the latest SCM platform capabilities and upcoming roadmap items.'],
            ['title' => 'SCM Level 1 Certification', 'type' => 'certification_exam', 'category' => 'Technical', 'product_area' => 'SCM', 'duration_hours' => 4, 'max_participants' => 30, 'target_audience' => 'Implementation Consultants', 'description' => 'Certification exam covering SCM fundamentals, configuration, and basic implementation methodology.'],

            // ─── SPM ─────────────────────────────────────────────
            ['title' => 'SPM Fundamentals Training', 'type' => 'training', 'category' => 'Technical', 'product_area' => 'SPM', 'duration_hours' => 16, 'max_participants' => 20, 'target_audience' => 'Model Builders', 'description' => 'Comprehensive training on Sales Performance Management covering territory planning, quota management, and incentive compensation.'],
            ['title' => 'SPM Pre-Sales Workshop', 'type' => 'workshop', 'category' => 'Pre-Sales', 'product_area' => 'SPM', 'duration_hours' => 4, 'max_participants' => 20, 'target_audience' => 'Pre-Sales Consultants', 'description' => 'Hands-on demo preparation workshop with live SPM environment for building compelling customer demos.'],
            ['title' => 'SPM Partner Onboarding', 'type' => 'onboarding', 'category' => 'General', 'product_area' => 'SPM', 'duration_hours' => 2, 'max_participants' => null, 'target_audience' => 'Practice Leads', 'description' => 'Onboarding session for new SPM partners covering the ecosystem, go-to-market strategy, and available resources.'],
            ['title' => 'SPM Roadmap Briefing', 'type' => 'webinar', 'category' => 'Sales', 'product_area' => 'SPM', 'duration_hours' => 1, 'max_participants' => null, 'target_audience' => 'Sales Engineers', 'description' => 'Quarterly SPM product roadmap briefing with Q&A session.'],

            // ─── OWP ─────────────────────────────────────────────
            ['title' => 'OWP Implementation Training', 'type' => 'training', 'category' => 'Implementation', 'product_area' => 'OWP', 'duration_hours' => 16, 'max_participants' => 20, 'target_audience' => 'Implementation Consultants', 'description' => 'Detailed training on implementing Operational Workforce Planning solutions including data integration, modeling, and reporting.'],
            ['title' => 'OWP Best Practices Workshop', 'type' => 'workshop', 'category' => 'Technical', 'product_area' => 'OWP', 'duration_hours' => 4, 'max_participants' => 15, 'target_audience' => 'Solution Architects', 'description' => 'Workshop covering proven patterns and best practices for workforce planning implementations.'],
            ['title' => 'OWP Innovation Hackathon', 'type' => 'hackathon', 'category' => 'Technical', 'product_area' => 'OWP', 'duration_hours' => 8, 'max_participants' => 30, 'target_audience' => 'Model Builders', 'description' => 'Build innovative workforce planning solutions in a competitive, team-based hackathon.'],

            // ─── Polaris ─────────────────────────────────────────
            ['title' => 'Polaris Platform Training', 'type' => 'training', 'category' => 'Technical', 'product_area' => 'Polaris', 'duration_hours' => 24, 'max_participants' => 15, 'target_audience' => 'Technical Architects', 'description' => 'Deep technical training on the Polaris platform architecture, API integrations, and extensibility framework.'],
            ['title' => 'Polaris Hands-On Workshop', 'type' => 'workshop', 'category' => 'Technical', 'product_area' => 'Polaris', 'duration_hours' => 8, 'max_participants' => 12, 'target_audience' => 'Technical Architects', 'description' => 'Hands-on lab environment exploring Polaris capabilities with guided exercises.'],
            ['title' => 'Polaris Certification Prep', 'type' => 'certification_exam', 'category' => 'Technical', 'product_area' => 'Polaris', 'duration_hours' => 4, 'max_participants' => 30, 'target_audience' => 'Technical Architects', 'description' => 'Exam preparation covering Polaris architecture, APIs, and development patterns.'],

            // ─── ADO ─────────────────────────────────────────────
            ['title' => 'ADO Configuration Training', 'type' => 'training', 'category' => 'Technical', 'product_area' => 'ADO', 'duration_hours' => 8, 'max_participants' => 20, 'target_audience' => 'Model Builders', 'description' => 'Training on ADO configuration, data orchestration pipelines, and automation workflows.'],
            ['title' => 'ADO Advanced Integration Workshop', 'type' => 'workshop', 'category' => 'Technical', 'product_area' => 'ADO', 'duration_hours' => 4, 'max_participants' => 15, 'target_audience' => 'Technical Architects', 'description' => 'Advanced workshop on complex integration scenarios, error handling, and monitoring.'],

            // ─── AI ──────────────────────────────────────────────
            ['title' => 'AI/ML for Planning Deep Dive', 'type' => 'training', 'category' => 'Technical', 'product_area' => 'AI', 'duration_hours' => 8, 'max_participants' => 20, 'target_audience' => 'Solution Architects', 'description' => 'Explore AI and machine learning capabilities within the planning platform. Covers PlanIQ, anomaly detection, and predictive analytics.'],
            ['title' => 'AI Product Update Webinar', 'type' => 'webinar', 'category' => 'Technical', 'product_area' => 'AI', 'duration_hours' => 1, 'max_participants' => null, 'target_audience' => 'Technical Architects', 'description' => 'Latest AI capability updates and real-world use cases.'],
            ['title' => 'AI Solution Challenge', 'type' => 'hackathon', 'category' => 'Technical', 'product_area' => 'AI', 'duration_hours' => 8, 'max_participants' => 25, 'target_audience' => 'Model Builders', 'description' => 'Team challenge to build innovative AI-powered planning solutions.'],

            // ─── Forecaster ──────────────────────────────────────
            ['title' => 'Forecaster Fundamentals', 'type' => 'training', 'category' => 'Technical', 'product_area' => 'Forecaster', 'duration_hours' => 8, 'max_participants' => 20, 'target_audience' => 'Model Builders', 'description' => 'Learn the Forecaster module from the ground up — time series forecasting, configuration, and integration with planning models.'],
            ['title' => 'Forecaster Best Practices Workshop', 'type' => 'workshop', 'category' => 'Technical', 'product_area' => 'Forecaster', 'duration_hours' => 4, 'max_participants' => 15, 'target_audience' => 'Solution Architects', 'description' => 'Best practices for tuning forecasting algorithms, managing seasonal patterns, and improving accuracy.'],

            // ─── Cross-Product ───────────────────────────────────
            ['title' => 'Partner Technical Summit', 'type' => 'conference', 'category' => 'General', 'product_area' => 'Other', 'duration_hours' => 16, 'max_participants' => 100, 'target_audience' => 'Practice Leads', 'description' => 'Annual partner technical summit covering all product lines, roadmap updates, and networking opportunities.'],
            ['title' => 'New Partner Onboarding Program', 'type' => 'onboarding', 'category' => 'General', 'product_area' => 'Other', 'duration_hours' => 4, 'max_participants' => null, 'target_audience' => 'Delivery Leads', 'description' => 'Welcome program for newly onboarded partners covering the partner ecosystem, resources, and getting started guides.'],
            ['title' => 'Implementation Methodology Training', 'type' => 'training', 'category' => 'Implementation', 'product_area' => 'Other', 'duration_hours' => 8, 'max_participants' => 25, 'target_audience' => 'Project Managers', 'description' => 'Standard implementation methodology covering project phases, deliverables, quality gates, and governance.'],
        ];

        $this->command->info('Creating enablement events...');

        $createdEvents = [];
        foreach ($eventDefinitions as $def) {
            $event = EnablementEvent::create(array_merge($def, [
                'status' => 'published',
                'created_by' => $adminId,
            ]));
            $createdEvents[] = $event;
        }

        $this->command->info('Created ' . count($createdEvents) . ' enablement events.');

        // Also create a couple of draft events
        $draftEvent = EnablementEvent::create([
            'title' => 'SCM Advanced Bootcamp (Coming Soon)',
            'type' => 'bootcamp',
            'category' => 'Technical',
            'product_area' => 'SCM',
            'duration_hours' => 40,
            'max_participants' => 12,
            'target_audience' => 'Implementation Consultants',
            'description' => 'Intensive SCM bootcamp for experienced consultants. Still in development.',
            'status' => 'draft',
            'created_by' => $adminId,
        ]);

        $archivedEvent = EnablementEvent::create([
            'title' => 'Legacy Platform Migration Guide',
            'type' => 'webinar',
            'category' => 'Technical',
            'product_area' => 'Other',
            'duration_hours' => 2,
            'description' => 'Archived migration guide webinar from previous platform version.',
            'status' => 'archived',
            'created_by' => $adminId,
        ]);

        $this->command->info('Creating sessions distributed over the next 6 months...');

        $geographies = ['NA', 'EMEA', 'APJ', 'LATAM'];
        $regionsByGeo = [
            'NA' => ['US West', 'US East', 'Canada'],
            'EMEA' => ['UK/I', 'DACH', 'Nordics', 'France', 'Benelux'],
            'APJ' => ['ANZ', 'Japan', 'SEA', 'India'],
            'LATAM' => ['Brazil', 'Mexico'],
        ];
        $locationsByGeo = [
            'NA' => ['Virtual', 'San Francisco, CA', 'New York, NY', 'Minneapolis, MN', 'Chicago, IL', 'Toronto, Canada'],
            'EMEA' => ['Virtual', 'London, UK', 'Frankfurt, Germany', 'Paris, France', 'Amsterdam, Netherlands', 'Stockholm, Sweden'],
            'APJ' => ['Virtual', 'Singapore', 'Tokyo, Japan', 'Sydney, Australia', 'Bangalore, India', 'Seoul, South Korea'],
            'LATAM' => ['Virtual', 'Sao Paulo, Brazil', 'Mexico City, Mexico'],
        ];
        $instructors = [
            'Sarah Mitchell', 'James Chen', 'Priya Sharma', 'Michael O\'Brien',
            'Anna Kowalski', 'David Thompson', 'Lisa Wang', 'Robert Garcia',
            'Emma Johnson', 'Carlos Mendez', 'Yuki Tanaka', 'Henrik Larsson',
        ];

        $now = Carbon::now();
        $sessionsCreated = 0;

        foreach ($createdEvents as $event) {
            // Each event gets 2-5 sessions spread across the next 6 months
            $sessionCount = fake()->numberBetween(2, 5);

            // Distribute across different geographies
            $eventGeos = fake()->randomElements($geographies, min($sessionCount, count($geographies)));

            for ($i = 0; $i < $sessionCount; $i++) {
                $geo = $eventGeos[$i % count($eventGeos)];
                $region = fake()->randomElement($regionsByGeo[$geo]);
                $location = fake()->randomElement($locationsByGeo[$geo]);

                // Spread sessions across next 6 months
                $weeksAhead = fake()->numberBetween(1, 26);
                $startDate = $now->copy()->addWeeks($weeksAhead);

                // Set to a weekday
                while ($startDate->isWeekend()) {
                    $startDate->addDay();
                }

                // Set reasonable start time (9 AM to 2 PM)
                $startHour = fake()->randomElement([9, 10, 11, 13, 14]);
                $startDate->setHour($startHour)->setMinute(0)->setSecond(0);

                $durationHours = (int) ($event->duration_hours ?? fake()->randomElement([1, 2, 4, 8]));
                $allDay = $durationHours >= 8;
                $endDate = $startDate->copy()->addHours(min($durationHours, 8));

                $session = EnablementSession::create([
                    'enablement_event_id' => $event->id,
                    'start_at' => $startDate,
                    'end_at' => $allDay ? $startDate->copy()->endOfDay() : $endDate,
                    'all_day' => $allDay,
                    'timezone' => 'UTC',
                    'location' => $location,
                    'meeting_url' => $location === 'Virtual' ? 'https://teams.microsoft.com/meet/' . fake()->uuid() : null,
                    'instructor' => fake()->randomElement($instructors),
                    'geography' => $geo,
                    'region' => $region,
                    'max_participants' => $event->max_participants,
                    'status' => 'scheduled',
                    'created_by' => $adminId,
                ]);

                $sessionsCreated++;

                // Register some partners and contacts for this session
                $this->seedRegistrations($session, $partners, $allUsers);
            }
        }

        // Also create a few past (completed) sessions
        $pastEvents = fake()->randomElements($createdEvents, min(8, count($createdEvents)));
        foreach ($pastEvents as $event) {
            $geo = fake()->randomElement($geographies);
            $region = fake()->randomElement($regionsByGeo[$geo]);
            $location = fake()->randomElement($locationsByGeo[$geo]);

            $pastDate = $now->copy()->subWeeks(fake()->numberBetween(1, 8));
            while ($pastDate->isWeekend()) {
                $pastDate->subDay();
            }
            $pastDate->setHour(fake()->randomElement([9, 10, 13]))->setMinute(0)->setSecond(0);

            $durationHours = (int) ($event->duration_hours ?? 4);
            $allDay = $durationHours >= 8;

            $session = EnablementSession::create([
                'enablement_event_id' => $event->id,
                'start_at' => $pastDate,
                'end_at' => $allDay ? $pastDate->copy()->endOfDay() : $pastDate->copy()->addHours(min($durationHours, 8)),
                'all_day' => $allDay,
                'timezone' => 'UTC',
                'location' => $location,
                'meeting_url' => $location === 'Virtual' ? 'https://teams.microsoft.com/meet/' . fake()->uuid() : null,
                'instructor' => fake()->randomElement($instructors),
                'geography' => $geo,
                'region' => $region,
                'max_participants' => $event->max_participants,
                'status' => 'completed',
                'summary' => 'Session completed successfully with ' . fake()->numberBetween(8, 20) . ' attendees.',
                'created_by' => $adminId,
            ]);

            $sessionsCreated++;

            // Completed sessions get more registrations with attended/no_show statuses
            $this->seedRegistrations($session, $partners, $allUsers, true);
        }

        $this->command->info("Created {$sessionsCreated} sessions across all events.");

        $regCount = EnablementRegistration::count();
        $partnerRegCount = \DB::table('partner_enablement_session')->count();
        $this->command->info("Created {$regCount} contact registrations across {$partnerRegCount} partner registrations.");
    }

    private function seedRegistrations(
        EnablementSession $session,
        $partners,
        $allUsers,
        bool $isCompleted = false
    ): void {
        // Pick 2-6 random partners to register
        $partnerCount = fake()->numberBetween(2, min(6, $partners->count()));
        $selectedPartners = $partners->random($partnerCount);

        foreach ($selectedPartners as $partner) {
            // Register partner for the session (pivot table)
            $psm = $partner->user_id
                ? $allUsers->firstWhere('id', $partner->user_id)
                : $allUsers->random();

            if (!$psm) continue;

            // Avoid duplicate partner registrations
            $exists = \DB::table('partner_enablement_session')
                ->where('partner_id', $partner->id)
                ->where('enablement_session_id', $session->id)
                ->exists();

            if ($exists) continue;

            \DB::table('partner_enablement_session')->insert([
                'partner_id' => $partner->id,
                'enablement_session_id' => $session->id,
                'added_by' => $psm->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Register 1-3 contacts from this partner
            $contacts = $partner->contacts;
            if ($contacts->isEmpty()) continue;

            $contactCount = fake()->numberBetween(1, min(3, $contacts->count()));
            $selectedContacts = $contacts->random($contactCount);

            foreach ($selectedContacts as $contact) {
                // Determine status based on whether session is completed
                if ($isCompleted) {
                    $status = fake()->randomElement(['attended', 'attended', 'attended', 'no_show', 'cancelled']);
                } else {
                    $status = fake()->randomElement(['nominated', 'nominated', 'registered', 'confirmed']);
                }

                $nominatedAt = fake()->dateTimeBetween('-2 months', '-1 week');

                $regData = [
                    'enablement_session_id' => $session->id,
                    'contact_id' => $contact->id,
                    'partner_id' => $partner->id,
                    'nominated_by' => $psm->id,
                    'status' => $status,
                    'nominated_at' => $nominatedAt,
                ];

                if (in_array($status, ['registered', 'confirmed', 'attended', 'no_show'])) {
                    $regData['registered_at'] = fake()->dateTimeBetween($nominatedAt, '-3 days');
                }
                if (in_array($status, ['confirmed', 'attended', 'no_show'])) {
                    $regData['confirmed_at'] = fake()->dateTimeBetween($regData['registered_at'], '-1 day');
                }
                if ($status === 'attended') {
                    $regData['attended_at'] = $session->start_at;
                }

                // Avoid duplicate contact registrations
                $existing = EnablementRegistration::where('enablement_session_id', $session->id)
                    ->where('contact_id', $contact->id)
                    ->exists();

                if (!$existing) {
                    EnablementRegistration::create($regData);
                }
            }
        }
    }
}
