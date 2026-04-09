<?php

namespace Database\Seeders;

use App\Models\PartnerPath;
use App\Models\TaskTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $path = PartnerPath::where('is_default', true)->first();
        if (! $path) {
            return;
        }

        $adminId = (User::whereRelation('role', 'slug', 'admin')->first() ?? User::first())->id;

        $stages = $path->stages()->pluck('id', 'stage_number');

        // ─── Stage 1: Onboarded Tasks ───────────────────────────

        if ($stages->has(1)) {
            $onboarded = [
                // Account tasks
                ['category' => 'account', 'requirement_level' => 'required', 'title' => 'Connect Salesforce Account', 'description' => 'Link the partner record to their Salesforce account by entering the Salesforce ID.', 'completion_criteria' => 'Partner salesforce_id field is populated.', 'effort_size' => 'small', 'default_owner' => 'psm', 'due_days_offset' => 14, 'auto_verify_rule' => 'partner.salesforce_id IS NOT NULL', 'evidence_type' => 'system_verified'],
                ['category' => 'account', 'requirement_level' => 'required', 'title' => 'Register Key Contacts', 'description' => 'Add at least 3 key contacts from the partner organization including partner manager, sales lead, and delivery lead.', 'completion_criteria' => 'At least 3 contacts created for this partner.', 'effort_size' => 'small', 'default_owner' => 'joint', 'due_days_offset' => 21, 'auto_verify_rule' => 'contacts.count >= 3', 'evidence_type' => 'system_verified'],
                ['category' => 'account', 'requirement_level' => 'required', 'title' => 'Complete Partner Profile', 'description' => 'Fill in all partner details: website, industry, geography, region.', 'completion_criteria' => 'Partner has website, industry, geography, and region fields populated.', 'effort_size' => 'small', 'default_owner' => 'psm', 'due_days_offset' => 7, 'evidence_type' => 'psm_attestation'],
                ['category' => 'account', 'requirement_level' => 'required', 'title' => 'Complete Marketplace Profile', 'description' => 'Submit the partner marketplace profile with headline, company description, and at least one marketplace entry for directory listing.', 'completion_criteria' => 'Marketplace profile approved by admin.', 'effort_size' => 'medium', 'default_owner' => 'joint', 'due_days_offset' => 30, 'auto_verify_rule' => "partner.marketplace_status = 'approved'", 'evidence_type' => 'system_verified'],
                ['category' => 'account', 'requirement_level' => 'required', 'title' => 'Schedule Kickoff Meeting', 'description' => 'Schedule and conduct an initial kickoff meeting with the partner to review the partnership program, expectations, and path progression.', 'completion_criteria' => 'Kickoff meeting conducted and documented as a partner event.', 'effort_size' => 'medium', 'default_owner' => 'psm', 'due_days_offset' => 14, 'evidence_type' => 'psm_attestation'],
                ['category' => 'account', 'requirement_level' => 'recommended', 'title' => 'Set Annual Revenue Goals', 'description' => 'Co-create annual sourced and influenced NNACV targets with the partner.', 'completion_criteria' => 'Partner goals created in the system for the current fiscal year.', 'effort_size' => 'medium', 'default_owner' => 'joint', 'due_days_offset' => 30, 'evidence_type' => 'psm_attestation'],

                // Enablement tasks
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Assign Core Enablement Blueprint', 'description' => 'Assign the core platform enablement blueprint to the partner for the delivery role.', 'completion_criteria' => 'At least 1 blueprint assignment exists for this partner.', 'effort_size' => 'small', 'default_owner' => 'psm', 'due_days_offset' => 14, 'evidence_type' => 'system_verified'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Certify 2 Model Builders', 'description' => 'Ensure at least 2 partner resources achieve Model Builder certification.', 'completion_criteria' => 'At least 2 active Model Builder certifications for partner resources.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 90, 'auto_verify_rule' => 'certifications.active.count >= 2', 'evidence_type' => 'system_verified'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Certify 1 Solution Architect', 'description' => 'Ensure at least 1 partner resource achieves Solution Architect certification.', 'completion_criteria' => 'At least 1 active Solution Architect certification for a partner resource.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 90, 'evidence_type' => 'system_verified'],

                // Program tasks
                ['category' => 'program', 'requirement_level' => 'recommended', 'title' => 'Review Partnership Agreement', 'description' => 'Review the partnership agreement and ensure all commercial terms are in place.', 'completion_criteria' => 'Partnership agreement reviewed and documented.', 'effort_size' => 'small', 'default_owner' => 'psm', 'due_days_offset' => 30, 'evidence_type' => 'psm_attestation'],
            ];

            foreach ($onboarded as $i => $task) {
                TaskTemplate::create(array_merge($task, [
                    'partner_path_id' => $path->id,
                    'stage_id' => $stages[1],
                    'sort_order' => $i,
                    'is_active' => true,
                    'created_by' => $adminId,
                ]));
            }
        }

        // ─── Stage 2: Enabled Tasks ─────────────────────────────

        if ($stages->has(2)) {
            $enabled = [
                ['category' => 'business_development', 'requirement_level' => 'required', 'title' => 'Build Initial Pipeline', 'description' => 'Work with partner to identify and register at least 3 opportunities totaling $500K+.', 'completion_criteria' => 'At least 3 open opportunities with total value >= $500K.', 'effort_size' => 'large', 'default_owner' => 'joint', 'due_days_offset' => 90, 'evidence_type' => 'system_verified'],
                ['category' => 'business_development', 'requirement_level' => 'required', 'title' => 'Close First Deal', 'description' => 'Guide partner through their first Closed-Won opportunity.', 'completion_criteria' => 'At least 1 Closed-Won opportunity in trailing 12 months.', 'effort_size' => 'large', 'default_owner' => 'joint', 'due_days_offset' => 180, 'evidence_type' => 'system_verified'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Complete Application Blueprint', 'description' => 'Ensure partner resources complete at least 1 application-specific enablement blueprint (e.g., FP&A, Supply Chain).', 'completion_criteria' => 'At least 1 application blueprint fully completed.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 120, 'evidence_type' => 'system_verified'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Expand Certified Resource Pool', 'description' => 'Grow certified resources to at least 4 (MB + SA combined).', 'completion_criteria' => 'At least 4 active certifications (MB + SA).', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 120, 'evidence_type' => 'system_verified'],
                ['category' => 'delivery', 'requirement_level' => 'required', 'title' => 'Complete First Project', 'description' => 'Successfully deliver the first customer implementation project.', 'completion_criteria' => 'At least 1 project with status = completed.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 180, 'evidence_type' => 'system_verified'],
                ['category' => 'account', 'requirement_level' => 'required', 'title' => 'Conduct 2 QBRs', 'description' => 'Conduct at least 2 Quarterly Business Reviews within 12 months.', 'completion_criteria' => 'At least 2 QBR events completed in trailing 12 months.', 'effort_size' => 'medium', 'default_owner' => 'psm', 'due_days_offset' => 180, 'evidence_type' => 'psm_attestation'],
                ['category' => 'account', 'requirement_level' => 'recommended', 'title' => 'Joint Account Planning', 'description' => 'Identify top 5 target accounts and create joint account plans.', 'completion_criteria' => 'Joint account plans documented and shared.', 'effort_size' => 'medium', 'default_owner' => 'joint', 'due_days_offset' => 60, 'evidence_type' => 'psm_attestation'],
            ];

            foreach ($enabled as $i => $task) {
                TaskTemplate::create(array_merge($task, [
                    'partner_path_id' => $path->id,
                    'stage_id' => $stages[2],
                    'sort_order' => $i,
                    'is_active' => true,
                    'created_by' => $adminId,
                ]));
            }
        }

        // ─── Stage 3: Performing Tasks ──────────────────────────

        if ($stages->has(3)) {
            $performing = [
                ['category' => 'business_development', 'requirement_level' => 'required', 'title' => 'Achieve 80% Revenue Target', 'description' => 'Drive partner to achieve at least 80% of annual NNACV target.', 'completion_criteria' => 'Revenue attainment at least 80% of annual target.', 'effort_size' => 'large', 'default_owner' => 'joint', 'evidence_type' => 'system_verified'],
                ['category' => 'business_development', 'requirement_level' => 'required', 'title' => 'Maintain 2x Pipeline Coverage', 'description' => 'Ensure pipeline coverage ratio is at least 2x against remaining target.', 'completion_criteria' => 'Pipeline / Remaining target >= 2.0x.', 'effort_size' => 'large', 'default_owner' => 'joint', 'evidence_type' => 'system_verified'],
                ['category' => 'delivery', 'requirement_level' => 'required', 'title' => 'Achieve 75% On-Time Delivery', 'description' => 'At least 75% of projects delivered on or before planned end date.', 'completion_criteria' => 'On-time delivery rate >= 75%.', 'effort_size' => 'large', 'default_owner' => 'partner', 'evidence_type' => 'system_verified'],
                ['category' => 'delivery', 'requirement_level' => 'required', 'title' => 'Achieve 4.0 Average CSAT', 'description' => 'Average customer satisfaction across all projects at least 4.0/5.0.', 'completion_criteria' => 'Average CSAT >= 4.0.', 'effort_size' => 'large', 'default_owner' => 'partner', 'evidence_type' => 'system_verified'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Expand to 2+ Product Areas', 'description' => 'Develop certifications in at least 2 distinct product areas.', 'completion_criteria' => 'Certifications exist in at least 2 different product areas.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 180, 'evidence_type' => 'system_verified'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Grow to 8 Certified Resources', 'description' => 'Expand certified resource pool to at least 8 active MB + SA certifications.', 'completion_criteria' => 'At least 8 active certifications.', 'effort_size' => 'large', 'default_owner' => 'partner', 'evidence_type' => 'system_verified'],
                ['category' => 'account', 'requirement_level' => 'required', 'title' => 'Maintain Quarterly QBR Cadence', 'description' => 'Conduct QBRs every quarter with documented outcomes and action items.', 'completion_criteria' => 'QBR completed each quarter.', 'effort_size' => 'medium', 'default_owner' => 'psm', 'evidence_type' => 'psm_attestation'],
                ['category' => 'solution', 'requirement_level' => 'recommended', 'title' => 'Develop Repeatable Solution', 'description' => 'Begin building a repeatable solution or accelerator for a specific product area or industry.', 'completion_criteria' => 'Solution registered in Solution Lifecycle tracker.', 'effort_size' => 'large', 'default_owner' => 'partner', 'evidence_type' => 'psm_attestation'],
            ];

            foreach ($performing as $i => $task) {
                TaskTemplate::create(array_merge($task, [
                    'partner_path_id' => $path->id,
                    'stage_id' => $stages[3],
                    'sort_order' => $i,
                    'is_active' => true,
                    'created_by' => $adminId,
                ]));
            }
        }

        $this->seedAppBuilderTemplates($adminId);
    }

    /**
     * Task templates for the Application Builder Partner path.
     */
    private function seedAppBuilderTemplates(int $adminId): void
    {
        $path = PartnerPath::where('name', 'Application Builder Partner')->first();
        if (! $path) {
            return;
        }

        $stages = $path->stages()->pluck('id', 'stage_number');

        // ─── Stage 1: Onboarded ─────────────────────────────────
        if ($stages->has(1)) {
            $tasks = [
                ['category' => 'account', 'requirement_level' => 'required', 'title' => 'Sign App Builder Program Agreement', 'description' => 'Execute the Application Builder addendum covering IP ownership, revenue share, support obligations, and SLAs.', 'effort_size' => 'medium', 'default_owner' => 'psm', 'due_days_offset' => 14, 'evidence_type' => 'file_upload'],
                ['category' => 'account', 'requirement_level' => 'required', 'title' => 'Register Product Owner & Technical Lead', 'description' => 'Register the partner product owner, technical lead, and designated support contact in Partner Central.', 'effort_size' => 'small', 'default_owner' => 'joint', 'due_days_offset' => 14, 'evidence_type' => 'system_verified'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Provision Development Sandbox', 'description' => 'Request and provision an Anaplan development sandbox for the app build team.', 'effort_size' => 'small', 'default_owner' => 'psm', 'due_days_offset' => 7, 'evidence_type' => 'psm_attestation'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Complete App Design Specification Review', 'description' => 'Review Anaplan app design specifications, UX guidelines, API standards, and security requirements with the partner team.', 'effort_size' => 'medium', 'default_owner' => 'joint', 'due_days_offset' => 21, 'evidence_type' => 'psm_attestation'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Certify 2 Developers as Model Builders', 'description' => 'Ensure at least 2 developers on the app team obtain Model Builder certification.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 60, 'evidence_type' => 'system_verified'],
                ['category' => 'program', 'requirement_level' => 'required', 'title' => 'Submit App Concept Brief', 'description' => 'Submit a one-page concept brief describing the application, target market, differentiation, and estimated timeline.', 'effort_size' => 'medium', 'default_owner' => 'partner', 'due_days_offset' => 30, 'evidence_type' => 'file_upload'],
            ];

            foreach ($tasks as $i => $task) {
                TaskTemplate::create(array_merge($task, [
                    'partner_path_id' => $path->id,
                    'stage_id' => $stages[1],
                    'sort_order' => $i,
                    'is_active' => true,
                    'created_by' => $adminId,
                ]));
            }
        }

        // ─── Stage 2: Building ──────────────────────────────────
        if ($stages->has(2)) {
            $tasks = [
                ['category' => 'solution', 'requirement_level' => 'required', 'title' => 'Submit App Architecture Document', 'description' => 'Submit detailed application architecture including data model, module design, integrations, and UX wireframes for Anaplan engineering review.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 30, 'evidence_type' => 'file_upload'],
                ['category' => 'solution', 'requirement_level' => 'required', 'title' => 'Complete Bi-Weekly Design Reviews', 'description' => 'Participate in bi-weekly design review sessions with Anaplan engineering team to ensure platform compliance.', 'effort_size' => 'medium', 'default_owner' => 'joint', 'evidence_type' => 'psm_attestation'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Complete App-Specific Blueprint', 'description' => 'Finish the application builder enablement blueprint covering Anaplan APIs, extension framework, and deployment patterns.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 60, 'evidence_type' => 'system_verified'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Grow Dev Team to 3 Certified', 'description' => 'Expand certified development team to at least 3 resources.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 90, 'evidence_type' => 'system_verified'],
                ['category' => 'solution', 'requirement_level' => 'required', 'title' => 'Submit Application for Validation', 'description' => 'Submit the application build to Anaplan for technical validation including UX review, security audit, and performance benchmarking.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 120, 'evidence_type' => 'psm_attestation'],
                ['category' => 'program', 'requirement_level' => 'recommended', 'title' => 'Draft Support Runbook', 'description' => 'Begin drafting the L1/L2 support runbook including escalation paths, known issues, and troubleshooting guides.', 'effort_size' => 'medium', 'default_owner' => 'partner', 'due_days_offset' => 90, 'evidence_type' => 'file_upload'],
            ];

            foreach ($tasks as $i => $task) {
                TaskTemplate::create(array_merge($task, [
                    'partner_path_id' => $path->id,
                    'stage_id' => $stages[2],
                    'sort_order' => $i,
                    'is_active' => true,
                    'created_by' => $adminId,
                ]));
            }
        }

        // ─── Stage 3: Validated ─────────────────────────────────
        if ($stages->has(3)) {
            $tasks = [
                ['category' => 'solution', 'requirement_level' => 'required', 'title' => 'Complete Marketplace Profile & Listing', 'description' => 'Submit full marketplace listing with app description, screenshots, pricing tiers, and target customer profile.', 'effort_size' => 'medium', 'default_owner' => 'partner', 'due_days_offset' => 30, 'evidence_type' => 'system_verified'],
                ['category' => 'business_development', 'requirement_level' => 'required', 'title' => 'Create Sales Enablement Package', 'description' => 'Deliver complete sales kit: battle cards, ROI calculator, competitive positioning, pricing sheet, and standard demo script.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 45, 'evidence_type' => 'file_upload'],
                ['category' => 'business_development', 'requirement_level' => 'required', 'title' => 'Build Demo Environment', 'description' => 'Create and maintain a dedicated demo environment with sample data for field sales presentations.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 30, 'evidence_type' => 'psm_attestation'],
                ['category' => 'business_development', 'requirement_level' => 'required', 'title' => 'Finalize Pricing & Contract Templates', 'description' => 'Finalize customer pricing tiers, licensing terms, and standard contract templates approved by Anaplan legal.', 'effort_size' => 'medium', 'default_owner' => 'joint', 'due_days_offset' => 45, 'evidence_type' => 'file_upload'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Deliver Field Sales Enablement', 'description' => 'Conduct training sessions for Anaplan AEs and SEs covering the application value prop, demo flow, qualification criteria, and objection handling.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 60, 'evidence_type' => 'psm_attestation'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Deliver L1 Support Enablement', 'description' => 'Train Anaplan L1 support team on the application: common issues, triage procedures, and escalation to partner L2.', 'effort_size' => 'large', 'default_owner' => 'partner', 'due_days_offset' => 60, 'evidence_type' => 'psm_attestation'],
                ['category' => 'program', 'requirement_level' => 'required', 'title' => 'Finalize L2 Support & SLA Agreement', 'description' => 'Sign off on L2 support SLAs covering response times, bug fix timelines, enhancement request process, and escalation procedures.', 'effort_size' => 'medium', 'default_owner' => 'joint', 'due_days_offset' => 30, 'evidence_type' => 'file_upload'],
                ['category' => 'business_development', 'requirement_level' => 'required', 'title' => 'Build Initial Pipeline', 'description' => 'Work with field sales to identify and register at least 2 qualified opportunities.', 'effort_size' => 'large', 'default_owner' => 'joint', 'due_days_offset' => 90, 'evidence_type' => 'system_verified'],
            ];

            foreach ($tasks as $i => $task) {
                TaskTemplate::create(array_merge($task, [
                    'partner_path_id' => $path->id,
                    'stage_id' => $stages[3],
                    'sort_order' => $i,
                    'is_active' => true,
                    'created_by' => $adminId,
                ]));
            }
        }

        // ─── Stage 4: Go-to-Market ─────────────────────────────
        if ($stages->has(4)) {
            $tasks = [
                ['category' => 'business_development', 'requirement_level' => 'required', 'title' => 'Close 3 Customer Deals', 'description' => 'Drive at least 3 closed-won customer deals through the field co-sell motion.', 'effort_size' => 'large', 'default_owner' => 'joint', 'evidence_type' => 'system_verified'],
                ['category' => 'business_development', 'requirement_level' => 'required', 'title' => 'Build Pipeline to $1M', 'description' => 'Grow active pipeline to at least $1M with qualified opportunities across multiple accounts.', 'effort_size' => 'large', 'default_owner' => 'joint', 'evidence_type' => 'system_verified'],
                ['category' => 'delivery', 'requirement_level' => 'required', 'title' => 'Achieve 4.0 Customer Satisfaction', 'description' => 'Maintain average CSAT of at least 4.0 across all deployed customers.', 'effort_size' => 'large', 'default_owner' => 'partner', 'evidence_type' => 'system_verified'],
                ['category' => 'delivery', 'requirement_level' => 'required', 'title' => 'Meet 90% SLA Compliance', 'description' => 'Resolve at least 90% of L2 support tickets within agreed SLA timeframes.', 'effort_size' => 'large', 'default_owner' => 'partner', 'evidence_type' => 'psm_attestation'],
                ['category' => 'delivery', 'requirement_level' => 'required', 'title' => 'Deliver Quarterly Enhancement Release', 'description' => 'Ship at least 1 enhancement release per quarter with release notes and customer communication.', 'effort_size' => 'large', 'default_owner' => 'partner', 'evidence_type' => 'psm_attestation'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Grow Support Team to 8 Certified', 'description' => 'Expand certified team to at least 8 resources covering development, L2 support, and sales engineering.', 'effort_size' => 'large', 'default_owner' => 'partner', 'evidence_type' => 'system_verified'],
                ['category' => 'enablement', 'requirement_level' => 'required', 'title' => 'Refresh Field Sales Enablement', 'description' => 'Update field sales training with new features, customer case studies, and updated competitive positioning.', 'effort_size' => 'medium', 'default_owner' => 'partner', 'evidence_type' => 'psm_attestation'],
                ['category' => 'program', 'requirement_level' => 'recommended', 'title' => 'Expand to Second Vertical or Geography', 'description' => 'Identify and begin targeting a second vertical market or geography for the application.', 'effort_size' => 'large', 'default_owner' => 'joint', 'evidence_type' => 'psm_attestation'],
            ];

            foreach ($tasks as $i => $task) {
                TaskTemplate::create(array_merge($task, [
                    'partner_path_id' => $path->id,
                    'stage_id' => $stages[4],
                    'sort_order' => $i,
                    'is_active' => true,
                    'created_by' => $adminId,
                ]));
            }
        }
    }
}
