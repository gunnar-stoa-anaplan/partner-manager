<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\ResourcePath;
use App\Models\ResourcePathEnrollment;
use App\Models\ResourceStageHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResourcePathEnrollmentSeeder extends Seeder
{
    /**
     * Map contact resource_role to the most appropriate resource path.
     * Contacts without a match get enrolled in Model Builder (default).
     */
    private const ROLE_TO_PATH = [
        'model_builder' => 'Model Builder',
        'solution_architect' => 'Solution Architect',
        'consultant' => 'Model Builder',
        'project_manager' => 'Champion',
        'developer' => 'Model Builder',
        'data_integration' => 'Model Builder',
        'other' => 'Pre-Sales',
    ];

    /**
     * Influencer contacts get enrolled in Champion or Sales paths.
     */
    private const INFLUENCER_PATHS = ['Sales', 'Champion', 'Pre-Sales'];

    public function run(): void
    {
        $this->command->info('Seeding resource path enrollments and stage history...');

        $paths = ResourcePath::with(['stages' => fn ($q) => $q->orderBy('sort_order')])->get()->keyBy('name');

        if ($paths->isEmpty()) {
            $this->command->warn('No resource paths found. Run ResourcePathSeeder first.');

            return;
        }

        $admin = User::whereRelation('role', 'slug', 'admin')->first();
        $users = User::all();

        $enrollmentCount = 0;
        $historyCount = 0;

        // Enroll resource-type contacts using role mapping
        $resourceContacts = Contact::where('contact_type', 'resource')
            ->whereHas('partner')
            ->with('partner')
            ->get();

        foreach ($resourceContacts as $contact) {
            $pathName = self::ROLE_TO_PATH[$contact->resource_role] ?? 'Model Builder';
            $path = $paths->get($pathName) ?? $paths->get('Model Builder');

            if (! $path) {
                continue;
            }

            [$enrollment, $historyRecords] = $this->enrollContact(
                $contact, $path, $users, $admin
            );

            if ($enrollment) {
                $enrollmentCount++;
                $historyCount += $historyRecords;
            }
        }

        // Enroll influencer-type contacts in Sales/Champion/Pre-Sales paths
        $influencerContacts = Contact::where('contact_type', 'influencer')
            ->whereHas('partner')
            ->with('partner')
            ->get();

        foreach ($influencerContacts as $contact) {
            $pathName = fake()->randomElement(self::INFLUENCER_PATHS);
            $path = $paths->get($pathName);

            if (! $path) {
                continue;
            }

            [$enrollment, $historyRecords] = $this->enrollContact(
                $contact, $path, $users, $admin
            );

            if ($enrollment) {
                $enrollmentCount++;
                $historyCount += $historyRecords;
            }
        }

        $this->command->info("  Created {$enrollmentCount} resource enrollments, {$historyCount} stage history records.");
    }

    /**
     * Enroll a contact in a resource path with weighted stage distribution.
     *
     * @return array{0: ResourcePathEnrollment|null, 1: int}
     */
    private function enrollContact(
        Contact $contact,
        ResourcePath $path,
        \Illuminate\Database\Eloquent\Collection $users,
        ?User $admin,
    ): array {
        $stages = $path->stages;
        if ($stages->isEmpty()) {
            return [null, 0];
        }

        // Check for existing enrollment
        $exists = ResourcePathEnrollment::where('contact_id', $contact->id)
            ->where('partner_id', $contact->partner_id)
            ->where('resource_path_id', $path->id)
            ->exists();

        if ($exists) {
            return [null, 0];
        }

        $psm = $users->firstWhere('id', $contact->partner?->user_id) ?? $admin;

        // Weighted stage distribution — most resources early-to-mid stage
        $stageIndex = fake()->randomElement(array_merge(
            array_fill(0, 4, 0), // Stage 1
            array_fill(0, 5, 1), // Stage 2
            array_fill(0, 3, 2), // Stage 3
            array_fill(0, 2, 3), // Stage 4
            array_fill(0, 1, 4), // Stage 5
        ));
        $stageIndex = min($stageIndex, $stages->count() - 1);
        $currentStage = $stages[$stageIndex];

        $enrolledAt = fake()->dateTimeBetween('-18 months', '-2 months');
        $stageEnteredAt = $stageIndex === 0
            ? $enrolledAt
            : fake()->dateTimeBetween($enrolledAt, 'now');

        $status = fake()->randomElement(['active', 'active', 'active', 'active', 'paused']);
        if ($stageIndex === $stages->count() - 1 && fake()->boolean(30)) {
            $status = 'completed';
        }

        $enrollment = ResourcePathEnrollment::create([
            'contact_id' => $contact->id,
            'partner_id' => $contact->partner_id,
            'resource_path_id' => $path->id,
            'current_stage_id' => $currentStage->id,
            'enrolled_at' => $enrolledAt,
            'stage_entered_at' => $stageEnteredAt,
            'last_reviewed_at' => fake()->boolean(60) ? fake()->dateTimeBetween($stageEnteredAt, 'now') : null,
            'reviewed_by' => fake()->boolean(60) ? $psm?->id : null,
            'status' => $status,
        ]);

        // Generate stage history for transitions up to current stage
        $historyCount = 0;
        $previousDate = $enrolledAt;

        for ($i = 0; $i <= $stageIndex; $i++) {
            $fromStage = $i === 0 ? null : $stages[$i - 1];
            $toStage = $stages[$i];

            $transitionDate = $i === 0
                ? $enrolledAt
                : fake()->dateTimeBetween($previousDate, $stageEnteredAt > $previousDate ? $stageEnteredAt : 'now');

            $gateResults = null;
            if ($i > 0) {
                $gateResults = [
                    'gate_status' => 'passed',
                    'criteria_met' => fake()->numberBetween(2, 4),
                    'criteria_total' => fake()->numberBetween(2, 4),
                    'reviewed_at' => $transitionDate->format('Y-m-d H:i:s'),
                ];
            }

            ResourceStageHistory::create([
                'contact_id' => $contact->id,
                'partner_id' => $contact->partner_id,
                'resource_path_id' => $path->id,
                'from_stage_id' => $fromStage?->id,
                'to_stage_id' => $toStage->id,
                'transitioned_at' => $transitionDate,
                'approved_by' => $psm?->id ?? $admin->id,
                'gate_results' => $gateResults,
                'notes' => $i === 0 ? 'Initial enrollment' : fake()->randomElement([
                    'All gate criteria met',
                    'Strong progress — advancing',
                    'Approved after review',
                    'Gate criteria satisfied',
                ]),
            ]);
            $historyCount++;
            $previousDate = $transitionDate;
        }

        return [$enrollment, $historyCount];
    }
}
