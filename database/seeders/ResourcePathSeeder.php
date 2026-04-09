<?php

namespace Database\Seeders;

use App\Models\ResourcePath;
use App\Models\ResourcePathGate;
use App\Models\ResourcePathStage;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResourcePathSeeder extends Seeder
{
    // =========================================================================
    //  PRODUCT CATALOG
    // =========================================================================

    private const PRODUCT_GROUPS = [
        'Finance' => [
            'Project Cost Planning',
            'Profitability Analysis',
            'Subscription Revenue Planning',
            'Consensus Margin Planning',
            'Financial Close and Consolidation',
            'Disclosure Management',
            'Integrated Financial Planning',
        ],
        'Supply Chain' => [
            'Trade Promotion Management',
            'Spend Analysis and Forecasting',
            'Assortment Planning',
            'Allocation and Replenishment Planning',
            'Demand Planning',
            'Production Planning',
            'Inventory Planning',
            'Capacity Planning',
            'Merchandise Financial Planning',
            'Integrated Business Planning',
        ],
        'HR & Workforce' => [
            'Contact Center Planning',
            'Project Resource Planning',
            'Operational Workforce Planning',
        ],
        'Sales & Marketing' => [
            'Sales Forecasting',
            'Territory & Quota Planning',
            'Go-To-Market Capacity Planning',
        ],
    ];

    private const PLATFORM_TECHNOLOGIES = ['Polaris', 'ADO', 'Forecaster', 'CoModeler', 'Agent Studio'];

    // =========================================================================
    //  ROLE TYPE DEFINITIONS — stages and gates reused across products
    // =========================================================================

    private const ROLE_TYPES = [
        'delivery' => [
            'stages' => [
                ['number' => 1, 'name' => 'Registered', 'description' => 'Resource registered, assigned to enablement track for {context}.', 'color' => '#6C757D', 'icon' => 'fal fa-id-card'],
                ['number' => 2, 'name' => 'Trained', 'description' => 'Core training completed for {context}. Familiar with product capabilities, data model, and delivery methodology.', 'color' => '#0D6EFD', 'icon' => 'fal fa-graduation-cap'],
                ['number' => 3, 'name' => 'Certified', 'description' => 'Certification obtained for {context}. Ready for supervised project work.', 'color' => '#198754', 'icon' => 'fal fa-certificate'],
                ['number' => 4, 'name' => 'Delivering', 'description' => 'Actively delivering {context} projects with demonstrated quality and methodology adherence.', 'color' => '#FFC107', 'icon' => 'fal fa-project-diagram'],
                ['number' => 5, 'name' => 'Expert', 'description' => 'Senior delivery expert for {context}. Mentoring others, complex design, thought leadership.', 'color' => '#DC3545', 'icon' => 'fal fa-crown'],
            ],
            'gates' => [
                '1→2' => [
                    ['key' => 'training_hours', 'label' => 'Training Hours', 'threshold' => 20, 'unit' => 'count', 'description' => 'At least 20 hours of {context} training completed'],
                    ['key' => 'blueprint_enrollment_count', 'label' => 'Blueprint Enrollments', 'threshold' => 1, 'unit' => 'count', 'description' => 'Enrolled in at least 1 {context} learning blueprint'],
                    ['key' => 'enablement_event_count', 'label' => 'Enablement Events', 'threshold' => 2, 'unit' => 'count', 'description' => 'Attended at least 2 {context} enablement events'],
                ],
                '2→3' => [
                    ['key' => 'active_certification_count', 'label' => 'Certification', 'threshold' => 1, 'unit' => 'count', 'description' => 'Obtained {context} certification'],
                    ['key' => 'blueprint_completion_count', 'label' => 'Blueprint Completions', 'threshold' => 1, 'unit' => 'count', 'description' => 'Completed at least 1 {context} blueprint'],
                    ['key' => 'training_hours', 'label' => 'Training Hours', 'threshold' => 40, 'unit' => 'count', 'description' => 'At least 40 hours of total {context} training'],
                ],
                '3→4' => [
                    ['key' => 'project_assignment_count', 'label' => 'Project Assignments', 'threshold' => 1, 'unit' => 'count', 'description' => 'Assigned to at least 1 {context} project'],
                    ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 1, 'unit' => 'count', 'description' => 'Active {context} certification maintained'],
                    ['key' => 'blueprint_completion_count', 'label' => 'Blueprints Completed', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 {context} blueprints completed'],
                ],
                '4→5' => [
                    ['key' => 'completed_project_count', 'label' => 'Completed Projects', 'threshold' => 3, 'unit' => 'count', 'description' => 'Successfully delivered at least 3 {context} projects'],
                    ['key' => 'active_certification_count', 'label' => 'Certifications Maintained', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 active {context}-related certifications'],
                    ['key' => 'avg_project_rating', 'label' => 'Avg Project Rating', 'threshold' => 4.0, 'unit' => 'count', 'description' => 'Average client satisfaction at least 4.0 on {context} projects'],
                ],
            ],
        ],

        'sales' => [
            'stages' => [
                ['number' => 1, 'name' => 'Registered', 'description' => 'Sales resource registered for {context}. Alignment with Anaplan field sales established.', 'color' => '#6C757D', 'icon' => 'fal fa-id-card'],
                ['number' => 2, 'name' => 'Enabled', 'description' => 'Completed {context} sales enablement. Understands value proposition, use cases, competitive positioning, and pricing.', 'color' => '#0D6EFD', 'icon' => 'fal fa-bullseye'],
                ['number' => 3, 'name' => 'Producing', 'description' => 'Actively sourcing and registering {context} deals. Building qualified pipeline.', 'color' => '#198754', 'icon' => 'fal fa-chart-line'],
                ['number' => 4, 'name' => 'Performing', 'description' => 'Consistently closing {context} deals. Strong win rate and expanding account footprint.', 'color' => '#FFC107', 'icon' => 'fal fa-trophy'],
                ['number' => 5, 'name' => 'Strategic Seller', 'description' => 'Top {context} producer. Multi-product positioning, executive relationships, market leadership.', 'color' => '#DC3545', 'icon' => 'fal fa-crown'],
            ],
            'gates' => [
                '1→2' => [
                    ['key' => 'training_hours', 'label' => 'Sales Training Hours', 'threshold' => 10, 'unit' => 'count', 'description' => 'At least 10 hours of {context} sales training completed'],
                    ['key' => 'enablement_event_count', 'label' => 'Sales Enablement Events', 'threshold' => 2, 'unit' => 'count', 'description' => 'Attended at least 2 {context} sales enablement sessions'],
                ],
                '2→3' => [
                    ['key' => 'deal_registration_count', 'label' => 'Deal Registrations', 'threshold' => 2, 'unit' => 'count', 'description' => 'Registered at least 2 {context} deals'],
                    ['key' => 'enablement_event_count', 'label' => 'Enablement Events', 'threshold' => 4, 'unit' => 'count', 'description' => 'Attended at least 4 {context} enablement events total'],
                ],
                '3→4' => [
                    ['key' => 'won_deal_count', 'label' => 'Won Deals', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 {context} deal registrations won'],
                    ['key' => 'deal_registration_count', 'label' => 'Total Deals Registered', 'threshold' => 5, 'unit' => 'count', 'description' => 'At least 5 total {context} deal registrations'],
                ],
                '4→5' => [
                    ['key' => 'won_deal_count', 'label' => 'Won Deals', 'threshold' => 5, 'unit' => 'count', 'description' => 'At least 5 {context} deals won'],
                    ['key' => 'deal_registration_count', 'label' => 'Total Deals Registered', 'threshold' => 10, 'unit' => 'count', 'description' => 'At least 10 total {context} deal registrations'],
                ],
            ],
        ],

        'pre-sales' => [
            'stages' => [
                ['number' => 1, 'name' => 'Registered', 'description' => 'Pre-sales resource registered for {context}. Demo environment access requested.', 'color' => '#6C757D', 'icon' => 'fal fa-id-card'],
                ['number' => 2, 'name' => 'Trained', 'description' => 'Completed {context} demo training. Familiar with standard demo environments and talk tracks.', 'color' => '#0D6EFD', 'icon' => 'fal fa-chalkboard-teacher'],
                ['number' => 3, 'name' => 'Qualified', 'description' => 'Capable of running independent {context} demos, technical discovery, and POC scoping.', 'color' => '#198754', 'icon' => 'fal fa-check-double'],
                ['number' => 4, 'name' => 'Lead Pre-Sales', 'description' => 'Leading {context} pre-sales engagements end-to-end. Customizing demos and influencing deal strategy.', 'color' => '#FFC107', 'icon' => 'fal fa-presentation'],
                ['number' => 5, 'name' => 'Principal SE', 'description' => 'Principal solutions engineer for {context}. Pre-sales thought leader, practice builder, win-room contributor.', 'color' => '#DC3545', 'icon' => 'fal fa-crown'],
            ],
            'gates' => [
                '1→2' => [
                    ['key' => 'training_hours', 'label' => 'Pre-Sales Training Hours', 'threshold' => 15, 'unit' => 'count', 'description' => 'At least 15 hours of {context} demo and discovery training'],
                    ['key' => 'blueprint_enrollment_count', 'label' => 'Blueprint Enrollments', 'threshold' => 1, 'unit' => 'count', 'description' => 'Enrolled in at least 1 {context} pre-sales blueprint'],
                ],
                '2→3' => [
                    ['key' => 'active_certification_count', 'label' => 'Technical Certification', 'threshold' => 1, 'unit' => 'count', 'description' => 'At least 1 {context}-related technical certification'],
                    ['key' => 'blueprint_completion_count', 'label' => 'Blueprint Completions', 'threshold' => 1, 'unit' => 'count', 'description' => 'Completed at least 1 {context} pre-sales blueprint'],
                    ['key' => 'training_hours', 'label' => 'Training Hours', 'threshold' => 30, 'unit' => 'count', 'description' => 'At least 30 hours of total {context} training'],
                ],
                '3→4' => [
                    ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 active {context}-related certifications'],
                    ['key' => 'enablement_event_count', 'label' => 'Enablement Events', 'threshold' => 6, 'unit' => 'count', 'description' => 'Attended at least 6 {context} enablement events'],
                ],
                '4→5' => [
                    ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 3, 'unit' => 'count', 'description' => 'At least 3 active {context}-related certifications'],
                    ['key' => 'training_hours', 'label' => 'Training Hours', 'threshold' => 60, 'unit' => 'count', 'description' => 'At least 60 hours of total {context} training and enablement'],
                ],
            ],
        ],
    ];

    // =========================================================================
    //  GENERAL (NON-PRODUCT-SPECIFIC) PATHS
    // =========================================================================

    private const GENERAL_TECHNICAL = [
        'Model Builder' => [
            'description' => 'Progression path for partner resources building and maintaining Anaplan models. Covers core platform skills through advanced modeling expertise.',
            'is_default' => true,
        ],
        'Solution Architect' => [
            'description' => 'Progression path for partner resources designing end-to-end Anaplan solutions. Requires deeper technical and business process expertise.',
        ],
        'Master Anaplanner' => [
            'description' => 'Elite progression path recognizing the highest level of Anaplan platform mastery. Requires deep expertise, community contribution, and thought leadership.',
        ],
    ];

    private const GENERAL_NON_TECHNICAL = [
        'Sales' => [
            'description' => 'Progression path for partner sales professionals driving Anaplan pipeline and revenue. Focused on deal origination, qualification, and closing.',
        ],
        'Pre-Sales' => [
            'description' => 'Progression path for partner pre-sales and demo specialists. Focused on technical discovery, proof-of-concept delivery, and solution positioning.',
        ],
        'Champion' => [
            'description' => 'Progression path for partner champions who advocate for Anaplan internally. Focused on practice building, internal enablement, and strategic alignment.',
        ],
    ];

    // =========================================================================
    //  CHAMPION has unique stages (not a standard role type)
    // =========================================================================

    private const CHAMPION_STAGES = [
        ['number' => 1, 'name' => 'Nominated', 'description' => 'Identified as an Anaplan champion within the partner organization.', 'color' => '#6C757D', 'icon' => 'fal fa-hand-point-up'],
        ['number' => 2, 'name' => 'Engaged', 'description' => 'Actively participating in joint planning, attending partner advisory events.', 'color' => '#0D6EFD', 'icon' => 'fal fa-handshake'],
        ['number' => 3, 'name' => 'Advocate', 'description' => 'Driving internal Anaplan adoption, facilitating resource onboarding and practice growth.', 'color' => '#198754', 'icon' => 'fal fa-bullhorn'],
        ['number' => 4, 'name' => 'Leader', 'description' => 'Practice leader — owning Anaplan P&L, driving strategic investment decisions.', 'color' => '#FFC107', 'icon' => 'fal fa-chess-king'],
        ['number' => 5, 'name' => 'Executive Sponsor', 'description' => 'Executive-level champion — C-suite engagement, market-making partnership, joint GTM.', 'color' => '#DC3545', 'icon' => 'fal fa-crown'],
    ];

    private const CHAMPION_GATES = [
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
    ];

    // =========================================================================
    //  MASTER ANAPLANNER has unique stages
    // =========================================================================

    private const MASTER_STAGES = [
        ['number' => 1, 'name' => 'Candidate', 'description' => 'Nominated for Master Anaplanner track, prerequisites under review.', 'color' => '#6C757D', 'icon' => 'fal fa-user-check'],
        ['number' => 2, 'name' => 'Qualifying', 'description' => 'Meeting certification and experience prerequisites, building portfolio.', 'color' => '#0D6EFD', 'icon' => 'fal fa-tasks'],
        ['number' => 3, 'name' => 'Practitioner', 'description' => 'Advanced certifications obtained, demonstrated complex delivery experience.', 'color' => '#198754', 'icon' => 'fal fa-award'],
        ['number' => 4, 'name' => 'Master', 'description' => 'Master Anaplanner designation achieved — recognized platform expert.', 'color' => '#FFC107', 'icon' => 'fal fa-star'],
        ['number' => 5, 'name' => 'Distinguished', 'description' => 'Distinguished Master — community leader, innovation contributor, practice builder.', 'color' => '#DC3545', 'icon' => 'fal fa-gem'],
    ];

    private const MASTER_GATES = [
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
            ['key' => 'active_certification_count', 'label' => 'Certifications', 'threshold' => 4, 'unit' => 'count', 'description' => 'At least 4 active certifications including Master Anaplanner'],
            ['key' => 'completed_project_count', 'label' => 'Completed Projects', 'threshold' => 8, 'unit' => 'count', 'description' => 'At least 8 completed project deliveries'],
            ['key' => 'product_area_count', 'label' => 'Product Areas', 'threshold' => 3, 'unit' => 'count', 'description' => 'Expertise across at least 3 product areas'],
        ],
        '4→5' => [
            ['key' => 'completed_project_count', 'label' => 'Completed Projects', 'threshold' => 12, 'unit' => 'count', 'description' => 'At least 12 completed projects'],
            ['key' => 'product_area_count', 'label' => 'Product Areas', 'threshold' => 4, 'unit' => 'count', 'description' => 'Expertise across at least 4 product areas'],
            ['key' => 'avg_project_rating', 'label' => 'Avg Project Rating', 'threshold' => 4.5, 'unit' => 'count', 'description' => 'Consistently exceptional delivery (4.5+ CSAT)'],
            ['key' => 'active_certification_count', 'label' => 'Active Certifications', 'threshold' => 5, 'unit' => 'count', 'description' => 'At least 5 active certifications maintained'],
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding resource paths, stages, and gates...');

        $admin = User::whereRelation('role', 'slug', 'admin')->first() ?? User::first();

        $pathCount = 0;
        $stageCount = 0;
        $gateCount = 0;

        // ── General Technical Paths ──────────────────────────────────────
        foreach (self::GENERAL_TECHNICAL as $name => $config) {
            $roleType = match ($name) {
                'Solution Architect' => 'delivery',
                'Master Anaplanner' => null, // custom
                default => 'delivery',
            };

            if ($name === 'Master Anaplanner') {
                $counts = $this->createPath($admin, $name, $config['description'], $config['is_default'] ?? false, self::MASTER_STAGES, self::MASTER_GATES, $name);
            } else {
                $counts = $this->createPathFromRoleType($admin, $name, $config['description'], $config['is_default'] ?? false, $roleType, $name);
            }

            $pathCount++;
            $stageCount += $counts['stages'];
            $gateCount += $counts['gates'];
        }

        // ── General Non-Technical Paths ──────────────────────────────────
        foreach (self::GENERAL_NON_TECHNICAL as $name => $config) {
            if ($name === 'Champion') {
                $counts = $this->createPath($admin, $name, $config['description'], false, self::CHAMPION_STAGES, self::CHAMPION_GATES, $name);
            } else {
                $roleType = $name === 'Sales' ? 'sales' : 'pre-sales';
                $counts = $this->createPathFromRoleType($admin, $name, $config['description'], false, $roleType, 'Anaplan');
            }

            $pathCount++;
            $stageCount += $counts['stages'];
            $gateCount += $counts['gates'];
        }

        // ── Platform Paths (Sales, Pre-Sales, Delivery per technology) ───
        foreach (self::PLATFORM_TECHNOLOGIES as $tech) {
            foreach (['delivery', 'sales', 'pre-sales'] as $roleType) {
                $label = ucfirst(str_replace('-', '-', $roleType));
                $name = "{$tech}: {$label}";
                $desc = "{$label} progression path for partner resources specializing in Anaplan {$tech}.";

                $counts = $this->createPathFromRoleType($admin, $name, $desc, false, $roleType, $tech);
                $pathCount++;
                $stageCount += $counts['stages'];
                $gateCount += $counts['gates'];
            }
        }

        // ── Product Group Paths (Sales, Pre-Sales, Delivery per group) ──
        foreach (self::PRODUCT_GROUPS as $group => $applications) {
            foreach (['delivery', 'sales', 'pre-sales'] as $roleType) {
                $label = ucfirst(str_replace('-', '-', $roleType));
                $name = "{$group}: {$label}";
                $desc = "{$label} progression path for partner resources focused on the Anaplan {$group} product group.";

                $counts = $this->createPathFromRoleType($admin, $name, $desc, false, $roleType, $group);
                $pathCount++;
                $stageCount += $counts['stages'];
                $gateCount += $counts['gates'];
            }

            // ── Individual Application Paths (Sales, Pre-Sales, Delivery per app)
            foreach ($applications as $app) {
                foreach (['delivery', 'sales', 'pre-sales'] as $roleType) {
                    $label = ucfirst(str_replace('-', '-', $roleType));
                    $name = "{$app}: {$label}";
                    $desc = "{$label} progression path for partner resources specializing in Anaplan {$app}.";

                    $counts = $this->createPathFromRoleType($admin, $name, $desc, false, $roleType, $app);
                    $pathCount++;
                    $stageCount += $counts['stages'];
                    $gateCount += $counts['gates'];
                }
            }
        }

        $this->command->info("  Created {$pathCount} resource paths, {$stageCount} stages, {$gateCount} gates.");
    }

    /**
     * Create a path using a standard role type template, substituting the context name.
     *
     * @return array{stages: int, gates: int}
     */
    private function createPathFromRoleType(User $admin, string $name, string $description, bool $isDefault, string $roleType, string $context): array
    {
        $template = self::ROLE_TYPES[$roleType];

        $stages = array_map(function ($stage) use ($context) {
            $stage['description'] = str_replace('{context}', $context, $stage['description']);

            return $stage;
        }, $template['stages']);

        $gates = [];
        foreach ($template['gates'] as $transition => $gateList) {
            $gates[$transition] = array_map(function ($gate) use ($context) {
                $gate['description'] = str_replace('{context}', $context, $gate['description']);

                return $gate;
            }, $gateList);
        }

        return $this->createPath($admin, $name, $description, $isDefault, $stages, $gates, $context);
    }

    /**
     * Create a path with explicit stages and gates.
     *
     * @return array{stages: int, gates: int}
     */
    private function createPath(User $admin, string $name, string $description, bool $isDefault, array $stages, array $gates, string $context): array
    {
        $path = ResourcePath::create([
            'name' => $name,
            'description' => $description,
            'is_default' => $isDefault,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $stageModels = [];
        foreach ($stages as $i => $stage) {
            $stageModels[$stage['number']] = ResourcePathStage::create([
                'resource_path_id' => $path->id,
                'stage_number' => $stage['number'],
                'name' => $stage['name'],
                'description' => $stage['description'],
                'color' => $stage['color'],
                'icon' => $stage['icon'],
                'sort_order' => $i + 1,
            ]);
        }

        $gateCount = 0;
        foreach ($gates as $transition => $gateList) {
            [$fromNum, $toNum] = explode('→', $transition);
            $fromStage = $stageModels[(int) $fromNum];
            $toStage = $stageModels[(int) $toNum];

            foreach ($gateList as $sortOrder => $gate) {
                ResourcePathGate::create([
                    'resource_path_id' => $path->id,
                    'from_stage_id' => $fromStage->id,
                    'to_stage_id' => $toStage->id,
                    'requirement_key' => $gate['key'],
                    'requirement_label' => $gate['label'],
                    'comparison' => 'gte',
                    'threshold_value' => $gate['threshold'],
                    'unit' => $gate['unit'],
                    'description' => str_replace('{context}', $context, $gate['description']),
                    'sort_order' => $sortOrder,
                ]);
                $gateCount++;
            }
        }

        return ['stages' => count($stages), 'gates' => $gateCount];
    }
}
