<?php

namespace Database\Seeders;

use App\Models\ResourcePath;
use App\Models\ResourcePathGate;
use App\Models\ResourcePathStage;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResourcePathSeeder extends Seeder
{
    /**
     * Resource path definitions with stages and gate requirements.
     *
     * Each path represents a distinct resource role with tailored progression.
     *
     * @var array<string, array{description: string, stages: list<array>, gates: array<string, list<array>>}>
     */
    private const PATHS = [
        'Model Builder' => [
            'description' => 'Progression path for partner resources building and maintaining Anaplan models. Covers core platform skills through advanced modeling expertise.',
            'is_default' => true,
            'stages' => [
                ['number' => 1, 'name' => 'Registered', 'description' => 'Resource registered in Partner Central, profile captured, assigned to enablement track.', 'color' => '#6C757D', 'icon' => 'fal fa-id-card'],
                ['number' => 2, 'name' => 'Trained', 'description' => 'Core Anaplan platform training completed, familiar with model-building fundamentals.', 'color' => '#0D6EFD', 'icon' => 'fal fa-graduation-cap'],
                ['number' => 3, 'name' => 'Certified', 'description' => 'Model Builder certification obtained, ready for supervised project work.', 'color' => '#198754', 'icon' => 'fal fa-certificate'],
                ['number' => 4, 'name' => 'Delivering', 'description' => 'Actively building models on client projects with demonstrated quality.', 'color' => '#FFC107', 'icon' => 'fal fa-project-diagram'],
                ['number' => 5, 'name' => 'Expert', 'description' => 'Senior model builder — mentoring others, complex model design, thought leadership.', 'color' => '#DC3545', 'icon' => 'fal fa-crown'],
            ],
            'gates' => [
                '1→2' => [
                    ['key' => 'training_hours', 'label' => 'Training Hours Completed', 'threshold' => 20, 'unit' => 'count', 'description' => 'At least 20 hours of platform training completed'],
                    ['key' => 'blueprint_enrollment_count', 'label' => 'Blueprint Enrollments', 'threshold' => 1, 'unit' => 'count', 'description' => 'Enrolled in at least 1 learning blueprint'],
                    ['key' => 'enablement_event_count', 'label' => 'Enablement Events Attended', 'threshold' => 2, 'unit' => 'count', 'description' => 'Attended at least 2 enablement events'],
                ],
                '2→3' => [
                    ['key' => 'mb_certification_count', 'label' => 'Model Builder Certification', 'threshold' => 1, 'unit' => 'count', 'description' => 'Obtained Model Builder certification'],
                    ['key' => 'blueprint_completion_count', 'label' => 'Blueprint Completions', 'threshold' => 1, 'unit' => 'count', 'description' => 'Completed at least 1 learning blueprint'],
                    ['key' => 'training_hours', 'label' => 'Training Hours', 'threshold' => 40, 'unit' => 'count', 'description' => 'At least 40 hours of total training'],
                ],
                '3→4' => [
                    ['key' => 'project_assignment_count', 'label' => 'Project Assignments', 'threshold' => 1, 'unit' => 'count', 'description' => 'Assigned to at least 1 delivery project'],
                    ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 1, 'unit' => 'count', 'description' => 'At least 1 active certification maintained'],
                    ['key' => 'blueprint_completion_count', 'label' => 'Blueprint Completions', 'threshold' => 2, 'unit' => 'count', 'description' => 'Completed at least 2 blueprints'],
                ],
                '4→5' => [
                    ['key' => 'completed_project_count', 'label' => 'Completed Projects', 'threshold' => 3, 'unit' => 'count', 'description' => 'Successfully delivered at least 3 projects'],
                    ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 active certifications maintained'],
                    ['key' => 'product_area_count', 'label' => 'Product Area Coverage', 'threshold' => 2, 'unit' => 'count', 'description' => 'Certified across at least 2 product areas'],
                    ['key' => 'avg_project_rating', 'label' => 'Avg Project Rating', 'threshold' => 4.0, 'unit' => 'count', 'description' => 'Average client satisfaction at least 4.0'],
                ],
            ],
        ],

        'Solution Architect' => [
            'description' => 'Progression path for partner resources designing end-to-end Anaplan solutions. Requires deeper technical and business process expertise.',
            'is_default' => false,
            'stages' => [
                ['number' => 1, 'name' => 'Registered', 'description' => 'Resource registered, background in enterprise architecture or consulting confirmed.', 'color' => '#6C757D', 'icon' => 'fal fa-id-card'],
                ['number' => 2, 'name' => 'Foundation', 'description' => 'Core platform training completed, familiar with solution design patterns.', 'color' => '#0D6EFD', 'icon' => 'fal fa-layer-group'],
                ['number' => 3, 'name' => 'Certified SA', 'description' => 'Solution Architect certification obtained, capable of designing multi-module solutions.', 'color' => '#198754', 'icon' => 'fal fa-drafting-compass'],
                ['number' => 4, 'name' => 'Lead Architect', 'description' => 'Leading solution design on projects, owning technical architecture decisions.', 'color' => '#FFC107', 'icon' => 'fal fa-sitemap'],
                ['number' => 5, 'name' => 'Principal', 'description' => 'Principal architect — driving practice methodology, pre-sales leadership, IP creation.', 'color' => '#DC3545', 'icon' => 'fal fa-crown'],
            ],
            'gates' => [
                '1→2' => [
                    ['key' => 'training_hours', 'label' => 'Training Hours', 'threshold' => 30, 'unit' => 'count', 'description' => 'At least 30 hours of platform and architecture training'],
                    ['key' => 'blueprint_enrollment_count', 'label' => 'Blueprint Enrollments', 'threshold' => 2, 'unit' => 'count', 'description' => 'Enrolled in at least 2 blueprints'],
                    ['key' => 'mb_certification_count', 'label' => 'Model Builder Certification', 'threshold' => 1, 'unit' => 'count', 'description' => 'Model Builder certification as prerequisite'],
                ],
                '2→3' => [
                    ['key' => 'sa_certification_count', 'label' => 'SA Certification', 'threshold' => 1, 'unit' => 'count', 'description' => 'Solution Architect certification obtained'],
                    ['key' => 'blueprint_completion_count', 'label' => 'Blueprint Completions', 'threshold' => 2, 'unit' => 'count', 'description' => 'Completed at least 2 solution design blueprints'],
                    ['key' => 'training_hours', 'label' => 'Training Hours', 'threshold' => 60, 'unit' => 'count', 'description' => 'At least 60 hours of total training'],
                ],
                '3→4' => [
                    ['key' => 'completed_project_count', 'label' => 'Completed Projects', 'threshold' => 2, 'unit' => 'count', 'description' => 'SA role on at least 2 completed projects'],
                    ['key' => 'product_area_count', 'label' => 'Product Areas', 'threshold' => 2, 'unit' => 'count', 'description' => 'Solution design across at least 2 product areas'],
                    ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 active certifications (MB + SA)'],
                ],
                '4→5' => [
                    ['key' => 'completed_project_count', 'label' => 'Completed Projects', 'threshold' => 5, 'unit' => 'count', 'description' => 'Led solution architecture on at least 5 projects'],
                    ['key' => 'product_area_count', 'label' => 'Product Areas', 'threshold' => 3, 'unit' => 'count', 'description' => 'Design expertise across at least 3 product areas'],
                    ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 3, 'unit' => 'count', 'description' => 'At least 3 active certifications maintained'],
                    ['key' => 'avg_project_rating', 'label' => 'Avg Project Rating', 'threshold' => 4.5, 'unit' => 'count', 'description' => 'Average client satisfaction at least 4.5'],
                ],
            ],
        ],

        'Master Anaplanner' => [
            'description' => 'Elite progression path recognizing the highest level of Anaplan platform mastery. Requires deep expertise, community contribution, and thought leadership.',
            'is_default' => false,
            'stages' => [
                ['number' => 1, 'name' => 'Candidate', 'description' => 'Nominated or self-nominated for Master Anaplanner track, prerequisites under review.', 'color' => '#6C757D', 'icon' => 'fal fa-user-check'],
                ['number' => 2, 'name' => 'Qualifying', 'description' => 'Meeting certification and experience prerequisites, building portfolio.', 'color' => '#0D6EFD', 'icon' => 'fal fa-tasks'],
                ['number' => 3, 'name' => 'Practitioner', 'description' => 'Advanced certifications obtained, demonstrated complex delivery experience.', 'color' => '#198754', 'icon' => 'fal fa-award'],
                ['number' => 4, 'name' => 'Master', 'description' => 'Master Anaplanner designation achieved — recognized platform expert.', 'color' => '#FFC107', 'icon' => 'fal fa-star'],
                ['number' => 5, 'name' => 'Distinguished', 'description' => 'Distinguished Master — community leader, innovation contributor, practice builder.', 'color' => '#DC3545', 'icon' => 'fal fa-gem'],
            ],
            'gates' => [
                '1→2' => [
                    ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 active certifications (MB + SA minimum)'],
                    ['key' => 'completed_project_count', 'label' => 'Completed Projects', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 completed project deliveries'],
                ],
                '2→3' => [
                    ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 3, 'unit' => 'count', 'description' => 'At least 3 active certifications'],
                    ['key' => 'completed_project_count', 'label' => 'Completed Projects', 'threshold' => 5, 'unit' => 'count', 'description' => 'At least 5 completed project deliveries'],
                    ['key' => 'product_area_count', 'label' => 'Product Areas', 'threshold' => 2, 'unit' => 'count', 'description' => 'Delivery experience across at least 2 product areas'],
                    ['key' => 'avg_project_rating', 'label' => 'Avg Project Rating', 'threshold' => 4.0, 'unit' => 'count', 'description' => 'Average client satisfaction at least 4.0'],
                ],
                '3→4' => [
                    ['key' => 'master_certification_count', 'label' => 'Master Anaplanner Cert', 'threshold' => 1, 'unit' => 'count', 'description' => 'Master Anaplanner certification obtained'],
                    ['key' => 'completed_project_count', 'label' => 'Completed Projects', 'threshold' => 8, 'unit' => 'count', 'description' => 'At least 8 completed project deliveries'],
                    ['key' => 'product_area_count', 'label' => 'Product Areas', 'threshold' => 3, 'unit' => 'count', 'description' => 'Delivery expertise across at least 3 product areas'],
                ],
                '4→5' => [
                    ['key' => 'completed_project_count', 'label' => 'Completed Projects', 'threshold' => 12, 'unit' => 'count', 'description' => 'At least 12 completed projects'],
                    ['key' => 'product_area_count', 'label' => 'Product Areas', 'threshold' => 4, 'unit' => 'count', 'description' => 'Expertise across at least 4 product areas'],
                    ['key' => 'avg_project_rating', 'label' => 'Avg Project Rating', 'threshold' => 4.5, 'unit' => 'count', 'description' => 'Consistently exceptional delivery (4.5+ CSAT)'],
                    ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 4, 'unit' => 'count', 'description' => 'At least 4 active certifications maintained'],
                ],
            ],
        ],

        'Sales' => [
            'description' => 'Progression path for partner sales professionals driving Anaplan pipeline and revenue. Focused on deal origination, qualification, and closing.',
            'is_default' => false,
            'stages' => [
                ['number' => 1, 'name' => 'Registered', 'description' => 'Sales contact registered, partner sales alignment established.', 'color' => '#6C757D', 'icon' => 'fal fa-id-card'],
                ['number' => 2, 'name' => 'Enabled', 'description' => 'Completed sales enablement, understands Anaplan value proposition and pricing.', 'color' => '#0D6EFD', 'icon' => 'fal fa-bullseye'],
                ['number' => 3, 'name' => 'Producing', 'description' => 'Actively sourcing and registering deals, building qualified pipeline.', 'color' => '#198754', 'icon' => 'fal fa-chart-line'],
                ['number' => 4, 'name' => 'Performing', 'description' => 'Consistently closing deals, strong win rate, expanding account footprint.', 'color' => '#FFC107', 'icon' => 'fal fa-trophy'],
                ['number' => 5, 'name' => 'Strategic Seller', 'description' => 'Top producer — driving multi-product deals, executive relationships, market leadership.', 'color' => '#DC3545', 'icon' => 'fal fa-crown'],
            ],
            'gates' => [
                '1→2' => [
                    ['key' => 'training_hours', 'label' => 'Sales Training Hours', 'threshold' => 10, 'unit' => 'count', 'description' => 'At least 10 hours of Anaplan sales training completed'],
                    ['key' => 'enablement_event_count', 'label' => 'Sales Enablement Events', 'threshold' => 2, 'unit' => 'count', 'description' => 'Attended at least 2 sales enablement sessions'],
                ],
                '2→3' => [
                    ['key' => 'deal_registration_count', 'label' => 'Deal Registrations', 'threshold' => 2, 'unit' => 'count', 'description' => 'Registered at least 2 deals'],
                    ['key' => 'enablement_event_count', 'label' => 'Enablement Events', 'threshold' => 4, 'unit' => 'count', 'description' => 'Attended at least 4 enablement events total'],
                ],
                '3→4' => [
                    ['key' => 'won_deal_count', 'label' => 'Won Deals', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 approved/won deal registrations'],
                    ['key' => 'deal_registration_count', 'label' => 'Total Deal Registrations', 'threshold' => 5, 'unit' => 'count', 'description' => 'At least 5 total deal registrations'],
                ],
                '4→5' => [
                    ['key' => 'won_deal_count', 'label' => 'Won Deals', 'threshold' => 5, 'unit' => 'count', 'description' => 'At least 5 won deals'],
                    ['key' => 'deal_registration_count', 'label' => 'Total Deal Registrations', 'threshold' => 10, 'unit' => 'count', 'description' => 'At least 10 total deal registrations submitted'],
                ],
            ],
        ],

        'Pre-Sales' => [
            'description' => 'Progression path for partner pre-sales and demo specialists. Focused on technical discovery, proof-of-concept delivery, and solution positioning.',
            'is_default' => false,
            'stages' => [
                ['number' => 1, 'name' => 'Registered', 'description' => 'Pre-sales resource registered, demo environment access requested.', 'color' => '#6C757D', 'icon' => 'fal fa-id-card'],
                ['number' => 2, 'name' => 'Trained', 'description' => 'Demo training completed, familiar with standard demo environments and talk tracks.', 'color' => '#0D6EFD', 'icon' => 'fal fa-chalkboard-teacher'],
                ['number' => 3, 'name' => 'Qualified', 'description' => 'Capable of running independent demos, technical discovery, and POC scoping.', 'color' => '#198754', 'icon' => 'fal fa-check-double'],
                ['number' => 4, 'name' => 'Lead Pre-Sales', 'description' => 'Leading pre-sales engagements end-to-end, customizing demos, influencing deal strategy.', 'color' => '#FFC107', 'icon' => 'fal fa-presentation'],
                ['number' => 5, 'name' => 'Principal SE', 'description' => 'Principal solutions engineer — pre-sales thought leader, practice builder, win-room contributor.', 'color' => '#DC3545', 'icon' => 'fal fa-crown'],
            ],
            'gates' => [
                '1→2' => [
                    ['key' => 'training_hours', 'label' => 'Pre-Sales Training Hours', 'threshold' => 15, 'unit' => 'count', 'description' => 'At least 15 hours of demo and discovery training'],
                    ['key' => 'blueprint_enrollment_count', 'label' => 'Blueprint Enrollments', 'threshold' => 1, 'unit' => 'count', 'description' => 'Enrolled in at least 1 pre-sales blueprint'],
                ],
                '2→3' => [
                    ['key' => 'mb_certification_count', 'label' => 'Model Builder Certification', 'threshold' => 1, 'unit' => 'count', 'description' => 'Model Builder certification as technical foundation'],
                    ['key' => 'blueprint_completion_count', 'label' => 'Blueprint Completions', 'threshold' => 1, 'unit' => 'count', 'description' => 'Completed at least 1 pre-sales blueprint'],
                    ['key' => 'training_hours', 'label' => 'Training Hours', 'threshold' => 30, 'unit' => 'count', 'description' => 'At least 30 hours of total training'],
                ],
                '3→4' => [
                    ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 active certifications (MB + product-specific)'],
                    ['key' => 'product_area_count', 'label' => 'Product Areas', 'threshold' => 2, 'unit' => 'count', 'description' => 'Demo capability across at least 2 product areas'],
                    ['key' => 'enablement_event_count', 'label' => 'Enablement Events', 'threshold' => 6, 'unit' => 'count', 'description' => 'Attended at least 6 enablement events'],
                ],
                '4→5' => [
                    ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 3, 'unit' => 'count', 'description' => 'At least 3 active certifications'],
                    ['key' => 'product_area_count', 'label' => 'Product Areas', 'threshold' => 3, 'unit' => 'count', 'description' => 'Demo and discovery across at least 3 product areas'],
                    ['key' => 'training_hours', 'label' => 'Training Hours', 'threshold' => 60, 'unit' => 'count', 'description' => 'At least 60 hours of total training and enablement'],
                ],
            ],
        ],

        'Champion' => [
            'description' => 'Progression path for partner champions who advocate for Anaplan internally. Focused on practice building, internal enablement, and strategic alignment.',
            'is_default' => false,
            'stages' => [
                ['number' => 1, 'name' => 'Nominated', 'description' => 'Identified as an Anaplan champion within the partner organization.', 'color' => '#6C757D', 'icon' => 'fal fa-hand-point-up'],
                ['number' => 2, 'name' => 'Engaged', 'description' => 'Actively participating in joint planning, attending partner advisory events.', 'color' => '#0D6EFD', 'icon' => 'fal fa-handshake'],
                ['number' => 3, 'name' => 'Advocate', 'description' => 'Driving internal Anaplan adoption, facilitating resource onboarding and practice growth.', 'color' => '#198754', 'icon' => 'fal fa-bullhorn'],
                ['number' => 4, 'name' => 'Leader', 'description' => 'Practice leader — owning Anaplan P&L, driving strategic investment decisions.', 'color' => '#FFC107', 'icon' => 'fal fa-chess-king'],
                ['number' => 5, 'name' => 'Executive Sponsor', 'description' => 'Executive-level champion — C-suite engagement, market-making partnership, joint GTM.', 'color' => '#DC3545', 'icon' => 'fal fa-crown'],
            ],
            'gates' => [
                '1→2' => [
                    ['key' => 'enablement_event_count', 'label' => 'Partner Events Attended', 'threshold' => 3, 'unit' => 'count', 'description' => 'Attended at least 3 partner advisory or planning events'],
                    ['key' => 'training_hours', 'label' => 'Platform Familiarity', 'threshold' => 5, 'unit' => 'count', 'description' => 'At least 5 hours of platform overview training'],
                ],
                '2→3' => [
                    ['key' => 'enablement_event_count', 'label' => 'Events Attended', 'threshold' => 6, 'unit' => 'count', 'description' => 'Attended at least 6 partner events total'],
                    ['key' => 'training_hours', 'label' => 'Training Hours', 'threshold' => 10, 'unit' => 'count', 'description' => 'At least 10 hours of training or enablement'],
                ],
                '3→4' => [
                    ['key' => 'enablement_event_count', 'label' => 'Events Attended', 'threshold' => 10, 'unit' => 'count', 'description' => 'Attended at least 10 partner events and planning sessions'],
                ],
                '4→5' => [
                    ['key' => 'enablement_event_count', 'label' => 'Events Attended', 'threshold' => 15, 'unit' => 'count', 'description' => 'Deep engagement — at least 15 events including executive sessions'],
                ],
            ],
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding resource paths, stages, and gates...');

        $admin = User::whereRelation('role', 'slug', 'admin')->first() ?? User::first();

        $pathCount = 0;
        $stageCount = 0;
        $gateCount = 0;

        foreach (self::PATHS as $pathName => $config) {
            $path = ResourcePath::create([
                'name' => $pathName,
                'description' => $config['description'],
                'is_default' => $config['is_default'],
                'is_active' => true,
                'created_by' => $admin->id,
            ]);
            $pathCount++;

            // Create stages
            $stageModels = [];
            foreach ($config['stages'] as $i => $stage) {
                $stageModels[$stage['number']] = ResourcePathStage::create([
                    'resource_path_id' => $path->id,
                    'stage_number' => $stage['number'],
                    'name' => $stage['name'],
                    'description' => $stage['description'],
                    'color' => $stage['color'],
                    'icon' => $stage['icon'],
                    'sort_order' => $i + 1,
                ]);
                $stageCount++;
            }

            // Create gates
            foreach ($config['gates'] as $transition => $gates) {
                [$fromNum, $toNum] = explode('→', $transition);
                $fromStage = $stageModels[(int) $fromNum];
                $toStage = $stageModels[(int) $toNum];

                foreach ($gates as $sortOrder => $gate) {
                    ResourcePathGate::create([
                        'resource_path_id' => $path->id,
                        'from_stage_id' => $fromStage->id,
                        'to_stage_id' => $toStage->id,
                        'requirement_key' => $gate['key'],
                        'requirement_label' => $gate['label'],
                        'comparison' => 'gte',
                        'threshold_value' => $gate['threshold'],
                        'unit' => $gate['unit'],
                        'description' => $gate['description'],
                        'sort_order' => $sortOrder,
                    ]);
                    $gateCount++;
                }
            }
        }

        $this->command->info("  Created {$pathCount} resource paths, {$stageCount} stages, {$gateCount} gates.");
    }
}
