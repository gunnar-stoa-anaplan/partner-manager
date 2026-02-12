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
    public function run(): void
    {
        $this->command->info('Creating blueprint assignments, enrollments, and progress...');

        $partners = Partner::with('contacts')->get();
        $blueprints = Blueprint::with('outcomes.contentItems')->where('status', 'published')->get();
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
            if ($contacts->isEmpty()) continue;

            $psm = $users->firstWhere('id', $partner->user_id) ?? $admin;

            // Each partner gets 2-5 blueprint assignments
            $bpCount = fake()->numberBetween(2, min(5, $blueprints->count()));
            $selectedBlueprints = $blueprints->random($bpCount);

            foreach ($selectedBlueprints as $blueprint) {
                $role = fake()->randomElement(['gtm', 'pre_sales', 'delivery']);

                // Avoid duplicate assignments
                $existing = PartnerBlueprintAssignment::where('partner_id', $partner->id)
                    ->where('blueprint_id', $blueprint->id)
                    ->where('role', $role)
                    ->exists();
                if ($existing) continue;

                $status = fake()->randomElement(['active', 'active', 'active', 'completed', 'paused']);

                $assignment = PartnerBlueprintAssignment::create([
                    'partner_id' => $partner->id,
                    'blueprint_id' => $blueprint->id,
                    'role' => $role,
                    'assigned_by' => $psm->id,
                    'status' => $status,
                ]);
                $assignmentCount++;

                // Enroll 1-4 contacts in this assignment
                $enrollCount = fake()->numberBetween(1, min(4, $contacts->count()));
                $enrolledContacts = $contacts->random($enrollCount);

                foreach ($enrolledContacts as $contact) {
                    $enrollStatus = match ($status) {
                        'completed' => 'completed',
                        'paused' => fake()->randomElement(['enrolled', 'in_progress']),
                        default => fake()->randomElement(['enrolled', 'in_progress', 'in_progress', 'completed']),
                    };

                    $enrollment = BlueprintEnrollment::create([
                        'partner_blueprint_assignment_id' => $assignment->id,
                        'contact_id' => $contact->id,
                        'enrolled_by' => $psm->id,
                        'status' => $enrollStatus,
                        'completed_at' => $enrollStatus === 'completed' ? fake()->dateTimeBetween('-3 months', 'now') : null,
                    ]);
                    $enrollmentCount++;

                    // Generate progress for content items based on enrollment status
                    $allItems = $blueprint->outcomes->flatMap->contentItems;
                    if ($allItems->isEmpty()) continue;

                    $completionRate = match ($enrollStatus) {
                        'completed' => 1.0,
                        'in_progress' => fake()->randomFloat(2, 0.2, 0.8),
                        default => fake()->randomFloat(2, 0.0, 0.2),
                    };

                    $completedCount = (int) round($allItems->count() * $completionRate);
                    $completedItems = $allItems->shuffle()->take($completedCount);

                    foreach ($completedItems as $item) {
                        BlueprintProgress::create([
                            'blueprint_enrollment_id' => $enrollment->id,
                            'blueprint_content_item_id' => $item->id,
                            'completed' => true,
                            'completed_at' => fake()->dateTimeBetween('-6 months', 'now'),
                        ]);
                        $progressCount++;
                    }
                }
            }
        }

        $this->command->info("Created {$assignmentCount} assignments, {$enrollmentCount} enrollments, {$progressCount} progress records.");
    }
}
