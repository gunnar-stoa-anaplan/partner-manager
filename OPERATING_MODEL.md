# Operating Model — who uses Partner Manager, how, and the data they generate

The authoritative picture of *intent and flow*: the personas, the loop they move
through, and the lifecycle of the data and metrics everyone creates and updates.
Read this with ARCHITECTURE.md (the technical model) and INSIGHT_KIT.md (the
metric/UX layer).

## What the platform is for

A company runs Partner Manager to turn its **partner ecosystem into a productive
extension of its own go-to-market motion** — sales, marketing, service, and
implementation. It is productized: **single-tenant, white-label, sold per client**
(ARCHITECTURE.md §11), so each client operates their own branded partner
ecosystem in their own instance.

## The spine

The **Partner (company)** is the hub. Its detail page aggregates every module as
tabs — Pipeline, Deal Registrations, Projects, Enablement, Calendar, Influencers,
Blueprints, PSM Priorities, Resources, Resource Paths, Launch Programs,
Workspaces, Marketplace. Each partner is **owned by an internal PSM** and holds
**partner people (contacts)**, opportunities, leads, projects, funding, resources.
Everything in the app is ultimately activity on or about a partner.

There are **two metric planes**:

- **Operational (live, app DB):** partner **health** (weighted — scorecard .25 +
  pipeline trend + goal attainment + engagement + delivery), **scorecards**
  (certification + pipeline + delivery + engagement), goal attainment,
  **signals → alerts**, risk. Drives day-to-day PSM action.
- **Audited (weekly warehouse snapshots, schema `wh`):** 21 metrics across
  **Pipeline / Delivery / Capability / Enablement / Catalog**, with provenance +
  drill-to-source. Drives reporting, QBRs, and the exec view.

## Personas — producers *and* consumers

### Internal (employees)
- **Admin / Super Admin** — configure the instance: activate modules, define
  roles/permissions, users, integrations, branding, system health. Bypass perms.
- **CoE (Center of Excellence)** — *authors the program*: strategy plans/scenarios,
  competency paths, courses & learning paths, blueprints, labs, activation
  (training) gates, goal templates, scorecard weights, benchmarks, knowledge base.
- **Manager** — oversight across PSMs: reporting hierarchy, team priorities.
- **PSM (Partner Success Manager)** — operational core: owns a book of partners,
  sets priorities/weekly focus, runs enablement/workshops/launch programs, works
  health/goals/alerts/QBRs/risk.
- **PAM (Partner Account Manager)** — commercial/relationship ownership.
- **Integration** — Salesforce/Anaplan connectors, directory sync, imports.
- **Workshop / Session administrators** — run workshop delivery.

### External (partners) — portal roles that mirror the GTM functions
- **partner_admin** — manages their company's team + profile; full portal.
- **sales** — pipeline, deal registration, leads, marketplace (extension of Sales).
- **marketing** — leads/influence, development funding, events (extension of Marketing).
- **delivery_lead** — projects, launch programs, labs, workspaces (extension of Implementation/Service).
- **engagement_manager / practice_lead** — enablement, competency paths,
  certifications, resource paths (build capability).

## The loop (how work and data flow)

1. **Assign** — internal creates a Partner, assigns the owning PSM, invites
   partner contacts (portal access + role); partner_admin builds out their team.
2. **Enable** — CoE authors paths/courses/labs/blueprints + training gates;
   partner people complete them → *certified people, role coverage
   (GTM/PreSales/Delivery), competency scores*. Workshops run (internal-led,
   partner participants, with threads).
3. **Transact** — partner **sales register deals → internal reviewer approves**;
   leads sourced/influenced/assigned; funding; marketplace → *pipeline, ACV,
   app deals, AI engagement*.
4. **Deliver** — projects/launch programs run (partner delivery_lead + internal),
   task boards, action plans, risks → *active / at-risk / red project metrics*.
5. **Measure & act** — platform computes health/scorecards/goals/signals; the PSM
   acts via priorities, alerts, QBRs; the warehouse rolls it into the audited
   dashboard.
6. **Strategize** — CoE/managers use the Strategy Workbench + AI (Idea Room,
   Knowledge Base, agents) to plan the next cycle.

## Data & metrics lifecycle — who generates/updates what

- **Partners generate:** deal registrations, leads, project updates, lab
  checkpoints, course/certification completions, event nominations, company
  profile, their own team membership. They are active producers, not just readers.
- **Internal generates:** partner assignments, priorities, goals, action plans,
  QBR notes, health/scorecard computations, risk assessments, interactions,
  workshop sessions, activation-path assignments, strategy plans.
- **External systems feed:** Salesforce (opportunities/accounts), Anaplan
  (workspaces/provisioning), directory sync (people).
- **The warehouse** takes weekly snapshots of opportunity/project/person/course/
  learning_path into the 21 audited metrics — the source of truth for "the
  numbers," with provenance + drill.

## The load-bearing insight (why the three pillars exist)

Everyone — internal and partner — both **produces and consumes** the data and
metrics; partners generate the raw activity that becomes the metrics internal
teams manage by, and the metric layer is the shared language between them. That
requires three things, which are the platform's design pillars:

1. **Multi-role + teams/functions** — people contribute across functions (a
   partner who both sells and delivers; a PSM who also manages), and "extension of
   the sales/marketing/service/implementation teams" is a team axis spanning
   internal and partner people.
2. **Row-level visibility** — the safety mechanism for a shared data fabric: a
   partner sees only their company; a PSM their book; a manager their team's
   books; CoE program-wide.
3. **An explainable metric layer (Insight Kit)** — every number a PSM acts on
   traces back to the partner activity that produced it.

These are not separate features; they are the three pillars this operating model
requires. See ARCHITECTURE.md §3 (authorization), §12 (Insight Kit), and the
target-model section.
