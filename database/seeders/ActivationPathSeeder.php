<?php

namespace Database\Seeders;

use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Models\StepProgress;
use Illuminate\Database\Seeder;
use Laravel\Pennant\Feature;

class ActivationPathSeeder extends Seeder
{
    /**
     * Paired activation paths with realistic steps and varied progress.
     */
    public function run(): void
    {
        $paths = $this->pathDefinitions();

        foreach ($paths as $def) {
            $path = ActivationPath::updateOrCreate(
                ['feature_slug' => $def['feature_slug']],
                [
                    'name' => $def['name'],
                    'description' => $def['description'],
                    'is_active' => true,
                ],
            );

            foreach ($def['steps'] as $i => $stepDef) {
                $step = ActivationStep::updateOrCreate(
                    ['activation_path_id' => $path->id, 'order' => $i + 1],
                    [
                        'name' => $stepDef['name'],
                        'description' => $stepDef['description'] ?? null,
                        'step_type' => $stepDef['type'],
                        'criteria' => ['target' => $stepDef['target']],
                        'is_required' => $stepDef['required'] ?? true,
                    ],
                );

                // Seed progress if specified
                if (isset($stepDef['progress'])) {
                    $current = $stepDef['progress'];
                    $target = $stepDef['target'];
                    $completed = $current >= $target;

                    StepProgress::updateOrCreate(
                        ['activation_step_id' => $step->id],
                        [
                            'current_value' => $current,
                            'target_value' => $target,
                            'completed' => $completed,
                            'completed_at' => $completed ? now()->subDays(rand(1, 30)) : null,
                        ],
                    );
                }
            }

            // If path is marked as already unlocked, activate in Pennant
            if ($def['unlocked'] ?? false) {
                Feature::for(null)->activate($def['feature_slug']);
            }

            $this->command->info("  {$def['name']} — {$path->steps()->count()} steps");
        }

        $this->command->info('Seeded '.count($paths).' activation paths.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pathDefinitions(): array
    {
        return [
            // ── Enablement (Admin) ── fully unlocked
            [
                'feature_slug' => 'enablement_admin',
                'name' => 'Enablement Administration',
                'description' => 'Steps to unlock admin setup for enablement events, blueprints, labs, and calendars.',
                'unlocked' => true,
                'steps' => [
                    ['name' => 'Import partner contact list', 'type' => 'data_populated', 'target' => 1, 'description' => 'Upload at least one batch of partner contacts.', 'progress' => 1],
                    ['name' => 'Create first blueprint', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Publish at least one enablement blueprint.', 'progress' => 1],
                    ['name' => 'Configure enablement calendar', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up the enablement calendar with at least one event.', 'progress' => 1],
                    ['name' => 'Complete admin training module', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Enablement Administration training.', 'progress' => 1],
                ],
            ],

            // ── Enablement (End User) ── 75% done
            [
                'feature_slug' => 'enablement_user',
                'name' => 'Field Enablement End Users',
                'description' => 'Steps to unlock end-user access to field enablement, influence maps, and depth/breadth views.',
                'steps' => [
                    ['name' => 'Enablement admin path unlocked', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Enablement Administration must be activated first.', 'progress' => 1],
                    ['name' => 'Assign blueprints to 3 partners', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Assign enablement blueprints to at least 3 partners.', 'progress' => 3],
                    ['name' => 'Schedule 5 enablement events', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Create at least 5 enablement events on the calendar.', 'progress' => 4],
                    ['name' => '10 partner contacts registered', 'type' => 'metric_threshold', 'target' => 10, 'description' => 'At least 10 partner contacts registered for events.', 'progress' => 6],
                ],
            ],

            // ── Deal Registration (Admin) ── 50% done
            [
                'feature_slug' => 'deal_registration_admin',
                'name' => 'Deal Registration Administration',
                'description' => 'Steps to unlock admin setup for deal registration workflows and lead assignment.',
                'steps' => [
                    ['name' => 'Configure Salesforce credentials', 'type' => 'data_populated', 'target' => 1, 'description' => 'Connect Salesforce for opportunity sync.', 'progress' => 1],
                    ['name' => 'Import opportunity data', 'type' => 'data_populated', 'target' => 1, 'description' => 'Run initial opportunity data import.', 'progress' => 1],
                    ['name' => 'Set up lead assignment rules', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Configure at least one lead assignment rule.'],
                    ['name' => 'Complete deal registration training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Deal Registration admin training module.'],
                ],
            ],

            // ── Deal Registration (End User) ── not started
            [
                'feature_slug' => 'deal_registration_user',
                'name' => 'Deal Registration End User',
                'description' => 'Steps to unlock end-user access to submit and track deal registrations.',
                'steps' => [
                    ['name' => 'Deal Registration admin path unlocked', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Deal Registration Administration must be activated first.'],
                    ['name' => 'Process 5 lead assignments', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Assign at least 5 leads through the assignment workflow.'],
                    ['name' => 'Submit first deal registration', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Admin submits a sample deal registration to verify the workflow.'],
                ],
            ],

            // ── Launch Programs (Admin) ── fully unlocked
            [
                'feature_slug' => 'launch_programs_admin',
                'name' => 'Launch Program Administration',
                'description' => 'Steps to unlock admin setup for launch programs, milestones, and partner nominations.',
                'unlocked' => true,
                'steps' => [
                    ['name' => 'Create first launch program', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Define at least one launch program with milestones.', 'progress' => 1],
                    ['name' => 'Add program milestones', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Define at least 3 milestones across your programs.', 'progress' => 5],
                    ['name' => 'Nominate first partner', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Nominate at least one partner for a launch program.', 'progress' => 1],
                ],
            ],

            // ── Launch Programs (End User) ── 66% done
            [
                'feature_slug' => 'launch_programs_user',
                'name' => 'Launch Program End User',
                'description' => 'Steps to unlock end-user access to view and participate in launch programs.',
                'steps' => [
                    ['name' => 'Launch Programs admin path unlocked', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Launch Program Administration must be activated first.', 'progress' => 1],
                    ['name' => 'Nominate 3 partners', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Nominate at least 3 partners for launch programs.', 'progress' => 3],
                    ['name' => 'Add participant contacts', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Add at least 5 participant contacts across nominated partners.', 'progress' => 2],
                ],
            ],

            // ── Project Health (Admin) ── 25% done
            [
                'feature_slug' => 'project_health_admin',
                'name' => 'Project Health Administration',
                'description' => 'Steps to unlock admin setup for project tracking, risk categories, and delivery metrics.',
                'steps' => [
                    ['name' => 'Import project data', 'type' => 'data_populated', 'target' => 1, 'description' => 'Import or create initial project records.', 'progress' => 1],
                    ['name' => 'Define risk categories', 'type' => 'feature_usage', 'target' => 3, 'description' => 'Configure at least 3 project risk categories.'],
                    ['name' => 'Set delivery metrics baseline', 'type' => 'data_populated', 'target' => 1, 'description' => 'Establish baseline delivery metrics for at least one partner.'],
                    ['name' => 'Complete project health training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Project Health admin training.'],
                ],
            ],

            // ── Project Health (End User) ── not started
            [
                'feature_slug' => 'project_health_user',
                'name' => 'Project Health End User',
                'description' => 'Steps to unlock end-user access to project health dashboards and risk tracking.',
                'steps' => [
                    ['name' => 'Project Health admin path unlocked', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Project Health Administration must be activated first.'],
                    ['name' => 'Track 10 active projects', 'type' => 'metric_threshold', 'target' => 10, 'description' => 'Have at least 10 projects in active tracking.'],
                    ['name' => 'Log first risk entry', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Log at least one risk against a project.'],
                    ['name' => 'Generate first delivery report', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Run delivery metrics report for at least one partner.', 'required' => false],
                ],
            ],

            // ── QBR Workbench (Admin) ── 75% done
            [
                'feature_slug' => 'qbr_admin',
                'name' => 'QBR Workbench Administration',
                'description' => 'Steps to unlock admin setup for QBR templates, session configuration, and review workflows.',
                'steps' => [
                    ['name' => 'Configure scorecard weights', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up scorecard category weights for QBR scoring.', 'progress' => 1],
                    ['name' => 'Define partner paths', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Create at least one partner path for QBR context.', 'progress' => 1],
                    ['name' => 'Create first QBR session', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Create a sample QBR session to verify the workflow.', 'progress' => 1],
                    ['name' => 'Complete QBR admin training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the QBR Workbench admin training module.'],
                ],
            ],

            // ── QBR Workbench (End User) ── not started
            [
                'feature_slug' => 'qbr_user',
                'name' => 'QBR Workbench End User',
                'description' => 'Steps to unlock end-user access to conduct and complete quarterly business reviews.',
                'steps' => [
                    ['name' => 'QBR admin path unlocked', 'type' => 'feature_usage', 'target' => 1, 'description' => 'QBR Workbench Administration must be activated first.'],
                    ['name' => 'Complete 3 QBR sessions', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Complete at least 3 QBR sessions with partners.'],
                    ['name' => 'Generate QBR report', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Generate a completed QBR report for review.'],
                ],
            ],

            // ── Provisioning (Admin) ── fully unlocked
            [
                'feature_slug' => 'provisioning_admin',
                'name' => 'Provisioning Administration',
                'description' => 'Steps to unlock admin setup for workspace provisioning, version configuration, and partner assignment.',
                'unlocked' => true,
                'steps' => [
                    ['name' => 'Configure Anaplan credentials', 'type' => 'data_populated', 'target' => 1, 'description' => 'Connect to Anaplan for workspace sync.', 'progress' => 1],
                    ['name' => 'Sync workspaces', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Run initial workspace sync from Anaplan.', 'progress' => 1],
                    ['name' => 'Set version configuration', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Configure model version tracking preferences.', 'progress' => 1],
                    ['name' => 'Assign first workspace', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Assign at least one workspace to a partner.', 'progress' => 1],
                ],
            ],

            // ── Provisioning (End User) ── 33% done
            [
                'feature_slug' => 'provisioning_user',
                'name' => 'Provisioning End User',
                'description' => 'Steps to unlock end-user access to view provisioned workspaces and workspace status.',
                'steps' => [
                    ['name' => 'Provisioning admin path unlocked', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Provisioning Administration must be activated first.', 'progress' => 1],
                    ['name' => 'Assign workspaces to 5 partners', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Assign workspaces to at least 5 different partners.', 'progress' => 2],
                    ['name' => '30 days of usage data', 'type' => 'time_elapsed', 'target' => 30, 'description' => 'Allow 30 days for model activity data to accumulate.', 'progress' => 12],
                ],
            ],

            // ── Goals & Targets (Admin) ── 50% done
            [
                'feature_slug' => 'goals_targets_admin',
                'name' => 'Goals & Targets Administration',
                'description' => 'Steps to unlock admin setup for goal templates, target configuration, and assignment rules.',
                'steps' => [
                    ['name' => 'Create first goal template', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Create at least one goal template for partner assignments.', 'progress' => 1],
                    ['name' => 'Define 5 goal templates', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Define at least 5 goal templates across different categories.', 'progress' => 5],
                    ['name' => 'Configure scorecard weights', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up scorecard category weights for goal scoring.'],
                    ['name' => 'Complete goals admin training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Goals & Targets admin training module.'],
                ],
            ],

            // ── Goals & Targets (End User) ── not started
            [
                'feature_slug' => 'goals_targets_user',
                'name' => 'Goals & Targets End User',
                'description' => 'Steps to unlock end-user access to view, track, and manage partner goals and targets.',
                'steps' => [
                    ['name' => 'Goals & Targets admin path unlocked', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Goals & Targets Administration must be activated first.'],
                    ['name' => 'Assign goals to 5 partners', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Assign goals to at least 5 different partners.'],
                    ['name' => 'Record first actual value', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Record at least one actual value against an assigned goal.'],
                    ['name' => 'Complete goals user training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Goals & Targets end-user training module.', 'required' => false],
                ],
            ],

            // ── Benchmark (Admin) ── 75% done
            [
                'feature_slug' => 'benchmark_admin',
                'name' => 'Benchmark Administration',
                'description' => 'Steps to unlock admin setup for benchmark definitions, peer groups, and scoring criteria.',
                'steps' => [
                    ['name' => 'Configure scorecard weights', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up scorecard category weights used in benchmarking.', 'progress' => 1],
                    ['name' => 'Define peer groups', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Define at least 3 peer groups for partner comparison.', 'progress' => 3],
                    ['name' => 'Import benchmark baseline data', 'type' => 'data_populated', 'target' => 1, 'description' => 'Import or generate baseline benchmark data.', 'progress' => 1],
                    ['name' => 'Complete benchmark admin training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Benchmark Administration training module.'],
                ],
            ],

            // ── Benchmark (End User) ── not started
            [
                'feature_slug' => 'benchmark_user',
                'name' => 'Benchmark End User',
                'description' => 'Steps to unlock end-user access to partner benchmarking, peer comparisons, and ranking views.',
                'steps' => [
                    ['name' => 'Benchmark admin path unlocked', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Benchmark Administration must be activated first.'],
                    ['name' => 'Score 10 partners', 'type' => 'metric_threshold', 'target' => 10, 'description' => 'Have at least 10 partners with computed scorecard values.'],
                    ['name' => 'Generate first benchmark report', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Generate a benchmark comparison report.'],
                ],
            ],

            // ── Action Plans (End User) ── 66% done
            [
                'feature_slug' => 'action_plans_user',
                'name' => 'Action Plans End User',
                'description' => 'Steps to unlock end-user access to create, manage, and track partner action plans.',
                'steps' => [
                    ['name' => 'Create first action plan', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Create at least one action plan for a partner.', 'progress' => 1],
                    ['name' => 'Add items to 3 action plans', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Add action items to at least 3 different plans.', 'progress' => 3],
                    ['name' => 'Complete 5 action items', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Mark at least 5 action items as complete across all plans.', 'progress' => 2],
                ],
            ],
        ];
    }
}
