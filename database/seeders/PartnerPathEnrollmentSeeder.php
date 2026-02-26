<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\PartnerPath;
use App\Models\PartnerPathEnrollment;
use App\Models\PartnerStageHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerPathEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating partner path enrollments and stage history...');

        $partners = Partner::all();
        $defaultPath = PartnerPath::where('is_default', true)->first()
            ?? PartnerPath::first();

        if (! $defaultPath) {
            $this->command->warn('No partner paths found. Run PartnerPathSeeder first.');

            return;
        }

        $stages = $defaultPath->stages()->orderBy('sort_order')->get();
        if ($stages->isEmpty()) {
            $this->command->warn('No stages found for path. Run PartnerPathSeeder first.');

            return;
        }

        $admin = User::whereRelation('role', 'slug', 'admin')->first();
        $users = User::all();
        $enrollmentCount = 0;
        $historyCount = 0;

        foreach ($partners as $partner) {
            $psm = $users->firstWhere('id', $partner->user_id) ?? $admin;

            // Weighted stage distribution — most partners early-to-mid stage
            $stageIndex = fake()->randomElement(array_merge(
                array_fill(0, 4, 0), // Onboarded
                array_fill(0, 5, 1), // Enabled
                array_fill(0, 3, 2), // Performing
                array_fill(0, 2, 3), // Accelerating
                array_fill(0, 1, 4), // Strategic
            ));
            $stageIndex = min($stageIndex, $stages->count() - 1);
            $currentStage = $stages[$stageIndex];

            $enrolledAt = fake()->dateTimeBetween('-18 months', '-2 months');
            $stageEnteredAt = $stageIndex === 0
                ? $enrolledAt
                : fake()->dateTimeBetween($enrolledAt, 'now');

            // Guarantee active status for the first 3 partners (Accenture, Deloitte, PwC)
            if ($partner->id <= 3) {
                $status = 'active';
            } else {
                $status = fake()->randomElement(['active', 'active', 'active', 'active', 'paused', 'completed']);
                if ($stageIndex === $stages->count() - 1 && fake()->boolean(40)) {
                    $status = 'completed';
                }
            }

            $enrollment = PartnerPathEnrollment::create([
                'partner_id' => $partner->id,
                'partner_path_id' => $defaultPath->id,
                'current_stage_id' => $currentStage->id,
                'enrolled_at' => $enrolledAt,
                'stage_entered_at' => $stageEnteredAt,
                'last_reviewed_at' => fake()->boolean(70) ? fake()->dateTimeBetween($stageEnteredAt, 'now') : null,
                'reviewed_by' => fake()->boolean(70) ? $psm->id : null,
                'status' => $status,
                'notes' => fake()->boolean(30) ? fake()->sentence(6) : null,
            ]);
            $enrollmentCount++;

            // Generate stage history for transitions up to current stage
            $previousDate = $enrolledAt;
            for ($i = 0; $i <= $stageIndex; $i++) {
                $fromStage = $i === 0 ? null : $stages[$i - 1];
                $toStage = $stages[$i];

                $transitionDate = $i === 0
                    ? $enrolledAt
                    : fake()->dateTimeBetween($previousDate, $stageEnteredAt > $previousDate ? $stageEnteredAt : 'now');

                // Build gate results for non-initial transitions
                $gateResults = null;
                if ($i > 0) {
                    $gateResults = [
                        'gate_status' => 'passed',
                        'criteria_met' => fake()->numberBetween(3, 6),
                        'criteria_total' => fake()->numberBetween(4, 6),
                        'reviewed_at' => $transitionDate->format('Y-m-d H:i:s'),
                    ];
                }

                PartnerStageHistory::create([
                    'partner_id' => $partner->id,
                    'partner_path_id' => $defaultPath->id,
                    'from_stage_id' => $fromStage?->id,
                    'to_stage_id' => $toStage->id,
                    'transitioned_at' => $transitionDate,
                    'approved_by' => $psm->id,
                    'gate_results' => $gateResults,
                    'notes' => $i === 0 ? 'Initial enrollment' : fake()->randomElement([
                        'All gate criteria met',
                        'Passed with strong performance',
                        'Approved after review',
                        'Gate criteria satisfied — advancing partner',
                    ]),
                ]);
                $historyCount++;

                $previousDate = $transitionDate;
            }
        }

        $this->command->info("Created {$enrollmentCount} enrollments, {$historyCount} stage history records.");
    }
}
