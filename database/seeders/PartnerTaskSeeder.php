<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\PartnerPathEnrollment;
use App\Models\PartnerTask;
use App\Models\TaskComment;
use App\Models\TaskTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerTaskSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating partner tasks and comments...');

        $enrollments = PartnerPathEnrollment::with(['partner.contacts', 'currentStage'])->get();
        $templates = TaskTemplate::where('is_active', true)->get();
        $users = User::all();
        $admin = User::whereRelation('role', 'slug', 'admin')->first();

        if ($enrollments->isEmpty()) {
            $this->command->warn('No enrollments found. Run PartnerPathEnrollmentSeeder first.');

            return;
        }

        $taskCount = 0;
        $commentCount = 0;

        foreach ($enrollments as $enrollment) {
            $partner = $enrollment->partner;
            $psm = $users->firstWhere('id', $partner->user_id) ?? $admin;
            $contacts = $partner->contacts;
            $currentStage = $enrollment->currentStage;

            // Get templates for stages up to and including current
            $relevantTemplates = $templates->filter(function ($t) use ($currentStage) {
                return ! $t->stage_id || $t->sort_order <= ($currentStage->sort_order ?? 999);
            });

            if ($relevantTemplates->isEmpty()) {
                $relevantTemplates = $templates->random(min(8, $templates->count()));
            }

            // Take a subset — not every template applies to every partner
            $count = fake()->numberBetween(5, min(15, $relevantTemplates->count()));
            $selectedTemplates = $relevantTemplates->shuffle()->take($count);

            foreach ($selectedTemplates as $template) {
                // If template has auto_verify_rule, check if partner already satisfies it
                $autoSatisfied = false;
                if ($template->auto_verify_rule && str_starts_with($template->auto_verify_rule, 'partner.')) {
                    $autoSatisfied = app(\App\Services\TaskAutoVerificationService::class)
                        ->evaluate($partner, $template->auto_verify_rule);
                }

                // Determine task status based on stage position
                $status = $autoSatisfied
                    ? 'completed'
                    : fake()->randomElement(array_merge(
                        array_fill(0, 3, 'completed'),
                        array_fill(0, 2, 'in_progress'),
                        array_fill(0, 2, 'not_started'),
                        array_fill(0, 1, 'blocked'),
                    ));

                $dueDate = fake()->dateTimeBetween('-3 months', '+3 months');
                $isPastDue = $dueDate < now() && ! in_array($status, ['completed', 'waived']);

                $startedAt = in_array($status, ['in_progress', 'completed', 'blocked'])
                    ? fake()->dateTimeBetween('-6 months', 'now')
                    : null;

                $completedAt = $status === 'completed'
                    ? fake()->dateTimeBetween($startedAt ?? '-3 months', 'now')
                    : null;

                $assignedContact = ($contacts->isNotEmpty() && fake()->boolean(40))
                    ? $contacts->random()
                    : null;

                $task = PartnerTask::create([
                    'partner_id' => $partner->id,
                    'task_template_id' => $template->id,
                    'title' => $template->title,
                    'description' => $template->description,
                    'category' => $template->category,
                    'requirement_level' => $template->requirement_level,
                    'status' => $status,
                    'priority' => fake()->randomElement(['low', 'medium', 'medium', 'high', 'critical']),
                    'effort_size' => $template->effort_size ?? 'medium',
                    'owner' => $template->default_owner ?? 'psm',
                    'assigned_to_user_id' => $psm->id,
                    'assigned_to_contact_id' => $assignedContact?->id,
                    'due_date' => $dueDate,
                    'started_at' => $startedAt,
                    'completed_at' => $completedAt,
                    'completed_by' => $completedAt ? $psm->id : null,
                    'completion_evidence' => $autoSatisfied
                        ? 'Automatically verified by system rule: '.$template->auto_verify_rule
                        : ($completedAt ? fake()->sentence(4) : null),
                    'auto_verified' => $autoSatisfied || ($completedAt && fake()->boolean(20)),
                    'auto_verified_at' => $autoSatisfied ? $completedAt : (($completedAt && fake()->boolean(20)) ? $completedAt : null),
                    'notes' => fake()->boolean(25) ? fake()->sentence(6) : null,
                    'stage_id' => $template->stage_id ?? $currentStage->id,
                    'partner_path_enrollment_id' => $enrollment->id,
                ]);
                $taskCount++;

                // Add 0-3 comments on some tasks
                if (fake()->boolean(40)) {
                    $commentNum = fake()->numberBetween(1, 3);
                    for ($c = 0; $c < $commentNum; $c++) {
                        TaskComment::create([
                            'partner_task_id' => $task->id,
                            'user_id' => $users->random()->id,
                            'body' => fake()->randomElement([
                                'Checked in with partner — making progress on this.',
                                'Waiting on partner to provide documentation.',
                                'Completed the initial setup, moving to validation phase.',
                                'Partner confirmed this is on track for next week.',
                                'Escalated to manager for review.',
                                'Updated the due date per partner request.',
                                'Partner team has capacity constraints — adjusting timeline.',
                                'Linked to the QBR action item.',
                            ]),
                        ]);
                        $commentCount++;
                    }
                }
            }
        }

        $this->command->info("Created {$taskCount} tasks, {$commentCount} comments.");
    }
}
