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

            // If path is marked as already unlocked, activate in Pennant
            // and mark the primary user as completed
            if ($def['unlocked'] ?? false) {
                Feature::for(null)->activate($def['feature_slug']);

                if ($primaryUser && $assignees->contains('id', $primaryUser->id)) {
                    $path->users()->updateExistingPivot($primaryUser->id, [
                        'completed_at' => now(),
                    ]);
                }

                $this->command->info("  {$def['name']} — {$path->steps()->count()} steps, {$assignees->count()} users assigned ✓ ACTIVATED");
            } else {
                Feature::for(null)->deactivate($def['feature_slug']);
                $this->command->info("  {$def['name']} — {$path->steps()->count()} steps, {$assignees->count()} users assigned");
            }
        }

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
        if (in_array($slug, ['strategy', 'feature_activation_coe'])) {
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

            // ── Enablement ── not started
            [
                'feature_slug' => 'enablement',
                'name' => 'Enablement',
                'description' => 'Steps to unlock enablement events, blueprints, labs, calendars, and field enablement.',
                'steps' => [
                    ['name' => 'Complete enablement training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Enablement training module.', 'phase' => 'tell'],
                    ['name' => 'Import partner contact list', 'type' => 'data_populated', 'target' => 1, 'description' => 'Upload at least one batch of partner contacts.', 'phase' => 'show'],
                    ['name' => 'Create first blueprint', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Publish at least one enablement blueprint.', 'phase' => 'show'],
                    ['name' => 'Schedule 5 enablement events', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Create at least 5 enablement events on the calendar.', 'phase' => 'do'],
                    ['name' => '10 partner contacts registered', 'type' => 'metric_threshold', 'target' => 10, 'description' => 'At least 10 partner contacts registered for events.', 'phase' => 'do'],
                ],
            ],

            // ── Deal Registration ── not started
            [
                'feature_slug' => 'deal_registration',
                'name' => 'Deal Registration',
                'description' => 'Steps to unlock deal registration workflows, lead assignment, and deal tracking.',
                'steps' => [
                    ['name' => 'Complete deal registration training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Deal Registration training module.', 'phase' => 'tell'],
                    ['name' => 'Import opportunity data', 'type' => 'data_populated', 'target' => 1, 'description' => 'Run initial opportunity data import.', 'phase' => 'show'],
                    ['name' => 'Process 5 lead assignments', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Assign at least 5 leads through the assignment workflow.', 'phase' => 'show'],
                    ['name' => 'Submit first deal registration', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Submit a sample deal registration to verify the workflow.', 'phase' => 'do'],
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

            // ── Project Health ── not started
            [
                'feature_slug' => 'project_health',
                'name' => 'Project Health',
                'description' => 'Steps to unlock project tracking, risk categories, delivery metrics, and health dashboards.',
                'steps' => [
                    ['name' => 'Complete project health training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Project Health training module.', 'phase' => 'tell'],
                    ['name' => 'Import project data', 'type' => 'data_populated', 'target' => 1, 'description' => 'Import or create initial project records.', 'phase' => 'show'],
                    ['name' => 'Track 10 active projects', 'type' => 'metric_threshold', 'target' => 10, 'description' => 'Have at least 10 projects in active tracking.', 'phase' => 'show'],
                    ['name' => 'Log first risk entry', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Log at least one risk against a project.', 'phase' => 'do'],
                ],
            ],

            // ── QBR Workbench ── not started
            [
                'feature_slug' => 'qbr',
                'name' => 'QBR Workbench',
                'description' => 'Steps to unlock QBR templates, session configuration, and quarterly business reviews.',
                'steps' => [
                    ['name' => 'Complete QBR training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the QBR Workbench training module.', 'phase' => 'tell'],
                    ['name' => 'Configure scorecard weights', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up scorecard category weights for QBR scoring.', 'phase' => 'show'],
                    ['name' => 'Create first QBR session', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Create a sample QBR session to verify the workflow.', 'phase' => 'show'],
                    ['name' => 'Complete 3 QBR sessions', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Complete at least 3 QBR sessions with partners.', 'phase' => 'do'],
                ],
            ],

            // ── Provisioning ── not started
            [
                'feature_slug' => 'provisioning',
                'name' => 'Provisioning',
                'description' => 'Steps to unlock workspace provisioning, version configuration, and partner assignment.',
                'steps' => [
                    ['name' => 'Configure Anaplan credentials', 'type' => 'data_populated', 'target' => 1, 'description' => 'Connect to Anaplan for workspace sync.', 'phase' => 'tell'],
                    ['name' => 'Sync workspaces', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Run initial workspace sync from Anaplan.', 'phase' => 'show'],
                    ['name' => 'Assign workspaces to 5 partners', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Assign workspaces to at least 5 different partners.', 'phase' => 'show'],
                    ['name' => '30 days of usage data', 'type' => 'time_elapsed', 'target' => 30, 'description' => 'Allow 30 days for model activity data to accumulate.', 'phase' => 'do'],
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

            // ── Benchmarking ── not started
            [
                'feature_slug' => 'benchmark',
                'name' => 'Benchmarking',
                'description' => 'Steps to unlock benchmark definitions, peer groups, scoring criteria, and comparisons.',
                'steps' => [
                    ['name' => 'Complete benchmark training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Benchmarking training module.', 'phase' => 'tell'],
                    ['name' => 'Configure scorecard weights', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up scorecard category weights used in benchmarking.', 'phase' => 'show'],
                    ['name' => 'Score 10 partners', 'type' => 'metric_threshold', 'target' => 10, 'description' => 'Have at least 10 partners with computed scorecard values.', 'phase' => 'show'],
                    ['name' => 'Generate first benchmark report', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Generate a benchmark comparison report.', 'phase' => 'do'],
                ],
            ],

            // ── Action Plans ── not started
            [
                'feature_slug' => 'action_plans',
                'name' => 'Action Plans',
                'description' => 'Steps to unlock action plan templates, categories, assignment rules, and task board.',
                'steps' => [
                    ['name' => 'Complete action plans training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Action Plans training module.', 'phase' => 'tell'],
                    ['name' => 'Create action plan template', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Create at least one action plan template.', 'phase' => 'show'],
                    ['name' => 'Add items to 3 action plans', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Add action items to at least 3 different plans.', 'phase' => 'show'],
                    ['name' => 'Complete 5 action items', 'type' => 'usage_threshold', 'target' => 5, 'description' => 'Mark at least 5 action items as complete across all plans.', 'phase' => 'do'],
                ],
            ],

            // ── Partner Portal ── not started
            [
                'feature_slug' => 'partner_portal',
                'name' => 'Partner Portal',
                'description' => 'Steps to unlock partner portal configuration, branding, access policies, and user management.',
                'steps' => [
                    ['name' => 'Complete portal training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Partner Portal training module.', 'phase' => 'tell'],
                    ['name' => 'Configure portal branding', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up portal branding, logo, and theme colors.', 'phase' => 'show'],
                    ['name' => 'Onboard 3 portal contacts', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Create at least 3 portal contacts with access.', 'phase' => 'show'],
                    ['name' => 'Verify portal login flow', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Confirm at least one partner contact can log into the portal.', 'phase' => 'do'],
                ],
            ],

            // ── Partner Portal (Partner Side) ── not started
            [
                'feature_slug' => 'partner_portal_partner',
                'name' => 'Partner Portal (Partner Side)',
                'description' => 'Steps to unlock partner-side portal access, team settings, and portal experience.',
                'steps' => [
                    ['name' => 'Complete partner admin training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the partner-side admin training module.', 'phase' => 'tell'],
                    ['name' => 'Configure team settings', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up team structure and role assignments.', 'phase' => 'show'],
                    ['name' => 'Invite team members', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Invite at least 3 team members to the portal.', 'phase' => 'show'],
                    ['name' => 'Access 3 portal sections', 'type' => 'usage_threshold', 'target' => 3, 'description' => 'Navigate and use at least 3 portal sections.', 'phase' => 'do'],
                ],
            ],

            // ── Analytics Suite ── not started
            [
                'feature_slug' => 'analytics',
                'name' => 'Analytics Suite',
                'description' => 'Steps to unlock portfolio analytics, revenue analysis, benchmarking, and custom filters.',
                'steps' => [
                    ['name' => 'Complete analytics overview training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Analytics Suite overview training.', 'phase' => 'tell'],
                    ['name' => 'Import revenue data', 'type' => 'data_populated', 'target' => 1, 'description' => 'Import or populate revenue data for at least one partner.', 'phase' => 'show'],
                    ['name' => 'Create first custom filter', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Save a custom filter for portfolio analysis.', 'phase' => 'show'],
                    ['name' => 'Generate portfolio report', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Run a portfolio analytics report.', 'phase' => 'do'],
                ],
            ],

            // ── Competency Module ── not started
            [
                'feature_slug' => 'competency',
                'name' => 'Competency Module',
                'description' => 'Steps to unlock competency matrix, scorecard, goals, partner paths, and action plans.',
                'steps' => [
                    ['name' => 'Complete competency training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Competency Module training.', 'phase' => 'tell'],
                    ['name' => 'Configure competency matrix', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up the competency matrix with dimensions and levels.', 'phase' => 'show'],
                    ['name' => 'Define partner paths', 'type' => 'usage_threshold', 'target' => 2, 'description' => 'Create at least 2 partner paths.', 'phase' => 'show'],
                    ['name' => 'Score first partner', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Complete a competency assessment for at least one partner.', 'phase' => 'do'],
                ],
            ],

            // ── Integration Hub ── not started
            [
                'feature_slug' => 'integration_hub',
                'name' => 'Integration Hub',
                'description' => 'Steps to unlock data ingestion, export, field mapping, and external connections.',
                'steps' => [
                    ['name' => 'Complete integration hub training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Integration Hub training module.', 'phase' => 'tell'],
                    ['name' => 'Create first connection', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up at least one external connection.', 'phase' => 'show'],
                    ['name' => 'Configure field mappings', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Define field mappings for an integration.', 'phase' => 'show'],
                    ['name' => 'Run first import job', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Execute a successful import job.', 'phase' => 'do'],
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

            // ── Strategy Workbench ── not started
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

            // ── MCP Server ── not started
            [
                'feature_slug' => 'mcp_server',
                'name' => 'MCP Server',
                'description' => 'Steps to unlock Model Context Protocol server for LLM access to partner data.',
                'steps' => [
                    ['name' => 'Review MCP security documentation', 'type' => 'training_completed', 'target' => 1, 'description' => 'Review the MCP security and access control documentation.', 'phase' => 'tell'],
                    ['name' => 'Configure MCP access policies', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up data access policies for MCP consumers.', 'phase' => 'show'],
                    ['name' => 'Validate first MCP query', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Run and validate a test query through the MCP interface.', 'phase' => 'do'],
                ],
            ],

            // ── AI Agents ── not started
            [
                'feature_slug' => 'ai_agents',
                'name' => 'AI Agents',
                'description' => 'Steps to unlock AI-powered agents for partner insights, enablement design, and lab generation.',
                'steps' => [
                    ['name' => 'Complete AI overview training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the AI Agents overview training.', 'phase' => 'tell'],
                    ['name' => 'Configure AI provider settings', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Set up and verify the AI provider configuration in system settings.', 'phase' => 'show'],
                    ['name' => 'Generate AI insights for a partner', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Use the Partner Advisor agent to generate insights for a partner.', 'phase' => 'show'],
                    ['name' => 'Generate enablement content with AI', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Use an AI agent to generate an enablement event or lab.', 'phase' => 'do'],
                ],
            ],

            // ── Enablement Events ── not started
            [
                'feature_slug' => 'enablement_events',
                'name' => 'Enablement Events',
                'description' => 'Steps to unlock enablement event catalog, sessions, registrations, and calendar.',
                'steps' => [
                    ['name' => 'Complete events training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Enablement Events training module.', 'phase' => 'tell'],
                    ['name' => 'Create an enablement event', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Create at least one enablement event in the catalog.', 'phase' => 'show'],
                    ['name' => 'Publish event with sessions', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Publish an event and schedule at least one session.', 'phase' => 'show'],
                    ['name' => 'Register for an event', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Register at least one participant for an enablement session.', 'phase' => 'do'],
                ],
            ],

            // ── Workshop Labs ── not started
            [
                'feature_slug' => 'workshop_labs',
                'name' => 'Workshop Labs',
                'description' => 'Steps to unlock workshop labs, checkpoints, participants, and exercises.',
                'steps' => [
                    ['name' => 'Complete labs training', 'type' => 'training_completed', 'target' => 1, 'description' => 'Finish the Workshop Labs training module.', 'phase' => 'tell'],
                    ['name' => 'Create a workshop lab', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Create at least one workshop lab with a golden model.', 'phase' => 'show'],
                    ['name' => 'Publish lab with checkpoints', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Publish a lab with at least one checkpoint defined.', 'phase' => 'show'],
                    ['name' => 'Complete a lab checkpoint', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Complete at least one checkpoint in a workshop lab.', 'phase' => 'do'],
                ],
            ],

            // ── Feature Activation (CoE) ── not started
            [
                'feature_slug' => 'feature_activation_coe',
                'name' => 'Feature Activation (CoE)',
                'description' => 'Training path for the CoE role to understand and manage feature activation across the platform.',
                'steps' => [
                    ['name' => 'Watch Feature Activation overview', 'type' => 'training_completed', 'target' => 1, 'description' => 'Watch the video overview of how feature flags and activation paths work.', 'phase' => 'tell'],
                    ['name' => 'Review the progression dashboard', 'type' => 'training_completed', 'target' => 1, 'description' => 'Explore the progression dashboard and understand path completion tracking.', 'phase' => 'tell'],
                    ['name' => 'Toggle a feature flag on and off', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Practice activating and deactivating a feature flag in the flags panel.', 'phase' => 'show'],
                    ['name' => 'Complete a step in an activation path', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Mark at least one step as complete in any activation path.', 'phase' => 'show'],
                    ['name' => 'Activate a feature through its full path', 'type' => 'feature_usage', 'target' => 1, 'description' => 'Complete all required steps in one activation path to activate its feature.', 'phase' => 'do'],
                ],
            ],
        ];
    }
}
