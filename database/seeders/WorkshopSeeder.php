<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopCredential;
use App\Models\WorkshopSessionParticipant;
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
                ['slug' => '2026-06-9', 'name' => 'June 9, 2026 Session', 'start_date' => '2026-06-09', 'end_date' => '2026-06-12', 'status' => 'upcoming', 'notes' => null],
                ['slug' => '2026-06-15', 'name' => 'June 15, 2026 Session', 'start_date' => '2026-06-15', 'end_date' => '2026-06-19', 'status' => 'upcoming', 'notes' => null],
                ['slug' => 'deloitte-2026-06-15', 'name' => 'June 15, 2026 Session', 'start_date' => '2026-06-15', 'end_date' => '2026-06-19', 'status' => 'upcoming', 'notes' => 'Dedicated session for Deloitte EMEA'],
                ['slug' => '2026-07-13', 'name' => 'June 23, 2026 Session', 'start_date' => '2026-07-13', 'end_date' => '2026-07-17', 'status' => 'upcoming', 'notes' => 'Deloitte US'],
                ['slug' => '2026-07-07', 'name' => 'July 7, 2026 Session', 'start_date' => '2026-07-07', 'end_date' => '2026-07-10', 'status' => 'upcoming', 'notes' => 'Session coordinated by Fuvas'],
            ],
        ],
        [
            'slug' => 'rpm',
            'name' => 'RPM Delivery Partner Workshop',
            'short_description' => 'Hands-on, self-paced workshop for RPM delivery partners.',
            'status' => 'active',
            'sessions' => [
                ['slug' => '2026-06-22', 'name' => 'June 22, 2026 Session', 'start_date' => '2026-06-22', 'end_date' => '2026-06-26', 'status' => 'upcoming', 'notes' => null],
            ],
        ],
        [
            'slug' => 'owp',
            'name' => 'OWP Delivery Partner Workshop',
            'short_description' => 'Hands-on, self-paced workshop for OWP delivery partners.',
            'status' => 'active',
            'sessions' => [
                ['slug' => '2026-06-30', 'name' => 'June 30, 2026 Session in London', 'start_date' => '2026-06-30', 'end_date' => '2026-07-03', 'status' => 'upcoming', 'notes' => 'Anju led-session for key partners in London'],
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

        WorkshopSurveyTemplate::updateOrCreate(
            ['workshop_id' => $ifp->id, 'kind' => 'post'],
            [
                'intro' => 'A few quick questions to confirm what you took away from the workshop.',
                'questions_json' => [
                    ['id' => 'q1', 'text' => 'Which module type holds transactional detail data in an IFP model?', 'type' => 'single', 'options' => ['Line Item', 'List', 'Module', 'Dashboard'], 'correct' => ['Module']],
                    ['id' => 'q2', 'text' => 'Which of these are part of the standard IFP dimensionality? (select all that apply)', 'type' => 'multiple', 'options' => ['Location', 'Customer', 'Job', 'Weather'], 'correct' => ['Location', 'Customer', 'Job']],
                    ['id' => 'q3', 'text' => 'What would you like covered in more depth next time?', 'type' => 'text'],
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
}
