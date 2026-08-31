<?php

namespace Database\Seeders;

use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Models\StepProgress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Laravel\Pennant\Feature;

class ActivationPathSeeder extends Seeder
{
    /**
     * Seed activation paths for all modules.
     *
     * All features start deactivated. Only paths marked 'unlocked'
     * will have their Pennant flag activated after seeding.
     */
    public function run(): void
    {
        // Purge all existing Pennant feature values so every flag starts inactive
        Feature::purge();

        // Load users by role for auto-assignment
        $admins = User::whereRelation('role', 'slug', 'admin')->get();
        $psms = User::whereRelation('role', 'slug', 'psm')->get();
        $coes = User::whereRelation('role', 'slug', 'coe')->get();
        $primaryUser = User::where('email', 'gunnar.stoa@gmail.com')->first();

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

            $steps = [];
            foreach ($def['steps'] as $i => $stepDef) {
                $steps[] = ActivationStep::updateOrCreate(
                    ['activation_path_id' => $path->id, 'order' => $i + 1],
                    [
                        'name' => $stepDef['name'],
                        'description' => $stepDef['description'] ?? null,
                        'step_type' => $stepDef['type'],
                        'criteria' => ['target' => $stepDef['target']],
                        'is_required' => $stepDef['required'] ?? true,
                        'phase' => $stepDef['phase'] ?? null,
                    ],
                );
            }

            // Auto-assign users based on slug pattern
            $assignees = $this->resolveAssignees($def['feature_slug'], $admins, $psms, $coes);
            $assignedBy = $admins->first();

            foreach ($assignees as $user) {
                $path->users()->syncWithoutDetaching([
                    $user->id => [
                        'assigned_by' => $assignedBy?->id,
                        'assigned_at' => now(),
                    ],
                ]);
            }

            // Seed per-user progress for steps that specify it
            foreach ($def['steps'] as $i => $stepDef) {
                if (! isset($stepDef['progress'])) {
                    continue;
                }

                $step = $steps[$i];
                $current = $stepDef['progress'];
                $target = $stepDef['target'];
                $completed = $current >= $target;

                // Seed progress for all assigned users
                foreach ($assignees as $user) {
                    StepProgress::updateOrCreate(
                        ['activation_step_id' => $step->id, 'user_id' => $user->id],
                        [
                            'current_value' => $current,
                            'target_value' => $target,
                            'completed' => $completed,
                            'completed_at' => $completed ? now()->subDays(rand(1, 30)) : null,
                        ],
                    );
                }
            }

            // Activate all features in Pennant — bypass_permissions users see
            // everything regardless, but non-bypass users need flags active.
            Feature::for(null)->activate($def['feature_slug']);

            // If path is marked as already unlocked, mark the primary user as completed
            if ($def['unlocked'] ?? false) {
                if ($primaryUser && $assignees->contains('id', $primaryUser->id)) {
                    $path->users()->updateExistingPivot($primaryUser->id, [
                        'completed_at' => now(),
                    ]);
                }

                $this->command->info("  {$def['name']} — {$path->steps()->count()} steps, {$assignees->count()} users assigned ✓ UNLOCKED");
            } else {
                $this->command->info("  {$def['name']} — {$path->steps()->count()} steps, {$assignees->count()} users assigned ✓ ACTIVATED");
            }
        }

        // Workshops has no activation path of its own (it's a standalone
        // feature, not part of the guided-training rollout), so the purge
        // above wipes the flag WorkshopSeeder already turned on. Re-activate
        // it here since this seeder runs last and is the final word on
        // Pennant state.
        Feature::for(null)->activate('workshops');

        $this->command->info('Seeded '.count($paths).' activation paths with per-user assignments.');
    }

    /**
     * Determine which users should be auto-assigned to a path based on its slug.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function resolveAssignees(
        string $slug,
        \Illuminate\Support\Collection $admins,
        \Illuminate\Support\Collection $psms,
        \Illuminate\Support\Collection $coes,
    ): \Illuminate\Support\Collection {
        // CoE-specific paths
        if (in_array($slug, ['strategy', 'system_administration'])) {
            return $admins->merge($coes)->unique('id');
        }

        // All unified paths assign to admins + psms + coes
        return $admins->merge($psms)->merge($coes)->unique('id');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pathDefinitions(): array
    {
        return [
            // ── Partner Management ── fully completed
            [
                'feature_slug' => 'partner_management',
                'name' => 'Partner Management',
                'description' => 'Steps to unlock partner management, assignments, dashboard, and partner details.',
                'unlocked' => true,
                'steps' => [
                    ['name' => 'Complete partner management training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Partner Management training module.', 'progress' => 1, 'phase' => 'tell'],
                    ['name' => 'Import or create partner data', 'type' => 'data_populated', 'target' => 1, 'description' => 'Import or manually create at least one partner record.', 'progress' => 1, 'phase' => 'show'],
                    ['name' => 'View 3 partner profiles', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Open at least 3 partner detail pages.', 'progress' => 3, 'phase' => 'show'],
                    ['name' => 'Assign partners to PSMs', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Assign at least 3 partners to PSM users.', 'progress' => 3, 'phase' => 'do'],
                ],
            ],

            // ── Competency ── not started
            [
                'feature_slug' => 'competency',
                'name' => 'Competency',
                'description' => 'Steps to unlock competency matrix, scorecard, partner paths, and path reviews.',
                'steps' => [
                    ['name' => 'Complete competency training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Competency training module.', 'phase' => 'tell'],
                    ['name' => 'Configure competency matrix', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up the competency matrix with dimensions and levels.', 'phase' => 'show'],
                    ['name' => 'Define partner paths', 'type' => 'usage_threshold', 'target' => 2, 'description' => 'Create at least 2 partner paths.', 'phase' => 'show'],
                    ['name' => 'Score first partner', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Complete a competency assessment for at least one partner.', 'phase' => 'do'],
                ],
            ],

            // ── Enablement (absorbs enablement_events) ── not started
            [
                'feature_slug' => 'enablement',
                'name' => 'Enablement',
                'description' => 'Steps to unlock enablement calendar, field enablement, events, and administration.',
                'steps' => [
                    ['name' => 'Complete enablement training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Enablement training module.', 'phase' => 'tell'],
                    ['name' => 'Import partner contact list', 'type' => 'data_populated', 'target' => 1, 'description' => 'Upload at least one batch of partner contacts.', 'phase' => 'show'],
                    ['name' => 'Create an enablement event', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Create at least one enablement event in the catalog.', 'phase' => 'show'],
                    ['name' => 'Schedule 5 enablement events', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Create at least 5 enablement events on the calendar.', 'phase' => 'do'],
                    ['name' => '10 partner contacts registered', 'type' => 'metric_threshold', 'target' => 10, 'description' => 'At least 10 partner contacts registered for events.', 'phase' => 'do'],
                ],
            ],

            // ── Launch Programs ── not started
            [
                'feature_slug' => 'launch_programs',
                'name' => 'Launch Programs',
                'description' => 'Steps to unlock launch programs, milestones, partner nominations, and participation.',
                'steps' => [
                    ['name' => 'Create first launch program', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Define at least one launch program with milestones.', 'phase' => 'tell'],
                    ['name' => 'Add program milestones', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Define at least 3 milestones across your programs.', 'phase' => 'show'],
                    ['name' => 'Nominate 3 partners', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Nominate at least 3 partners for launch programs.', 'phase' => 'show'],
                    ['name' => 'Add participant contacts', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Add at least 5 participant contacts across nominated partners.', 'phase' => 'do'],
                ],
            ],

            // ── Deal Registration ── not started
            [
                'feature_slug' => 'deal_registration',
                'name' => 'Deal Registration',
                'description' => 'Steps to unlock deal registration, lead assignment, business development, and marketplace.',
                'steps' => [
                    ['name' => 'Complete deal registration training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Deal Registration training module.', 'phase' => 'tell'],
                    ['name' => 'Import opportunity data', 'type' => 'data_populated', 'target' => 1, 'description' => 'Run initial opportunity data import.', 'phase' => 'show'],
                    ['name' => 'Process 5 lead assignments', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Assign at least 5 leads through the assignment workflow.', 'phase' => 'show'],
                    ['name' => 'Submit first deal registration', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Submit a sample deal registration to verify the workflow.', 'phase' => 'do'],
                ],
            ],

            // ── Goals & Targets ── not started
            [
                'feature_slug' => 'goals_targets',
                'name' => 'Goals & Targets',
                'description' => 'Steps to unlock goal templates, target configuration, assignment rules, and progress tracking.',
                'steps' => [
                    ['name' => 'Complete goals training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Goals & Targets training module.', 'phase' => 'tell'],
                    ['name' => 'Create first goal template', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Create at least one goal template for partner assignments.', 'phase' => 'show'],
                    ['name' => 'Assign goals to 5 partners', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Assign goals to at least 5 different partners.', 'phase' => 'show'],
                    ['name' => 'Record first actual value', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Record at least one actual value against an assigned goal.', 'phase' => 'do'],
                ],
            ],

            // ── Measurement (absorbs benchmark + qbr + health) ── not started
            [
                'feature_slug' => 'measurement',
                'name' => 'Measurement',
                'description' => 'Steps to unlock analytics dashboards, benchmarks, partner health, QBR workbench, and alerts.',
                'steps' => [
                    ['name' => 'Complete measurement training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Measurement training module.', 'phase' => 'tell'],
                    ['name' => 'Configure scorecard weights', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up scorecard category weights for benchmarking and QBR.', 'phase' => 'show'],
                    ['name' => 'Score 10 partners', 'type' => 'metric_threshold', 'target' => 10, 'description' => 'Have at least 10 partners with computed scorecard values.', 'phase' => 'show'],
                    ['name' => 'Create first QBR session', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Create a sample QBR session to verify the workflow.', 'phase' => 'do'],
                ],
            ],

            // ── Strategy ── not started
            [
                'feature_slug' => 'strategy',
                'name' => 'Strategy Workbench',
                'description' => 'Steps to unlock strategic planning, scenario modeling, and program governance for the CoE.',
                'steps' => [
                    ['name' => 'Complete strategy training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Strategy Workbench training module.', 'phase' => 'tell'],
                    ['name' => 'Create first strategy plan', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Build a strategy plan with initiatives and targets.', 'phase' => 'show'],
                    ['name' => 'Model a scenario', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Create a what-if scenario with overrides.', 'phase' => 'show'],
                    ['name' => 'Submit plan for review', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Submit a strategy plan for stakeholder review.', 'phase' => 'do'],
                ],
            ],

            // ── AI & Knowledge (absorbs ai_agents + knowledge_base) ── not started
            [
                'feature_slug' => 'ai_knowledge',
                'name' => 'AI & Knowledge',
                'description' => 'Steps to unlock AI agents, knowledge base, and MCP integrations.',
                'steps' => [
                    ['name' => 'Complete AI overview training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the AI & Knowledge overview training.', 'phase' => 'tell'],
                    ['name' => 'Configure AI provider settings', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up and verify the AI provider configuration.', 'phase' => 'show'],
                    ['name' => 'Generate AI insights for a partner', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Use the Partner Advisor agent to generate insights.', 'phase' => 'show'],
                    ['name' => 'Ingest first knowledge document', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Upload and index a document in the knowledge base.', 'phase' => 'do'],
                ],
            ],

            // ── Idea Room ── not started
            [
                'feature_slug' => 'idea_room',
                'name' => 'Idea Room',
                'description' => 'Steps to unlock AI-powered idea management with gallery wall, chat rooms, and constellation graph.',
                'steps' => [
                    ['name' => 'Complete Idea Room orientation', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Idea Room orientation video.', 'phase' => 'tell'],
                    ['name' => 'Submit first idea', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Submit at least one idea via the capture interface.', 'phase' => 'show'],
                    ['name' => 'Explore constellation graph', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Navigate the constellation graph to explore idea connections.', 'phase' => 'do'],
                ],
            ],

            // ── Integration Hub (absorbs mcp_server) ── not started
            [
                'feature_slug' => 'integration_hub',
                'name' => 'Integration Hub',
                'description' => 'Steps to unlock data connectors, import/export, field mappings, MCP server, and API tokens.',
                'steps' => [
                    ['name' => 'Complete integration hub training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Integration Hub training module.', 'phase' => 'tell'],
                    ['name' => 'Create first connection', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up at least one external connection.', 'phase' => 'show'],
                    ['name' => 'Configure field mappings', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Define field mappings for an integration.', 'phase' => 'show'],
                    ['name' => 'Run first import job', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Execute a successful import job.', 'phase' => 'do'],
                ],
            ],

            // ── Portal (absorbs partner_portal + partner_portal_partner) ── not started
            [
                'feature_slug' => 'portal',
                'name' => 'Portal',
                'description' => 'Steps to unlock portal management, branding, user access, and partner-side experience.',
                'steps' => [
                    ['name' => 'Complete portal training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Portal training module.', 'phase' => 'tell'],
                    ['name' => 'Configure portal branding', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up portal branding, logo, and theme colors.', 'phase' => 'show'],
                    ['name' => 'Onboard 3 portal contacts', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Create at least 3 portal contacts with access.', 'phase' => 'show'],
                    ['name' => 'Verify portal login flow', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Confirm at least one partner contact can log into the portal.', 'phase' => 'do'],
                ],
            ],

            // ── System Administration ── not started
            [
                'feature_slug' => 'system_administration',
                'name' => 'System Administration',
                'description' => 'Steps to unlock admin dashboard, configuration, system status, roles, and feature activation.',
                'steps' => [
                    ['name' => 'Watch system administration overview', 'type' => 'training_completed', 'target' => 1, 'description' => 'Watch the video overview of system administration features.', 'phase' => 'tell'],
                    ['name' => 'Review the admin dashboard', 'type' => 'training_completed', 'target' => 1, 'description' => 'Explore the admin dashboard and understand system status.', 'phase' => 'tell'],
                    ['name' => 'Configure a system setting', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Update at least one system configuration value.', 'phase' => 'show'],
                    ['name' => 'Toggle a feature flag', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Practice activating and deactivating a feature flag.', 'phase' => 'do'],
                ],
            ],
        ];
    }
}
