<?php

namespace Database\Seeders;

use App\Models\Blueprint;
use App\Models\BlueprintContentItem;
use App\Models\BlueprintEnrollment;
use App\Models\BlueprintProgress;
use App\Models\Partner;
use App\Models\PartnerBlueprintAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlueprintAssignmentSeeder extends Seeder
{
    /**
     * Blueprint assignment profiles per product area.
     * Each entry: [blueprint_id, role, completionMin, completionMax].
     *
     * @var array<string, list<array{int, string, float, float}>>
     */
    private const AREA_PROFILES = [
        'Finance' => [
            [5, 'delivery', 0.55, 0.90],   // IFP Delivery
            [5, 'pre_sales', 0.40, 0.75],   // IFP Pre-Sales
            [7, 'gtm', 0.35, 0.65],         // FCR Sales/GTM
            [7, 'delivery', 0.25, 0.60],     // FCR Delivery
        ],
        'Platform' => [
            [1, 'delivery', 0.60, 0.95],     // Polaris Delivery
            [1, 'pre_sales', 0.35, 0.70],    // Polaris Pre-Sales
            [12, 'gtm', 0.30, 0.65],         // Forecaster Sales/GTM
            [12, 'delivery', 0.20, 0.55],     // Forecaster Delivery
        ],
        'Sales' => [
            [10, 'delivery', 0.50, 0.85],    // RPM Delivery
            [10, 'gtm', 0.40, 0.75],         // RPM Sales/GTM
            [10, 'pre_sales', 0.30, 0.65],   // RPM Pre-Sales
        ],
        'Supply Chain' => [
            [2, 'delivery', 0.45, 0.80],     // IDP Delivery
            [2, 'gtm', 0.30, 0.65],          // IDP Sales/GTM
            [3, 'pre_sales', 0.25, 0.60],    // IBP Pre-Sales
            [3, 'delivery', 0.35, 0.70],      // IBP Delivery
        ],
        'Workforce' => [
            [11, 'delivery', 0.40, 0.80],    // OWP Delivery
            [11, 'gtm', 0.30, 0.65],         // OWP Sales/GTM
            [11, 'pre_sales', 0.25, 0.60],   // OWP Pre-Sales
        ],
    ];

    public function run(): void
    {
        $this->command->info('Creating blueprint assignments, enrollments, and progress...');

        // Clear existing data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        BlueprintProgress::truncate();
        BlueprintEnrollment::truncate();
        PartnerBlueprintAssignment::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $partners = Partner::with('contacts')->get();
        $blueprints = Blueprint::with('outcomes.contentItems')->where('status', 'published')->get()->keyBy('id');
        $admin = User::where('role', 'admin')->first();
        $users = User::all();

        if ($blueprints->isEmpty()) {
            $this->command->warn('No blueprints found. Run BlueprintSeeder first.');

            return;
        }

        $assignmentCount = 0;
        $enrollmentCount = 0;
        $progressCount = 0;

        foreach ($partners as $partner) {
            $contacts = $partner->contacts;
            if ($contacts->isEmpty()) {
                continue;
            }

            $psm = $users->firstWhere('id', $partner->user_id) ?? $admin;

            // Every partner gets assignments across all 5 product areas
            $selectedAreas = collect(array_keys(self::AREA_PROFILES))->shuffle();

            // Give each partner a "strength" area with higher completion
            $strengthArea = $selectedAreas->first();

            foreach ($selectedAreas as $areaIndex => $area) {
                $profiles = self::AREA_PROFILES[$area];
                // Strength area: use all profiles; other areas: use 1-3 profiles
                $profileCount = $area === $strengthArea
                    ? count($profiles)
                    : fake()->numberBetween(1, min(3, count($profiles)));
                $selectedProfiles = collect($profiles)->shuffle()->take($profileCount);

                foreach ($selectedProfiles as [$blueprintId, $role, $completionMin, $completionMax]) {
                    $blueprint = $blueprints->get($blueprintId);
                    if (! $blueprint) {
                        continue;
                    }

                    // Strength area gets a completion boost
                    $boost = $area === $strengthArea ? 0.15 : 0;
                    $minPct = min($completionMin + $boost, 0.95);
                    $maxPct = min($completionMax + $boost, 1.0);

                    $status = $maxPct >= 0.95 && fake()->boolean(20)
                        ? 'completed'
                        : 'active';

                    $assignment = PartnerBlueprintAssignment::create([
                        'partner_id' => $partner->id,
                        'blueprint_id' => $blueprintId,
                        'role' => $role,
                        'assigned_by' => $psm->id,
                        'status' => $status,
                        'created_at' => now()->subDays(rand(14, 120)),
                    ]);
                    $assignmentCount++;

                    // Get applicable content items for this role
                    $roleColumn = match ($role) {
                        'gtm' => 'gtm_applicable',
                        'pre_sales' => 'pre_sales_applicable',
                        'delivery' => 'delivery_applicable',
                    };

                    $applicableItems = BlueprintContentItem::whereHas('outcome', function ($q) use ($blueprintId) {
                        $q->where('blueprint_id', $blueprintId);
                    })->where($roleColumn, '!=', 'na')->orderBy('id')->get();

                    if ($applicableItems->isEmpty()) {
                        continue;
                    }

                    // Enroll 1-4 contacts
                    $enrollCount = fake()->numberBetween(1, min(4, $contacts->count()));
                    $enrolledContacts = $contacts->random($enrollCount);

                    foreach ($enrolledContacts as $contact) {
                        $targetPct = fake()->randomFloat(2, $minPct, $maxPct);
                        $itemsToComplete = (int) round($applicableItems->count() * $targetPct);

                        $enrollment = BlueprintEnrollment::create([
                            'partner_blueprint_assignment_id' => $assignment->id,
                            'contact_id' => $contact->id,
                            'enrolled_by' => $psm->id,
                            'created_at' => $assignment->created_at->copy()->addDays(rand(0, 7)),
                        ]);
                        $enrollmentCount++;

                        // Complete items in order for realism
                        $completedItems = $applicableItems->take($itemsToComplete);
                        foreach ($completedItems as $item) {
                            BlueprintProgress::create([
                                'blueprint_enrollment_id' => $enrollment->id,
                                'blueprint_content_item_id' => $item->id,
                                'completed' => true,
                                'completed_at' => $enrollment->created_at->copy()->addDays(rand(1, 60)),
                            ]);
                            $progressCount++;
                        }

                        $enrollment->updateStatus();
                    }
                }
            }

            $this->command->info("  {$partner->name}: {$selectedAreas->count()} areas assigned");
        }

        $this->command->info("Created {$assignmentCount} assignments, {$enrollmentCount} enrollments, {$progressCount} progress records.");
    }
}
