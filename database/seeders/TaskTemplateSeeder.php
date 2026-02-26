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
    }
}
