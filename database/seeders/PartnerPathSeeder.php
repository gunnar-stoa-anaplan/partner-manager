<?php

namespace Database\Seeders;

use App\Models\PartnerPath;
use App\Models\PartnerPathGate;
use App\Models\PartnerPathStage;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerPathSeeder extends Seeder
{
    /**
     * All partner path definitions.
     *
     * @var array<string, array>
     */
    private const PATHS = [
        // =====================================================================
        //  STRATEGIC IMPLEMENTATION PARTNER
        //  Large firms (Deloitte, Accenture, PwC) building an Anaplan practice.
        //  Dedicated sales/pre-sales, offshore delivery, co-sell motion.
        // =====================================================================
        'Strategic Implementation Partner' => [
            'description' => 'Progression path for large implementation partners with dedicated sales, pre-sales, and delivery teams. Focused on building an Anaplan practice with co-sell pipeline, multi-product expansion, and scaled delivery operations.',
            'is_default' => true,
            'stages' => [
                ['number' => 1, 'name' => 'Onboarded', 'description' => 'Partner agreement signed, practice lead identified, PSM assigned. Initial team registered and core certifications underway.', 'color' => '#6C757D', 'icon' => 'fal fa-flag'],
                ['number' => 2, 'name' => 'Enabled', 'description' => 'Co-sell motion activated, dedicated sales/pre-sales ramped, first joint pipeline building. Core certifications achieved, initial enablement complete.', 'color' => '#0D6EFD', 'icon' => 'fal fa-graduation-cap'],
                ['number' => 3, 'name' => 'Supervised Delivery', 'description' => 'First projects underway with Anaplan design review oversight. Model design, extension requirements, gap-fit analysis, and sprint plans reviewed by Anaplan SA. Must pass 2 project reviews with no major findings.', 'color' => '#17A2B8', 'icon' => 'fal fa-search'],
                ['number' => 4, 'name' => 'Performing', 'description' => 'Delivering projects independently, self-sourcing pipeline, offshore delivery operating. Meeting baseline revenue and quality metrics. PS Augmentation qualified.', 'color' => '#198754', 'icon' => 'fal fa-chart-line'],
                ['number' => 5, 'name' => 'Accelerating', 'description' => 'Multi-product and multi-geo expansion, building IP and solution accelerators. High self-sourced ratio, strong win rate, consistent delivery quality.', 'color' => '#FFC107', 'icon' => 'fal fa-rocket'],
                ['number' => 6, 'name' => 'Strategic', 'description' => 'Market-making partnership with executive alignment, joint GTM strategy, thought leadership. Top-tier revenue, industry solutions, and co-innovation.', 'color' => '#DC3545', 'icon' => 'fal fa-crown'],
            ],
            'gates' => [
                // ── Onboarded → Enabled ─────────────────────────────
                '1→2' => [
                    ['key' => 'certified_mb_count', 'label' => 'Model Builder Certifications', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 certified Model Builders'],
                    ['key' => 'certified_sa_count', 'label' => 'Solution Architect Certifications', 'threshold' => 1, 'unit' => 'count', 'description' => 'At least 1 certified Solution Architect'],
                    ['key' => 'core_blueprints_completed', 'label' => 'Core Enablement Completed', 'threshold' => 1, 'unit' => 'count', 'description' => 'At least 1 core blueprint fully completed'],
                    ['key' => 'onboarding_tasks_pct', 'label' => 'Onboarding Tasks Complete', 'threshold' => 100, 'unit' => 'percentage', 'description' => 'All required onboarding tasks completed'],
                    ['key' => 'salesforce_connected', 'label' => 'CRM Connected', 'threshold' => 1, 'unit' => 'boolean', 'description' => 'Partner Salesforce ID populated for deal tracking'],
                    ['key' => 'contact_count', 'label' => 'Key Contacts Registered', 'threshold' => 5, 'unit' => 'count', 'description' => 'Practice lead, sales lead, pre-sales lead, delivery lead, and support contact registered'],
                ],
                // ── Enabled → Supervised Delivery ───────────────────
                '2→3' => [
                    ['key' => 'open_opportunity_count', 'label' => 'Active Pipeline', 'threshold' => 3, 'unit' => 'count', 'description' => 'At least 3 open co-sell opportunities'],
                    ['key' => 'pipeline_value', 'label' => 'Pipeline Value', 'threshold' => 500000, 'unit' => 'currency', 'description' => 'Total open pipeline at least $500K'],
                    ['key' => 'certified_resource_count', 'label' => 'Certified Resources', 'threshold' => 4, 'unit' => 'count', 'description' => 'At least 4 active MB + SA certifications'],
                    ['key' => 'app_blueprints_completed', 'label' => 'App Blueprint Completed', 'threshold' => 1, 'unit' => 'count', 'description' => 'At least 1 product-specific blueprint completed'],
                    ['key' => 'qbr_count_12m', 'label' => 'QBR Cadence', 'threshold' => 1, 'unit' => 'count', 'description' => 'At least 1 QBR completed'],
                ],
                // ── Supervised Delivery → Performing ────────────────
                '3→4' => [
                    ['key' => 'completed_project_count', 'label' => 'Reviewed Projects Passed', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 projects passed Anaplan delivery review with no major findings'],
                    ['key' => 'avg_csat', 'label' => 'Delivery Quality', 'threshold' => 4.0, 'unit' => 'count', 'description' => 'Average CSAT at least 4.0 on reviewed projects'],
                    ['key' => 'won_opp_count_12m', 'label' => 'Won Opportunities', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 closed-won deals (trailing 12m)'],
                    ['key' => 'certified_resource_count', 'label' => 'Certified Resources', 'threshold' => 6, 'unit' => 'count', 'description' => 'At least 6 active certifications to support independent delivery'],
                    ['key' => 'health_score', 'label' => 'Health Score', 'threshold' => 35, 'unit' => 'count', 'description' => 'Overall scorecard score at least 35'],
                ],
                // ── Performing → Accelerating ───────────────────────
                '4→5' => [
                    ['key' => 'revenue_attainment_pct', 'label' => 'Revenue Achievement', 'threshold' => 80, 'unit' => 'percentage', 'description' => 'At least 80% of annual NNACV target'],
                    ['key' => 'win_rate', 'label' => 'Win Rate', 'threshold' => 30, 'unit' => 'percentage', 'description' => 'Won / (Won + Lost) trailing 12m at least 30%'],
                    ['key' => 'pipeline_coverage', 'label' => 'Pipeline Coverage', 'threshold' => 2.0, 'unit' => 'ratio', 'description' => 'Pipeline / Remaining target at least 2.0x'],
                    ['key' => 'partner_originated_pct', 'label' => 'Self-Sourced Ratio', 'threshold' => 20, 'unit' => 'percentage', 'description' => 'At least 20% of pipeline partner-originated'],
                    ['key' => 'avg_csat', 'label' => 'Delivery Quality', 'threshold' => 4.0, 'unit' => 'count', 'description' => 'Average CSAT at least 4.0'],
                    ['key' => 'ontime_delivery_pct', 'label' => 'On-Time Delivery', 'threshold' => 75, 'unit' => 'percentage', 'description' => 'At least 75% of projects delivered on-time'],
                    ['key' => 'certified_resource_count', 'label' => 'Certified Resources', 'threshold' => 10, 'unit' => 'count', 'description' => 'At least 10 active certifications'],
                    ['key' => 'product_area_count', 'label' => 'Product Areas', 'threshold' => 2, 'unit' => 'count', 'description' => 'Certifications in at least 2 product areas'],
                    ['key' => 'health_score', 'label' => 'Health Score', 'threshold' => 55, 'unit' => 'count', 'description' => 'Overall scorecard score at least 55'],
                ],
                // ── Accelerating → Strategic ────────────────────────
                '5→6' => [
                    ['key' => 'revenue_attainment_pct', 'label' => 'Revenue Achievement', 'threshold' => 100, 'unit' => 'percentage', 'description' => 'At least 100% of annual NNACV target'],
                    ['key' => 'win_rate', 'label' => 'Win Rate', 'threshold' => 40, 'unit' => 'percentage', 'description' => 'Won / (Won + Lost) trailing 12m at least 40%'],
                    ['key' => 'pipeline_coverage', 'label' => 'Pipeline Coverage', 'threshold' => 3.0, 'unit' => 'ratio', 'description' => 'Pipeline / Remaining target at least 3.0x'],
                    ['key' => 'partner_originated_pct', 'label' => 'Self-Sourced Ratio', 'threshold' => 40, 'unit' => 'percentage', 'description' => 'At least 40% of pipeline partner-originated'],
                    ['key' => 'avg_csat', 'label' => 'Delivery Quality', 'threshold' => 4.5, 'unit' => 'count', 'description' => 'Average CSAT at least 4.5'],
                    ['key' => 'ontime_delivery_pct', 'label' => 'On-Time Delivery', 'threshold' => 85, 'unit' => 'percentage', 'description' => 'At least 85% of projects delivered on-time'],
                    ['key' => 'certified_resource_count', 'label' => 'Certified Resources', 'threshold' => 20, 'unit' => 'count', 'description' => 'At least 20 active certifications'],
                    ['key' => 'product_area_count', 'label' => 'Product Areas', 'threshold' => 3, 'unit' => 'count', 'description' => 'Certifications in at least 3 product areas'],
                    ['key' => 'solution_count', 'label' => 'Solution Assets / IP', 'threshold' => 1, 'unit' => 'count', 'description' => 'At least 1 registered solution or accelerator'],
                    ['key' => 'health_score', 'label' => 'Health Score', 'threshold' => 80, 'unit' => 'count', 'description' => 'Overall scorecard score at least 80'],
                ],
            ],
        ],

        // =====================================================================
        //  SPECIALIST IMPLEMENTATION PARTNER
        //  Smaller firms (Spaulding Ridge, Argano) with deep industry expertise.
        //  Small but talented delivery teams, no dedicated sales/pre-sales.
        //  Win through expertise and reputation on Anaplan-sourced deals.
        // =====================================================================
        'Specialist Implementation Partner' => [
            'description' => 'Progression path for smaller implementation partners with deep industry or product expertise. Focused on delivery quality, building reference stories, and becoming the go-to partner for a specific niche. Primarily Anaplan-sourced engagements.',
            'is_default' => false,
            'stages' => [
                ['number' => 1, 'name' => 'Onboarded', 'description' => 'Partner agreement signed, small team registered, PSM assigned. Core certifications underway.', 'color' => '#6C757D', 'icon' => 'fal fa-flag'],
                ['number' => 2, 'name' => 'Delivery Ready', 'description' => 'Team certified, aligned to Anaplan delivery methodology. Ready for first Anaplan-sourced engagement.', 'color' => '#0D6EFD', 'icon' => 'fal fa-check-circle'],
                ['number' => 3, 'name' => 'Supervised Delivery', 'description' => 'First project underway with Anaplan SA oversight. Model design, gap-fit analysis, and delivery artifacts reviewed. Must pass 1 project review with Anaplan SA sign-off.', 'color' => '#17A2B8', 'icon' => 'fal fa-search'],
                ['number' => 4, 'name' => 'Proven', 'description' => 'Track record of quality delivery, strong client references, niche expertise recognized. Reduced oversight with periodic PSM review.', 'color' => '#198754', 'icon' => 'fal fa-shield-check'],
                ['number' => 5, 'name' => 'Trusted Expert', 'description' => 'Go-to partner for a specific product or vertical. Consistently high CSAT, PS Augmentation qualified. Can deliver under Anaplan brand.', 'color' => '#FFC107', 'icon' => 'fal fa-star'],
                ['number' => 6, 'name' => 'Elite Specialist', 'description' => 'Top-tier delivery reputation. Contributing to Anaplan methodology, mentoring other partners, leading in their niche.', 'color' => '#DC3545', 'icon' => 'fal fa-gem'],
            ],
            'gates' => [
                // ── Onboarded → Delivery Ready ──────────────────────
                '1→2' => [
                    ['key' => 'certified_mb_count', 'label' => 'Model Builder Certifications', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 certified Model Builders (core team)'],
                    ['key' => 'core_blueprints_completed', 'label' => 'Core Enablement Completed', 'threshold' => 1, 'unit' => 'count', 'description' => 'Core platform blueprint completed'],
                    ['key' => 'onboarding_tasks_pct', 'label' => 'Onboarding Tasks Complete', 'threshold' => 100, 'unit' => 'percentage', 'description' => 'All onboarding tasks completed'],
                    ['key' => 'contact_count', 'label' => 'Team Contacts Registered', 'threshold' => 3, 'unit' => 'count', 'description' => 'At least 3 delivery team contacts registered'],
                ],
                // ── Delivery Ready → Supervised Delivery ────────────
                '2→3' => [
                    ['key' => 'certified_resource_count', 'label' => 'Certified Resources', 'threshold' => 3, 'unit' => 'count', 'description' => 'At least 3 active certifications across the team'],
                    ['key' => 'app_blueprints_completed', 'label' => 'Product Blueprint', 'threshold' => 1, 'unit' => 'count', 'description' => 'At least 1 product-specific blueprint completed'],
                    ['key' => 'open_opportunity_count', 'label' => 'Assigned Engagement', 'threshold' => 1, 'unit' => 'count', 'description' => 'At least 1 Anaplan-sourced engagement assigned'],
                ],
                // ── Supervised Delivery → Proven ────────────────────
                '3→4' => [
                    ['key' => 'completed_project_count', 'label' => 'Reviewed Project Passed', 'threshold' => 1, 'unit' => 'count', 'description' => 'At least 1 project passed Anaplan SA delivery review and sign-off'],
                    ['key' => 'avg_csat', 'label' => 'Delivery Quality', 'threshold' => 4.0, 'unit' => 'count', 'description' => 'CSAT at least 4.0 on reviewed project'],
                    ['key' => 'certified_resource_count', 'label' => 'Certified Resources', 'threshold' => 4, 'unit' => 'count', 'description' => 'At least 4 active certifications (depth over breadth)'],
                    ['key' => 'health_score', 'label' => 'Health Score', 'threshold' => 30, 'unit' => 'count', 'description' => 'Overall scorecard score at least 30'],
                ],
                // ── Proven → Trusted Expert ──────────────────────────
                '4→5' => [
                    ['key' => 'completed_project_count', 'label' => 'Completed Projects', 'threshold' => 4, 'unit' => 'count', 'description' => 'At least 4 completed projects with strong delivery track record'],
                    ['key' => 'avg_csat', 'label' => 'Delivery Quality', 'threshold' => 4.3, 'unit' => 'count', 'description' => 'Average CSAT at least 4.3 across all projects'],
                    ['key' => 'ontime_delivery_pct', 'label' => 'On-Time Delivery', 'threshold' => 80, 'unit' => 'percentage', 'description' => 'At least 80% of projects delivered on-time'],
                    ['key' => 'referenceable_pct', 'label' => 'Referenceable Projects', 'threshold' => 60, 'unit' => 'percentage', 'description' => 'At least 60% of completed projects are referenceable'],
                    ['key' => 'certified_resource_count', 'label' => 'Certified Resources', 'threshold' => 6, 'unit' => 'count', 'description' => 'At least 6 active certifications'],
                    ['key' => 'qbr_count_12m', 'label' => 'PSM Review Cadence', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 PSM reviews completed (trailing 12m)'],
                    ['key' => 'health_score', 'label' => 'Health Score', 'threshold' => 55, 'unit' => 'count', 'description' => 'Overall scorecard score at least 55'],
                ],
                // ── Trusted Expert → Elite Specialist ───────────────
                '5→6' => [
                    ['key' => 'completed_project_count', 'label' => 'Completed Projects', 'threshold' => 8, 'unit' => 'count', 'description' => 'At least 8 completed projects demonstrating consistent excellence'],
                    ['key' => 'avg_csat', 'label' => 'Delivery Quality', 'threshold' => 4.5, 'unit' => 'count', 'description' => 'Average CSAT at least 4.5 — consistently exceptional'],
                    ['key' => 'ontime_delivery_pct', 'label' => 'On-Time Delivery', 'threshold' => 85, 'unit' => 'percentage', 'description' => 'At least 85% of projects delivered on-time'],
                    ['key' => 'referenceable_pct', 'label' => 'Referenceable Projects', 'threshold' => 75, 'unit' => 'percentage', 'description' => 'At least 75% of completed projects are referenceable'],
                    ['key' => 'certified_resource_count', 'label' => 'Certified Resources', 'threshold' => 8, 'unit' => 'count', 'description' => 'At least 8 active certifications across the team'],
                    ['key' => 'product_area_count', 'label' => 'Niche Depth', 'threshold' => 2, 'unit' => 'count', 'description' => 'Deep expertise in at least 2 product areas'],
                    ['key' => 'health_score', 'label' => 'Health Score', 'threshold' => 75, 'unit' => 'count', 'description' => 'Overall scorecard score at least 75'],
                ],
            ],
        ],

        // =====================================================================
        //  APPLICATION BUILDER PARTNER
        //  ISV-style partners building custom apps on the Anaplan platform.
        //  Own full lifecycle: design, validation, GTM, support, enhancements.
        // =====================================================================
        'Application Builder Partner' => [
            'description' => 'Progression path for partners building custom applications on the Anaplan platform. These partners follow Anaplan app design specifications, submit applications for validation, provide field sales enablement, pricing, contracts, demos, L1/L2 support, and ongoing delivery of enhancements and bug fixes.',
            'is_default' => false,
            'stages' => [
                ['number' => 1, 'name' => 'Onboarded', 'description' => 'Partner agreement signed, app builder program enrolled, PSM assigned. Development sandbox provisioned, design specification documentation shared.', 'color' => '#6C757D', 'icon' => 'fal fa-file-signature'],
                ['number' => 2, 'name' => 'Building', 'description' => 'Application under active development. Partner following Anaplan design specs, building to platform standards. Regular design reviews with Anaplan engineering.', 'color' => '#0D6EFD', 'icon' => 'fal fa-code'],
                ['number' => 3, 'name' => 'Validated', 'description' => 'Application submitted and passed Anaplan technical validation. UX review complete, security audit passed, performance benchmarks met. Approved for marketplace listing.', 'color' => '#198754', 'icon' => 'fal fa-shield-check'],
                ['number' => 4, 'name' => 'Go-to-Market', 'description' => 'Application listed on marketplace. Sales materials, pricing, demo environments, and contracts finalized. Field sales and L1 support enablement delivered. Active selling motion underway.', 'color' => '#FFC107', 'icon' => 'fal fa-rocket'],
                ['number' => 5, 'name' => 'Scaling', 'description' => 'Proven field traction with multiple customers. L2 support and enhancement delivery operating smoothly. Revenue growing, expanding to new verticals or geos. Strategic co-sell relationship.', 'color' => '#DC3545', 'icon' => 'fal fa-chart-network'],
            ],
            'gates' => [
                '1→2' => [
                    ['key' => 'onboarding_tasks_pct', 'label' => 'Onboarding Tasks Complete', 'threshold' => 100, 'unit' => 'percentage', 'description' => 'All required onboarding tasks completed (agreement, NDA, program enrollment)'],
                    ['key' => 'contact_count', 'label' => 'Key Contacts Registered', 'threshold' => 3, 'unit' => 'count', 'description' => 'Product owner, technical lead, and support contact registered'],
                    ['key' => 'certified_mb_count', 'label' => 'Platform Certified Developers', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 developers with Model Builder certification'],
                    ['key' => 'core_blueprints_completed', 'label' => 'Design Spec Review', 'threshold' => 1, 'unit' => 'count', 'description' => 'App design specification blueprint reviewed and acknowledged'],
                    ['key' => 'salesforce_connected', 'label' => 'CRM Connected', 'threshold' => 1, 'unit' => 'boolean', 'description' => 'Partner Salesforce ID populated for deal tracking'],
                ],
                '2→3' => [
                    ['key' => 'certified_resource_count', 'label' => 'Certified Developers', 'threshold' => 3, 'unit' => 'count', 'description' => 'At least 3 certified resources on the development team'],
                    ['key' => 'app_blueprints_completed', 'label' => 'App Design Blueprint', 'threshold' => 1, 'unit' => 'count', 'description' => 'Application-specific design blueprint fully completed'],
                    ['key' => 'solution_count', 'label' => 'App Submitted for Validation', 'threshold' => 1, 'unit' => 'count', 'description' => 'At least 1 application submitted for Anaplan technical validation'],
                    ['key' => 'qbr_count_12m', 'label' => 'Design Review Cadence', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 design reviews completed with Anaplan engineering (trailing 12m)'],
                    ['key' => 'completed_project_count', 'label' => 'Validation Milestones', 'threshold' => 1, 'unit' => 'count', 'description' => 'UX review, security audit, and performance benchmarks passed'],
                    ['key' => 'health_score', 'label' => 'Program Health Score', 'threshold' => 30, 'unit' => 'count', 'description' => 'Overall program health score at least 30'],
                ],
                '3→4' => [
                    ['key' => 'marketplace_approved', 'label' => 'Marketplace Listing Approved', 'threshold' => 1, 'unit' => 'boolean', 'description' => 'Application approved and listed on Anaplan marketplace'],
                    ['key' => 'solution_count', 'label' => 'Sales Materials Package', 'threshold' => 1, 'unit' => 'count', 'description' => 'Complete sales kit: battle cards, ROI calculator, pricing sheet, demo script'],
                    ['key' => 'certified_resource_count', 'label' => 'Support Team Certified', 'threshold' => 4, 'unit' => 'count', 'description' => 'At least 4 certified resources covering dev, support, and sales engineering'],
                    ['key' => 'open_opportunity_count', 'label' => 'Initial Pipeline', 'threshold' => 2, 'unit' => 'count', 'description' => 'At least 2 qualified opportunities in pipeline'],
                    ['key' => 'pipeline_value', 'label' => 'Pipeline Value', 'threshold' => 250000, 'unit' => 'currency', 'description' => 'At least $250K in qualified pipeline'],
                    ['key' => 'contact_count', 'label' => 'Field Enablement Delivered', 'threshold' => 5, 'unit' => 'count', 'description' => 'At least 5 field contacts enabled (AEs, SEs trained on the application)'],
                    ['key' => 'health_score', 'label' => 'Program Health Score', 'threshold' => 50, 'unit' => 'count', 'description' => 'Overall program health score at least 50'],
                ],
                '4→5' => [
                    ['key' => 'won_opp_count_12m', 'label' => 'Closed-Won Customers', 'threshold' => 3, 'unit' => 'count', 'description' => 'At least 3 closed-won customer deals (trailing 12m)'],
                    ['key' => 'revenue_attainment_pct', 'label' => 'Revenue Achievement', 'threshold' => 80, 'unit' => 'percentage', 'description' => 'At least 80% of annual app revenue target achieved'],
                    ['key' => 'pipeline_value', 'label' => 'Active Pipeline', 'threshold' => 1000000, 'unit' => 'currency', 'description' => 'At least $1M in active pipeline'],
                    ['key' => 'pipeline_coverage', 'label' => 'Pipeline Coverage', 'threshold' => 2.0, 'unit' => 'ratio', 'description' => 'Pipeline / Remaining target at least 2.0x'],
                    ['key' => 'avg_csat', 'label' => 'Customer Satisfaction', 'threshold' => 4.0, 'unit' => 'count', 'description' => 'Average CSAT from deployed customers at least 4.0'],
                    ['key' => 'certified_resource_count', 'label' => 'Full Support Team', 'threshold' => 8, 'unit' => 'count', 'description' => 'At least 8 certified resources across dev, L1 support, L2 support, and sales engineering'],
                    ['key' => 'ontime_delivery_pct', 'label' => 'SLA Compliance', 'threshold' => 90, 'unit' => 'percentage', 'description' => 'At least 90% of L2 support tickets resolved within SLA'],
                    ['key' => 'product_area_count', 'label' => 'Vertical/Geo Expansion', 'threshold' => 2, 'unit' => 'count', 'description' => 'Application deployed across at least 2 verticals or geographies'],
                    ['key' => 'health_score', 'label' => 'Program Health Score', 'threshold' => 75, 'unit' => 'count', 'description' => 'Overall program health score at least 75'],
                ],
            ],
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding partner paths, stages, and gates...');

        $admin = User::whereRelation('role', 'slug', 'admin')->first() ?? User::first();

        $pathCount = 0;
        $stageCount = 0;
        $gateCount = 0;

        foreach (self::PATHS as $pathName => $config) {
            $path = PartnerPath::create([
                'name' => $pathName,
                'description' => $config['description'],
                'is_default' => $config['is_default'],
                'is_active' => true,
                'created_by' => $admin->id,
            ]);
            $pathCount++;

            $stageModels = [];
            foreach ($config['stages'] as $i => $stage) {
                $stageModels[$stage['number']] = PartnerPathStage::create([
                    'partner_path_id' => $path->id,
                    'stage_number' => $stage['number'],
                    'name' => $stage['name'],
                    'description' => $stage['description'],
                    'color' => $stage['color'],
                    'icon' => $stage['icon'],
                    'sort_order' => $i + 1,
                ]);
                $stageCount++;
            }

            foreach ($config['gates'] as $transition => $gates) {
                [$fromNum, $toNum] = explode('→', $transition);
                $fromStage = $stageModels[(int) $fromNum];
                $toStage = $stageModels[(int) $toNum];

                foreach ($gates as $sortOrder => $gate) {
                    PartnerPathGate::create([
                        'partner_path_id' => $path->id,
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

        $this->command->info("  Created {$pathCount} partner paths, {$stageCount} stages, {$gateCount} gates.");
    }
}
