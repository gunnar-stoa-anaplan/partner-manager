<?php

namespace Database\Seeders;

use App\Models\PartnerPath;
use App\Models\TaskTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskTemplateSeeder extends Seeder
{
    /**
     * Task templates keyed by path name → stage number → list of templates.
     *
     * @var array<string, array<int, list<array>>>
     */
    private const TEMPLATES = [
        // =====================================================================
        //  STRATEGIC IMPLEMENTATION PARTNER
        // =====================================================================
        'Strategic Implementation Partner' => [
            // ─── Stage 1: Onboarded ─────────────────────────────
            1 => [
                ['category' => 'account', 'level' => 'required', 'title' => 'Connect Salesforce Account', 'description' => 'Link partner record to their Salesforce account.', 'effort' => 'small', 'owner' => 'psm', 'days' => 14, 'evidence' => 'system_verified'],
                ['category' => 'account', 'level' => 'required', 'title' => 'Register Key Contacts (5+)', 'description' => 'Add practice lead, sales lead, pre-sales lead, delivery lead, and support contact.', 'effort' => 'small', 'owner' => 'joint', 'days' => 21, 'evidence' => 'system_verified'],
                ['category' => 'account', 'level' => 'required', 'title' => 'Complete Partner Profile', 'description' => 'Fill in all partner details: website, industry, geography, region.', 'effort' => 'small', 'owner' => 'psm', 'days' => 7, 'evidence' => 'psm_attestation'],
                ['category' => 'account', 'level' => 'required', 'title' => 'Schedule Kickoff Meeting', 'description' => 'Conduct initial kickoff reviewing partnership program, expectations, and path progression.', 'effort' => 'medium', 'owner' => 'psm', 'days' => 14, 'evidence' => 'psm_attestation'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Assign Core Enablement Blueprint', 'description' => 'Assign the core platform enablement blueprint to the partner delivery team.', 'effort' => 'small', 'owner' => 'psm', 'days' => 14, 'evidence' => 'system_verified'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Certify 2 Model Builders', 'description' => 'Ensure at least 2 partner resources achieve Model Builder certification.', 'effort' => 'large', 'owner' => 'partner', 'days' => 90, 'evidence' => 'system_verified'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Certify 1 Solution Architect', 'description' => 'Ensure at least 1 partner resource achieves Solution Architect certification.', 'effort' => 'large', 'owner' => 'partner', 'days' => 90, 'evidence' => 'system_verified'],
                ['category' => 'program', 'level' => 'recommended', 'title' => 'Set Annual Revenue Goals', 'description' => 'Co-create annual sourced and influenced NNACV targets.', 'effort' => 'medium', 'owner' => 'joint', 'days' => 30, 'evidence' => 'psm_attestation'],
                ['category' => 'account', 'level' => 'required', 'title' => 'Complete Marketplace Profile', 'description' => 'Submit marketplace profile with headline, description, and at least one listing.', 'effort' => 'medium', 'owner' => 'joint', 'days' => 30, 'evidence' => 'system_verified'],
            ],
            // ─── Stage 2: Enabled ───────────────────────────────
            2 => [
                ['category' => 'business_development', 'level' => 'required', 'title' => 'Activate Co-Sell Motion', 'description' => 'Establish joint sales cadence with Anaplan AEs. Schedule weekly pipeline reviews.', 'effort' => 'medium', 'owner' => 'joint', 'days' => 30, 'evidence' => 'psm_attestation'],
                ['category' => 'business_development', 'level' => 'required', 'title' => 'Build Pipeline to $500K', 'description' => 'Identify and register at least 3 co-sell opportunities totaling $500K+.', 'effort' => 'large', 'owner' => 'joint', 'days' => 90, 'evidence' => 'system_verified'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Ramp Sales & Pre-Sales Team', 'description' => 'Enable dedicated partner sales and pre-sales resources on Anaplan value prop, demo environments, and qualification.', 'effort' => 'large', 'owner' => 'partner', 'days' => 60, 'evidence' => 'psm_attestation'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Complete Product Blueprint', 'description' => 'Finish at least 1 product-specific enablement blueprint (IFP, SCM, etc.).', 'effort' => 'large', 'owner' => 'partner', 'days' => 120, 'evidence' => 'system_verified'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Grow to 4 Certified Resources', 'description' => 'Expand certified resources to at least 4 (MB + SA combined).', 'effort' => 'large', 'owner' => 'partner', 'days' => 120, 'evidence' => 'system_verified'],
                ['category' => 'account', 'level' => 'required', 'title' => 'Conduct First QBR', 'description' => 'Complete first quarterly business review with documented outcomes.', 'effort' => 'medium', 'owner' => 'psm', 'days' => 90, 'evidence' => 'psm_attestation'],
                ['category' => 'account', 'level' => 'recommended', 'title' => 'Joint Account Planning', 'description' => 'Identify top 5 target accounts and create joint account plans.', 'effort' => 'medium', 'owner' => 'joint', 'days' => 60, 'evidence' => 'psm_attestation'],
            ],
            // ─── Stage 3: Supervised Delivery ───────────────────
            //
            // This is the critical stage where Anaplan reviews the partner's
            // actual project work. Projects now start from pre-built applications,
            // so the focus is on whether the partner makes good design decisions,
            // follows app architecture, and avoids unnecessary customization.
            //
            // The partner must pass 2 projects through this review process
            // with no major findings before advancing to independent delivery.
            //
            3 => [
                // ── Phase 1: Project Kickoff & Planning ──────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Confirm Pre-Built App Selection', 'description' => 'Validate that the correct pre-built application has been selected for this client engagement. Anaplan SA confirms the app matches the client use case, industry, and scope. Document any known limitations.', 'effort' => 'small', 'owner' => 'joint', 'days' => 7, 'evidence' => 'psm_attestation'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Complete Client Discovery & Requirements', 'description' => 'Document client business requirements mapped against the pre-built app capabilities. Identify which requirements are met out-of-the-box, which require configuration, and which require extension. Submit to Anaplan SA for review.', 'effort' => 'large', 'owner' => 'partner', 'days' => 14, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit Gap-Fit Analysis', 'description' => 'Formal gap-fit document comparing pre-built app to client needs. For each gap: classify as configuration vs. extension vs. out-of-scope. Include business justification for every proposed extension. PSM and Anaplan SA must sign off before work begins.', 'effort' => 'large', 'owner' => 'partner', 'days' => 14, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit Sprint / Delivery Plan', 'description' => 'Detailed delivery plan with sprint breakdown, milestones, resource assignments, and timeline. Must follow Anaplan delivery methodology phases: Discover, Design, Build, Validate, Deploy. Include risk register and mitigation plan.', 'effort' => 'medium', 'owner' => 'partner', 'days' => 14, 'evidence' => 'file_upload'],

                // ── Phase 2: Design Review ───────────────────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit Model Design Document', 'description' => 'Detailed model design showing module structure, list hierarchy, line item definitions, data flow, and calculation logic. Must clearly show what is inherited from the pre-built app vs. what is new/modified. Anaplan SA reviews for platform best practices and app compliance.', 'effort' => 'large', 'owner' => 'partner', 'days' => 21, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit Extension Requirements with Justification', 'description' => 'For every proposed extension beyond the pre-built app: document the business requirement, why configuration cannot solve it, the proposed technical approach, and impact on upgrade path. Anaplan SA reviews each extension for necessity and design quality.', 'effort' => 'large', 'owner' => 'partner', 'days' => 21, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Data Integration Design Review', 'description' => 'Document all planned integrations: source systems, data volumes, frequency, transformation logic, and error handling. Must follow Anaplan integration best practices. Anaplan SA reviews for performance impact and platform compliance.', 'effort' => 'medium', 'owner' => 'partner', 'days' => 21, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'UX / Dashboard Design Review', 'description' => 'Submit dashboard layouts, user navigation flows, and end-user experience design. Must follow Anaplan UX guidelines for the pre-built app. Anaplan SA reviews for usability, performance (avoid overly complex views), and consistency with app standards.', 'effort' => 'medium', 'owner' => 'partner', 'days' => 21, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Anaplan SA Design Approval', 'description' => 'Formal design approval checkpoint. Anaplan SA reviews all submitted design artifacts and provides written approval or a list of required changes. No build work should proceed until design approval is obtained.', 'effort' => 'medium', 'owner' => 'joint', 'evidence' => 'psm_attestation'],

                // ── Phase 3: Build & Quality Checkpoints ─────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Mid-Sprint Model Quality Review', 'description' => 'Anaplan SA conducts an in-model review during the build phase. Checks: naming conventions, module organization, formula efficiency, list management, version/time settings, and adherence to the approved design. Provides written feedback.', 'effort' => 'large', 'owner' => 'joint', 'evidence' => 'psm_attestation'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Mid-Project Checkpoint', 'description' => 'Formal mid-project review covering: sprint progress vs. plan, scope changes and their justification, risks and blockers, resource utilization, and any deviations from the approved design. Joint review between partner PM, Anaplan SA, and PSM.', 'effort' => 'medium', 'owner' => 'joint', 'evidence' => 'psm_attestation'],
                ['category' => 'delivery', 'level' => 'recommended', 'title' => 'Performance & Scalability Assessment', 'description' => 'Test model performance with representative data volumes. Check calculation times, import/export performance, and dashboard load times. Anaplan SA reviews against platform performance benchmarks. Identify and resolve any bottlenecks before UAT.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'file_upload'],

                // ── Phase 4: Testing & Validation ────────────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit Test Plan & UAT Scripts', 'description' => 'Formal test plan covering unit testing, integration testing, regression testing, and UAT. Include test scripts, expected results, and sign-off criteria. Anaplan SA reviews test coverage — particularly around pre-built app functionality that must still work after extensions.', 'effort' => 'large', 'owner' => 'partner', 'days' => 14, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit UAT Results & Defect Log', 'description' => 'Document UAT execution results, client sign-off status, open defects with severity, and remediation plan. All critical and high defects must be resolved before go-live. Share with Anaplan SA.', 'effort' => 'medium', 'owner' => 'partner', 'evidence' => 'file_upload'],

                // ── Phase 5: Go-Live & Handoff ───────────────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Go-Live Readiness Review', 'description' => 'Comprehensive go-live assessment: cutover plan with rollback procedure, data migration validation, end-user training completion, support handoff documentation, monitoring plan for first 2 weeks. Both Anaplan SA and PSM must approve.', 'effort' => 'large', 'owner' => 'joint', 'evidence' => 'psm_attestation'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit End-User Training Materials', 'description' => 'Provide client-facing training materials: user guides, quick reference cards, recorded walkthroughs. Must cover both standard pre-built app workflows and any custom extensions.', 'effort' => 'medium', 'owner' => 'partner', 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit Support Handoff Documentation', 'description' => 'Document for ongoing support: model architecture overview, known issues, admin procedures, common troubleshooting steps, escalation contacts. Must be sufficient for L1 support without partner involvement.', 'effort' => 'medium', 'owner' => 'partner', 'evidence' => 'file_upload'],

                // ── Phase 6: Retrospective & Scorecard ───────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Post-Project Retrospective', 'description' => 'Joint retrospective between partner, Anaplan SA, and PSM. Review: what went well, what could improve, design decisions that should be replicated or avoided, client feedback. Document actionable lessons learned.', 'effort' => 'medium', 'owner' => 'joint', 'days' => 14, 'evidence' => 'psm_attestation'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Anaplan SA Delivery Scorecard', 'description' => 'Anaplan SA completes a formal delivery scorecard rating: design compliance, extension discipline, model quality, delivery execution, client satisfaction, and methodology adherence. Must score "meets expectations" or above in all areas to count toward supervised delivery graduation.', 'effort' => 'medium', 'owner' => 'joint', 'days' => 14, 'evidence' => 'psm_attestation'],

                // ── Graduation Requirement ────────────────────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Pass 2nd Supervised Project', 'description' => 'Repeat the full supervised delivery process on a second project. Must receive "meets expectations" or above on the delivery scorecard with no major findings. Two passing scorecards required to advance to independent delivery (Performing stage).', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'psm_attestation'],
            ],
            // ─── Stage 4: Performing ────────────────────────────
            4 => [
                ['category' => 'business_development', 'level' => 'required', 'title' => 'Achieve 80% Revenue Target', 'description' => 'Drive partner to at least 80% of annual NNACV target.', 'effort' => 'large', 'owner' => 'joint', 'evidence' => 'system_verified'],
                ['category' => 'business_development', 'level' => 'required', 'title' => 'Build Self-Sourced Pipeline (20%+)', 'description' => 'At least 20% of pipeline should be partner-originated, not just Anaplan-sourced.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Maintain 75% On-Time Delivery', 'description' => 'At least 75% of projects delivered on or before planned end date.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Establish Offshore Delivery Governance', 'description' => 'Document offshore delivery operating model: quality checks, escalation paths, resource vetting.', 'effort' => 'large', 'owner' => 'partner', 'days' => 60, 'evidence' => 'file_upload'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Expand to 2+ Product Areas', 'description' => 'Develop certifications in at least 2 distinct product areas.', 'effort' => 'large', 'owner' => 'partner', 'days' => 180, 'evidence' => 'system_verified'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Grow to 10 Certified Resources', 'description' => 'Expand certified resource pool to at least 10 active certifications.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'program', 'level' => 'required', 'title' => 'Complete PS Augmentation Qualification', 'description' => 'Meet all requirements for PS Augmentation: MSA signed, resource vetting process, methodology alignment, NDA compliance.', 'effort' => 'large', 'owner' => 'joint', 'days' => 60, 'evidence' => 'file_upload'],
                ['category' => 'account', 'level' => 'required', 'title' => 'Maintain Quarterly QBR Cadence', 'description' => 'Conduct QBRs every quarter with documented outcomes and action items.', 'effort' => 'medium', 'owner' => 'psm', 'evidence' => 'psm_attestation'],
            ],
            // ─── Stage 5: Accelerating ──────────────────────────
            5 => [
                ['category' => 'business_development', 'level' => 'required', 'title' => 'Achieve 100% Revenue Target', 'description' => 'Drive partner to full annual NNACV target attainment.', 'effort' => 'large', 'owner' => 'joint', 'evidence' => 'system_verified'],
                ['category' => 'business_development', 'level' => 'required', 'title' => 'Grow Self-Sourced Ratio to 40%+', 'description' => 'At least 40% of pipeline partner-originated.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'solution', 'level' => 'required', 'title' => 'Build Solution IP / Accelerator', 'description' => 'Develop at least 1 registered solution, industry accelerator, or methodology asset.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'psm_attestation'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Expand to 3+ Product Areas', 'description' => 'Certifications across at least 3 product areas.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Grow to 20+ Certified Resources', 'description' => 'Expand certified bench to at least 20 resources.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'program', 'level' => 'recommended', 'title' => 'Executive Business Review', 'description' => 'Conduct annual executive business review with C-level alignment on strategic direction.', 'effort' => 'large', 'owner' => 'joint', 'evidence' => 'psm_attestation'],
            ],
        ],

        // =====================================================================
        //  SPECIALIST IMPLEMENTATION PARTNER
        // =====================================================================
        'Specialist Implementation Partner' => [
            // ─── Stage 1: Onboarded ─────────────────────────────
            1 => [
                ['category' => 'account', 'level' => 'required', 'title' => 'Connect Salesforce Account', 'description' => 'Link partner record to Salesforce for deal tracking.', 'effort' => 'small', 'owner' => 'psm', 'days' => 14, 'evidence' => 'system_verified'],
                ['category' => 'account', 'level' => 'required', 'title' => 'Register Delivery Team Contacts', 'description' => 'Add at least 3 delivery team contacts.', 'effort' => 'small', 'owner' => 'joint', 'days' => 21, 'evidence' => 'system_verified'],
                ['category' => 'account', 'level' => 'required', 'title' => 'Schedule Kickoff Meeting', 'description' => 'Conduct kickoff reviewing partnership program and expectations.', 'effort' => 'medium', 'owner' => 'psm', 'days' => 14, 'evidence' => 'psm_attestation'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Assign Core Enablement Blueprint', 'description' => 'Assign the core platform enablement blueprint.', 'effort' => 'small', 'owner' => 'psm', 'days' => 14, 'evidence' => 'system_verified'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Certify 2 Model Builders', 'description' => 'Core team members achieve Model Builder certification.', 'effort' => 'large', 'owner' => 'partner', 'days' => 60, 'evidence' => 'system_verified'],
            ],
            // ─── Stage 2: Delivery Ready ────────────────────────
            2 => [
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Complete Product Blueprint', 'description' => 'Finish at least 1 product-specific blueprint for their niche area.', 'effort' => 'large', 'owner' => 'partner', 'days' => 60, 'evidence' => 'system_verified'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Grow to 3 Certified Resources', 'description' => 'Expand certified team to at least 3 active certifications.', 'effort' => 'large', 'owner' => 'partner', 'days' => 90, 'evidence' => 'system_verified'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Align to Anaplan Delivery Methodology', 'description' => 'Review and acknowledge Anaplan delivery methodology, project governance standards, and pre-built app approach.', 'effort' => 'medium', 'owner' => 'joint', 'days' => 30, 'evidence' => 'psm_attestation'],
                ['category' => 'account', 'level' => 'recommended', 'title' => 'Define Niche Specialty', 'description' => 'Document the partner niche: specific product area, vertical, or use case specialty.', 'effort' => 'small', 'owner' => 'joint', 'days' => 30, 'evidence' => 'psm_attestation'],
            ],
            // ─── Stage 3: Supervised Delivery ───────────────────
            //
            // Specialist partners work in a paired model with an Anaplan SA
            // who is more hands-on than with Strategic partners. The SA acts
            // as a technical mentor, not just a reviewer. The specialist team
            // is smaller so the SA is involved throughout, not just at checkpoints.
            //
            // 1 project must pass with Anaplan SA sign-off to graduate.
            //
            3 => [
                // ── Phase 1: Project Kickoff & Planning ──────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Paired SA Assignment & Kickoff', 'description' => 'Anaplan assigns a Solution Architect to pair with the specialist team for the duration of the project. Joint kickoff establishes working cadence, communication channels, and review touchpoints.', 'effort' => 'small', 'owner' => 'joint', 'days' => 7, 'evidence' => 'psm_attestation'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Confirm Pre-Built App Selection', 'description' => 'Validate that the correct pre-built application matches the client use case. Anaplan SA confirms app selection and documents any known limitations or considerations for this engagement.', 'effort' => 'small', 'owner' => 'joint', 'days' => 7, 'evidence' => 'psm_attestation'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Complete Client Discovery & Requirements', 'description' => 'Document client requirements mapped against pre-built app capabilities. Classify each requirement as: met out-of-the-box, requires configuration, or requires extension. Anaplan SA participates in discovery sessions.', 'effort' => 'large', 'owner' => 'partner', 'days' => 14, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit Gap-Fit Analysis', 'description' => 'Formal gap-fit with business justification for every proposed extension. Anaplan SA co-reviews in working session (not just document review). PSM approves final scope.', 'effort' => 'large', 'owner' => 'partner', 'days' => 14, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit Delivery Plan', 'description' => 'Delivery plan following Anaplan methodology phases: Discover, Design, Build, Validate, Deploy. Include resource plan showing where Anaplan SA is involved vs. partner-led.', 'effort' => 'medium', 'owner' => 'partner', 'days' => 14, 'evidence' => 'file_upload'],

                // ── Phase 2: Paired Design ───────────────────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit Model Design Document', 'description' => 'Model design showing module structure, list hierarchy, line items, data flow, and calculation logic. Clearly distinguish pre-built app components from new/modified elements. Anaplan SA reviews in a working session and provides written feedback.', 'effort' => 'large', 'owner' => 'partner', 'days' => 21, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit Extension Requirements with Justification', 'description' => 'For each extension: business requirement, why configuration cannot solve it, proposed approach, and impact on future app upgrades. Anaplan SA must approve each extension individually.', 'effort' => 'large', 'owner' => 'partner', 'days' => 21, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Data Integration Design Review', 'description' => 'Document planned integrations: source systems, data volumes, frequency, transformation logic. Anaplan SA reviews for platform best practices and performance.', 'effort' => 'medium', 'owner' => 'partner', 'days' => 21, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Anaplan SA Design Approval', 'description' => 'Formal design approval. Anaplan SA provides written approval or required changes. Build work should not proceed until approval is obtained.', 'effort' => 'medium', 'owner' => 'joint', 'evidence' => 'psm_attestation'],

                // ── Phase 3: Paired Build & Review ───────────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Weekly Build Check-In with Anaplan SA', 'description' => 'Weekly working sessions during the build phase. Anaplan SA reviews model build progress in the live environment — checks naming, formula quality, module structure, and design compliance. More hands-on than Strategic path reviews.', 'effort' => 'large', 'owner' => 'joint', 'evidence' => 'psm_attestation'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Mid-Project Checkpoint', 'description' => 'Formal mid-project review: sprint progress vs. plan, scope changes, risks, resource utilization, design deviations. Joint review between partner lead, Anaplan SA, and PSM.', 'effort' => 'medium', 'owner' => 'joint', 'evidence' => 'psm_attestation'],

                // ── Phase 4: Testing & Validation ────────────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit Test Plan & UAT Scripts', 'description' => 'Test plan covering unit, integration, regression, and UAT testing. Include test coverage for pre-built app functionality that must still work after extensions. Anaplan SA reviews test coverage.', 'effort' => 'large', 'owner' => 'partner', 'days' => 14, 'evidence' => 'file_upload'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit UAT Results & Defect Resolution', 'description' => 'UAT execution results, client sign-off status, defect log with resolution. All critical defects resolved. Share with Anaplan SA.', 'effort' => 'medium', 'owner' => 'partner', 'evidence' => 'file_upload'],

                // ── Phase 5: Go-Live & Handoff ───────────────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Go-Live Readiness Review', 'description' => 'Joint go-live assessment: cutover plan with rollback, data migration validation, training completion, support handoff docs, post-go-live monitoring plan. Anaplan SA and PSM must both approve.', 'effort' => 'large', 'owner' => 'joint', 'evidence' => 'psm_attestation'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Submit Training & Support Documentation', 'description' => 'End-user training materials and support handoff docs: model overview, admin procedures, troubleshooting, escalation contacts. Must be sufficient for L1 support without partner involvement.', 'effort' => 'medium', 'owner' => 'partner', 'evidence' => 'file_upload'],

                // ── Phase 6: Retrospective & Scorecard ───────────────
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Post-Project Retrospective', 'description' => 'Joint retrospective with partner, Anaplan SA, and PSM. Review what went well, what could improve, and design decisions to replicate or avoid. Document lessons learned.', 'effort' => 'medium', 'owner' => 'joint', 'days' => 14, 'evidence' => 'psm_attestation'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Anaplan SA Delivery Scorecard', 'description' => 'Anaplan SA completes formal scorecard: design compliance, extension discipline, model quality, delivery execution, client satisfaction, methodology adherence. Must score "meets expectations" or above in all areas to graduate from supervised delivery.', 'effort' => 'medium', 'owner' => 'joint', 'days' => 14, 'evidence' => 'psm_attestation'],
            ],
            // ─── Stage 4: Proven ────────────────────────────────
            4 => [
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Complete 4 Projects', 'description' => 'Build delivery track record with at least 4 completed projects.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Achieve 4.3+ Average CSAT', 'description' => 'Maintain average CSAT of at least 4.3 across all projects.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Build Reference Stories', 'description' => 'At least 60% of completed projects should be referenceable.', 'effort' => 'large', 'owner' => 'joint', 'evidence' => 'psm_attestation'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Grow to 6 Certified Resources', 'description' => 'Deepen certification coverage to at least 6 active certifications.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'program', 'level' => 'required', 'title' => 'Begin PS Augmentation Qualification', 'description' => 'Start MSA/subcontracting paperwork, resource vetting process, and methodology compliance review.', 'effort' => 'large', 'owner' => 'joint', 'days' => 90, 'evidence' => 'file_upload'],
                ['category' => 'account', 'level' => 'required', 'title' => 'Conduct PSM Reviews (2x/year)', 'description' => 'At least 2 PSM partnership reviews per year.', 'effort' => 'medium', 'owner' => 'psm', 'evidence' => 'psm_attestation'],
            ],
            // ─── Stage 5: Trusted Expert ────────────────────────
            5 => [
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Complete 8+ Projects', 'description' => 'Demonstrate consistent excellence across at least 8 projects.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Achieve 4.5+ Average CSAT', 'description' => 'Consistently exceptional delivery quality.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'program', 'level' => 'required', 'title' => 'Complete PS Augmentation Qualification', 'description' => 'All requirements met: MSA signed, resources vetted, methodology aligned, NDA compliant. Approved for Anaplan-branded delivery.', 'effort' => 'large', 'owner' => 'joint', 'evidence' => 'file_upload'],
                ['category' => 'enablement', 'level' => 'recommended', 'title' => 'Contribute Methodology Insights', 'description' => 'Share delivery best practices, lessons learned, or niche-specific methodology improvements back to Anaplan.', 'effort' => 'medium', 'owner' => 'partner', 'evidence' => 'psm_attestation'],
                ['category' => 'enablement', 'level' => 'recommended', 'title' => 'Mentor a Newer Partner', 'description' => 'Pair with an earlier-stage specialist partner to share practical delivery knowledge.', 'effort' => 'medium', 'owner' => 'partner', 'evidence' => 'psm_attestation'],
            ],
        ],

        // =====================================================================
        //  APPLICATION BUILDER PARTNER
        // =====================================================================
        'Application Builder Partner' => [
            // ─── Stage 1: Onboarded ─────────────────────────────
            1 => [
                ['category' => 'account', 'level' => 'required', 'title' => 'Sign App Builder Program Agreement', 'description' => 'Execute the Application Builder addendum covering IP ownership, revenue share, support obligations, and SLAs.', 'effort' => 'medium', 'owner' => 'psm', 'days' => 14, 'evidence' => 'file_upload'],
                ['category' => 'account', 'level' => 'required', 'title' => 'Register Product Owner & Technical Lead', 'description' => 'Register product owner, technical lead, and designated support contact in Partner Central.', 'effort' => 'small', 'owner' => 'joint', 'days' => 14, 'evidence' => 'system_verified'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Provision Development Sandbox', 'description' => 'Request and provision an Anaplan development sandbox for the build team.', 'effort' => 'small', 'owner' => 'psm', 'days' => 7, 'evidence' => 'psm_attestation'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Complete App Design Specification Review', 'description' => 'Review Anaplan app design specs, UX guidelines, API standards, and security requirements.', 'effort' => 'medium', 'owner' => 'joint', 'days' => 21, 'evidence' => 'psm_attestation'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Certify 2 Developers as Model Builders', 'description' => 'At least 2 developers on the app team obtain Model Builder certification.', 'effort' => 'large', 'owner' => 'partner', 'days' => 60, 'evidence' => 'system_verified'],
                ['category' => 'program', 'level' => 'required', 'title' => 'Submit App Concept Brief', 'description' => 'One-page concept brief: application, target market, differentiation, and timeline.', 'effort' => 'medium', 'owner' => 'partner', 'days' => 30, 'evidence' => 'file_upload'],
            ],
            // ─── Stage 2: Building ──────────────────────────────
            2 => [
                ['category' => 'solution', 'level' => 'required', 'title' => 'Submit App Architecture Document', 'description' => 'Detailed architecture: data model, module design, integrations, and UX wireframes.', 'effort' => 'large', 'owner' => 'partner', 'days' => 30, 'evidence' => 'file_upload'],
                ['category' => 'solution', 'level' => 'required', 'title' => 'Complete Bi-Weekly Design Reviews', 'description' => 'Regular design review sessions with Anaplan engineering team.', 'effort' => 'medium', 'owner' => 'joint', 'evidence' => 'psm_attestation'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Complete App Builder Blueprint', 'description' => 'Finish the builder enablement blueprint covering APIs, extensions, and deployment.', 'effort' => 'large', 'owner' => 'partner', 'days' => 60, 'evidence' => 'system_verified'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Grow Dev Team to 3 Certified', 'description' => 'Expand certified development team to at least 3 resources.', 'effort' => 'large', 'owner' => 'partner', 'days' => 90, 'evidence' => 'system_verified'],
                ['category' => 'solution', 'level' => 'required', 'title' => 'Submit Application for Validation', 'description' => 'Submit build for technical validation: UX review, security audit, performance benchmarks.', 'effort' => 'large', 'owner' => 'partner', 'days' => 120, 'evidence' => 'psm_attestation'],
                ['category' => 'program', 'level' => 'recommended', 'title' => 'Draft Support Runbook', 'description' => 'Begin drafting L1/L2 support runbook: escalation paths, known issues, troubleshooting.', 'effort' => 'medium', 'owner' => 'partner', 'days' => 90, 'evidence' => 'file_upload'],
            ],
            // ─── Stage 3: Validated ─────────────────────────────
            3 => [
                ['category' => 'solution', 'level' => 'required', 'title' => 'Complete Marketplace Listing', 'description' => 'Submit full marketplace listing: description, screenshots, pricing tiers, target customer.', 'effort' => 'medium', 'owner' => 'partner', 'days' => 30, 'evidence' => 'system_verified'],
                ['category' => 'business_development', 'level' => 'required', 'title' => 'Create Sales Enablement Package', 'description' => 'Battle cards, ROI calculator, competitive positioning, pricing, and demo script.', 'effort' => 'large', 'owner' => 'partner', 'days' => 45, 'evidence' => 'file_upload'],
                ['category' => 'business_development', 'level' => 'required', 'title' => 'Build Demo Environment', 'description' => 'Create dedicated demo environment with sample data for field sales.', 'effort' => 'large', 'owner' => 'partner', 'days' => 30, 'evidence' => 'psm_attestation'],
                ['category' => 'business_development', 'level' => 'required', 'title' => 'Finalize Pricing & Contracts', 'description' => 'Finalize pricing tiers, licensing terms, and contract templates with Anaplan legal.', 'effort' => 'medium', 'owner' => 'joint', 'days' => 45, 'evidence' => 'file_upload'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Deliver Field Sales Enablement', 'description' => 'Train Anaplan AEs and SEs on value prop, demo, qualification, and objection handling.', 'effort' => 'large', 'owner' => 'partner', 'days' => 60, 'evidence' => 'psm_attestation'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Deliver L1 Support Enablement', 'description' => 'Train Anaplan L1 support: common issues, triage, escalation to partner L2.', 'effort' => 'large', 'owner' => 'partner', 'days' => 60, 'evidence' => 'psm_attestation'],
                ['category' => 'program', 'level' => 'required', 'title' => 'Finalize L2 Support & SLA Agreement', 'description' => 'Sign off on L2 SLAs: response times, bug fix timelines, enhancement process, escalation.', 'effort' => 'medium', 'owner' => 'joint', 'days' => 30, 'evidence' => 'file_upload'],
                ['category' => 'business_development', 'level' => 'required', 'title' => 'Build Initial Pipeline ($250K)', 'description' => 'Work with field sales to register at least 2 qualified opportunities.', 'effort' => 'large', 'owner' => 'joint', 'days' => 90, 'evidence' => 'system_verified'],
            ],
            // ─── Stage 4: Go-to-Market ──────────────────────────
            4 => [
                ['category' => 'business_development', 'level' => 'required', 'title' => 'Close 3 Customer Deals', 'description' => 'At least 3 closed-won customer deals through field co-sell.', 'effort' => 'large', 'owner' => 'joint', 'evidence' => 'system_verified'],
                ['category' => 'business_development', 'level' => 'required', 'title' => 'Build Pipeline to $1M', 'description' => 'Grow active pipeline to at least $1M across multiple accounts.', 'effort' => 'large', 'owner' => 'joint', 'evidence' => 'system_verified'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Achieve 4.0 CSAT', 'description' => 'Average customer satisfaction at least 4.0 across deployed customers.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Meet 90% SLA Compliance', 'description' => 'At least 90% of L2 support tickets resolved within agreed SLA.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'psm_attestation'],
                ['category' => 'delivery', 'level' => 'required', 'title' => 'Ship Quarterly Enhancement Release', 'description' => 'At least 1 enhancement release per quarter with release notes.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'psm_attestation'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Grow Team to 8 Certified', 'description' => 'Expand certified team to at least 8 across dev, support, and SE.', 'effort' => 'large', 'owner' => 'partner', 'evidence' => 'system_verified'],
                ['category' => 'enablement', 'level' => 'required', 'title' => 'Refresh Field Sales Enablement', 'description' => 'Update training with new features, case studies, and competitive positioning.', 'effort' => 'medium', 'owner' => 'partner', 'evidence' => 'psm_attestation'],
                ['category' => 'program', 'level' => 'recommended', 'title' => 'Expand to Second Vertical/Geo', 'description' => 'Identify and begin targeting a second vertical or geography.', 'effort' => 'large', 'owner' => 'joint', 'evidence' => 'psm_attestation'],
            ],
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding partner task templates...');

        $adminId = (User::whereRelation('role', 'slug', 'admin')->first() ?? User::first())->id;
        $templateCount = 0;

        foreach (self::TEMPLATES as $pathName => $stageTemplates) {
            $path = PartnerPath::where('name', $pathName)->first();
            if (! $path) {
                $this->command->warn("  Path '{$pathName}' not found — skipping templates.");

                continue;
            }

            $stages = $path->stages()->orderBy('sort_order')->get()->keyBy('stage_number');

            foreach ($stageTemplates as $stageNumber => $templates) {
                $stage = $stages->get($stageNumber);
                if (! $stage) {
                    continue;
                }

                foreach ($templates as $sortOrder => $tpl) {
                    TaskTemplate::create([
                        'partner_path_id' => $path->id,
                        'stage_id' => $stage->id,
                        'category' => $tpl['category'],
                        'requirement_level' => $tpl['level'],
                        'title' => $tpl['title'],
                        'description' => $tpl['description'],
                        'effort_size' => $tpl['effort'],
                        'default_owner' => $tpl['owner'],
                        'due_days_offset' => $tpl['days'] ?? null,
                        'evidence_type' => $tpl['evidence'],
                        'sort_order' => $sortOrder,
                        'is_active' => true,
                        'created_by' => $adminId,
                    ]);
                    $templateCount++;
                }
            }
        }

        $this->command->info("  Created {$templateCount} partner task templates.");
    }
}
