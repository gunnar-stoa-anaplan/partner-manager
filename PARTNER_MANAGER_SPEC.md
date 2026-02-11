# Partner Manager — Application Specification & Roadmap

> **Purpose:** This document is the single source of truth for the Partner Manager application architecture, current state, and planned enhancements. Review and edit this document before any implementation work begins. It serves as the prompt/specification that drives all development.
>
> **Design Philosophy:** This specification is modeled after world-class partner management platforms (AWS Partner Central, Salesforce PRM, Microsoft Partner Center). Every feature is prescriptive — it tells the PSM exactly what to do, when to do it, and how to measure success. The platform doesn't just track data; it drives behavior.

---

## Table of Contents

1. [Application Purpose](#1-application-purpose)
2. [User Roles & Personas](#2-user-roles--personas)
3. [Current Data Model](#3-current-data-model)
4. [Current Navigation & Views](#4-current-navigation--views)
5. [Proposed Navigation Structure](#5-proposed-navigation-structure)
6. [Partner Paths — Gate-Based Progression](#6-partner-paths--gate-based-progression)
7. [Prescriptive Task System](#7-prescriptive-task-system)
8. [Enablement Blueprints — Structured Learning Paths](#8-enablement-blueprints--structured-learning-paths)
9. [Solution & Engagement Lifecycle](#9-solution--engagement-lifecycle)
10. [Partner Scorecard](#10-partner-scorecard)
11. [Section: Plan](#11-section-plan)
12. [Section: Engage (Enhanced)](#12-section-engage-enhanced)
13. [Section: Measure — Tabbed Analytics](#13-section-measure--tabbed-analytics)
14. [Section: Act](#14-section-act)
15. [Pipeline & Opportunity Intelligence](#15-pipeline--opportunity-intelligence)
16. [Delivery Capability Risk Engine](#16-delivery-capability-risk-engine)
17. [Notification Template System](#17-notification-template-system)
18. [Data Integration Roadmap](#18-data-integration-roadmap)
19. [New Data Entities Required](#19-new-data-entities-required)
20. [Implementation Phases](#20-implementation-phases)
21. [Technical Constraints](#21-technical-constraints)

---

## 1. Application Purpose

Partner Manager is a **prescriptive partner management platform** for **Anaplan Partner Success Managers (PSMs)** to track, analyze, manage, and actively develop their portfolio of assigned partners. Anaplan partners are a critical driver of the business — they identify opportunities, deliver projects, and expand customer relationships. Successful partners lead to add-on business, customer retention, and new logo acquisition.

### Core Problem

PSMs must guide partners through a structured maturity journey across multiple dimensions:
- **Opportunity identification** — qualifying deals, scoping correctly, winning business
- **Project delivery** — assembling skilled teams, executing on time, achieving customer outcomes
- **Enablement & certification** — keeping resources current on an evolving product platform
- **Solution development** — building repeatable IP, industry solutions, and go-to-market capability
- **Growth & maturity** — progressing through defined path stages from onboarded to strategic partner

The challenge is providing a **consistent, thorough, and prescriptive** engagement model tied to a partner's current stage, with clear requirements for advancement and measurable outcomes at every step.

### Design Principles

1. **Prescriptive, not descriptive** — The platform tells PSMs what to do next, not just what happened. Every partner has a clear path, defined tasks, and measurable gates.
2. **Stage-driven** — Partners exist at a specific stage in a defined path. Their stage determines what tasks are required, what metrics matter, and what resources are available.
3. **Task-oriented** — Work is organized into discrete, trackable tasks with clear ownership, deadlines, effort estimates, and completion criteria. Nothing is vague.
4. **Data-driven scoring** — Every partner has a quantitative scorecard. Scores are computed from real data, not subjective assessments. Scores determine stage eligibility.
5. **Origin-aware** — Every opportunity, lead, and engagement is tagged with its origin (Partner Originated, Anaplan Originated, Customer Engagement) to measure true partner contribution.
6. **Lifecycle-conscious** — Solutions, engagements, and partner relationships follow defined lifecycle stages. The platform tracks where everything is in its lifecycle.
7. **Action-oriented** — Insights always lead to a recommended action. Dashboards surface "What should I do?" not just "What is happening?"
8. **Goal-driven with revenue math** — PSMs and partners jointly create and track goals spanning all aspects of the business: annual and quarterly objectives for sourced and influenced NNACV/license revenue, depth of certified resources, project delivery success, and more. Revenue goals are backed by quantitative planning: historical average license sales price (ASP), opportunity/pipeline velocity from Stage 1 (Identify) through Stage 5 (Close-Won), and a clear calculation of the number of opportunities required to achieve the revenue target. The desired pipeline multiple is derived from pipeline velocity and the natural attrition rate of opportunities as they progress through stages.

---

## 2. User Roles & Personas

### PSM (Partner Success Manager) — Primary User
- Assigned a portfolio of 10-30 partners at various path stages
- Needs to prioritize time based on partner stage, task urgency, and business impact
- Conducts QBRs, enablement sessions, pipeline reviews, path advancement reviews
- Responsible for guiding each partner through their path — completing required tasks, meeting scorecard thresholds, and earning stage advancement
- Wants a clear "what should I do today?" answer, organized by partner and urgency
- Must document all touchpoints and task completions as evidence for path progression
- Jointly plans with each partner — co-creates goals, assigns blueprints, monitors resource development

### Admin
- Full system access
- **Defines and configures Partner Paths/Blueprints** — stages, gates, required tasks, scorecard weights
- **Creates Task Templates** — prescriptive tasks that populate automatically per path/stage
- **Defines and configures Global Enablement Calendars** — events, sessions, geographies, capacity
- **Creates and manages Notification Templates** — email/notification templates for blueprint assignments, event registrations, certification completions, and other system events
- Manages users, partner assignments, data imports, system configuration
- Views platform-wide metrics, PSM utilization, and path/blueprint progression analytics
- Configures enablement programs, blueprints, events, funding programs

### Manager
- Oversees a team of PSMs
- Needs aggregate views across PSM portfolios
- Tracks PSM effectiveness: task completion rates, partner progression velocity, scorecard trends
- Identifies bottlenecks: which stages have the most stuck partners? Which PSMs need support?

### Partner (Future Phase)
- Partner contacts can log in to a self-service portal scoped to their own partner data
- **View-only access to:** their scorecard, goals & objectives, assigned resources, certification status, project status, blueprint assignments and progress
- **Edit access to:** add/update team members (contacts), update resource certifications, submit enablement event registrations
- **Calendar access:** view their enablement calendar with upcoming sessions, deadlines, and milestones
- **API access:** GET/POST key information (resources, certifications, project status) via secure, authenticated REST endpoints
- Partner role is scoped exclusively to their own partner record — they cannot see other partners, PSM data, or system-wide metrics

---

## 3. Current Data Model

### Core Entities (26 models, 30+ tables)

#### Partners & Relationships
| Entity | Key Fields | Relationships |
|--------|-----------|---------------|
| **Partner** | name, salesforce_id, industry, geography, region, psm, website | belongsTo User, hasMany Projects/Contacts/Opportunities/Leads/Fundings/Resources/Events/Goals |
| **Account** | name | belongsToMany Partners |
| **Contact** | first_name, last_name, email, phone, title | belongsTo Partner, belongsToMany Projects/Events |
| **User** | name, email, role (admin/psm/manager), avatar | hasMany Partners, belongsToMany Partners (via user_partner pivot with role) |

#### Pipeline & Revenue
| Entity | Key Fields | Relationships |
|--------|-----------|---------------|
| **Opportunity** | opportunity_name, account_name, stage, amount, close_date, fiscal_quarter, fiscal_year, type, lead_source, revenue_type (sourced/influenced), product_area, probability, owner | belongsTo Partner/Import |
| **OpportunitySnapshot** | snapshot_date + all opportunity fields | belongsTo Opportunity/Import/Partner |
| **Lead** | name, email, company, source, status (unassigned/assigned/accepted/rejected) | belongsTo Partner |
| **Funding** | name, amount, status, type, description, date | belongsTo Partner |

#### Project Delivery
| Entity | Key Fields | Relationships |
|--------|-----------|---------------|
| **Project** | name, start_date, end_date, acv, status, percent_complete, total_sprints, current_sprint, budget, delivery_team | belongsTo Partner/Account, belongsToMany Contacts, belongsTo Contact (cal/tal/ae) |

#### Enablement & Certification
| Entity | Key Fields | Relationships |
|--------|-----------|---------------|
| **Resource** | name, role, program, status, date_certified | belongsTo Partner/Import |
| **ResourceSnapshot** | snapshot_date, program, role, started_count, completed_count, total_count, unique_people_count | belongsTo Partner/Import |
| **EnablementEvent** | title, type, category, product_area, status, max_participants | hasMany Sessions |
| **EnablementSession** | start_at, end_at, location, meeting_url, instructor, geography, status | belongsTo Event, hasMany Registrations |
| **EnablementRegistration** | status (nominated/registered/confirmed/attended/no_show/cancelled), timestamps per status | belongsTo Session/Contact/Partner |
| **Blueprint** | name, category (application/product), product_area, version, status | hasMany Outcomes |
| **BlueprintOutcome** | title, description, sort_order | hasMany ContentItems |
| **BlueprintContentItem** | title, url, duration_minutes, source, gtm/pre_sales/delivery_applicable | belongsTo Outcome |
| **PartnerBlueprintAssignment** | role (gtm/pre_sales/delivery), status, notes | belongsTo Partner/Blueprint, hasMany Enrollments |
| **BlueprintEnrollment** | status (enrolled/in_progress/completed), completed_at | belongsTo Assignment/Contact |
| **BlueprintProgress** | completed, completed_at, notes | belongsTo Enrollment/ContentItem |

#### Calendar & Events
| Entity | Key Fields | Relationships |
|--------|-----------|---------------|
| **PartnerEvent** | title, type (qbr/training/workshop/demo/etc.), status, start_at, end_at, location, outcome, next_steps, recurrence_type | belongsTo Partner/User/Opportunity/Project, belongsToMany Contacts |

#### System & Configuration
| Entity | Key Fields |
|--------|-----------|
| **Import** | type, file_name, total_rows, successful/failed_rows, partners_created/updated, status, error_log |
| **AppSetting** | key, value (fiscal_year_start_month, default_currency, etc.) |
| **SalesforceCredential** | client_id, username, client_secret_encrypted, instance_url |
| **Objective** | (placeholder — empty model) |
| **PartnerGoal** | fiscal_year, sourced_nnacv_target, influenced_nnacv_target |

### Pivot Tables
- `account_partner` — Account to Partner
- `contact_project` — Contact to Project
- `user_partner` — User to Partner (with role)
- `contact_partner_event` — Contact to PartnerEvent
- `partner_enablement_session` — Partner to EnablementSession (with added_by, notes)

### Historical Tracking
- Opportunity snapshots captured per import (point-in-time pipeline state)
- Resource snapshots aggregated per import (certification counts over time)

### Data Integration Roadmap
Currently, all data enters via manual CSV/XLS imports. Future integration paths include:
- **Salesforce batch sync** — Automated periodic data loads via direct Salesforce API connection
- **REST API layer** — Secure, authenticated APIs for external systems to push/pull data
- **Partner self-service API** — Partners can GET/POST their own resources, certifications, project status, and team members via API endpoints scoped to their partner record

See [Section 18: Data Integration Roadmap](#18-data-integration-roadmap) for full specification.

---

## 4. Current Navigation & Views

### Sidebar Structure (as of current state)

The navigation follows the natural flow of how a PSM manages a partner: **Plan → Engage → Measure → Act**. This grouping is central to the application's design and should be preserved as features are added.

```
[Top Level - No Section Title]
  Welcome
  My Partners
  Custom Analysis
  Guided Analysis
  Partner Coach

[Engage]
  Project Health → Projects, Risks
  Field Engagements → Partner Calendar, Enablement Calendar
  Field Enablement → Influence Map, Breadth, Depth, Key Influencers

[Key Metrics] (shown when partner selected)
  Partner Profile
  Summary
  Insights
  Calendar
  Blueprints

[Business Development] (shown when partner selected)
  Sourced Leads
  Influenced Leads
  Development Funding

[Dashboards]
  Full Summary
  Full Insights

[Administration] (admin only)
  Admin Dashboard
  Partner Goals
  Enablement Blueprints
  Partner Assignments
  Lead Assignment → New, Assigned, Rejected
  User Management

[Program Administration] (admin only)
  Enablement Events
  Enablement Calendar
  Blueprints

[System] (admin only)
  Configuration
  Data Imports
  Salesforce Credentials
  System Pulse
```

### View Count: ~195 blade files across 35+ directories

---

## 5. Proposed Navigation Structure

The navigation follows the PSM's prescriptive workflow: **Understand → Plan → Engage → Measure → Act**

Planning is a joint activity with the partner — setting goals, assigning blueprints, tracking tasks, and reviewing path progression together. Each section maps to a phase of the partner management lifecycle.

```
[Top Level]
  Welcome                          — Landing with task queue and daily priorities
  My Partners                      — Portfolio with path stage, scorecard, and next tasks
  Partner Coach (AI)               — AI-powered analysis and recommendations

[Plan]                             — STRATEGIC PLANNING & READINESS (joint with partner)
  Partner Paths                    — Path stage map, gate requirements, progression tracker
  Scorecard                        — Multi-dimensional partner scoring dashboard
  Competency Matrix                — Certification intelligence, skill gaps, requirements
  Blueprints                       — Enablement pathway assignment & progression (Sales/Pre-Sales/Delivery)
  Goals & Targets                  — Revenue math, pipeline planning, multi-dimension objectives
  Task Board                       — Prescriptive tasks by category, status, priority
  Action Plans                     — QBR outcomes, tracked commitments, follow-through

[Engage]                           — EXECUTION & FIELD WORK
  Project Health → Projects, Risks
  Field Engagements → Partner Calendar, Enablement Calendar
  Field Enablement → Influence Map, Breadth, Depth, Key Influencers
  Solution Lifecycle               — Solution stage tracking (Build → Market → Sell → Grow)

[Key Metrics] (partner selected)   — PARTNER-SPECIFIC VIEWS
  Partner Profile
  Summary
  Insights
  Calendar

[Business Development] (partner selected) — PIPELINE & REVENUE
  Pipeline Overview                — Funnel with origin tracking
  Sourced Leads
  Influenced Leads
  Development Funding

[Measure]                          — ANALYTICS & INTELLIGENCE (tabbed)
  At a Glance                      — Executive summary, KPI cards, trend indicators
  Pipeline Analysis                — Velocity, win rates, stage progression, origin breakdown
  Lead Flow                        — Lead conversion funnel, response times, quality
  Funding                          — Investment tracking, ROI, utilization
  Revenue                          — Bookings, ACV trends, partner contribution
  Enablement                       — Training completion, certification trends, ROI
  Delivery                         — Project outcomes, CSAT, case tracking

[Act]                              — ACTIONS & DECISIONS
  Alerts & Recommendations         — System-generated next-best-actions (incl. delivery capability risks)
  QBR Workbench                    — Structured QBR prep, execution, follow-up
  Path Reviews                     — Stage advancement evaluations

[Dashboards]                       — CROSS-PORTFOLIO VIEWS
  Full Summary
  Full Insights

[Administration]                   — ADMIN ONLY
  Admin Dashboard
  Path Configuration               — Define paths, stages, gates, task templates
  Blueprint Management             — Create/edit blueprints, outcomes, content items
  Notification Templates           — Email/notification templates for system events
  Partner Goals
  Partner Assignments
  Lead Assignment → New, Assigned, Rejected
  User Management

[Program Administration]           — ADMIN ONLY
  Enablement Events
  Enablement Calendar
  Blueprints (catalog view)

[System]                           — ADMIN ONLY
  Configuration
  Data Imports
  Salesforce Credentials
  System Pulse
```

---

## 6. Partner Paths — Gate-Based Progression

### Concept

Every partner follows a **Partner Path** — a structured progression through defined stages. Each stage has specific requirements (gates) that must be met before advancement. This replaces ad-hoc maturity assessment with a formal, measurable progression model.

Inspired by AWS Partner Path tiers (Enrolled → Confirmed → Validated → Differentiated), adapted for Anaplan's partner ecosystem.

### Path Stages

| Stage | Name | Description | Typical Duration |
|-------|------|-------------|-----------------|
| 1 | **Onboarded** | Partner is registered, initial agreements signed, PSM assigned. No delivery capability yet. | 0-3 months |
| 2 | **Enabled** | Core certifications obtained, first enablement completed, initial pipeline building. | 3-6 months |
| 3 | **Performing** | Actively delivering projects, sourcing opportunities, meeting baseline metrics. | 6-18 months |
| 4 | **Accelerating** | Consistently exceeding targets, expanding product coverage, high delivery quality. | 12-24 months |
| 5 | **Strategic** | Top-tier partner driving significant business, building solutions, leading in market. | Ongoing |

### Gate Requirements (per stage transition)

Each gate defines the **minimum requirements** a partner must satisfy to advance to the next stage. Gates are quantitative and verified against real data in the system.

#### Gate 1 → 2 (Onboarded → Enabled)
| Requirement | Metric | Threshold |
|-------------|--------|-----------|
| Model Builder Certifications | Certified MB count | ≥ 2 |
| Solution Architect Certifications | Certified SA count | ≥ 1 |
| Enablement Completion | Core blueprints completed | ≥ 1 core blueprint (all outcomes complete) |
| PSM Onboarding | Required onboarding tasks completed | 100% |
| Salesforce Connected | salesforce_id populated | Yes |
| Key Contacts Registered | Contact count | ≥ 3 |

#### Gate 2 → 3 (Enabled → Performing)
| Requirement | Metric | Threshold |
|-------------|--------|-----------|
| Active Pipeline | Open opportunity count | ≥ 3 |
| Pipeline Value | Total open pipeline amount | ≥ $500K |
| Won Opportunities | Closed-Won count (trailing 12m) | ≥ 1 |
| Certified Resources | Active MB + SA count | ≥ 4 |
| Blueprint Completion | Application-specific blueprints completed | ≥ 1 application blueprint |
| Project Delivery | Completed projects | ≥ 1 |
| QBR Cadence | QBRs completed (trailing 12m) | ≥ 2 |
| Health Score | Overall scorecard score | ≥ 30 |

#### Gate 3 → 4 (Performing → Accelerating)
| Requirement | Metric | Threshold |
|-------------|--------|-----------|
| Revenue Achievement | % of annual NNACV target | ≥ 80% |
| Win Rate | Won / (Won + Lost) trailing 12m | ≥ 30% |
| Pipeline Coverage | Pipeline / Remaining target | ≥ 2.0x |
| Delivery Quality | Average CSAT | ≥ 4.0 |
| On-Time Delivery | % projects on-time | ≥ 75% |
| Delivery Capability | No critical capability risk flags | 0 critical risks |
| Certified Resources | Active MB + SA count | ≥ 8 |
| Product Areas | Distinct product areas with certs | ≥ 2 |
| Blueprint Coverage | Blueprints complete for all product areas with pipeline | 100% |
| Engagement | PSM touchpoints per quarter | ≥ 6 |
| Escalation Rate | % cases escalated | ≤ 15% |
| Health Score | Overall scorecard score | ≥ 55 |

#### Gate 4 → 5 (Accelerating → Strategic)
| Requirement | Metric | Threshold |
|-------------|--------|-----------|
| Revenue Achievement | % of annual NNACV target | ≥ 100% |
| Win Rate | Won / (Won + Lost) trailing 12m | ≥ 40% |
| Pipeline Coverage | Pipeline / Remaining target | ≥ 3.0x |
| Delivery Quality | Average CSAT | ≥ 4.5 |
| On-Time Delivery | % projects on-time | ≥ 85% |
| Referenceable Customers | % projects referenceable | ≥ 50% |
| Expansion Rate | % projects leading to expansion | ≥ 25% |
| Certified Resources | Active MB + SA count | ≥ 15 |
| Product Areas | Distinct product areas with certs | ≥ 3 |
| Solution Assets | Registered solutions/IP | ≥ 1 |
| Self-Sourced Ratio | % of pipeline partner-originated | ≥ 40% |
| Health Score | Overall scorecard score | ≥ 80 |

### Data Model: Partner Paths

**New migration: `partner_paths`** (admin-configured)
```
id
name                — string (e.g., 'Implementation Partner', 'Reseller Partner', 'Technology Partner')
description         — text (nullable)
is_default          — boolean (default false)
is_active           — boolean (default true)
created_by          — foreignId
```

**New migration: `partner_path_stages`** (admin-configured)
```
id
partner_path_id     — foreignId
stage_number        — integer (1-5)
name                — string (e.g., 'Onboarded', 'Enabled', 'Performing', 'Accelerating', 'Strategic')
description         — text (nullable)
color               — string (hex color for UI, e.g., '#6C757D', '#0D6EFD', '#198754', '#FFC107', '#DC3545')
icon                — string (FontAwesome class, e.g., 'fal fa-flag', 'fal fa-graduation-cap')
sort_order          — integer
```

**New migration: `partner_path_gates`** (admin-configured requirements per stage transition)
```
id
partner_path_id     — foreignId
from_stage_id       — foreignId (partner_path_stages)
to_stage_id         — foreignId (partner_path_stages)
requirement_key     — string (e.g., 'certified_mb_count', 'pipeline_value', 'win_rate')
requirement_label   — string (human-readable, e.g., 'Model Builder Certifications')
comparison          — enum (gte, lte, eq, gt, lt) — default 'gte'
threshold_value     — decimal
unit                — string (count, currency, percentage, boolean)
description         — text (nullable, explains what this measures)
sort_order          — integer
```

**New migration: `partner_path_enrollments`** (partner's position in a path)
```
id
partner_id          — foreignId
partner_path_id     — foreignId
current_stage_id    — foreignId (partner_path_stages)
enrolled_at         — datetime
stage_entered_at    — datetime (when they entered current stage)
last_reviewed_at    — datetime (nullable)
reviewed_by         — foreignId (nullable)
status              — enum (active, paused, graduated, withdrawn)
notes               — text (nullable)
```

**New migration: `partner_stage_history`** (audit trail of stage transitions)
```
id
partner_id          — foreignId
partner_path_id     — foreignId
from_stage_id       — foreignId (nullable, null for initial enrollment)
to_stage_id         — foreignId
transitioned_at     — datetime
approved_by         — foreignId
gate_results        — json (snapshot of all gate evaluations at time of transition)
notes               — text (nullable)
```

### View: Partner Path Dashboard

**URL:** `/partner-paths` (or `/plan/paths`)
**Purpose:** Visualize where every partner in the PSM's portfolio sits in their path, and what's needed to advance.

**Layout:**
- **Top:** Path stage pipeline visualization — horizontal funnel showing count of partners per stage, with colored stage indicators
- **Main table:** All partners with columns: Partner Name, Path, Current Stage (badge), Days in Stage, Gate Progress (X/Y requirements met), Next Required Task, Health Score, Actions
- **Side panel (on partner click):** Gate requirements checklist with real-time met/unmet status, including the actual current value vs. threshold for each gate
- **Filters:** By path, by stage, by "stuck" (in stage > X days), by gate progress percentage

---

## 7. Prescriptive Task System

### Concept

Every partner at every stage has a set of **prescriptive tasks** that the PSM and/or partner must complete. Tasks are generated from **admin-defined templates** based on the partner's current path and stage. This ensures consistent, repeatable partner management across all PSMs.

Inspired by AWS Partner Central's task system (Required/Optional, categorized by Account/Solution/Program, with effort estimates and clear completion criteria).

### Task Categories

| Category | Description | Examples |
|----------|-------------|---------|
| **Account** | Administrative and setup tasks for the partner relationship | Connect Salesforce, register contacts, set goals, schedule QBR |
| **Enablement** | Training, certification, and capability building tasks | Complete core blueprint, certify 2 MBs, attend product training, assign blueprint to resources |
| **Solution** | Solution development, IP creation, and go-to-market tasks | Build demo environment, create industry solution, register joint offering |
| **Delivery** | Project execution and quality assurance tasks | Complete first delivery, achieve CSAT ≥ 4.0, build delivery playbook |
| **Business Development** | Pipeline building, lead generation, and revenue tasks | Build $500K pipeline, close first deal, source 3 qualified leads |
| **Program** | Compliance, reporting, and program participation tasks | Submit quarterly report, complete annual review, attend partner summit |

### Task Priority & Requirement Level

Each task is classified as:
- **Required** — Must be completed to advance to the next stage. Blocks gate progression if incomplete.
- **Recommended** — Strongly suggested for success but does not block advancement.
- **Optional** — Enhances the partnership but is not expected at this stage.

### Task Properties

Each task instance has:
- **Effort estimate** — Estimated hours to complete (displayed as S/M/L: Small < 2h, Medium 2-8h, Large 8h+)
- **Owner** — Who is responsible: PSM, Partner, Joint, Admin
- **Due date** — Auto-calculated from stage entry date + offset, or manually set
- **Completion criteria** — Prescriptive definition of "done" (e.g., "At least 2 contacts with 'Model Builder' certification in active status")
- **Auto-verification** — Whether the system can automatically detect completion from data
- **Evidence type** — What proof is needed: system-verified, file upload, PSM attestation, URL link

### Data Model: Task Templates & Instances

**New migration: `task_templates`** (admin-configured prescriptive task library)
```
id
partner_path_id     — foreignId (nullable, null = applies to all paths)
stage_id            — foreignId (partner_path_stages, nullable = applies to all stages)
category            — enum (account, enablement, solution, delivery, business_development, program)
requirement_level   — enum (required, recommended, optional)
title               — string (e.g., 'Connect Salesforce Account')
description         — text (detailed instructions)
completion_criteria  — text (prescriptive definition of "done")
effort_size         — enum (small, medium, large) — Small < 2h, Medium 2-8h, Large 8h+
default_owner       — enum (psm, partner, joint, admin)
due_days_offset     — integer (nullable, days after stage entry that this is due)
auto_verify_rule    — string (nullable, e.g., 'partner.salesforce_id IS NOT NULL', 'certifications.count >= 2')
evidence_type       — enum (system_verified, file_upload, psm_attestation, url_link)
sort_order          — integer
is_active           — boolean (default true)
gate_requirement_id — foreignId (nullable, links to partner_path_gates if this task maps to a gate req)
created_by          — foreignId
```

**New migration: `partner_tasks`** (instantiated tasks for each partner)
```
id
partner_id          — foreignId
task_template_id    — foreignId (nullable, null for ad-hoc tasks)
title               — string (copied from template, can be customized)
description         — text (nullable)
category            — enum (account, enablement, solution, delivery, business_development, program)
requirement_level   — enum (required, recommended, optional)
status              — enum (not_started, in_progress, completed, blocked, waived)
priority            — enum (low, medium, high, critical)
effort_size         — enum (small, medium, large)
owner               — enum (psm, partner, joint, admin)
assigned_to_user_id — foreignId (nullable, specific user)
assigned_to_contact_id — foreignId (nullable, specific partner contact)
due_date            — date (nullable)
started_at          — datetime (nullable)
completed_at        — datetime (nullable)
completed_by        — foreignId (nullable)
completion_evidence — text (nullable, notes/URL proving completion)
evidence_file       — string (nullable, file path)
auto_verified       — boolean (default false)
auto_verified_at    — datetime (nullable)
waived_by           — foreignId (nullable)
waived_reason       — text (nullable)
notes               — text (nullable)
stage_id            — foreignId (nullable, stage when task was created)
partner_path_enrollment_id — foreignId (nullable)
```

**New migration: `task_comments`** (collaboration on tasks)
```
id
partner_task_id     — foreignId
user_id             — foreignId
body                — text
created_at          — timestamp
```

### Task Auto-Population Logic

When a partner **enters a new stage** (via path enrollment or stage advancement):
1. Query all `task_templates` matching the partner's `partner_path_id` + `stage_id`
2. Also include templates where `partner_path_id` is null (global) and `stage_id` matches
3. For each template, create a `partner_tasks` record with:
   - Title, description, category, requirement_level, effort_size copied from template
   - Status = `not_started`
   - Due date = `stage_entered_at + due_days_offset` (if set)
   - Owner = `default_owner` from template
4. Skip templates where an equivalent task already exists (prevent duplicates on re-entry)

### Task Auto-Verification Logic

For tasks with `auto_verify_rule`, a scheduled command runs daily:
1. Query all `partner_tasks` where `status != completed` and `task_template.auto_verify_rule IS NOT NULL`
2. For each task, evaluate the rule against current partner data
3. If the rule is satisfied, set `status = completed`, `auto_verified = true`, `auto_verified_at = now()`
4. Generate a notification/alert for the PSM

**Example auto-verify rules:**
| Rule String | Evaluation |
|-------------|-----------|
| `partner.salesforce_id IS NOT NULL` | Check if partner has a Salesforce ID |
| `certifications.active.count >= 2` | Count active certifications for partner |
| `opportunities.open.sum(amount) >= 500000` | Sum open pipeline value |
| `projects.completed.count >= 1` | Count completed projects |
| `partner_events.qbr.trailing_12m.count >= 2` | Count QBRs in last 12 months |
| `contacts.count >= 3` | Count partner contacts |
| `health_score.overall >= 55` | Check latest health score |
| `blueprints.completed.count >= 1` | Count completed blueprint assignments |

### View: Task Board

**URL:** `/plan/tasks` (or `/tasks`)
**Purpose:** The PSM's daily worklist — all tasks across their portfolio, organized for action.

**Layout Options (toggleable):**

**1. Kanban View** (default)
- Columns: Not Started | In Progress | Blocked | Completed
- Cards show: Partner name, task title, category badge, requirement level badge (Required=red, Recommended=yellow, Optional=blue), effort size (S/M/L), due date, owner icon
- Cards are color-coded by overdue status (red border if past due)
- Drag-and-drop to change status
- Filter bar: By partner, category, requirement level, owner, due date range

**2. List View**
- Table with columns: Partner, Task, Category, Required?, Effort, Owner, Due Date, Status, Actions
- Sortable by any column
- Bulk actions: Mark complete, reassign owner, change priority

**3. Partner-Grouped View**
- Accordion by partner name
- Within each partner: tasks listed by category, then by requirement level (required first)
- Progress bar per partner: X of Y required tasks completed

**Task Detail Modal:**
- Full task description and completion criteria
- Evidence upload/entry area
- Comment thread (task_comments)
- History log (status changes)
- Related gate requirement (if linked)
- "Mark Complete" button with evidence prompt

---

## 8. Enablement Blueprints — Structured Learning Paths

### Concept

Blueprints are **structured enablement pathways** that ensure partner resources (Sales, Pre-Sales, and Delivery) develop the specific skills required for each Anaplan product area. A blueprint defines a sequence of learning outcomes and content items that, when completed, qualifies a resource to sell, scope, or deliver in that product area.

Blueprints are a **critical link** between partner enablement and delivery capability. The system must surface when a partner has pipeline in a product area but lacks resources who have completed the corresponding blueprint — this is a high-risk situation requiring immediate action.

### Blueprint Roles

Each blueprint assignment targets a specific role within the partner organization:

| Role | Description | Typical Blueprint Focus |
|------|-------------|------------------------|
| **GTM (Sales)** | Go-to-market and sales resources | Product messaging, competitive positioning, demo skills, qualification criteria |
| **Pre-Sales** | Solution architects and pre-sales consultants | Technical scoping, architecture design, POC delivery, integration planning |
| **Delivery** | Model builders and implementation consultants | Model building, data integration, testing, deployment, customer training |

### Existing Data Model (already implemented)

The blueprint system already exists in the database:
- `blueprints` — Blueprint definitions with name, category, product_area, version, status
- `blueprint_outcomes` — Learning objectives within a blueprint (ordered)
- `blueprint_content_items` — Specific content pieces (URLs, durations, source) within each outcome, with role applicability flags (gtm, pre_sales, delivery)
- `partner_blueprint_assignments` — Assignment of a blueprint to a partner for a specific role, with status
- `blueprint_enrollments` — Individual contact enrollment in a blueprint assignment, with completion tracking
- `blueprint_progress` — Per-content-item completion tracking for each enrolled contact

### PSM Blueprint Workflow

The PSM's blueprint workflow is a critical part of the Plan section:

1. **Assess needs** — Review partner's pipeline by product area (from opportunities) and compare against available certified/blueprint-completed resources → identify gaps
2. **Assign blueprint** — PSM assigns a blueprint to the partner for a specific role (GTM/Pre-Sales/Delivery), selecting which product area to target
3. **Enroll resources** — PSM or partner enrolls specific contacts (resources) into the blueprint assignment
4. **Monitor progress** — Track each enrolled contact's progress through blueprint outcomes and content items
5. **Complete & certify** — When all content items are completed, the resource is blueprint-complete for that product area/role → feeds into Competency Matrix and Delivery Capability Risk engine
6. **Link to enablement calendar** — If a blueprint outcome requires attending a training session, the system should suggest available enablement calendar events that satisfy that outcome

### View: Blueprint Dashboard (under Plan)

**URL:** `/plan/blueprints`
**Purpose:** PSM manages blueprint assignments across their portfolio, monitors resource progression, identifies gaps.

**Layout:**
- **Top row:** KPI cards — Total Assignments (active), Enrollment Count, Completion Rate (%), Avg Days to Complete, Overdue Enrollments
- **Assignment table:** Partner, Blueprint Name, Product Area, Role (badge), Status, Enrolled/Completed count, Progress %, Days Active, Actions (View/Edit)
- **Filter bar:** By partner, product area, role, status, blueprint
- **Gap analysis panel:** Cross-references pipeline product areas against blueprint completions — highlights partners with pipeline in areas where no resources have completed the corresponding blueprint (red flag, links to Delivery Capability Risk engine)
- **Enrollment detail (expandable):** Per assignment, show each enrolled contact with: Name, Progress (X/Y items completed), Status badge, Enrolled date, Last activity date

### Blueprint Notifications

When blueprint-related events occur, the system sends notifications using admin-configured templates (see [Section 17: Notification Template System](#17-notification-template-system)):
- **New blueprint assigned** → Notify enrolled contacts
- **Resource enrolled in blueprint** → Notify the resource contact
- **Blueprint completed** → Notify PSM and partner admin
- **Overdue blueprint** → Notify PSM (> 90 days with < 50% progress)

---

## 9. Solution & Engagement Lifecycle

### Concept

Solutions and major engagements follow defined lifecycle stages. Tracking where solutions are in their lifecycle helps PSMs guide partners from initial capability building through to revenue generation.

Inspired by AWS Partner Central's Solution lifecycle (Build → Market → Sell → Grow).

### Solution Lifecycle Stages

| Stage | Name | Description | Key Activities |
|-------|------|-------------|---------------|
| 1 | **Build** | Partner is developing the solution/IP/demo environment | Technical enablement, architecture reviews, demo builds, POC creation |
| 2 | **Market** | Solution is ready; partner is building go-to-market capability | Marketing materials, joint messaging, partner listing, case studies |
| 3 | **Sell** | Actively selling the solution to customers | Lead generation, pipeline building, joint selling, deal support |
| 4 | **Grow** | Solution is established; focus on scaling and expanding | Repeat deployments, customer success, expansion, thought leadership |

### Data Model: Solutions

**New migration: `partner_solutions`**
```
id
partner_id          — foreignId
name                — string (e.g., 'Supply Chain Control Tower', 'FP&A Rapid Deployment')
description         — text (nullable)
product_area        — string (nullable, e.g., 'Supply Chain', 'FP&A', 'Workforce')
industry_focus      — string (nullable, e.g., 'Manufacturing', 'Retail', 'Life Sciences')
lifecycle_stage     — enum (build, market, sell, grow)
stage_entered_at    — datetime
status              — enum (active, paused, retired)
demo_url            — string (nullable)
documentation_url   — string (nullable)
estimated_acv       — decimal (nullable, typical deal size)
deployment_duration_weeks — integer (nullable, typical implementation timeline)
created_by          — foreignId
notes               — text (nullable)
```

**New migration: `solution_stage_history`**
```
id
partner_solution_id — foreignId
from_stage          — string (nullable)
to_stage            — string
transitioned_at     — datetime
transitioned_by     — foreignId
notes               — text (nullable)
```

### View: Solution Lifecycle Dashboard

**URL:** `/engage/solutions` (under Engage section)
**Purpose:** Track all partner solutions through their lifecycle, identify stalled solutions, celebrate graduates.

**Layout:**
- **Top:** 4-column pipeline showing solution counts per stage (Build | Market | Sell | Grow) with partner logos/badges
- **Table:** All solutions with: Partner, Solution Name, Product Area, Industry, Stage (badge), Days in Stage, Status, Est. ACV, Actions
- **Detail panel:** Stage history timeline, related opportunities (linked by product_area + partner), related projects, task checklist per stage
- **Filters:** By partner, product area, industry, stage, status

---

## 10. Partner Scorecard

### Concept

Every partner has a **quantitative scorecard** — a multi-dimensional evaluation that produces an overall score (0-100) and individual dimension scores. The scorecard replaces subjective maturity assessments with data-driven evaluation.

The scorecard directly feeds into **gate eligibility** — partners must meet minimum scorecard thresholds to advance path stages.

### Scorecard Dimensions (6 dimensions, configurable weights)

| Dimension | Default Weight | Description |
|-----------|---------------|-------------|
| **Certification Readiness** | 15% | How well-staffed is the partner with certified resources? |
| **Pipeline Maturity** | 20% | Pipeline health: coverage, quality, velocity |
| **Delivery Quality** | 20% | Project execution: on-time, CSAT, escalations |
| **Engagement Velocity** | 15% | PSM touchpoint frequency, responsiveness, participation |
| **Growth Trajectory** | 20% | Revenue growth, expansion, new logos |
| **Self-Sufficiency** | 10% | Partner-originated pipeline, independent deal closing, solution IP |

### Dimension Scoring Details

**Certification Readiness (15%)**
| Sub-Metric | Weight | Scoring | Data Source |
|-----------|--------|---------|-------------|
| Certified MB count vs. requirement | 30% | 100 if count ≥ 2x requirement, 0 if count = 0 | certifications, certification_requirements |
| Certified SA count vs. requirement | 30% | Same scaling | certifications, certification_requirements |
| Cert-to-requirement ratio | 20% | 100 if all requirements met, proportional otherwise | certifications, certification_requirements |
| Expiring certs (within 90 days) | 10% | 100 if none expiring, -20 per expiring cert | certifications.expiry_date |
| Product area coverage | 10% | 100 if ≥ 3 areas covered, proportional otherwise | certifications.product_area |

**Pipeline Maturity (20%)**
| Sub-Metric | Weight | Scoring | Data Source |
|-----------|--------|---------|-------------|
| Pipeline coverage ratio | 25% | 100 if ≥ 3x, 0 if < 0.5x, linear between | opportunities, partner_goals |
| Win rate (trailing 12m) | 25% | 100 if ≥ 50%, 0 if < 10%, linear between | opportunities (Won vs Lost) |
| Average deal size trend | 15% | 100 if growing ≥ 20% YoY, 0 if declining ≥ 20% | opportunities.amount |
| Stage velocity (avg days per stage) | 15% | 100 if < 30 days avg, 0 if > 120 days | opportunity_snapshots |
| Product area diversity | 10% | 100 if ≥ 3 areas, proportional | opportunities.product_area |
| Stale deals (no movement 30+ days) | 10% | 100 if 0 stale, -15 per stale deal | opportunity_snapshots |

**Delivery Quality (20%)**
| Sub-Metric | Weight | Scoring | Data Source |
|-----------|--------|---------|-------------|
| On-time delivery rate | 25% | 100 if ≥ 90%, proportional | projects (end_date vs actual) |
| Average CSAT | 25% | 100 if ≥ 4.5, 0 if < 2.0, linear between | projects.customer_satisfaction |
| Escalation rate | 20% | 100 if 0%, 0 if > 25%, inverse linear | cases.escalated |
| Referenceable rate | 15% | 100 if ≥ 50%, proportional | projects.is_referenceable |
| Scope change frequency | 15% | 100 if 0, -10 per scope change on avg | projects.scope_changes |

**Engagement Velocity (15%)**
| Sub-Metric | Weight | Scoring | Data Source |
|-----------|--------|---------|-------------|
| PSM touchpoints (trailing 90 days) | 30% | 100 if ≥ 12 (weekly), 0 if 0 | partner_events |
| Enablement attendance rate | 25% | 100 if ≥ 80%, proportional | enablement_registrations (attended / registered) |
| Blueprint progress | 20% | 100 if all assigned complete, proportional | blueprint_enrollments |
| Action plan completion rate | 15% | 100 if ≥ 90%, proportional | action_plans |
| Response time (task turnaround) | 10% | 100 if < 3 days avg, 0 if > 14 days | partner_tasks (started_at - created_at) |

**Growth Trajectory (20%)**
| Sub-Metric | Weight | Scoring | Data Source |
|-----------|--------|---------|-------------|
| QoQ revenue growth | 30% | 100 if ≥ 30% growth, 0 if declining | opportunities (Won, by quarter) |
| New logo acquisition | 20% | 100 if ≥ 3 new logos trailing 12m, proportional | opportunities (unique accounts) |
| Expansion rate | 20% | 100 if ≥ 30% of projects lead to expansion, proportional | projects.led_to_expansion |
| Pipeline growth rate | 15% | 100 if ≥ 20% QoQ pipeline growth | opportunity_snapshots |
| Goal attainment | 15% | 100 if ≥ 100% of NNACV target, proportional | opportunities vs partner_goals |

**Self-Sufficiency (10%)**
| Sub-Metric | Weight | Scoring | Data Source |
|-----------|--------|---------|-------------|
| Partner-originated pipeline ratio | 40% | 100 if ≥ 50% partner-originated, proportional | opportunities.origin |
| Independent deal closing | 30% | 100 if ≥ 30% deals closed without Anaplan support, proportional | opportunities (custom field) |
| Solution/IP assets | 30% | 100 if ≥ 2 active solutions, proportional | partner_solutions |

### Data Model: Scorecard

**New migration: `partner_scorecards`**
```
id
partner_id              — foreignId
scored_at               — date (indexed)
overall_score           — decimal (0-100, 1 decimal)
certification_score     — decimal (0-100)
pipeline_score          — decimal (0-100)
delivery_score          — decimal (0-100)
engagement_score        — decimal (0-100)
growth_score            — decimal (0-100)
self_sufficiency_score  — decimal (0-100)
tier                    — enum (onboarded, enabled, performing, accelerating, strategic) — derived from path stage
gate_eligible           — boolean — whether score meets next gate threshold
score_details           — json (full breakdown of every sub-metric)
scoring_config          — json (weights used at time of scoring, for auditability)
```

**New migration: `scorecard_weights`** (admin-configured)
```
id
dimension               — string (certification, pipeline, delivery, engagement, growth, self_sufficiency)
weight                  — decimal (0-1, all must sum to 1.0)
is_active               — boolean (default true)
updated_by              — foreignId
```

### Scorecard Calculation

- **On demand** when viewing Scorecard dashboard for a partner
- **Scheduled daily** via `php artisan partners:calculate-scorecards` (stores snapshot for trending)
- **Triggered** after significant data changes (import complete, project status change, task completion)
- **Cached** per partner with 1-hour TTL for dashboard performance

### View: Scorecard Dashboard

**URL:** `/plan/scorecard`
**Purpose:** See at-a-glance health of every partner in portfolio, drill into dimension breakdowns, identify trends.

**Layout:**
- **Top row:** 6 KPI cards showing portfolio averages per dimension
- **Portfolio heatmap:** Grid of all partners, each cell color-coded by overall score (red < 30, yellow 30-55, green 55-80, blue 80+), click to expand
- **Expanded partner view:** Radar chart (6 axes for dimensions), trend sparkline (last 6 scores), gate readiness checklist, top 3 improvement opportunities (lowest sub-metrics with actionable suggestions)
- **Comparison mode:** Select 2-3 partners side-by-side, overlay radar charts
- **Trend analysis:** Line chart of overall score over time for selected partners
- **Distribution:** Histogram of partners by score band, grouped by path stage

---

## 11. Section: Plan

### 11.1 Partner Paths Dashboard
See [Section 6](#6-partner-paths--gate-based-progression) for full specification.

### 11.2 Scorecard Dashboard
See [Section 10](#10-partner-scorecard) for full specification.

### 11.3 Competency Matrix

**Purpose:** Track partner capabilities against Anaplan product areas. Show where partners can sell/deliver vs. where they have gaps. Directly feeds the Certification Readiness dimension of the scorecard.

**Existing data:**
- `resources` table: name, role, program, status, date_certified
- `resource_snapshots`: aggregated counts by program/role

**What's missing:**
- No concept of "certification type" with expiry, version, or product area mapping
- No minimum thresholds per path stage
- No mapping between certifications and what a partner is qualified to deliver

**New migration: `certifications`**
```
id
contact_id          — foreignId (nullable, for named individuals)
partner_id          — foreignId
type                — enum (model_builder, solution_architect, master_anaplanner, data_integration_specialist, planning_consultant)
product_area        — string (nullable, e.g., 'FP&A', 'Supply Chain', 'Workforce', 'Sales Performance', 'Connected Planning')
level               — enum (associate, professional, master)
status              — enum (active, expired, in_progress, scheduled, revoked)
issued_date         — date (nullable)
expiry_date         — date (nullable)
exam_score          — integer (nullable)
version             — string (nullable, e.g., 'UX', 'Classic')
credential_id       — string (nullable, external cert ID)
source              — string (nullable, e.g., 'Anaplan Academy', 'Partner Import', 'Salesforce Sync')
import_id           — foreignId (nullable)
```

**New migration: `certification_requirements`** (admin-configured, tied to path stages)
```
id
partner_path_id     — foreignId (nullable, null = global)
stage_id            — foreignId (partner_path_stages, nullable)
certification_type  — string
minimum_count       — integer
product_area        — string (nullable, specific area or null = any)
description         — string (nullable)
```

**View features:**
- **Matrix grid:** Partners (rows) × product areas (columns), cells showing cert count with color coding:
  - Red: below minimum for current stage
  - Yellow: at minimum (no buffer)
  - Green: above minimum
  - Blue: significantly above (2x+)
- **Individual partner drill-down:** All certified resources listed with: Name, Type, Level, Product Area, Status badge, Expiry date, Days until expiry
- **Expiring soon alerts:** 30/60/90 day warning windows with contact names
- **Certification pipeline:** Contacts with status = `in_progress` or `scheduled`
- **Historical trend:** Line chart of active cert count over time (from resource_snapshots)
- **Gap analysis table:** For each stage gate requirement, show current count vs. required count

### 11.4 Blueprints Dashboard
See [Section 8](#8-enablement-blueprints--structured-learning-paths) for full specification.

### 11.5 Goals & Targets

**Purpose:** Manage fiscal year goals per partner across all dimensions and track progress. Revenue goals are backed by quantitative pipeline math — the PSM and partner jointly calculate how many opportunities are needed to hit targets based on historical data.

**Existing data:**
- `partner_goals` table: fiscal_year, sourced_nnacv_target, influenced_nnacv_target
- `opportunities`: amount, revenue_type, fiscal_year

**What's missing:**
- Goals only cover revenue (sourced/influenced NNACV). No goals for certifications, project delivery, enablement, pipeline.
- No quarterly breakdown of annual targets
- No goal categories beyond revenue
- No pipeline math: ASP, velocity, required opportunity count, pipeline multiple

**New migration: `partner_goal_dimensions`** (extends partner_goals)
```
id
partner_goal_id     — foreignId
dimension           — enum (sourced_nnacv, influenced_nnacv, certification_mb, certification_sa, projects_delivered, projects_ontime_pct, enablement_sessions, pipeline_coverage_ratio, win_rate, customer_satisfaction, partner_originated_pct, solutions_registered, custom)
target_value        — decimal
actual_value        — decimal (nullable, computed/cached)
unit                — string (count, percentage, currency, ratio)
label               — string (nullable, for custom dimensions)
q1_target           — decimal (nullable, quarterly breakdown)
q2_target           — decimal (nullable)
q3_target           — decimal (nullable)
q4_target           — decimal (nullable)
```

### Revenue Goal Planning — Pipeline Math

The Goals & Targets view includes a **Revenue Planning Calculator** that helps PSMs and partners set realistic targets backed by data:

**Inputs (computed from historical data + editable overrides):**
| Input | Source | Description |
|-------|--------|-------------|
| **Historical ASP** | `AVG(amount)` from Won opps (trailing 12-24m) | Average license sales price |
| **Win Rate** | `Won / (Won + Lost)` trailing 12m | Historical conversion rate |
| **Stage Velocity** | From opportunity_snapshots: avg days at each stage | How fast deals move from Stage 1 → Stage 5 |
| **Stage Attrition** | From snapshots: % of opps that drop out at each stage | Natural pipeline shrinkage rates |
| **Pipeline Multiple** | Derived: `1 / Win Rate` (e.g., 25% win rate → 4x multiple) | How much pipeline needed per $1 of target |

**Outputs (auto-calculated):**
| Output | Formula | Example |
|--------|---------|---------|
| **Required Closed Deals** | `NNACV Target / ASP` | $2M target / $200K ASP = 10 deals |
| **Required Pipeline** | `NNACV Target × Pipeline Multiple` | $2M × 4x = $8M pipeline needed |
| **Required New Opps (per quarter)** | `(Required Closed / Win Rate) / 4` adjusted for velocity | 40 opps / 4 quarters = 10 new opps/quarter |
| **Pipeline Gap** | `Required Pipeline - Current Pipeline` | $8M needed - $3M current = $5M gap |
| **Quarterly Booking Pace** | `NNACV Target / 4` vs. actual YTD run rate | $500K/quarter needed vs. $400K actual |

**View features:**
- **Goal progress dashboard:** Per partner, donut/gauge charts per dimension with target line
- **Revenue Planning Calculator:** Interactive panel showing the pipeline math above with editable assumptions (ASP, win rate overrides)
- **Pipeline waterfall:** Visual showing how many opps at each stage are needed to yield the target, accounting for stage attrition
- **Portfolio-wide attainment:** Table of all partners with % attainment per goal dimension
- **Quarterly tracking:** Bar chart showing Q1-Q4 targets vs. actuals, with run rate projection
- **Gap analysis:** Red/yellow/green status per partner × dimension, highlighting which goals are at risk
- **Goal templates:** Admin can define standard goal sets per path stage

### 11.6 Task Board
See [Section 7](#7-prescriptive-task-system) for full specification.

### 11.7 Action Plans

**Purpose:** Formalized, time-bound commitments from QBRs and partner meetings. Close the loop from insight to action.

**New migration: `action_plans`**
```
id
partner_id          — foreignId
title               — string
description         — text (nullable)
source              — enum (qbr, partner_review, escalation, psm_initiative, system_recommendation, path_review)
status              — enum (open, in_progress, completed, cancelled, overdue)
priority            — enum (low, medium, high, critical)
owner_type          — enum (psm, partner, joint)
owner_user_id       — foreignId (nullable, for PSM-owned)
owner_contact_id    — foreignId (nullable, for partner-owned)
due_date            — date
completed_at        — datetime (nullable)
outcome             — text (nullable)
created_by          — foreignId
fiscal_quarter      — string (nullable)
partner_event_id    — foreignId (nullable, links to originating QBR/meeting)
linked_task_id      — foreignId (nullable, links to partner_tasks)
```

**New migration: `action_plan_items`**
```
id
action_plan_id      — foreignId
description         — string
status              — enum (pending, in_progress, done, blocked)
due_date            — date (nullable)
assigned_to         — string (nullable)
completed_at        — datetime (nullable)
notes               — text (nullable)
sort_order          — integer
```

**View features:**
- **Kanban board view** (by status) and **list view** — toggleable
- Filter by partner, priority, owner, source, fiscal quarter
- Overdue items highlighted with red border and age indicator
- Linked to partner events (e.g., "Created from Q3 QBR on 2026-01-15")
- Linked to prescriptive tasks (e.g., "Related to: Complete core blueprint")
- Completion rate over time chart (trailing 6 quarters)
- Per-partner action plan history

---

## 12. Section: Engage (Enhanced)

The existing Engage section (Project Health, Field Engagements, Field Enablement) is functional. Enhancements below.

### 12.1 Project Health — Enhancements
- **Post-go-live tracking:** Add outcome fields to projects (customer_satisfaction, is_referenceable, led_to_expansion)
- **Delivery team performance:** Aggregate project outcomes per contact/consultant
- **Case tracking integration:** Link support cases to projects
- **Delivery capability risk indicator:** Show warning when project's product area lacks blueprint-completed resources (see Section 16)

### 12.2 Field Engagements — Enhancements
- **Meeting notes & outcomes:** Already supported via PartnerEvent.outcome and next_steps — ensure views surface these prominently
- **Touchpoint frequency scoring:** Compute engagement cadence per partner (feeds scorecard Engagement Velocity dimension)
- **Auto-task creation:** When a touchpoint is logged, automatically complete relevant partner_tasks if applicable

### 12.3 Field Enablement — Enhancements
- **Certification tracking link:** Connect to new Competency Matrix
- **Blueprint completion trends:** Chart progress over time, linked to scorecard
- **Blueprint → Calendar linking:** When a blueprint outcome requires a training session, suggest available enablement calendar events that satisfy the requirement

### 12.4 Solution Lifecycle (NEW)
See [Section 9](#9-solution--engagement-lifecycle) for full specification.

---

## 13. Section: Measure — Tabbed Analytics

### Concept

The Measure section is organized as a **tabbed analytics dashboard** inspired by AWS Partner Central's analytics layout. Each tab focuses on a specific analytical domain, with consistent design patterns:

- **KPI cards** at top (4-6 metrics with trend indicators)
- **Primary chart** (the main visualization for the tab)
- **Supporting charts** (2-3 smaller charts providing context)
- **Detail table** (underlying data with filters and sorting)

All tabs share global filters: **Fiscal Year**, **Quarter**, **Partner** (or "All Portfolio"), **Geography**, **Product Area**

### Tab 1: At a Glance

**Purpose:** Executive summary of the PSM's entire portfolio in one view.

**KPI Cards (8):**
| KPI | Metric | Trend |
|-----|--------|-------|
| Portfolio Health | Average scorecard score across all partners | ▲▼ vs. last quarter |
| Total Pipeline | Sum of open opportunity amounts | ▲▼ vs. last quarter |
| Win Rate | Portfolio-wide won / (won + lost) trailing 12m | ▲▼ vs. prior 12m |
| Revenue (Sourced) | Closed-Won sourced NNACV FY-to-date | vs. target |
| Revenue (Influenced) | Closed-Won influenced NNACV FY-to-date | vs. target |
| Active Projects | Count of in-progress projects | ▲▼ vs. last quarter |
| Active Certifications | Count of active certs across portfolio | ▲▼ vs. last quarter |
| Overdue Tasks | Count of overdue required tasks | — |

**Charts:**
1. **Portfolio scorecard distribution** — Stacked bar showing partner count per score band (0-30, 31-55, 56-80, 81-100) colored by tier
2. **Revenue vs. Target** — Grouped bar chart: Sourced target/actual + Influenced target/actual, by quarter
3. **Path stage funnel** — Horizontal funnel showing count of partners per path stage
4. **Activity heatmap** — Calendar heatmap showing PSM touchpoints per day (last 90 days)

### Tab 2: Pipeline Analysis

**Purpose:** Deep dive into pipeline health, velocity, and quality.

**KPI Cards:**
| KPI | Metric |
|-----|--------|
| Pipeline Value | Total open pipeline amount |
| Pipeline Coverage | Pipeline / Remaining annual target |
| Avg Deal Size | Average opportunity amount |
| Stale Deals | Opportunities with no stage change in 30+ days |
| Stage Velocity | Average days per stage |
| Product Areas | Distinct product areas in pipeline |

**Charts:**
1. **Pipeline waterfall** — Funnel showing opportunity count and value at each stage, with drop-off rates
2. **Pipeline by origin** — Stacked bar showing Partner Originated vs. Anaplan Originated vs. Customer Engagement per quarter
3. **Win rate trend** — Line chart by quarter, with peer benchmark overlay
4. **Stage velocity heatmap** — Partners (rows) × stages (columns), cells colored by avg days (green=fast, red=slow)
5. **Stale deal table** — Opportunities with days since last movement, current stage, partner, amount

### Tab 3: Lead Flow

**Purpose:** Track lead generation, assignment, conversion, and quality.

**KPI Cards:**
| KPI | Metric |
|-----|--------|
| Total Leads | Count of all leads (FY) |
| Conversion Rate | Leads → Opportunities (%) |
| Avg Response Time | Days from lead created to assigned |
| Accepted Rate | % of assigned leads accepted by partner |
| Pipeline from Leads | Total $ of opportunities originating from leads |
| Rejected Rate | % of leads rejected |

**Charts:**
1. **Lead funnel** — Sankey diagram: New → Assigned → Accepted → Opportunity Created → Won
2. **Lead volume trend** — Line chart of leads per month, by status
3. **Lead quality by partner** — Grouped bar showing accepted vs. rejected vs. pending, per partner
4. **Lead source breakdown** — Donut chart by lead source
5. **Response time distribution** — Histogram of days to respond

### Tab 4: Funding

**Purpose:** Track development funding investments, utilization, and ROI.

**KPI Cards:**
| KPI | Metric |
|-----|--------|
| Total Funding | Sum of all funding amounts (FY) |
| Utilized | % of funding consumed |
| Pending Requests | Count of pending funding requests |
| ROI Ratio | Revenue generated / Funding invested |

**Charts:**
1. **Funding by type** — Donut chart (MDF, development, training, co-marketing, etc.)
2. **Funding utilization timeline** — Stacked area chart showing allocated vs. consumed over months
3. **Funding by partner** — Horizontal bar chart, sorted by amount
4. **ROI analysis** — Scatter plot: funding amount (x) vs. revenue generated (y), bubble size = partner size

### Tab 5: Revenue

**Purpose:** Detailed revenue analytics — bookings, ACV, partner contribution.

**KPI Cards:**
| KPI | Metric |
|-----|--------|
| Total Bookings (FY) | Closed-Won amount FY-to-date |
| Sourced NNACV | Partner-sourced closed won |
| Influenced NNACV | Partner-influenced closed won |
| Avg ACV | Average deal size for closed won |
| YoY Growth | Revenue growth vs. prior FY |
| Partner Contribution | % of partner-originated revenue |

**Charts:**
1. **Revenue by quarter** — Grouped bar: Sourced + Influenced per quarter with target line
2. **Revenue by product area** — Horizontal bar chart
3. **Revenue by partner** — Treemap showing relative revenue contribution
4. **Revenue trend (trailing 8 quarters)** — Line chart with trend line and YoY growth annotation
5. **ACV distribution** — Histogram of deal sizes with median marker

### Tab 6: Enablement

**Purpose:** Measure training, certification, and blueprint effectiveness.

**KPI Cards:**
| KPI | Metric |
|-----|--------|
| Active Certifications | Total active certs in portfolio |
| Expiring (90 days) | Certs expiring within 90 days |
| Attendance Rate | Event attendance / registrations |
| Blueprint Completion | % of assigned blueprints completed |
| Training Hours | Total enablement hours (FY) |
| Cert → Revenue | Revenue per certified resource |

**Charts:**
1. **Certification trend** — Stacked area chart of cert counts by type over time
2. **Blueprint progress** — Horizontal stacked bar per partner showing complete/in-progress/not-started
3. **Enablement ROI** — Scatter plot: enablement hours (x) vs. win rate improvement (y)
4. **Expiry timeline** — Timeline chart showing upcoming cert expirations by month with contact names
5. **Training attendance** — Bar chart of registrations vs. attendees per event, with no-show rate

### Tab 7: Delivery

**Purpose:** Project execution quality, customer satisfaction, case tracking.

**KPI Cards:**
| KPI | Metric |
|-----|--------|
| Active Projects | Count of in-progress projects |
| Avg CSAT | Average customer satisfaction (1-5) |
| On-Time Rate | % of projects delivered on or before end_date |
| Escalation Rate | % of cases escalated |
| Referenceable | % of completed projects marked referenceable |
| Open Cases | Count of unresolved cases |

**Charts:**
1. **Project status distribution** — Donut chart by status (planning, in_progress, completed, on_hold, cancelled)
2. **CSAT trend** — Line chart of avg CSAT by quarter
3. **Delivery timeline** — Gantt chart of active projects with planned vs. actual dates
4. **Case volume by partner** — Bar chart colored by severity
5. **Escalation trend** — Line chart of escalation rate over time with threshold line
6. **Time to resolution** — Box plot by case category

---

## 14. Section: Act

### 14.1 Alerts & Recommendations

**Purpose:** System-generated, prioritized next-best-actions based on partner data, scorecard trends, path requirements, task status, and **delivery capability risk**.

**New migration: `partner_alerts`**
```
id
partner_id          — foreignId
type                — enum (certification_expiry, pipeline_risk, delivery_risk, delivery_capability_risk, engagement_gap, goal_gap, stale_partner, task_overdue, case_escalation, path_gate_ready, path_gate_blocked, scorecard_decline, blueprint_overdue, system)
severity            — enum (info, warning, critical, positive)
title               — string
description         — text
recommended_action  — text (prescriptive: what the PSM should do)
data                — json (nullable, structured payload for the alert)
status              — enum (active, acknowledged, resolved, dismissed)
acknowledged_by     — foreignId (nullable)
resolved_at         — datetime (nullable)
auto_resolve_when   — string (nullable, describes condition for auto-resolution)
linked_task_id      — foreignId (nullable, partner_tasks — suggested task to address this)
created_at          — timestamp
```

**Alert generation rules (computed on demand or via `php artisan partners:generate-alerts`):**

| Alert Type | Trigger Condition | Severity | Recommended Action |
|-----------|-------------------|----------|--------------------|
| certification_expiry | Cert expiring within 30 days | warning | "Schedule certification renewal for [Contact Name] — expires [Date]. Book through Anaplan Academy." |
| certification_expiry | Cert expired or expiring within 7 days | critical | "URGENT: [Contact Name]'s [Cert Type] certification expires in [N] days. This affects gate requirement [X]. Contact partner immediately." |
| pipeline_risk | Pipeline coverage ratio < 1.5x for current quarter | warning | "Partner [Name] pipeline coverage is [X]x against [Target]. Schedule pipeline review meeting and identify joint prospecting opportunities." |
| pipeline_risk | No new opportunities in 60+ days | critical | "No new pipeline created for [Partner] in [N] days. This blocks Performing stage gate. Schedule business development planning session." |
| delivery_risk | Project risk score ≥ 60 | critical | "Project [Name] at [Partner] has risk score [X]. Key risks: [list]. Escalate to delivery leadership and schedule intervention." |
| delivery_risk | Project risk score ≥ 35 | warning | "Project [Name] shows elevated risk ([X]). Monitor closely and address: [specific risk factors]." |
| **delivery_capability_risk** | Partner has pipeline in product area with no blueprint-completed delivery resources | critical | "CRITICAL: [Partner] has [N] opportunities ($[Amount]) in [Product Area] but NO delivery resources have completed the [Blueprint Name] blueprint. Assign resources to blueprint immediately and register for upcoming [Event Name] on [Date]." |
| **delivery_capability_risk** | Partner has pipeline in product area with < 2 qualified resources | warning | "Partner [Name] has $[Amount] pipeline in [Product Area] but only [N] resource(s) have completed the delivery blueprint. Consider enrolling additional resources in [Blueprint Name]." |
| engagement_gap | No PSM touchpoint in 30+ days | warning | "No recorded interaction with [Partner] in [N] days. Engagement Velocity scorecard dimension is declining. Schedule a touchpoint." |
| engagement_gap | No PSM touchpoint in 60+ days | critical | "CRITICAL: [N] days since last interaction with [Partner]. This violates Performing stage requirements. Immediate outreach required." |
| goal_gap | Any goal dimension at < 50% with < 50% of FY remaining | warning | "Partner [Name] is at [X%] of [Goal Dimension] target with [N] months remaining. Current run rate projects [Y%] at year end." |
| task_overdue | Required task past due date | warning | "[Task Title] for [Partner] is [N] days overdue. This is a required task for [Stage] stage. Follow up with [Owner]." |
| task_overdue | Required task 14+ days overdue | critical | "CRITICAL: Required task [Title] for [Partner] is [N] days overdue. This blocks path advancement. Escalate immediately." |
| case_escalation | New case escalation | critical | "Case [Number] for [Partner] has been escalated (severity: [X]). Subject: [Subject]. Coordinate with support team." |
| path_gate_ready | All gate requirements for next stage met | positive | "Partner [Name] meets ALL requirements for [Next Stage] stage! Schedule path review meeting to formalize advancement." |
| path_gate_blocked | Partner in stage > 6 months with < 50% gates met | warning | "Partner [Name] has been in [Stage] for [N] months with only [X/Y] gate requirements met. Review path viability and create action plan." |
| scorecard_decline | Overall score dropped > 10 points in 30 days | warning | "Partner [Name] scorecard dropped from [X] to [Y]. Largest decline in [Dimension]. Investigate and address root cause." |
| **blueprint_overdue** | Blueprint assignment > 90 days with < 50% progress | warning | "Blueprint [Name] assigned to [Partner] for [Role] role is overdue — [X%] complete after [N] days. Follow up with enrolled resources: [Names]." |
| stale_partner | No data updates in 45+ days | info | "No data activity for [Partner] in [N] days. Verify Salesforce sync is active and data imports are current." |

**View features:**
- **Alert feed** sorted by severity (critical first) then recency
- **Grouped view** by partner or by alert type
- **Filter:** By partner, type, severity, status (active/acknowledged/resolved)
- **Actions per alert:** Acknowledge, Dismiss (with reason), Resolve (with notes), Create Task, Schedule Meeting, Assign Blueprint (for capability risks)
- **Alert history** with resolution notes — builds institutional knowledge
- **Badge count** in sidebar navigation showing unresolved critical + warning count
- **Daily digest** summary (future: email/Slack notification)

### 14.2 QBR Workbench

**Purpose:** Structured workflow for the PSM's most important recurring activity — the Quarterly Business Review. The workbench auto-generates a QBR agenda pre-populated with all relevant data, provides a facilitation interface during the meeting, and ensures follow-through via auto-created action plans.

**New migration: `qbr_sessions`**
```
id
partner_id          — foreignId
fiscal_quarter      — string (e.g., 'Q1 FY2027')
status              — enum (planning, scheduled, in_progress, completed, cancelled)
scheduled_date      — date (nullable)
completed_date      — date (nullable)
partner_event_id    — foreignId (nullable, links to calendar event)
prepared_by         — foreignId
attendees           — json (nullable, list of contact IDs and external names)
overall_rating      — enum (strong, satisfactory, needs_improvement, at_risk) (nullable)
executive_summary   — text (nullable)
```

**New migration: `qbr_sections`**
```
id
qbr_session_id      — foreignId
section_type        — enum (scorecard_review, pipeline_review, delivery_review, enablement_review, goals_review, path_review, task_review, action_review, open_discussion)
sort_order          — integer
auto_generated_data — json (nullable, system-generated metrics snapshot)
psm_notes           — text (nullable)
partner_feedback    — text (nullable)
rating              — enum (strong, satisfactory, needs_improvement, at_risk) (nullable)
```

**QBR Workflow:**

1. **Prep Mode** — PSM clicks "Prepare QBR" for a partner + quarter. System auto-generates:
   - Scorecard snapshot with delta from last QBR
   - Pipeline summary (new, progressed, won, lost since last QBR) with revenue planning math
   - Project delivery status (active, completed, any issues)
   - Enablement progress (certifications gained/expiring, blueprints, attendance)
   - Goal attainment snapshot (% achieved YTD per dimension)
   - Path stage and gate progress (with advancement eligibility)
   - Delivery capability risk summary (product areas with pipeline but no qualified resources)
   - Overdue/open task summary
   - Open action items from previous QBR

2. **Execution Mode** — During the QBR meeting:
   - Walk through sections in order, each pre-populated with data
   - PSM adds notes and partner feedback per section
   - Rate each section (strong/satisfactory/needs_improvement/at_risk)
   - Capture new action items in real-time

3. **Follow-Up Mode** — After QBR:
   - Auto-create action_plans from captured items
   - Auto-create partner_tasks for committed activities
   - Generate executive summary
   - Schedule next QBR
   - If partner meets all gate requirements, prompt path advancement review

**View features:**
- **QBR Calendar:** When is each partner's next QBR? Overdue QBRs flagged (> 100 days since last)
- **QBR History:** Side-by-side comparison of QBR data across quarters for same partner
- **QBR Templates:** Admin can define standard QBR section templates
- **Print/Export:** Generate PDF of QBR materials for sharing

### 14.3 Path Reviews (NEW)

**Purpose:** Formal evaluation of whether a partner should advance to the next path stage. This is a deliberate, documented process — not an automatic promotion.

**Process:**
1. PSM initiates a Path Review from the Partner Paths dashboard (or prompted by `path_gate_ready` alert)
2. System auto-evaluates all gate requirements and displays met/unmet status with actual values
3. PSM reviews and adds commentary on each gate requirement
4. PSM makes recommendation: Advance, Hold (with reason), or Override (with justification)
5. If Advance: partner moves to next stage, new tasks auto-populated, stage_history recorded
6. If Hold: action plan created to address gaps, review date set
7. If Override: admin approval required (for cases where quantitative gates are missed but qualitative factors justify advancement)

**This uses the existing `partner_stage_history` table for the audit trail and `partner_path_gates` for evaluation.**

---

## 15. Pipeline & Opportunity Intelligence

### Origin Tracking

Every opportunity must be tagged with its **origin** — how the deal was sourced. This is critical for measuring true partner contribution and self-sufficiency.

**New column on `opportunities` table:**
```
origin              — enum (partner_originated, anaplan_originated, customer_engagement) — nullable, default null
```

**Definitions:**
| Origin | Description | Self-Sufficiency Impact |
|--------|-------------|----------------------|
| **Partner Originated** | Partner identified the opportunity independently and brought it to Anaplan | High — counts toward Self-Sufficiency scorecard dimension |
| **Anaplan Originated** | Anaplan sales team sourced the deal and engaged the partner for delivery | Low — partner is reactive, not driving business |
| **Customer Engagement** | Customer initiated the engagement, partner was selected by customer or Anaplan | Medium — partner has reputation but didn't actively source |

**View impact:**
- Pipeline Analysis tab shows origin breakdown
- Scorecard Self-Sufficiency dimension weights partner-originated deals
- Path gate requirements reference partner-originated pipeline ratios
- Revenue tab shows partner contribution by origin

### Win/Loss Tracking

**New columns on `opportunities` table:**
```
win_loss_reason     — string (nullable)
lost_to_competitor  — string (nullable, competitor name)
```

### Data Model: Cases (Support Tracking)

**New migration: `cases`** (support tickets/escalations linked to partners and projects)
```
id
partner_id          — foreignId
project_id          — foreignId (nullable)
account_name        — string (nullable)
case_number         — string (nullable, unique)
subject             — string
severity            — enum (low, medium, high, critical)
status              — enum (open, in_progress, escalated, resolved, closed)
category            — string (nullable, e.g., 'Data Integration', 'Platform', 'Permissions', 'Performance')
opened_date         — date
resolved_date       — date (nullable)
escalated           — boolean (default false)
escalation_reason   — text (nullable)
resolution_notes    — text (nullable)
resolution_days     — integer (nullable, computed: resolved_date - opened_date)
import_id           — foreignId (nullable)
```

### New Columns on `projects` Table

```
customer_satisfaction — integer (1-5 scale, nullable)
is_referenceable     — boolean (default false)
led_to_expansion     — boolean (default false)
scope_changes        — integer (default 0)
go_live_date         — date (nullable)
outcome_notes        — text (nullable)
```

---

## 16. Delivery Capability Risk Engine

### Concept

One of the most critical risk scenarios for a PSM is when a partner has **significant pipeline in a specific product area but lacks qualified delivery resources** for that area. For example, a partner may have $2M in Supply Chain opportunities but zero resources who have completed the Supply Chain delivery blueprint. This means the partner cannot staff projects if those deals close — leading to failed implementations, delayed starts, or loss of the opportunity.

The Delivery Capability Risk Engine cross-references **pipeline product areas** against **blueprint completions** and **certifications** to identify these gaps in real time.

### Risk Detection Logic

The engine evaluates each partner's portfolio by:

1. **Group open opportunities by product_area** — sum pipeline value and count per area
2. **For each product area with pipeline**, check:
   a. Does the partner have a **blueprint assignment** for this product area with role = `delivery`?
   b. If yes, how many enrolled contacts have **completed** the blueprint?
   c. Does the partner have **certified resources** (from `certifications` table) for this product area?
3. **Risk classification:**

| Pipeline in Area | Blueprint Assigned? | Completed Resources | Certified Resources | Risk Level | Action |
|-----------------|--------------------|--------------------|--------------------:|------------|--------|
| > $0 | No | 0 | 0 | **Critical** | Assign delivery blueprint immediately, register for next enablement event |
| > $0 | Yes | 0 | 0 | **Critical** | Enroll resources in blueprint, register for next enablement event |
| > $0 | Yes | 1 | ≤ 1 | **High** | Enroll additional resources — single point of failure |
| > $0 | Yes | ≥ 2 | ≥ 2 | **Low** | Adequate coverage |
| > $500K | Yes | < 3 | < 3 | **High** | High-value pipeline needs depth — enroll more resources |
| $0 | Any | Any | Any | **None** | No pipeline in this area |

### Alert Integration

Delivery capability risks generate `delivery_capability_risk` alerts (see Section 14.1) with prescriptive actions:
- **Critical:** Identifies the specific blueprint, suggests the next available enablement calendar event that maps to the blueprint, and names the partner contacts who should be enrolled
- **High/Warning:** Suggests enrolling additional resources with links to the blueprint assignment page

### Enablement Calendar Cross-Reference

When a capability risk is detected, the system should:
1. Look up `enablement_sessions` with matching product_area or blueprint category
2. Filter to sessions with `start_at` in the future and `status = 'active'`
3. Present these as recommended actions: "Register [Contact Name] for [Session Title] on [Date] to complete [Blueprint Name]"

### View Integration

Delivery capability risk data surfaces in:
- **Alerts & Recommendations** (Section 14.1) — as dedicated alert type
- **Blueprint Dashboard** (Section 8) — gap analysis panel
- **Project Health** (Section 12.1) — risk indicator per project
- **QBR Workbench** (Section 14.2) — auto-generated section
- **Scorecard** (Section 10) — feeds into Certification Readiness and Delivery Quality dimensions

---

## 17. Notification Template System

### Concept

The admin must be able to create and manage **notification templates** used to communicate with partner resources and PSMs about system events. Templates support variable substitution so that personalized notifications are sent automatically when events occur.

### Notification Events

| Event | Recipients | Description |
|-------|-----------|-------------|
| `blueprint_assigned` | Enrolled contacts | A new blueprint has been assigned to the partner for their role |
| `resource_enrolled_blueprint` | The enrolled contact | A specific resource has been enrolled in a blueprint |
| `blueprint_completed` | PSM, partner admin contact | A resource has completed all items in a blueprint |
| `blueprint_overdue` | PSM | A blueprint assignment is overdue (> 90 days, < 50% progress) |
| `event_registration` | The registered contact | A resource has been registered for an enablement event session |
| `event_reminder` | Registered contacts | Upcoming event session reminder (configurable: 7d, 1d, 1h before) |
| `event_completed` | The attendee, PSM | A resource has attended/completed an enablement event |
| `certification_earned` | The contact, PSM | A resource has earned a new certification |
| `certification_expiring` | The contact, PSM | A certification is expiring within a configurable window |
| `task_assigned` | Assigned user/contact | A new task has been assigned |
| `task_overdue` | Assigned user/contact, PSM | A task is past its due date |
| `path_stage_advanced` | PSM, partner admin contact | Partner has advanced to a new path stage |
| `qbr_scheduled` | Attendee contacts | A QBR has been scheduled |
| `alert_generated` | PSM | A new alert has been generated for a partner |

### Data Model: Notification Templates

**New migration: `notification_templates`** (admin-configured)
```
id
event_type          — string (from list above, e.g., 'blueprint_assigned')
channel             — enum (email, in_app, both)
subject             — string (supports variables, e.g., 'New Blueprint Assigned: {{blueprint_name}}')
body                — text (supports variables + basic HTML for email)
is_active           — boolean (default true)
created_by          — foreignId
updated_by          — foreignId (nullable)
```

**New migration: `notification_logs`** (audit trail)
```
id
notification_template_id — foreignId
event_type          — string
recipient_type      — enum (user, contact)
recipient_id        — foreignId
channel             — enum (email, in_app)
subject             — string (rendered)
body                — text (rendered)
status              — enum (sent, failed, queued)
sent_at             — datetime (nullable)
error               — text (nullable)
related_type        — string (nullable, polymorphic: 'App\Models\Blueprint', 'App\Models\EnablementSession', etc.)
related_id          — foreignId (nullable)
```

### Template Variables

Templates support `{{variable_name}}` syntax. Available variables per event type:

**Global (available in all templates):**
- `{{recipient_name}}`, `{{recipient_email}}`, `{{partner_name}}`, `{{psm_name}}`, `{{psm_email}}`, `{{app_url}}`

**Blueprint events:**
- `{{blueprint_name}}`, `{{blueprint_product_area}}`, `{{role}}`, `{{progress_pct}}`, `{{enrolled_date}}`, `{{blueprint_url}}`

**Event/session events:**
- `{{event_title}}`, `{{session_date}}`, `{{session_time}}`, `{{session_location}}`, `{{session_url}}`, `{{instructor_name}}`

**Certification events:**
- `{{certification_type}}`, `{{certification_level}}`, `{{product_area}}`, `{{expiry_date}}`, `{{days_until_expiry}}`

**Task events:**
- `{{task_title}}`, `{{task_category}}`, `{{task_due_date}}`, `{{task_owner}}`, `{{task_url}}`

### Admin View: Notification Template Management

**URL:** `/admin/notification-templates`
**Purpose:** Admin creates and edits notification templates with variable substitution and preview.

**Layout:**
- **Template list:** Table with Event Type, Channel, Subject, Status (active/inactive), Last Updated, Actions
- **Edit modal:** Subject field, rich-text body editor, variable picker sidebar showing available variables for the selected event type, preview panel showing rendered template with sample data
- **Test send:** Admin can send a test notification to themselves with sample variable data

---

## 18. Data Integration Roadmap

### Current State
All external data enters the system via manual CSV/XLS file uploads processed by `ImportController`. Each import creates an `Import` record for tracking and historical auditing.

### Phase 1 (Current): Manual Imports
- CSV/XLS upload via web UI
- Import types: opportunities, resources, contacts, partners
- Each import tracked with row counts, success/failure stats, error logs
- Opportunity and resource snapshots captured per import

### Phase 2 (Future): Salesforce Batch Sync
- Direct connection to Salesforce using stored credentials (`salesforce_credentials` table)
- Scheduled batch loads (configurable frequency: daily, weekly, on-demand)
- Sync scope: opportunities, contacts, accounts, cases
- Bi-directional mapping configuration (which SF fields map to which PM fields)
- Conflict resolution rules (SF wins, PM wins, most recent wins)
- Import records still created for each sync batch for auditability

### Phase 3 (Future): REST API Layer
- Authenticated REST API endpoints for external system integration
- API key or OAuth2 authentication per integration partner
- **Inbound (POST/PUT):** Accept data from external systems (Salesforce, partner systems, BI tools)
- **Outbound (GET):** Expose partner data, scorecard results, pipeline metrics for consumption by dashboards, BI tools, or other internal systems
- Rate limiting and audit logging for all API calls

### Phase 4 (Future): Partner Self-Service API
- Scoped exclusively to the authenticated partner's own data
- **GET endpoints:** Resources, certifications, project status, blueprint progress, scorecard, goals, enablement calendar
- **POST endpoints:** Add/update team members (contacts), submit certification data, update resource profiles, register for enablement events
- API authentication via partner-specific API keys or OAuth tokens
- All mutations create audit trail entries

---

## 19. New Data Entities Required — Summary

### New Tables (21 tables)

| Table | Section | Priority | Dependencies |
|-------|---------|----------|-------------|
| `partner_paths` | Partner Paths | Phase 1 | None (admin configured) |
| `partner_path_stages` | Partner Paths | Phase 1 | partner_paths |
| `partner_path_gates` | Partner Paths | Phase 1 | partner_path_stages |
| `partner_path_enrollments` | Partner Paths | Phase 1 | partners, partner_paths, partner_path_stages |
| `partner_stage_history` | Partner Paths | Phase 1 | partners, partner_paths, partner_path_stages |
| `task_templates` | Prescriptive Tasks | Phase 1 | partner_paths, partner_path_stages |
| `partner_tasks` | Prescriptive Tasks | Phase 1 | partners, task_templates |
| `task_comments` | Prescriptive Tasks | Phase 1 | partner_tasks, users |
| `partner_scorecards` | Scorecard | Phase 1 | partners |
| `scorecard_weights` | Scorecard | Phase 1 | None (admin configured) |
| `certifications` | Competency Matrix | Phase 1 | contacts, partners |
| `certification_requirements` | Competency Matrix | Phase 1 | partner_paths, partner_path_stages |
| `partner_goal_dimensions` | Goals & Targets | Phase 2 | partner_goals |
| `action_plans` | Action Plans | Phase 2 | partners, partner_events |
| `action_plan_items` | Action Plans | Phase 2 | action_plans |
| `partner_solutions` | Solution Lifecycle | Phase 2 | partners |
| `solution_stage_history` | Solution Lifecycle | Phase 2 | partner_solutions |
| `cases` | Delivery / Cases | Phase 2 | partners, projects |
| `notification_templates` | Notification System | Phase 2 | None (admin configured) |
| `notification_logs` | Notification System | Phase 2 | notification_templates |
| `partner_alerts` | Alerts & Actions | Phase 3 | partners, partner_tasks |
| `qbr_sessions` | QBR Workbench | Phase 3 | partners, partner_events |
| `qbr_sections` | QBR Workbench | Phase 3 | qbr_sessions |

### Modified Tables

| Table | Changes | Section |
|-------|---------|---------|
| `projects` | Add: customer_satisfaction, is_referenceable, led_to_expansion, scope_changes, go_live_date, outcome_notes | Delivery Outcomes |
| `opportunities` | Add: origin (enum), win_loss_reason (string), lost_to_competitor (string) | Pipeline Intelligence |
| `resources` | Add: expiry_date, certification_level, product_area (or use new `certifications` table exclusively) | Competency Matrix |

### Existing Tables (leveraged, no changes needed)

| Table | Used By |
|-------|---------|
| `blueprints` | Blueprint Dashboard, Delivery Capability Risk Engine |
| `blueprint_outcomes` | Blueprint Dashboard |
| `blueprint_content_items` | Blueprint Dashboard |
| `partner_blueprint_assignments` | Blueprint Dashboard, Delivery Capability Risk Engine, Scorecard |
| `blueprint_enrollments` | Blueprint Dashboard, Delivery Capability Risk Engine, Scorecard |
| `blueprint_progress` | Blueprint Dashboard |
| `enablement_events` | Enablement Calendar, Delivery Capability Risk cross-reference |
| `enablement_sessions` | Enablement Calendar, Delivery Capability Risk cross-reference |
| `enablement_registrations` | Enablement Tab, Scorecard |

### Computed Views (no new tables needed)

| View | Data Sources |
|------|-------------|
| Pipeline Analysis (Measure tab) | opportunities, opportunity_snapshots, partner_goals |
| Lead Flow (Measure tab) | leads, opportunities |
| Funding (Measure tab) | fundings, opportunities |
| Revenue (Measure tab) | opportunities, partner_goals |
| Enablement (Measure tab) | enablement_registrations, resources, certifications, blueprints |
| Delivery (Measure tab) | projects, cases |
| Delivery Capability Risk | opportunities, partner_blueprint_assignments, blueprint_enrollments, certifications, enablement_sessions |
| Revenue Planning Calculator | opportunities, opportunity_snapshots, partner_goals |
| Benchmarks | All partner data, aggregated and anonymized |
| At a Glance | All data sources, aggregated |

---

## 20. Implementation Phases

### Phase 1: Path & Scorecard Foundation (Highest Impact)

**Goal:** Establish the foundational partner progression system — paths, stages, gates, scorecard, tasks, and certifications. This transforms the platform from a data tracker into a prescriptive management tool.

**Duration estimate:** 3-4 sprints

**Deliverables:**
1. **Partner Paths infrastructure:**
   - `partner_paths`, `partner_path_stages`, `partner_path_gates` migrations + models + seeders (with default Implementation Partner path)
   - `partner_path_enrollments`, `partner_stage_history` migrations + models
   - Admin: Path Configuration view (CRUD for paths, stages, gates)
   - PSM: Partner Paths dashboard (stage visualization, gate progress)

2. **Prescriptive Task System:**
   - `task_templates`, `partner_tasks`, `task_comments` migrations + models
   - Admin: Task Template management (CRUD per path/stage)
   - PSM: Task Board view (kanban, list, partner-grouped)
   - Auto-population service (creates tasks when partner enters stage)
   - Auto-verification command: `php artisan tasks:auto-verify`

3. **Partner Scorecard:**
   - `partner_scorecards`, `scorecard_weights` migrations + models
   - Scorecard calculation service (all 6 dimensions, all sub-metrics)
   - Admin: Scorecard weight configuration
   - PSM: Scorecard dashboard (heatmap, radar, trends)
   - Scheduled command: `php artisan partners:calculate-scorecards`

4. **Certifications:**
   - `certifications`, `certification_requirements` migrations + models
   - PSM: Competency Matrix view (matrix grid, drill-down, gap analysis)
   - Certification import support in ImportController

5. **Blueprint Dashboard (leveraging existing tables):**
   - PSM: Blueprint Dashboard under Plan (assignment table, enrollment detail, gap analysis)
   - Delivery Capability Risk Engine (cross-references pipeline vs. blueprint completion)
   - Blueprint-related alert types added to alert generation

6. **Navigation updates:**
   - "Plan" section added with: Partner Paths, Scorecard, Competency Matrix, Blueprints, Task Board
   - "Path Configuration" and "Blueprint Management" under Administration

7. **Schema changes:**
   - `opportunities`: add origin, win_loss_reason, lost_to_competitor

**Seed data:**
- Default "Implementation Partner" path with 5 stages
- Default gate requirements per stage transition (as specified in Section 6)
- Default scorecard weights (as specified in Section 10)
- 20-30 task templates covering Account, Enablement, and Business Development categories for first 3 stages
- Default certification requirements per stage

### Phase 2: Goals, Actions, Solutions, Notifications & Measure Foundation

**Goal:** Complete the Plan section with revenue-math-backed goals and action plans, add solution lifecycle tracking, build notification system, build out the Measure analytics tabs, and add case tracking.

**Duration estimate:** 3-4 sprints

**Deliverables:**
1. **Goals & Targets with Revenue Planning Calculator:**
   - `partner_goal_dimensions` migration + model
   - Enhanced Goals & Targets view with multi-dimensional tracking
   - Revenue Planning Calculator (ASP, velocity, required opps, pipeline multiple)
   - Goal templates per path stage

2. **Action Plans:**
   - `action_plans`, `action_plan_items` migrations + models
   - Action Plans view (kanban + list)

3. **Solution Lifecycle:**
   - `partner_solutions`, `solution_stage_history` migrations + models
   - Solution Lifecycle dashboard under Engage

4. **Cases:**
   - `cases` migration + model
   - Case import support
   - Project outcome columns migration

5. **Notification Template System:**
   - `notification_templates`, `notification_logs` migrations + models
   - Admin: Notification Template management (CRUD, variable picker, preview, test send)
   - Notification dispatch service (processes events → renders templates → sends via channel)
   - Initial templates for: blueprint_assigned, resource_enrolled_blueprint, blueprint_completed, event_registration, certification_earned, certification_expiring

6. **Measure Section (Tabbed Analytics):**
   - Tab 1: At a Glance
   - Tab 2: Pipeline Analysis (with origin breakdown)
   - Tab 5: Revenue
   - Tab 7: Delivery (with case tracking)
   - Navigation: "Measure" section added

7. **Additional Measure Tabs:**
   - Tab 3: Lead Flow
   - Tab 4: Funding
   - Tab 6: Enablement (with blueprint progress metrics)

### Phase 3: Act Section & Advanced Features

**Goal:** Close the loop with alerts, QBR workbench, path reviews, and advanced intelligence.

**Duration estimate:** 2-3 sprints

**Deliverables:**
1. **Alerts & Recommendations:**
   - `partner_alerts` migration + model
   - Alert generation service (all rules from Section 14.1, including delivery capability risk)
   - Alerts view with sidebar badge
   - Scheduled command: `php artisan partners:generate-alerts`

2. **QBR Workbench:**
   - `qbr_sessions`, `qbr_sections` migrations + models
   - QBR prep/execute/follow-up workflow
   - Auto-generation of QBR data sections (including delivery capability risk summary)
   - Auto-creation of action plans from QBR outcomes

3. **Path Reviews:**
   - Path review workflow (evaluate → recommend → advance/hold)
   - Gate auto-evaluation service
   - Stage transition with audit trail

4. **Enhanced Welcome Page:**
   - Replace static welcome with dynamic task queue: "Your priorities today"
   - Show overdue tasks, upcoming QBRs, critical alerts, delivery capability risks, path review opportunities

5. **Additional Notification Templates:**
   - task_assigned, task_overdue, path_stage_advanced, qbr_scheduled, alert_generated

### Phase 4: Intelligence, Manager Views & Integration

**Goal:** Add computed intelligence, manager oversight, partner self-service, and external integrations.

**Duration estimate:** 2-3 sprints

**Deliverables:**
1. **Manager Dashboard** — Aggregate across PSM portfolios: path progression velocity, task completion rates, scorecard distributions
2. **Benchmarking Engine** — Anonymized peer comparisons by path stage, geography, industry
3. **Enablement → Outcome Correlation** — Analytical view showing training ROI
4. **Salesforce Batch Sync** — Automated periodic data loads via Salesforce API
5. **Notifications** — Email delivery for notification templates (Slack integration future)
6. **Export** — CSV/PDF export for QBR materials, scorecards, path reviews
7. **Partner Segmentation** — Auto-segment partners by behavior clusters (strategic/growth/maintain/watch)

### Phase 5 (Future): Partner Portal & API

**Goal:** Enable partner self-service access and external system integration.

**Deliverables:**
1. **Partner role & authentication** — Partner contacts can log in with scoped access
2. **Partner self-service views** — Scorecard, goals, resources, certifications, blueprint progress, enablement calendar
3. **Partner edit capabilities** — Add/update team members, submit certifications, register for events
4. **REST API layer** — Authenticated API endpoints for external system integration
5. **Partner API** — Scoped GET/POST endpoints for partner self-service automation

---

## 21. Technical Constraints

### Environment
- **Framework:** Laravel 11, PHP 8.3+, MySQL 8
- **Development:** Laravel Sail (Docker), port 8083
- **Frontend:** SmartAdmin v5, Bootstrap 5, ApexCharts via CDN, SmartTables
- **View pattern:** `@extends('layouts.vertical')`, blade components
- **Auth:** Laravel UI with role-based middleware (`auth`, `admin`)
- **Monitoring:** Laravel Pulse v1.5.0

### Data Import Pattern
- All external data enters via Excel imports processed by ImportController
- Each import creates an Import record for tracking
- Snapshots captured per import for historical trending
- New import types needed: certifications, cases, solutions
- Origin field should be importable on opportunity imports
- Future: Salesforce batch sync creates import records for auditability

### Performance Considerations
- Partner model has `$with = ['projects', 'contacts']` — avoid loading full Partner models in aggregate queries
- Use DB facade joins and `count()`/`sum()` for dashboard metrics
- Scorecard computation should be cached per partner with 1-hour TTL
- Task auto-verification should batch-process efficiently (not per-task queries)
- Delivery Capability Risk Engine should cache results with invalidation on import/blueprint progress events
- Measure tab queries should use eager loading + query scopes
- Large datasets (opportunities, snapshots) should use pagination in views
- Gate evaluation should cache results during path review session
- Notification dispatch should be queued (Laravel Jobs) to avoid blocking the request lifecycle

### Authorization Pattern
- Admin sees all data, can configure paths/tasks/weights/gates/notification templates
- PSM sees only `Partner::where('user_id', auth()->id())` — this applies to all new features
- Partner (future) sees only their own partner record and related data
- All new controllers must follow this pattern
- New admin-only routes go in `Route::middleware(['admin'])` group
- New authenticated routes go in `Route::middleware(['auth'])` group
- Path advancement requires PSM initiation, admin approval for overrides
- API endpoints (future) use token-based auth with role scoping

### Naming Conventions
- Models: PascalCase singular (PartnerPathStage)
- Tables: snake_case plural (partner_path_stages)
- Controllers: PascalCase + Controller suffix (PartnerPathController)
- Services: PascalCase + Service suffix (ScorecardCalculationService)
- Views: kebab-case directories, snake_case blade files
- Routes: kebab-case URLs, dot-notation names (partner-paths.index)
- Artisan Commands: kebab-case (partners:calculate-scorecards)
- Notification Events: snake_case (blueprint_assigned, certification_earned)

### View Design Standards
- All views extend `layouts.vertical`
- Include page title via `@include('layouts.partials/app-pagetitle')`
- Cards use `card shadow-0 mb-g` pattern
- Charts: ApexCharts with `window.colorMap` theme colors
- Tables: SmartTables with `sortable: true`, `perPage: 10-15`, responsive column priorities
- Empty states: Show helpful message + suggested action (never blank areas)
- Loading states: Skeleton screens for chart areas
- Badge color conventions: danger=critical/required, warning=warning/recommended, info=info/optional, success=completed/met, primary=in-progress

---

## Revision History

| Date | Author | Changes |
|------|--------|---------|
| 2026-02-10 | Claude + Gunnar | Initial specification created |
| 2026-02-10 | Claude + Gunnar | Major update: Added Partner Paths (gate-based progression), Prescriptive Task System, Solution Lifecycle, Partner Scorecard (6 dimensions), Origin Tracking, Tabbed Analytics (7 tabs), Path Reviews. Restructured from 13 to 17 sections. |
| 2026-02-10 | Claude + Gunnar | Incorporated Gunnar's feedback: Added revenue goal math (ASP, pipeline velocity, required opps, pipeline multiple). Elevated Blueprints to dedicated section with PSM workflow, gap analysis, and enablement calendar cross-reference. Added Delivery Capability Risk Engine (pipeline vs. blueprint completion cross-referencing). Added Notification Template System (admin-managed templates with variable substitution for 15 event types). Added Partner role (future phase) with self-service portal and API access. Added Data Integration Roadmap (Salesforce batch sync, REST API, partner API). Updated gate requirements to include blueprint coverage and delivery capability checks. Expanded from 17 to 21 sections. |

---

> **Next Step:** Review the updated specification. All `[GUNNAR]` comments have been incorporated. Key additions to validate:
> - Revenue Planning Calculator math (Section 11.5) — does the ASP/velocity/pipeline multiple model match your thinking?
> - Delivery Capability Risk Engine (Section 16) — are the risk thresholds right?
> - Notification Template events (Section 17) — are all critical notification scenarios covered?
> - Blueprint Dashboard under Plan (Section 8) — does the PSM workflow match how you envision blueprint management?
> - Partner Portal scope (Phase 5) — is the self-service scope correct for what partners should see/edit?
