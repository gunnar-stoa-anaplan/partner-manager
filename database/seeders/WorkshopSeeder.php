<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopCheckAttempt;
use App\Models\WorkshopCredential;
use App\Models\WorkshopSessionParticipant;
use App\Models\WorkshopSurveyResponse;
use App\Models\WorkshopSurveyTemplate;
use Illuminate\Database\Seeder;
use Laravel\Pennant\Feature;

class WorkshopSeeder extends Seeder
{
    /**
     * Real workshops and sessions pulled from the ifp-delivery-workshop
     * prototype's pre-demo database (2026-08-17 snapshot). Participant/people
     * data from that prototype is intentionally NOT imported - only the
     * workshop and session shells, tied to this app's own demo contacts.
     *
     * @var array<int, array{slug: string, name: string, short_description: string, status: string, sessions: array<int, array{slug: string, name: string, start_date: string, end_date: string, status: string, notes: ?string}>}>
     */
    private const WORKSHOPS = [
        [
            'slug' => 'ifp',
            'name' => 'IFP Delivery Partner Workshop',
            'short_description' => 'IFP 2.1 Application Framework · Model Generation · Extensions',
            'status' => 'active',
            'sessions' => [
                ['slug' => '2026-06-9', 'name' => 'June 9, 2026 Session', 'start_date' => '2026-06-09', 'end_date' => '2026-06-12', 'status' => 'completed', 'notes' => null],
                ['slug' => '2026-06-15', 'name' => 'June 15, 2026 Session', 'start_date' => '2026-06-15', 'end_date' => '2026-06-19', 'status' => 'completed', 'notes' => null],
                ['slug' => 'deloitte-2026-06-15', 'name' => 'June 15, 2026 Session', 'start_date' => '2026-06-15', 'end_date' => '2026-06-19', 'status' => 'completed', 'notes' => 'Dedicated session for Deloitte EMEA'],
                ['slug' => '2026-07-13', 'name' => 'June 23, 2026 Session', 'start_date' => '2026-07-13', 'end_date' => '2026-07-17', 'status' => 'completed', 'notes' => 'Deloitte US'],
                ['slug' => '2026-07-07', 'name' => 'July 7, 2026 Session', 'start_date' => '2026-07-07', 'end_date' => '2026-07-10', 'status' => 'completed', 'notes' => 'Session coordinated by Fuvas'],
                ['slug' => '2026-08-25', 'name' => 'August 25, 2026 Session', 'start_date' => '2026-08-25', 'end_date' => '2026-08-28', 'status' => 'completed', 'notes' => null],
                ['slug' => '2026-09-08', 'name' => 'September 8, 2026 Session', 'start_date' => '2026-09-08', 'end_date' => '2026-09-11', 'status' => 'upcoming', 'notes' => null],
                ['slug' => '2026-09-22', 'name' => 'September 22, 2026 Session', 'start_date' => '2026-09-22', 'end_date' => '2026-09-25', 'status' => 'upcoming', 'notes' => 'Session coordinated by Twelve CG'],
            ],
        ],
        [
            'slug' => 'rpm',
            'name' => 'RPM Delivery Partner Workshop',
            'short_description' => 'Hands-on, self-paced workshop for RPM delivery partners.',
            'status' => 'active',
            'sessions' => [
                ['slug' => '2026-06-22', 'name' => 'June 22, 2026 Session', 'start_date' => '2026-06-22', 'end_date' => '2026-06-26', 'status' => 'completed', 'notes' => null],
                ['slug' => '2026-07-20', 'name' => 'July 20, 2026 Session', 'start_date' => '2026-07-20', 'end_date' => '2026-07-24', 'status' => 'completed', 'notes' => 'Session coordinated by Genpact'],
                ['slug' => '2026-09-14', 'name' => 'September 14, 2026 Session', 'start_date' => '2026-09-14', 'end_date' => '2026-09-18', 'status' => 'upcoming', 'notes' => null],
            ],
        ],
        [
            'slug' => 'owp',
            'name' => 'OWP Delivery Partner Workshop',
            'short_description' => 'Hands-on, self-paced workshop for OWP delivery partners.',
            'status' => 'active',
            'sessions' => [
                ['slug' => '2026-06-30', 'name' => 'June 30, 2026 Session in London', 'start_date' => '2026-06-30', 'end_date' => '2026-07-03', 'status' => 'completed', 'notes' => 'Anju led-session for key partners in London'],
                ['slug' => '2026-08-03', 'name' => 'August 3, 2026 Session', 'start_date' => '2026-08-03', 'end_date' => '2026-08-07', 'status' => 'completed', 'notes' => null],
                ['slug' => '2026-09-21', 'name' => 'September 21, 2026 Session', 'start_date' => '2026-09-21', 'end_date' => '2026-09-25', 'status' => 'upcoming', 'notes' => null],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Don't rely solely on GunnarSeeder's generic app/Features/* scan to
        // turn this on - that scan has silently missed newly-added feature
        // classes before, leaving the whole Workshops nav hidden with no
        // error. Seeding real workshop data with the feature still off is
        // never correct, so activate it explicitly here too.
        Feature::for(null)->activate('workshops');

        // Drop the earlier synthetic placeholder workshop, if present, now
        // that real workshops are seeded instead.
        Workshop::where('slug', 'anaplan-way-ifp')->delete();

        $facilitator = User::where('email', 'gunnar.stoa@gmail.com')->first();

        if (! $facilitator) {
            $this->command->warn('  ⚠ Skipping WorkshopSeeder - facilitator user not found (expected GunnarSeeder to have run first).');

            return;
        }

        $workshopCount = 0;
        $sessionCount = 0;

        foreach (self::WORKSHOPS as $definition) {
            $workshop = Workshop::updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'short_description' => $definition['short_description'],
                    'status' => $definition['status'],
                    'created_by' => $facilitator->id,
                ],
            );
            $workshopCount++;

            foreach ($definition['sessions'] as $sessionDefinition) {
                $workshop->sessions()->updateOrCreate(
                    ['slug' => $sessionDefinition['slug']],
                    [
                        'name' => $sessionDefinition['name'],
                        'start_date' => $sessionDefinition['start_date'],
                        'end_date' => $sessionDefinition['end_date'],
                        'status' => $sessionDefinition['status'],
                        'notes' => $sessionDefinition['notes'],
                        'created_by' => $facilitator->id,
                    ],
                );
                $sessionCount++;
            }
        }

        $this->command->info("  ✓ Seeded {$workshopCount} real workshops with {$sessionCount} sessions");

        [$practiceLead, $deliveryResource] = $this->seedDemoParticipation($facilitator);
        $this->seedSessionParticipants($facilitator, $practiceLead, $deliveryResource);
        $this->seedSurveyTemplatesAndCredentials($facilitator, $practiceLead, $deliveryResource);
        $this->seedContentPages();
        $this->seedLeaderboardActivity();
        $this->seedRpmAndOwpSurveyActivity($facilitator);
    }

    /**
     * The real IFP workshop curriculum, extracted from the
     * ifp-delivery-workshop prototype's docs/*.html - not placeholder text.
     * Participant pages (17, including the real lab-check prompts) come from
     * database/seeders/data/ifp_workshop_pages.php; facilitator-only pages
     * (Run-of-Show, Launch Toolkit) come from
     * database/seeders/data/ifp_workshop_facilitator_pages.php.
     */
    private function seedContentPages(): void
    {
        $ifp = Workshop::where('slug', 'ifp')->first();

        if (! $ifp) {
            return;
        }

        // Remove the earlier placeholder pages (different slugs than the
        // real content below) so they don't linger as orphaned duplicates.
        $ifp->pages()->whereIn('slug', ['overview', 'model-generation', 'dimensionality'])->delete();

        $pages = require database_path('seeders/data/ifp_workshop_pages.php');

        foreach ($pages as $page) {
            $ifp->pages()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'audience' => 'participant',
                    'title' => $page['title'],
                    'body' => $page['body'],
                    'sort_order' => $page['sort_order'],
                    'checks_json' => $page['checks'] ?: null,
                ],
            );
        }

        $this->command->info('  ✓ Seeded '.count($pages).' real content pages for the IFP workshop');

        $facilitatorPages = require database_path('seeders/data/ifp_workshop_facilitator_pages.php');

        foreach ($facilitatorPages as $page) {
            $ifp->pages()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'audience' => 'facilitator',
                    'title' => $page['title'],
                    'body' => $page['body'],
                    'sort_order' => $page['sort_order'],
                    'checks_json' => $page['checks'] ?: null,
                ],
            );
        }

        $this->command->info('  ✓ Seeded '.count($facilitatorPages).' facilitator-only content pages for the IFP workshop');
    }

    /**
     * Wire up this app's own demo contacts (not real prototype participants)
     * to one real session, so the workshop portal flows have something to
     * show. Uses the focused workshop_practice_lead/workshop_delivery_resource
     * roles (not the broader practice_lead/delivery_resource roles those
     * contacts might otherwise carry) so these two logins are scoped to just
     * Workshops, per the user's request to test this feature in isolation.
     *
     * @return array{0: ?Contact, 1: ?Contact} the practice lead and delivery
     *                                         resource contacts used as the two focused demo logins, or [null,
     *                                         null] if no partner has enough contacts to seed them - callers must
     *                                         handle that case rather than assume a specific named partner exists.
     */
    private function seedDemoParticipation(User $facilitator): array
    {
        // Pick deterministically (lowest id) rather than by a specific
        // partner name - which partner has enough contacts to qualify can
        // shift as the partner/contact data sources change, and hardcoding
        // a name here previously caused this to silently no-op on a fresh
        // seed once that partner stopped existing.
        $partner = Partner::withCount(['contacts as contacts_with_email_count' => fn ($query) => $query->whereNotNull('email')])
            ->having('contacts_with_email_count', '>=', 2)
            ->orderBy('id')
            ->first();

        if (! $partner) {
            return [null, null];
        }

        $contacts = Contact::where('partner_id', $partner->id)
            ->whereNotNull('email')
            ->orderBy('id')
            ->limit(2)
            ->get();

        [$practiceLead, $deliveryResource] = $contacts;

        $practiceLeadRole = Role::where('slug', 'workshop_practice_lead')->first();
        $deliveryResourceRole = Role::where('slug', 'workshop_delivery_resource')->first();

        $practiceLead->update([
            'portal_role_id' => $practiceLeadRole->id,
            'portal_access' => true,
            'password' => $practiceLead->password ?? 'password',
        ]);

        $deliveryResource->update([
            'portal_role_id' => $deliveryResourceRole->id,
            'portal_access' => true,
            'password' => $deliveryResource->password ?? 'password',
        ]);

        // Ben Wilson tests the internal side, focused on Workshops only.
        $workshopOwnerRole = Role::where('slug', 'workshop_owner')->first();
        $benWilson = User::where('email', 'ben.wilson@example.com')->first();
        $benWilson?->update(['role_id' => $workshopOwnerRole->id]);

        $session = Workshop::where('slug', 'ifp')->first()?->sessions()->where('slug', '2026-06-9')->first();

        if (! $session) {
            return [$practiceLead, $deliveryResource];
        }

        // These two demo logins must only ever appear enrolled in this one
        // session - remove any stray enrollment left over from an earlier
        // seed run (e.g. before the generic bulk-enrollment pool excluded
        // them) in every other session.
        WorkshopSessionParticipant::whereIn('contact_id', [$practiceLead->id, $deliveryResource->id])
            ->where('workshop_session_id', '!=', $session->id)
            ->delete();

        foreach ([$practiceLead, $deliveryResource] as $contact) {
            $session->participants()->updateOrCreate(
                ['contact_id' => $contact->id],
                [
                    'status' => 'active',
                    'enrolled_at' => now()->subDays(3),
                    'enrolled_by_user_id' => $facilitator->id,
                ],
            );
        }

        $session->monitors()->updateOrCreate(
            ['user_id' => $benWilson?->id ?? $facilitator->id],
            [
                'assigned_at' => now()->subDays(3),
                'assigned_by' => $facilitator->id,
            ],
        );

        $this->command->info("  ✓ Enrolled demo contacts from {$partner->name} into \"{$session->name}\", Ben Wilson focused on Workshops");

        return [$practiceLead, $deliveryResource];
    }

    /**
     * Populate every real session with a handful of this app's own demo
     * Contacts, since email-matching against the real prototype's actual
     * participants found zero overlap (this app's seeded contacts are
     * synthetic - there's no real person in common). Sessions with a
     * "Deloitte" note in the real data get contacts from a real Deloitte
     * partner record where one exists, for a bit of realism.
     */
    private function seedSessionParticipants(User $facilitator, ?Contact $practiceLead, ?Contact $deliveryResource): void
    {
        $genericPartners = Partner::whereIn('name', ['PwC', 'Deloitte Consulting LLP - US (HQ)', 'KPMG US (HQ)', 'Tata Consultancy Services'])
            ->orWhere('name', 'like', 'Accenture%')
            ->get();

        if ($genericPartners->isEmpty()) {
            return;
        }

        // The focused demo logins (whichever contacts seedDemoParticipation()
        // picked as practice lead / delivery resource) must only be enrolled
        // in the one session it deliberately puts them in, not swept into
        // every other generic session's participant pool alongside them.
        $reservedContactIds = collect([$practiceLead?->id, $deliveryResource?->id])->filter();

        $enrolled = 0;

        foreach (Workshop::with('sessions')->get() as $workshop) {
            foreach ($workshop->sessions as $session) {
                $partners = str_contains((string) $session->notes, 'Deloitte')
                    ? Partner::where('name', 'like', 'Deloitte%')->get()
                    : $genericPartners;

                if ($partners->isEmpty()) {
                    $partners = $genericPartners;
                }

                $contacts = Contact::whereIn('partner_id', $partners->pluck('id'))
                    ->whereNotNull('email')
                    ->whereNotIn('id', $reservedContactIds)
                    ->orderBy('id')
                    ->limit(4)
                    ->get();

                foreach ($contacts as $index => $contact) {
                    $isPastSession = $session->end_date->isPast();
                    $completed = $isPastSession && $index % 2 === 0;

                    $session->participants()->updateOrCreate(
                        ['contact_id' => $contact->id],
                        [
                            'status' => 'active',
                            'enrolled_at' => $session->created_at ?? now(),
                            'enrolled_by_user_id' => $facilitator->id,
                            'completed_at' => $completed ? $session->end_date : null,
                            'completed_by' => $completed ? $facilitator->id : null,
                        ],
                    );
                    $enrolled++;
                }
            }
        }

        $this->command->info("  ✓ Enrolled {$enrolled} demo participants across all real sessions");
    }

    /**
     * A sample post-workshop knowledge check and a sample sandbox credential
     * for the IFP workshop, plus a demo discussion thread, so the reporting
     * and Q&A features have real data to show.
     */
    private function seedSurveyTemplatesAndCredentials(User $facilitator, ?Contact $practiceLead, ?Contact $deliveryResource): void
    {
        $ifp = Workshop::where('slug', 'ifp')->first();

        if (! $ifp) {
            return;
        }

        $likertTopics = [
            ['id' => 'anaplan_way', 'text' => 'Anaplan Way Methodology'],
            ['id' => 'alm', 'text' => 'Application Lifecycle Management (ALM)'],
            ['id' => 'app_config', 'text' => 'Anaplan Application Configuration'],
            ['id' => 'ifp_app', 'text' => 'Integrated Financial Planning (IFP) Application'],
            ['id' => 'extensions', 'text' => 'Extensions to IFP'],
            ['id' => 'ado', 'text' => 'Anaplan Data Orchestrator (ADO)'],
        ];

        WorkshopSurveyTemplate::updateOrCreate(
            ['workshop_id' => $ifp->id, 'kind' => 'pre'],
            [
                'intro' => "Take a few minutes before the workshop starts to share your background and where you're starting from. Your answers help us calibrate the live discussions and the wrap-up retrospective.",
                'questions_json' => [
                    ['id' => 'background', 'text' => 'Your background and role (select all that apply)', 'type' => 'multi_select', 'options' => ['Certified Solution Architect', 'Certified Model Builder', 'Certified Master Anaplanner', 'Program Architect', 'Practice Lead', 'Project Manager'], 'required' => false],
                    ['id' => 'experience', 'text' => 'Your project experience', 'type' => 'single_select', 'options' => ['No Projects (yet)', '2 to 5 Projects', 'More than 5 Projects'], 'required' => true],
                    ...array_map(fn (array $topic) => ['id' => $topic['id'], 'text' => $topic['text'], 'type' => 'likert', 'required' => true], $likertTopics),
                    ['id' => 'goal', 'text' => 'Add one or two things you hope to learn from the course', 'type' => 'text', 'required' => false],
                ],
                'updated_by' => $facilitator->id,
            ],
        );

        WorkshopSurveyTemplate::updateOrCreate(
            ['workshop_id' => $ifp->id, 'kind' => 'post'],
            [
                'intro' => "Now that you've completed the workshop, revisit the same topics - this is what lets us measure how much ground you covered.",
                'questions_json' => [
                    ...array_map(fn (array $topic) => ['id' => $topic['id'], 'text' => $topic['text'], 'type' => 'likert', 'required' => true], $likertTopics),
                    ['id' => 'delivered_lab', 'text' => 'Which lab did you find most valuable?', 'type' => 'single_select', 'options' => ['Exercise 1: OpEx and Headcount Planning', 'Exercise 2: Insurance - 3-Statement Planning', 'Exercise 3: Nuclear Plant - OpEx and Headcount with WBS', 'Extension Exercise 1', 'Extension Exercise 2'], 'required' => false],
                    ['id' => 'next_topic', 'text' => 'What would you like covered in more depth next time?', 'type' => 'text', 'required' => false],
                ],
                'updated_by' => $facilitator->id,
            ],
        );

        WorkshopSurveyTemplate::updateOrCreate(
            ['workshop_id' => $ifp->id, 'kind' => 'knowledge'],
            [
                'intro' => 'A short knowledge check to confirm what you took away from the workshop.',
                'questions_json' => [
                    ['id' => 'kc1', 'text' => 'Which module type holds transactional detail data in an IFP model?', 'type' => 'single_select', 'options' => ['Line Item', 'List', 'Module', 'Dashboard'], 'correct' => 'Module', 'required' => true],
                    ['id' => 'kc2', 'text' => 'Which of these are part of the standard IFP dimensionality? (select all that apply)', 'type' => 'multi_select', 'options' => ['Location', 'Customer', 'Job', 'Weather'], 'correct' => ['Location', 'Customer', 'Job'], 'required' => true],
                    ['id' => 'kc3', 'text' => 'What is a key feature of the Anaplan Application Framework for IFPv2 configuration?', 'type' => 'single_select', 'options' => ['A rigid, one-time build process for stability.', 'A simplified, interview-style configuration process.', 'A command-line interface for advanced users.'], 'correct' => 'A simplified, interview-style configuration process.', 'required' => true],
                    ['id' => 'kc4', 'text' => 'When working with hierarchies in the Application Framework, what is the recommended approach?', 'type' => 'single_select', 'options' => ['Overview', 'Hierarchies', 'Models', 'Configurations'], 'correct' => 'Configurations', 'required' => true],
                    ['id' => 'kc5', 'text' => 'What is the overarching goal of the IFPv2 Implementation Workshop?', 'type' => 'single_select', 'options' => ['To teach attendees advanced formula writing and model building techniques.', 'To provide a deep dive into the financial theories behind integrated planning.', 'To enable partners to successfully configure, generate, and implement the IFPv2 application for customers.'], 'correct' => 'To enable partners to successfully configure, generate, and implement the IFPv2 application for customers.', 'required' => true],
                ],
                'passing_score' => 70,
                'updated_by' => $facilitator->id,
            ],
        );

        $credential = WorkshopCredential::updateOrCreate(
            ['workshop_id' => $ifp->id, 'username' => 'ifp-sandbox-01'],
            [
                'email' => 'ifp-sandbox-01@anaplan-demo.com',
                'password_enc' => 'DemoPass!2026',
                'status' => 'available',
                'notes' => 'Seeded demo credential - not a real Anaplan tenant.',
            ],
        );

        $session = $ifp->sessions()->where('slug', '2026-06-9')->first();

        if ($session && $deliveryResource && ! $credential->activeAssignment()) {
            $credential->assignments()->create([
                'workshop_session_id' => $session->id,
                'contact_id' => $deliveryResource->id,
                'assigned_at' => now()->subDays(2),
                'hold_until' => $session->end_date->addDay(),
            ]);
        }

        if ($session && $session->threads()->count() === 0) {
            $thread = $session->threads()->create([
                'author_contact_id' => $practiceLead?->id,
                'kind' => 'question',
                'body' => 'Is the golden model snapshot refreshed automatically before each session, or do we need to request it?',
                'status' => 'answered',
                'answered_at' => now()->subDay(),
                'answered_by' => $facilitator->id,
            ]);

            $thread->replies()->create([
                'author_user_id' => $facilitator->id,
                'is_monitor_reply' => true,
                'body' => "It's refreshed automatically 24 hours before the session start - no action needed on your end.",
            ]);
        }

        $this->command->info('  ✓ Seeded a survey template, a demo credential, and a discussion thread');
    }

    /**
     * A believable spread of lab-check attempts and knowledge-check
     * submissions for the IFP workshop's June 9 session, so the leaderboard
     * shows a real mix of done/partial/not-started instead of a blank grid.
     * Must run after seedContentPages() (needs the real lab pages/checks).
     */
    private function seedLeaderboardActivity(): void
    {
        $ifp = Workshop::where('slug', 'ifp')->first();
        $session = $ifp?->sessions()->where('slug', '2026-06-9')->first();
        $preSurvey = $ifp?->surveyTemplates()->where('kind', 'pre')->first();
        $postSurvey = $ifp?->surveyTemplates()->where('kind', 'post')->first();
        $knowledgeCheck = $ifp?->surveyTemplates()->where('kind', 'knowledge')->first();
        $labPages = $ifp?->participantPages->filter(fn ($page) => ! empty($page->checks()))->values();

        if (! $session || ! $knowledgeCheck || ! $labPages || $labPages->isEmpty()) {
            return;
        }

        if (WorkshopCheckAttempt::where('workshop_session_id', $session->id)->exists()) {
            return;
        }

        $participants = $session->participants()->notRemoved()->orderBy('id')->get();
        $ahead = $participants->slice(0, 5);
        $inProgress = $participants->slice(5, 6);

        foreach ($ahead as $participant) {
            foreach ($labPages as $page) {
                foreach ($page->checks() as $check) {
                    WorkshopCheckAttempt::create([
                        'contact_id' => $participant->contact_id,
                        'workshop_session_id' => $session->id,
                        'workshop_page_id' => $page->id,
                        'check_id' => $check['id'],
                        'passed' => true,
                        'attempted_at' => now()->subDays(2),
                    ]);
                }
            }

            if ($preSurvey) {
                WorkshopSurveyResponse::create([
                    'workshop_survey_template_id' => $preSurvey->id,
                    'workshop_session_id' => $session->id,
                    'contact_id' => $participant->contact_id,
                    'responses_json' => $this->likertResponses($preSurvey, 3) + ['background' => ['Certified Model Builder'], 'experience' => '2 to 5 Projects', 'goal' => 'Get comfortable configuring the Application Framework end-to-end.'],
                    'score' => 0,
                    'passed' => false,
                    'attempt_number' => 1,
                    'submitted_at' => now()->subDays(3),
                ]);
            }

            if ($postSurvey) {
                WorkshopSurveyResponse::create([
                    'workshop_survey_template_id' => $postSurvey->id,
                    'workshop_session_id' => $session->id,
                    'contact_id' => $participant->contact_id,
                    'responses_json' => $this->likertResponses($postSurvey, 5) + ['delivered_lab' => 'Exercise 1: OpEx and Headcount Planning', 'next_topic' => 'More on extensions.'],
                    'score' => 0,
                    'passed' => false,
                    'attempt_number' => 1,
                    'submitted_at' => now()->subDay(),
                ]);
            }

            WorkshopSurveyResponse::create([
                'workshop_survey_template_id' => $knowledgeCheck->id,
                'workshop_session_id' => $session->id,
                'contact_id' => $participant->contact_id,
                'responses_json' => ['kc1' => 'Module', 'kc2' => ['Location', 'Customer', 'Job'], 'kc3' => 'A simplified, interview-style configuration process.', 'kc4' => 'Configurations', 'kc5' => 'To enable partners to successfully configure, generate, and implement the IFPv2 application for customers.'],
                'score' => 100,
                'passed' => true,
                'attempt_number' => 1,
                'submitted_at' => now()->subDay(),
            ]);
        }

        foreach ($inProgress as $index => $participant) {
            $completedPageCount = ($index % 3) + 1;

            foreach ($labPages->take($completedPageCount) as $page) {
                foreach ($page->checks() as $check) {
                    WorkshopCheckAttempt::create([
                        'contact_id' => $participant->contact_id,
                        'workshop_session_id' => $session->id,
                        'workshop_page_id' => $page->id,
                        'check_id' => $check['id'],
                        'passed' => true,
                        'attempted_at' => now()->subHours(6),
                    ]);
                }
            }

            // One extra page attempted but not yet passed - shows "In Progress".
            if ($labPages->count() > $completedPageCount) {
                $strugglingPage = $labPages[$completedPageCount];
                WorkshopCheckAttempt::create([
                    'contact_id' => $participant->contact_id,
                    'workshop_session_id' => $session->id,
                    'workshop_page_id' => $strugglingPage->id,
                    'check_id' => $strugglingPage->checks()[0]['id'],
                    'passed' => false,
                    'attempted_at' => now()->subHours(3),
                ]);
            }

            if ($preSurvey) {
                WorkshopSurveyResponse::create([
                    'workshop_survey_template_id' => $preSurvey->id,
                    'workshop_session_id' => $session->id,
                    'contact_id' => $participant->contact_id,
                    'responses_json' => $this->likertResponses($preSurvey, 2) + ['background' => ['Project Manager'], 'experience' => 'No Projects (yet)', 'goal' => 'Understand the extension exercises before the live session.'],
                    'score' => 0,
                    'passed' => false,
                    'attempt_number' => 1,
                    'submitted_at' => now()->subDays(3),
                ]);
            }

            WorkshopSurveyResponse::create([
                'workshop_survey_template_id' => $knowledgeCheck->id,
                'workshop_session_id' => $session->id,
                'contact_id' => $participant->contact_id,
                'responses_json' => ['kc1' => 'List', 'kc2' => ['Location'], 'kc3' => 'A rigid, one-time build process for stability.'],
                'score' => 33,
                'passed' => false,
                'attempt_number' => 1,
                'submitted_at' => now()->subHours(2),
            ]);
        }

        $this->command->info("  ✓ Seeded leaderboard activity for {$ahead->count()} finished + {$inProgress->count()} in-progress participants");
    }

    /**
     * @return array<string, int>
     */
    private function likertResponses(WorkshopSurveyTemplate $template, int $value): array
    {
        return $template->likertQuestionIds()->mapWithKeys(fn (string $id) => [$id => $value])->all();
    }

    /**
     * RPM and OWP don't have real ported curriculum (no lab pages/checks to
     * draw on), but they do have real sessions and generically-enrolled
     * demo participants from seedSessionParticipants() - give each a real
     * pre/post/knowledge survey template plus response data, so their
     * leaderboards, reports, and analytics have real numbers too instead of
     * only IFP looking populated.
     */
    private function seedRpmAndOwpSurveyActivity(User $facilitator): void
    {
        $topicsByWorkshop = [
            'rpm' => [
                ['id' => 'tq', 'text' => 'Trade & Quota Configuration'],
                ['id' => 'segmentation', 'text' => 'Segmentation Modeling'],
                ['id' => 'capacity', 'text' => 'Capacity Planning'],
                ['id' => 'forecasting', 'text' => 'AI-Driven Sales Forecasting'],
            ],
            'owp' => [
                ['id' => 'app_config', 'text' => 'Application Configuration'],
                ['id' => 'model_gen', 'text' => 'Model Generation & Validation'],
                ['id' => 'reporting', 'text' => 'Reporting & Dashboards'],
                ['id' => 'integration', 'text' => 'Integration & Data Loading'],
            ],
        ];

        $knowledgeQuestionsByWorkshop = [
            'rpm' => [
                ['id' => 'kc1', 'text' => 'What is the primary purpose of the Segmentation module in RPM?', 'type' => 'single_select', 'options' => ['To set territory quotas', 'To group accounts by shared characteristics for targeted planning', 'To generate the model from scratch', 'To manage user access'], 'correct' => 'To group accounts by shared characteristics for targeted planning', 'required' => true],
                ['id' => 'kc2', 'text' => 'Which inputs typically drive the AI-Driven Sales Forecasting engine?', 'type' => 'multi_select', 'options' => ['Historical pipeline data', 'Seasonality patterns', 'Rep tenure', 'Weather data'], 'correct' => ['Historical pipeline data', 'Seasonality patterns'], 'required' => true],
                ['id' => 'kc3', 'text' => 'Capacity Planning in RPM is primarily used to answer which question?', 'type' => 'single_select', 'options' => ['How many reps do we need to hit the number?', 'What is our win rate?', 'Which accounts are at risk?', 'What is the average deal size?'], 'correct' => 'How many reps do we need to hit the number?', 'required' => true],
            ],
            'owp' => [
                ['id' => 'kc1', 'text' => 'What does the Application Framework Configurator streamline?', 'type' => 'single_select', 'options' => ['Interview-style setup of the application', 'Manual line item creation only', 'User license management', 'Data export scheduling'], 'correct' => 'Interview-style setup of the application', 'required' => true],
                ['id' => 'kc2', 'text' => 'Which of these are common data loading methods? (select all that apply)', 'type' => 'multi_select', 'options' => ['CSV/XLSX import', 'Anaplan Connect', 'Direct database write', 'Manual line item entry'], 'correct' => ['CSV/XLSX import', 'Anaplan Connect'], 'required' => true],
                ['id' => 'kc3', 'text' => 'What is the recommended first step before customizing a generated model?', 'type' => 'single_select', 'options' => ['Validate the base model against requirements', 'Delete unused modules immediately', 'Rename all line items', 'Disable version control'], 'correct' => 'Validate the base model against requirements', 'required' => true],
            ],
        ];

        foreach (['rpm', 'owp'] as $slug) {
            $workshop = Workshop::where('slug', $slug)->first();

            if (! $workshop) {
                continue;
            }

            $topics = $topicsByWorkshop[$slug];

            $preTemplate = WorkshopSurveyTemplate::updateOrCreate(
                ['workshop_id' => $workshop->id, 'kind' => 'pre'],
                [
                    'intro' => "A couple of quick questions before you get started with the {$workshop->name}.",
                    'questions_json' => [
                        ...array_map(fn (array $topic) => ['id' => $topic['id'], 'text' => "How confident are you with {$topic['text']} today?", 'type' => 'likert', 'required' => true], $topics),
                        ['id' => 'goal', 'text' => 'What do you most want to get out of this workshop?', 'type' => 'text', 'required' => false],
                    ],
                    'updated_by' => $facilitator->id,
                ],
            );

            $postTemplate = WorkshopSurveyTemplate::updateOrCreate(
                ['workshop_id' => $workshop->id, 'kind' => 'post'],
                [
                    'intro' => "Now that you've completed the workshop, revisit the same topics.",
                    'questions_json' => [
                        ...array_map(fn (array $topic) => ['id' => $topic['id'], 'text' => "How confident are you with {$topic['text']} today?", 'type' => 'likert', 'required' => true], $topics),
                        ['id' => 'next_topic', 'text' => 'What would you like covered in more depth next time?', 'type' => 'text', 'required' => false],
                    ],
                    'updated_by' => $facilitator->id,
                ],
            );

            $knowledgeTemplate = WorkshopSurveyTemplate::updateOrCreate(
                ['workshop_id' => $workshop->id, 'kind' => 'knowledge'],
                [
                    'intro' => 'A short knowledge check to confirm what you took away from the workshop.',
                    'questions_json' => $knowledgeQuestionsByWorkshop[$slug],
                    'passing_score' => 70,
                    'updated_by' => $facilitator->id,
                ],
            );

            foreach ($workshop->sessions()->where('status', 'completed')->get() as $session) {
                if (WorkshopSurveyResponse::where('workshop_session_id', $session->id)->exists()) {
                    continue;
                }

                $participants = $session->participants()->notRemoved()->orderBy('id')->get();

                foreach ($participants as $index => $participant) {
                    // A believable spread: most finish strong, one struggles.
                    $struggling = $index === $participants->count() - 1 && $participants->count() > 1;
                    $preValue = $struggling ? 2 : 3;
                    $postValue = $struggling ? 3 : 5;

                    WorkshopSurveyResponse::create([
                        'workshop_survey_template_id' => $preTemplate->id,
                        'workshop_session_id' => $session->id,
                        'contact_id' => $participant->contact_id,
                        'responses_json' => $this->likertResponses($preTemplate, $preValue) + ['goal' => 'Get comfortable with the core workflow end-to-end.'],
                        'score' => 0,
                        'passed' => false,
                        'attempt_number' => 1,
                        'submitted_at' => $session->start_date->copy()->subDay(),
                    ]);

                    WorkshopSurveyResponse::create([
                        'workshop_survey_template_id' => $postTemplate->id,
                        'workshop_session_id' => $session->id,
                        'contact_id' => $participant->contact_id,
                        'responses_json' => $this->likertResponses($postTemplate, $postValue) + ['next_topic' => 'More hands-on time with real data.'],
                        'score' => 0,
                        'passed' => false,
                        'attempt_number' => 1,
                        'submitted_at' => $session->end_date,
                    ]);

                    $knowledgeQuestions = collect($knowledgeTemplate->questions_json);
                    $knowledgeResponses = $struggling
                        ? $knowledgeQuestions->mapWithKeys(fn (array $q) => [$q['id'] => is_array($q['correct'] ?? null) ? [$q['options'][0]] : $q['options'][0]])
                        : $knowledgeQuestions->mapWithKeys(fn (array $q) => [$q['id'] => $q['correct']]);
                    $result = $knowledgeTemplate->score($knowledgeResponses->all());

                    WorkshopSurveyResponse::create([
                        'workshop_survey_template_id' => $knowledgeTemplate->id,
                        'workshop_session_id' => $session->id,
                        'contact_id' => $participant->contact_id,
                        'responses_json' => $knowledgeResponses->all(),
                        'score' => $result['score'],
                        'passed' => $result['passed'],
                        'attempt_number' => 1,
                        'submitted_at' => $session->end_date,
                    ]);

                    if ($struggling) {
                        $participant->update(['completed_at' => null]);
                    } else {
                        $participant->update(['completed_at' => $session->end_date, 'completed_by' => $facilitator->id]);
                    }
                }
            }

            $this->command->info("  ✓ Seeded survey templates and response activity for {$workshop->name}");
        }
    }
}
