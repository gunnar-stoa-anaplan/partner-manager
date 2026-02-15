<?php

namespace Database\Seeders;

use App\Models\AnaplanModel;
use App\Models\Contact;
use App\Models\EnablementSession;
use App\Models\Lab;
use App\Models\LabCheckpoint;
use App\Models\LabCheckpointResult;
use App\Models\LabParticipant;
use App\Models\User;
use Illuminate\Database\Seeder;

class LabSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating workshop labs with checkpoints, participants, and results...');

        $sessions = EnablementSession::whereIn('status', ['scheduled', 'in_progress', 'completed'])
            ->with('registeredPartners')
            ->take(3)
            ->get();

        if ($sessions->isEmpty()) {
            $this->command->warn('No enablement sessions found. Skipping LabSeeder.');

            return;
        }

        $goldenModel = AnaplanModel::where('active_state', '!=', 'ARCHIVED')->first();
        $creator = User::first();

        if (! $goldenModel || ! $creator) {
            $this->command->warn('No Anaplan model or user found. Skipping LabSeeder.');

            return;
        }

        $labConfigs = [
            [
                'name' => 'FP&A Model Build Lab',
                'description' => 'Build a connected financial planning model with revenue drivers, cost allocations, and consolidated reporting.',
                'instructions' => "1. Create the Revenue Planning module with line items for each product line\n2. Add the Cost Center module with allocation formulas\n3. Build the Consolidated P&L module that rolls up revenue and costs\n4. Configure list hierarchies for Geography and Product\n5. Validate each checkpoint when complete",
                'duration_minutes' => 90,
                'checkpoints' => [
                    ['title' => 'Revenue Planning module exists', 'type' => 'structure', 'points' => 1, 'description' => 'Verify the Revenue Planning module has been created with the required line items.', 'config' => ['type' => 'module', 'name' => 'Revenue Planning', 'expected_line_items' => ['Gross Revenue', 'Net Revenue', 'Discount Rate']]],
                    ['title' => 'Revenue formula is correct', 'type' => 'formula', 'points' => 2, 'description' => 'Check that Net Revenue is calculated correctly from Gross Revenue and Discount Rate.', 'config' => ['module_name' => 'Revenue Planning', 'line_item_name' => 'Net Revenue', 'module_anaplan_id' => 'mod001', 'line_item_anaplan_id' => 'li001', 'expected_formula' => 'Gross Revenue * (1 - Discount Rate)']],
                    ['title' => 'Cost Center module configured', 'type' => 'config', 'points' => 1, 'description' => 'Verify the Cost Center module uses the correct format and summary method.', 'config' => ['module_anaplan_id' => 'mod002', 'line_item_anaplan_id' => 'li002', 'expected_format' => 'NUMBER', 'expected_summary' => 'SUM']],
                    ['title' => 'Geography list populated', 'type' => 'list', 'points' => 1, 'description' => 'The Geography list should contain all required regions.', 'config' => ['list_anaplan_id' => 'list001', 'list_name' => 'Geography', 'expected_items' => ['North America', 'EMEA', 'APAC', 'LATAM']], 'hint' => 'Make sure you have exactly four regions: North America, EMEA, APAC, and LATAM.'],
                    ['title' => 'Consolidated P&L total matches', 'type' => 'value', 'points' => 3, 'description' => 'The Total P&L value should match the expected output.', 'config' => ['module_name' => 'Consolidated P&L', 'line_item_name' => 'Net Income', 'export_anaplan_id' => 'exp001', 'expected_value' => 250000, 'tolerance' => 0.05], 'hint' => 'Double-check that all revenue streams roll up and cost allocations are applied correctly.'],
                ],
            ],
            [
                'name' => 'Supply Chain Planning Lab',
                'description' => 'Configure a demand planning model with forecasting inputs, safety stock calculations, and S&OP dashboard modules.',
                'instructions' => "1. Create the Demand Forecast module with time-series line items\n2. Build the Safety Stock module with min/max calculations\n3. Create the S&OP Summary module with key metrics\n4. Validate your work at each step",
                'duration_minutes' => 75,
                'checkpoints' => [
                    ['title' => 'Demand Forecast module exists', 'type' => 'structure', 'points' => 1, 'description' => 'Verify the Demand Forecast module exists with forecast line items.', 'config' => ['type' => 'module', 'name' => 'Demand Forecast', 'expected_line_items' => ['Base Demand', 'Seasonal Factor', 'Adjusted Forecast']]],
                    ['title' => 'Adjusted Forecast formula', 'type' => 'formula', 'points' => 2, 'description' => 'Check that Adjusted Forecast uses the correct calculation.', 'config' => ['module_name' => 'Demand Forecast', 'line_item_name' => 'Adjusted Forecast', 'module_anaplan_id' => 'mod003', 'line_item_anaplan_id' => 'li003', 'expected_formula' => 'Base Demand * Seasonal Factor']],
                    ['title' => 'Product Categories list', 'type' => 'list', 'points' => 1, 'description' => 'The Product Categories list should contain all required categories.', 'config' => ['list_anaplan_id' => 'list002', 'list_name' => 'Product Categories', 'expected_items' => ['Raw Materials', 'Work in Progress', 'Finished Goods']]],
                    ['title' => 'Safety Stock configuration', 'type' => 'config', 'points' => 2, 'description' => 'Verify Safety Stock line item uses correct format.', 'config' => ['module_anaplan_id' => 'mod004', 'line_item_anaplan_id' => 'li004', 'expected_format' => 'NUMBER', 'expected_summary' => 'NONE']],
                ],
            ],
            [
                'name' => 'Workforce Planning Basics',
                'description' => 'Build a headcount planning model with FTE calculations, compensation modules, and department rollups.',
                'instructions' => "1. Create the Headcount module with FTE and compensation line items\n2. Build the Department Rollup module\n3. Verify calculations match expected values",
                'duration_minutes' => 60,
                'checkpoints' => [
                    ['title' => 'Headcount module structure', 'type' => 'structure', 'points' => 1, 'description' => 'Verify the Headcount module has FTE and Compensation line items.', 'config' => ['type' => 'module', 'name' => 'Headcount Planning', 'expected_line_items' => ['FTE Count', 'Average Salary', 'Total Compensation']]],
                    ['title' => 'Total Compensation formula', 'type' => 'formula', 'points' => 2, 'description' => 'Check that Total Compensation = FTE Count * Average Salary.', 'config' => ['module_name' => 'Headcount Planning', 'line_item_name' => 'Total Compensation', 'module_anaplan_id' => 'mod005', 'line_item_anaplan_id' => 'li005', 'expected_formula' => 'FTE Count * Average Salary']],
                    ['title' => 'Department list populated', 'type' => 'list', 'points' => 1, 'description' => 'The Department list should be populated.', 'config' => ['list_anaplan_id' => 'list003', 'list_name' => 'Departments', 'expected_items' => ['Engineering', 'Sales', 'Marketing', 'Finance', 'HR']]],
                ],
            ],
        ];

        $labCount = 0;

        foreach ($labConfigs as $idx => $cfg) {
            $session = $sessions[$idx % $sessions->count()];

            $lab = Lab::create([
                'enablement_session_id' => $session->id,
                'name' => $cfg['name'],
                'description' => $cfg['description'],
                'instructions' => $cfg['instructions'],
                'golden_model_id' => $goldenModel->id,
                'golden_snapshot' => ['seeded' => true, 'modules' => [], 'lists' => []],
                'status' => $idx === 0 ? 'published' : ($idx === 1 ? 'published' : 'draft'),
                'published_at' => $idx < 2 ? now()->subDays(rand(1, 14)) : null,
                'duration_minutes' => $cfg['duration_minutes'],
                'created_by' => $creator->id,
            ]);

            // Create checkpoints
            foreach ($cfg['checkpoints'] as $order => $cp) {
                LabCheckpoint::create([
                    'lab_id' => $lab->id,
                    'title' => $cp['title'],
                    'description' => $cp['description'] ?? null,
                    'sort_order' => $order + 1,
                    'checkpoint_type' => $cp['type'],
                    'validation_config' => $cp['config'],
                    'is_required' => true,
                    'points' => $cp['points'],
                    'hint' => $cp['hint'] ?? null,
                ]);
            }

            // Add participants for published labs
            if ($lab->isPublished()) {
                $partnerIds = $session->registeredPartners->pluck('id');
                if ($partnerIds->isEmpty()) {
                    $partnerIds = collect([Contact::inRandomOrder()->first()?->partner_id])->filter();
                }

                $contacts = Contact::whereIn('partner_id', $partnerIds)
                    ->where('contact_type', 'resource')
                    ->inRandomOrder()
                    ->take(rand(3, 6))
                    ->get();

                if ($contacts->isEmpty()) {
                    $contacts = Contact::inRandomOrder()->take(3)->get();
                }

                $checkpoints = $lab->checkpoints;

                foreach ($contacts as $contact) {
                    $participant = LabParticipant::create([
                        'lab_id' => $lab->id,
                        'contact_id' => $contact->id,
                        'partner_id' => $contact->partner_id,
                        'assigned_model_id' => $goldenModel->id,
                        'status' => 'not_started',
                        'total_points_earned' => 0,
                        'total_points_possible' => $lab->totalPoints(),
                        'enrolled_by' => $creator->id,
                    ]);

                    // Simulate some validation results
                    $passedCount = rand(0, $checkpoints->count());
                    $pointsEarned = 0;

                    foreach ($checkpoints->take($passedCount) as $checkpoint) {
                        $attempts = rand(1, 3);

                        // Create failed attempts first
                        for ($a = 1; $a < $attempts; $a++) {
                            LabCheckpointResult::create([
                                'lab_participant_id' => $participant->id,
                                'lab_checkpoint_id' => $checkpoint->id,
                                'passed' => false,
                                'validated_at' => now()->subMinutes(rand(30, 120)),
                                'validation_details' => ['seeded' => true],
                                'error_message' => fake()->randomElement([
                                    'Formula does not match expected pattern.',
                                    'Module not found in model.',
                                    'Missing required list items.',
                                    'Value exceeds allowed tolerance.',
                                ]),
                                'attempt_number' => $a,
                            ]);
                        }

                        // Final passing attempt
                        LabCheckpointResult::create([
                            'lab_participant_id' => $participant->id,
                            'lab_checkpoint_id' => $checkpoint->id,
                            'passed' => true,
                            'validated_at' => now()->subMinutes(rand(5, 30)),
                            'validation_details' => ['seeded' => true, 'matched' => true],
                            'error_message' => null,
                            'attempt_number' => $attempts,
                        ]);

                        $pointsEarned += $checkpoint->points;
                    }

                    // Add some failed-only results for incomplete checkpoints
                    $remaining = $checkpoints->slice($passedCount);
                    foreach ($remaining->take(rand(0, $remaining->count())) as $checkpoint) {
                        LabCheckpointResult::create([
                            'lab_participant_id' => $participant->id,
                            'lab_checkpoint_id' => $checkpoint->id,
                            'passed' => false,
                            'validated_at' => now()->subMinutes(rand(5, 60)),
                            'validation_details' => ['seeded' => true],
                            'error_message' => 'Configuration does not match expected values.',
                            'attempt_number' => 1,
                        ]);
                    }

                    // Update participant progress
                    $status = 'not_started';
                    if ($pointsEarned >= $lab->totalPoints()) {
                        $status = 'completed';
                    } elseif ($pointsEarned > 0) {
                        $status = 'in_progress';
                    }

                    $participant->update([
                        'total_points_earned' => $pointsEarned,
                        'status' => $status,
                        'started_at' => $pointsEarned > 0 ? now()->subHours(rand(1, 4)) : null,
                        'completed_at' => $status === 'completed' ? now()->subMinutes(rand(10, 60)) : null,
                    ]);
                }
            }

            $labCount++;
        }

        $this->command->info("Created {$labCount} labs with checkpoints, participants, and results.");
    }
}
