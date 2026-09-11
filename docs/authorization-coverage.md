# Authorization Coverage Inventory (Increment 0)

Read-only inventory. No behavior changed. Companion to
[`authorization-spec.md`](authorization-spec.md); this turns "we think scope is
universal" into a checkable list and orders the work.

Method: static scan of `app/Models/*.php` for principal columns and the
`ScopedToActor` trait, plus a scan of `app/Http` / `app/Services` / `app/Console`
for scope bypasses and manual-filter paths. "Auto-scoped" = the model carries the
`ScopedToActor` global scope, so *every* Eloquent query is filtered
fail-closed without the caller doing anything.

## Headline numbers

- **145** models total; **80** carry a partner or user-owner link (scope candidates).
- **17** are auto-scoped.
- **23** are partner-bearing but **not** auto-scoped — protected only where a
  caller filters manually or reaches them through a scoped parent.
- **40** are user-owned only (no `partner_id`) and unscoped — the second
  principal axis (workshops, plans, templates, etc.) that the current
  partner-only `VisibilityContext` cannot express at all.

## 1. Auto-scoped models (17) — structurally safe

Certification, ContactInteraction, DealRegistration, Funding, Lead, Opportunity,
Partner (scope column `id`), PartnerAlert, PartnerEvent, PartnerGoal, PartnerTask,
Project, PsmObjective, QbrSession, Resource, Signal, SupportCase.

## 2. Partner-bearing, NOT auto-scoped (23) — coverage gap, review each

Each is safe only if every read/aggregate/export/API path either filters by
`partner_id` against the authorized set or loads through a scoped parent. That is
not guaranteed by the model and must be verified per read path.

| Model | Principal cols | Notes / where it surfaces |
|---|---|---|
| ActionPlan | partner_id, created_by | launch/action plans |
| AnaplanWorkspace | partner_id | provisioning |
| Contact | partner_id | partner contacts, **search/autocomplete risk** |
| EnablementGoal | partner_id, user_id | enablement |
| EnablementRegistration | partner_id | enablement |
| EnablementSession | partner_id, created_by | enablement |
| LabParticipant | partner_id | workshops/labs |
| LaunchProgramPartner | partner_id | launch programs |
| OpportunitySnapshot | partner_id | **metrics/aggregation risk** |
| PartnerAssignedGoal | partner_id | goals |
| PartnerBlueprintAssignment | partner_id | enablement blueprints |
| PartnerHealthSnapshot | partner_id | **dashboards/KPI risk** |
| PartnerMarketplaceEntry | partner_id | marketplace |
| PartnerPathEnrollment | partner_id | competency |
| PartnerPlanCommit | partner_id | **confirmed** manually scoped in PartnerSuccessController; unsafe if queried elsewhere |
| PartnerScorecard | partner_id | **scorecards/KPI risk** |
| PartnerSolution | partner_id, created_by | solutions |
| PartnerStageHistory | partner_id | pipeline history |
| ResourcePathEnrollment | partner_id | competency (resource) |
| ResourceSnapshot | partner_id | metrics |
| ResourceStageHistory | partner_id | history |
| ResourceTask | partner_id | tasks |
| WeeklyFocusItem | partner_id | priorities |

Highest-priority review targets (directly feed lists/search/aggregates):
**Contact, PartnerScorecard, PartnerHealthSnapshot, OpportunitySnapshot,
ResourceSnapshot, PartnerPlanCommit.**

## 3. User-owned only, NOT scoped (40) — the second principal axis

These have no `partner_id`; visibility (once we adopt the principal model) would
come from `owner`/`created_by`/`assigned_to` + hierarchy rollup, not partner. They
are unscoped today. Grouped:

- **Workshops:** Workshop, WorkshopGuide, WorkshopSession, WorkshopSessionMonitor,
  WorkshopThread, WorkshopReply, WorkshopReaction, WorkshopReadState,
  WorkshopPresence, WorkshopContentReviewComment, Lab, ZoomMeeting,
  ZoomChatResponder.
- **Plans / strategy:** StrategyPlan, StrategyPlanInitiative, StrategyScenario,
  LaunchProgram, Blueprint, Journey, JourneyAssignment, JourneyPendingAction.
- **Paths / templates:** PartnerPath, ResourcePath, ActivationPath, ActivationStep,
  StepProgress, GoalTemplate, TaskTemplate, ResourceTaskTemplate,
  NotificationTemplate.
- **User-scoped utility:** SavedFilter, WeeklyFocus, ActionPlanItem, TaskComment,
  Import, Asset, SalesforceCredential, AuditLog, EnablementEvent.

Note: many of these are **catalog / template / config** records that may be
program-wide by design (spec §4, DECISION 5 / increment-3 option c), not
owner-scoped. Which are owner-scoped vs program-wide is a business call per group.

## 4. Scope bypasses (`withoutGlobalScope(s)`) — 3 real, all currently sound

| Site | Classification |
|---|---|
| PartnerSuccessController:87, :116 | Re-applies scope manually (`whereIn('id', visibleIds)`); safe, but correctness lives in the controller, not the model |
| PartnerHealthService:97 (`snapshotAll`) | System/console job, no actor → unrestricted by design; safe |
| PortalImpersonationController:19 | Admin impersonation flow; acceptable, but confirm it is capability-gated |

No bypass leaks data today. The concern is that they are the *pattern* the 23
unscoped partner models rely on — discipline, not structure.

## 5. Manual-filter paths (mechanism 2 — the audited exception)

Places that read `VisibilityContext` and build their own WHERE clauses instead of
relying on the global scope:

- `app/Http/Controllers/PartnerSuccessController.php`
- `app/Http/Controllers/InsightController.php`
- `app/Services/PartnerSuccess/MetricService.php` (the `filt()` helper — all
  PSM-engagement KPIs/aggregations)

These carry the universality guarantee for the warehouse/metrics surface. Every
new metric or export must go through `filt()` (or equivalent); there is no
structural backstop if one forgets.

## 6. Recommended ordering for later increments

1. **Increment 1** (unblocks everything, resource-independent): replace the
   hard-coded `admin`/`isAdmin`/`coe` shortcut in `VisibilityContext` with the
   explicit unrestricted privilege (spec DECISION 5).
2. **Increment 2**, in this order, each with a test proving an out-of-scope actor
   gets zero rows: Contact → PartnerScorecard → PartnerHealthSnapshot →
   OpportunitySnapshot / ResourceSnapshot → PartnerPlanCommit → the remaining 17.
   For each, decide auto-scope (add trait) vs confirmed-parent-scoped (document
   why no trait is needed).
3. **Increment 3** (only when a non-partner resource needs enforcement):
   generalize to the principal model; decide per §3 group which are owner-scoped
   vs program-wide.

## 7. Increment-2 progress and findings (live)

**Scoped so far (ScopedToActor added, each with a zero-leak test):**
PartnerStageHistory, ResourceStageHistory, PartnerMarketplaceEntry,
PartnerSolution, ResourceTask, WeeklyFocusItem, LabParticipant, ActionPlan,
PartnerPathEnrollment, ResourcePathEnrollment (with the PartnerPathController
reconciliation), PartnerBlueprintAssignment, PartnerPlanCommit.
Plus the increment-1 privilege refactor and the MCP/sanctum actor fix.

**Program-wide (intentionally NOT partner-scoped):**
- `EnablementSession` — a shared session catalog; `partner_id` lives on the
  registration pivot, not the model. Per-partner scoping belongs on
  `EnablementRegistration`, not the session.

**Deferred with findings (NOT mechanical — need a decision or reconciliation):**

- **`PartnerPathEnrollment` / `ResourcePathEnrollment`** — entangled with
  `PartnerPathController`, which scopes with **hard-coded role logic** that
  diverges from `VisibilityContext`:
  `$canSeeAll = $user->isAdmin() || $user->role?->slug === 'manager'` →
  a `manager` gets `Partner::pluck('id')` (EVERY partner in the DB, not their
  hierarchy), everyone else gets `Partner::where('user_id')` (owned only —
  ignores assignments and reporting hierarchy). So managers are *over*-exposed
  and assignees/hierarchy are *under*-shown, and it uses primary-role-only
  (`role?->slug`) not multi-role. Adding the model scope would silently narrow
  managers via intersection. Correct fix: reconcile the controller to rely on
  `VisibilityContext` (remove the hard-coded role scoping), then add the model
  scope. Tracked as a follow-up task.

- **`AnaplanWorkspace`** — has provisioning logic that queries
  `static::whereNull('partner_id')` to link unassigned workspaces; a blanket
  partner scope makes that query return nothing for non-admin/non-system callers.
  Needs `->withoutGlobalScope('actor_visibility')` on the provisioning/linking
  path before the trait is added. Deferred.

- **`Contact`** — DONE. Uses a LOCAL scope (`scopeVisibleToActor`) applied at
  internal read sites, not the global scope (which would recurse through the
  portal guard during auth). `ContactController` and `SearchController` were
  reconciled off their owned-only `isAdmin()` scoping to `VisibilityContext` /
  the scoped `Partner` query, and the four `Contact::findOrFail` IDOR sites now
  go through `visibleToActor()` (out-of-scope → 404).

- The **benchmark tables** (PartnerScorecard, PartnerHealthSnapshot,
  OpportunitySnapshot, ResourceSnapshot, PartnerAssignedGoal) — deferred per the
  accepted spec approach: ScopedToActor + explicit `withoutGlobalScope` on the
  cross-population benchmark/cohort reads.

- **`EnablementRegistration`, `EnablementGoal`, `LaunchProgramPartner`** —
  entangled with controllers that scope with the same hard-coded role logic as
  the path controllers (`Api\EnablementController`, `StateOfBusinessController`,
  `LaunchProgramController` / `PartnerController`). `LaunchProgramPartner` also
  drives program partner-count semantics and has pre-existing failing tests.
  Reconcile each controller to `VisibilityContext` first, then add the model
  scope — same pattern as the PartnerPathController fix. Tracked as a follow-up.

**Remaining after this batch:** the entangled enablement/launch models above
(tasked); the benchmark tables (scope + `withoutGlobalScope` on benchmark reads).
Then the `MetricService::filt()` / export universality audit and increment 3.
