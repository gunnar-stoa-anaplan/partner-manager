# Partner Manager — Architecture Reference

Authoritative model of the application. Keep this current when architecture
changes. It is the starting point for any deep work and the backbone of the
security-review pack.

_Last deep pass: 2026-09-06 (Laravel 12, PHP 8.2+)._

> **Foundational model — read §11 first.** This is a **single-tenant, white-label
> product**: each paying client gets its **own** Laravel instance + own Postgres,
> deployed on their infra or hosted by us on AWS. It is _not_ a shared
> multi-tenant deployment. Within each instance, users are **internal**
> (employees) or **external** (partners, clients, prospects), each with distinct
> CRUD rights and **row-level** data visibility. See §3 (authorization) and §11
> (tenancy/commercial model).

---

## 1. Stack at a glance

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 (PHP ^8.2), Sail/Docker for local dev |
| App database | **PostgreSQL** (migrated from MySQL; `DB_CONNECTION=pgsql`) |
| Analytics warehouse | **PostgreSQL** schema `wh` inside the app DB — audited weekly snapshots (Partner Success / PSM Engagement) |
| Knowledge base | **PostgreSQL + pgvector** (`knowledge_base` connection) for RAG/embeddings |
| Queue / Cache / Session | **database** driver (all three) on Postgres |
| Mail / Broadcast | `log` (⚠ no real provider configured) |
| Frontend | SmartAdmin theme: **Bootstrap 5 + Alpine.js**, compiled via **Vite** (SCSS in `resources/sass`, entry `smartapp.scss`; themes in `public/css/themes/*.css`) |
| API | **Sanctum** (tokens) + **Scramble** (auto OpenAPI docs) |
| AI | **laravel/ai**, MCP tools (`app/Mcp/Tools`), pgvector KB, IdeaRoom |

Scale: ~157 models, ~177 controllers, ~102 services, 183 migrations, 12 enums,
7 queued jobs, 17 console commands. Routes split across
`web.php` (957 lines), `portal.php`, `api.php`, `ai.php`, `docs.php`,
`workshop-guides.php`, plus module route files wired in `bootstrap/app.php`.

---

## 2. Data stores

All three databases below live in the **same Postgres instance** (the
`pgvector/pgvector:pg17` container in the app's compose stack) — one instance to
deploy, back up, and ship per client instance (§11). There is no external DB
container.

1. **App DB (pgsql, `partner_manager`)** — all transactional data: users, roles,
   permissions, partners, opportunities, workshops, blueprints, activation,
   signals, etc. Session/cache/queue tables live here too.
2. **Warehouse (`warehouse` connection → `partner_manager` DB, schema `wh`)** —
   append-only weekly snapshots for the Partner Success / PSM Engagement
   dashboards. Same database as the app, isolated to the `wh` schema via
   `search_path=wh`; a separate read connection so it stays logically distinct.
   Every row carries `snapshot_date`, `load_run_id`, `source_file`, `source_id`
   (provenance). Metric definitions live in `wh.metric_definition`; the dashboard
   derives every number from that catalog. **Read-only from the app.**
   _History:_ this was formerly a standalone `enablement-db` Postgres container
   fed by the Python ingest pipeline in the separate `partner-enablement-db` repo
   (zip of weekly CSVs); the `wh` schema was consolidated into `partner_manager`
   and that container retired (2026-09). ⚠ **Follow-up:** the weekly ingest still
   targets the retired warehouse's `wh`/`staging`/`public` layers — repoint it at
   `partner_manager.wh` (and migrate/recreate its `staging`/`public` working
   layers) before the next load, or the dashboard data will go stale.
3. **Knowledge base (`knowledge_base` connection, pgvector)** — `knowledge_documents`,
   `knowledge_chunks` (vector embeddings) for semantic search / RAG. Vector columns
   use Laravel 12's **native** schema support (`$table->vector(..., dimensions:)`
   and `Schema::…->ensureVectorExtensionExists()` from the framework Blueprint) —
   no extra composer package required.

Cross-DB portability lives in `App\Support\DatabasePortability` (FIELD,
DATE_FORMAT, DATE, YEARWEEK, LEAST, `modifyEnum`, `makeColumnNullable`) — use it
instead of raw MySQL-only SQL.

---

## 3. Authentication & authorization

- **Auth:** email + password via `laravel/ui` (Bootstrap scaffolding).
  ⚠ **No MFA/2FA and no SSO.** Seeder sets every user's password to `password`
  (dev only — must not exist in prod).
- **Two guards / four audience types:** internal **users** (`users.role_id → roles`)
  and external **portal contacts** (guard `portal`, gated by
  `PortalAuth`/`PortalRole`/`PortalCan`). Business audience taxonomy:
  - **Internal — employees** of the client operating the instance (full-ish CRUD
    per role: admin, coe, manager, psm, pam, workshop/session admins…).
  - **External — partners** (portal roles: partner_admin, delivery_lead,
    engagement_manager, practice_lead, marketing, sales) — CRUD limited to their
    own partner's records.
  - **External — clients** and **prospects** — read-mostly, scoped to what's been
    shared with them.
  Each type has a distinct CRUD profile; **row-level visibility is a hard
  requirement** ("users see only data they're allowed to view").

### Data visibility (row-level) — requirement + current state

**Requirement:** every query that returns partner/opportunity/person/workshop
data must be constrained to the rows the current user is entitled to, by
audience type and ownership/assignment. This is separate from module access
(can you open Pipeline) — it is which *rows* you get once you're in.

**Current state (gap):** authorization is mostly **module-level** middleware
plus **ad-hoc** `where('user_id', …)` in ~49 controllers. There are **no Laravel
Policies** (`app/Policies` does not exist). Some models already register
**global scopes** (`Partner`, `Blueprint`, `Lab`, `LaunchProgram`,
`EnablementEvent`, `EnablementRegistration`, `WorkshopGuide`) — the right
foundation, but coverage is partial. ⚠ The PSM Engagement dashboard is behind
plain `auth`, so any internal user currently sees **all** partners.

**Target design:** a consistent **visibility layer** — model global scopes (or a
`Visible`/`ScopedToUser` trait) that filter by owner/assignment/partner and by
audience, backed by **Laravel Policies** for per-model CRUD authorization
(`viewAny/view/create/update/delete`), enforced in controllers via
`authorize()`/`Gate`. Prefer first-party Policies + query scopes over bespoke
per-controller checks. (See §8 audit.)
- **Custom RBAC (not spatie/permission):**
  - `roles` (slug, display_name, type, landing_route, `bypass_permissions`).
  - `role_permissions` (`role_id`, `module_slug`, `access_level` = read|write).
  - `Role::hasPermission($id)` — exact match for `module.section`, prefix match
    for a bare `module` slug. `User::hasModulePermission()`, `canWriteModule()`,
    `isAdmin()` (admin/super_admin), `isPsm()`, `isDashboardOnly()`.
  - `super_admin` (+ any `bypass_permissions` role) bypasses all checks.
- **Middleware (aliases in `bootstrap/app.php`):** `admin` (IsAdmin),
  `module:{feature|feature.section}` (EnsureModuleEnabled — feature state +
  write-permission enforcement on non-safe methods), `portal`, `portal.role`,
  `portal.can`, `admin_or_integration`, `journey.token`. Appended to the web
  group: **`RedirectRestrictedRoles`** — confines limited roles to allowed path
  prefixes (e.g. `dashboard_viewer` → partner-success, partner-workshops,
  welcome, logout).
- **Authorization is middleware/permission-driven; there are _no_ Laravel
  Policies or Gates in `app/Policies`** (except the `manage-activation` gate).
- **No impersonation via a package** — there is an `impersonate` route/flow;
  treat as high-privilege and ensure it is audited.

### Roles (internal, from RoleSeeder)
`super_admin`, `admin`, `coe`, `manager`, `psm`, `pam`, `integration`,
`workshop_administrator`, `session_administrator`, `dashboard_viewer`
(+ portal roles: partner_admin, delivery_lead, engagement_manager,
practice_lead, marketing, sales, …).

`dashboard_viewer` = focused role (currently gunnar.stoa@gmail.com): nav shows
only Welcome + PSM Engagement + Workshops; route access confined by
`RedirectRestrictedRoles`; read perms on `workshops.management/.reports`.

---

## 4. Module / feature-gating system

- `config/modules.php` declares modules and their nav sections/items.
- `App\Services\ModuleRegistry` resolves **feature state** per user:
  `hidden` (inactive or role lacks permission) → 403;
  `locked` (training required, not completed) → redirect to training;
  `nudge` (flash a training reminder); `unlocked`.
- Backed by the **Activation** system (`ActivationService`, `ActivationTracker`,
  `activation_paths`, feature flags) — training-gated feature rollout.
- **`laravel/pennant` is installed** — feature flags. ⚠ Overlaps conceptually
  with the custom Activation/ModuleRegistry; confirm they're aligned rather than
  two parallel flag systems (see audit §8).
- Nav is rendered in `layouts/partials/generated-navigation.blade.php` from
  `$navSections` + explicit `<li>`s, gated by `@can`, `Route::has`, and role
  checks. The brand/logo lives in the **header** (`app-logo` partial, `brand`
  flag) — single instance; sidebar does not repeat it.

---

## 5. Domain map (what the services do)

- **Partner Success / analytics:** `PartnerSuccess\MetricService` (PSM Engagement
  dashboard over `wh`), `PartnerHealthService`, `ScorecardCalculationService`,
  `GoalComputationService`, `ObjectiveComputationService`, `PrioritiesService`,
  `StrategyActualsService`, `BenchmarkService`, `DeliveryCapabilityRiskService`,
  `RiskAnalyzerService`.
- **Signals/alerts:** `SignalEngineService`, `SignalProcessors/*`,
  `SignalAggregateService`, `AlertGenerationService` (`SignalProcessor` contract).
- **Activation/enablement:** `ActivationService`, `ActivationTracker`,
  `GateEvaluationService`, `ResourceGateEvaluationService`, `EnablementGoalService`.
- **Workshops:** routes under prefix `partner-workshops` (names `workshops.*`),
  content review/audit pipeline (`WorkshopContentDiffService`,
  `WorkshopContentReviewComment`, `WorkshopContentAuditObserver`), sessions,
  participants, surveys, `LabValidationService`.
- **Blueprints:** `BlueprintExportService` (→ PPTX/Word via PhpOffice).
- **AI:** `IdeaRoom/*` (brainstorming agents), `KnowledgeBase/*` +
  `KnowledgeBaseSearchService` (pgvector RAG), `ChatContextService`,
  `SimulatedChatService`, MCP tools in `app/Mcp/Tools`.
- **Integrations/ETL:** `AnaplanApiService`, `DirectorySyncService`,
  `DataIngestionService`, `ChangeDetectionService`, Zoom (jobs).
- **Platform:** `ModuleRegistry`, `LicenseStatusService`, `SystemStatusService`,
  `NotificationDispatchService`, `DocumentationService`.

---

## 6. Integrations & async

- **Salesforce** — partner/opportunity sync; `SalesforceCredential` (OAuth).
- **Zoom** — meetings + webhooks; jobs: Create/Cancel/Sync meeting, sync
  participants, process chat. Webhook secrets in `config/services.php`.
- **GitHub** — `Workshops\GithubWorkshopContentService` (branch→PR→merge content
  authoring); token in `services.github`.
- **Anaplan** — `AnaplanApiService`.
- **Directory sync** — `ProcessDirectorySync` job.
- **Queue:** database driver (no Horizon). **Scheduler:** `routes/console.php` +
  commands in `app/Console/Commands`.

---

## 7. Installed packages & what they're for

**Backend:** laravel/framework 12, sanctum (API tokens), **pennant** (feature
flags), **pulse** (app monitoring), **laravel/ai** (AI SDK), laravel/ui (auth
scaffold), dedoc/scramble (OpenAPI docs), firebase/php-jwt (JWT), maatwebsite/excel
(Excel import/export), phpoffice/phppresentation + phpword (PPTX/Word),
smalot/pdfparser (PDF ingest). Dev: boost, pail, pint, sail, collision, phpunit,
faker, mockery.

**Frontend:** bootstrap 5 + alpinejs (SmartAdmin), **apexcharts + d3** (charts),
fullcalendar, jsvectormap, easymde (markdown), shepherd.js (product tours),
sortablejs, masonry, peity/easy-pie-chart (sparklines), three + vanta (animated
backgrounds — the logo "orb"), p5, jquery, axios, vite, sass.

---

## 8. Library-vs-custom audit (prefer the library/service)

Principle: **do not build custom what a maintained library/first-party feature
already does.** Current state and recommendations:

| Area | Today | Recommendation |
|------|-------|----------------|
| **Dashboard charts / UX consistency** | PSM Engagement uses hand-rolled inline SVG bars/donuts; no shared UI components exist (`resources/views/components` is empty), so every view is bespoke | Build the **Insight Kit** (INSIGHT_KIT.md, §12): shared Blade components over the installed **ApexCharts**, plus a `MetricSource` contract so the audit-drill works everywhere. Filament was piloted and rejected (see §12). |
| **Feature flags** | Custom Activation + ModuleRegistry, **and** Pennant installed | Consolidate on **Pennant** as the flag primitive; keep the training/activation layer on top of it rather than a parallel system |
| **Audit logging** | Custom `audit_logs` + observer | Consider **owen-it/laravel-auditing** or **spatie/activitylog** for model-level audit trails (less custom code, standard format) |
| **RBAC** | Custom roles/`role_permissions` | Works well and is tailored; migrating to **spatie/laravel-permission** is optional (only if you want its ecosystem). Not urgent. |
| **MFA / SSO** | None (email+password) | **laravel/fortify** for 2FA/TOTP; **laravel/socialite** or SAML for SSO — *security priority* |
| **Queue ops** | database driver, no dashboard | **Horizon** (needs Redis) for a queue dashboard + retries/metrics as volume grows |
| **Full-text search** | Custom (KB is pgvector; global search is bespoke) | KB is appropriately pgvector; for entity search consider **Laravel Scout** (DB/Meilisearch driver) if it grows |
| **Realtime** | `BROADCAST=log` (none) | **Laravel Reverb** (websockets) if you want live dashboards / Zoom chat / notifications |
| **Mail** | `MAIL_MAILER=log` | Configure a real mailer (SES/Postmark/etc.) before any user-facing email |
| **Row-level authorization** | Ad-hoc `where('user_id')` in ~49 controllers, partial global scopes, **no Policies** | Adopt **Laravel Policies/Gates** + a shared model **visibility scope/trait** — centralized, testable, closes the "sees all partners" gap. *Core requirement (§3).* |
| **Multi-tenancy** | None (single instance per client) | **Do NOT** add stancl/tenancy or spatie/multitenancy — the model is single-tenant-per-instance (§11). Isolation is physical, not row-based-by-tenant. |
| **Licensing / entitlement** | `LicenseStatusService` (custom) | Sign the per-instance license as a **JWT** (firebase/php-jwt already installed), validate at boot, drive **Pennant** flags/edition from it |
| **Fleet deploy / upgrades** | Sail (dev only) | Ship a production Docker image + IaC (Terraform/ECS or Forge/Envoyer) to deploy & migrate N instances repeatably (§11) |
| **Excel/Office/PDF** | maatwebsite/excel, phpoffice, pdfparser | ✅ Already using libraries — good |
| **API docs** | Scramble | ✅ Good |

Leftover: `config/telescope.php` exists but **laravel/telescope is not
installed** — dead config; remove or install intentionally.

---

## 9. Known risks / gaps (security-review inputs)

- Seeded default password (`password`) for all users — dev only.
- **Row-level visibility is not systematically enforced** — no Policies, partial
  global scopes, ad-hoc controller scoping. This is the top functional-security
  gap given internal/external audiences (§3). The PSM Engagement dashboard is the
  clearest instance: behind plain `auth`, so any authenticated internal user sees
  **all** partners' pipeline/ACV/Salesforce links.
- **Per-instance isolation depends on deployment discipline** — because tenancy
  is physical (one DB/instance per client, §11), a misconfigured shared DB or
  leaked cross-instance credential would break the core promise. Each instance
  must have its own DB, secrets, backups, and license.
- **PII** (partner people: names, emails, countries) + commercially sensitive
  ACV/account data in app DB, warehouse, and KB. Classify + minimize exposure.
- Secrets (Salesforce, Zoom, GitHub, Anaplan, DB) in `.env` — move to a secrets
  manager; rotate.
- No MFA/SSO; no real mailer; broadcast/mail on `log`.
- Impersonation is high-privilege — ensure audited.
- Legacy: the standalone Flask dashboards (`partner-enablement-db`, ports
  8060/8070/8080) expose the same data without the app's auth — confirm retired
  wherever deployed.

---

## 10. Conventions

- Business logic in `app/Services` (thin controllers). Enums in `app/Enums`.
- Cross-DB SQL via `DatabasePortability`; enum columns evolve via
  `DatabasePortability::modifyEnum`.
- Branch model: feature branches (current work on
  `feature/postgres-partner-success`). Commits co-authored; PR bodies note
  Claude Code.
- Tests: PHPUnit, `RefreshDatabase` against a `testing` DB (Pulse/Telescope
  disabled in test env). Run on Postgres.

---

## 11. Tenancy, deployment & commercial model

**The product is sold as a single-tenant, white-label platform.** Each paying
client gets a **dedicated instance** — its own Laravel app + its own PostgreSQL
database — deployed one of two ways:

1. **Client-hosted** — deployed onto the client's own infrastructure (they own
   the DB and data residency).
2. **We-host (for a fee)** — deployed onto our AWS as an isolated per-client
   stack.

**This is _not_ shared multi-tenancy.** There is no `tenant_id` discriminator and
no shared database across clients. Tenant isolation is **physical** (separate DB
+ separate app instance), which is the strongest form of data separation and a
selling point for security-conscious buyers.

### What this model requires the app to support

- **White-label per instance** — colors, logos, app name, custom CSS are
  `AppSetting`-driven and admin-editable (the branding area). This is the
  per-client theming mechanism; it must stay fully data-driven (no hard-coded
  brand). ✅ Exists.
- **Config by environment only (12-factor)** — every instance differs only by
  `.env` / DB / secrets. Integration credentials (Salesforce, Zoom, GitHub,
  Anaplan) are per-instance and configured in-app or via env — never shared
  across clients.
- **Licensing / entitlement control plane** — each instance carries a license
  that defines the paid edition, enabled modules, seat/usage limits, and expiry.
  Recommendation: issue a **signed JWT license** (firebase/php-jwt is installed),
  validate it at boot in `LicenseStatusService`, and drive **Pennant** flags +
  `ModuleRegistry` from it — so entitlement, not code branches, gates features.
- **Fleet release engineering** — the hard part of instanced SaaS is operating
  _N_ instances: a versioned production **Docker image**, **IaC** (Terraform/ECS,
  or Laravel Forge + Envoyer) to provision and update stacks, automated
  **migrations on deploy**, per-instance **backups**, and a rollout plan so an
  upgrade can be applied fleet-wide. Sail is dev-only and not a deploy target.
- **Per-instance observability & backups** — Pulse (installed) per instance;
  centralized log shipping optional. Client-hosted instances may be opaque to us
  by design — decide the support/telemetry contract (a privacy-respecting
  "phone-home" for version/license only, if any).

### Within-instance isolation (still required)

Physical tenancy separates *clients* from each other. **Inside** one instance
you still have internal employees + external partners/clients/prospects who must
only see their own data — that is the row-level authorization work in §3, and it
is independent of the deployment model.

### Explicit non-goals

- No multi-tenant database, no tenancy package (stancl/tenancy,
  spatie/laravel-multitenancy). Adding one would fight the chosen model.
- No cross-client data access or shared identity — each instance is an island.

---

## 12. Presentation layer & the audit-drill (Insight Kit)

**Decision (2026-09):** standardize UX and the "explain any number" audit-drill
via a lightweight **Insight Kit** on the existing Blade + Alpine + ApexCharts
stack — not an admin-panel framework. Full spec in **INSIGHT_KIT.md**.

- **Problem:** there is no shared UI component layer (`resources/views/components`
  is empty), so every view hand-builds its cards/charts/tables, and the
  value→source-rows+SQL+provenance drill is wired only into PSM Engagement's
  `MetricService`. Nothing is consistent because nothing is shared.
- **Filament v4 was piloted and rejected** for this app: it force-downgrades
  Livewire v4→v3, adds ~30 packages and a second chart library (Chart.js), and
  duplicates the custom nav/RBAC platform — while the audit-drill still has to be
  custom. It reused the app's web session cleanly, but the collisions outweigh it.
- **Chosen:** shared Blade components (`resources/views/components/insight/*`) over
  the installed ApexCharts, a `MetricSource` contract + `MetricRegistry` (the
  warehouse `MetricService` becomes the first adapter), and one
  `GET /insights/{metric}/…` endpoint + one Alpine behavior driving a single
  `<x-insight.drill-panel>`.
- **Sequencing:** the drill must enforce row-level visibility, so it depends on
  the row-level authorization layer (§3). Build that first or alongside; do not
  ship a drill that ignores visibility.

---

## 13. Target operating model — the three pillars

The intent and flow of who uses the app and the data everyone generates is
documented in **OPERATING_MODEL.md**. The platform's design rests on three
pillars, which are being built together because they depend on each other:

1. **Multi-role + teams/functions.** People carry one *or more* roles (a manager
   who is also a PSM; a partner who both sells and delivers), and both internal
   users and partner contacts belong to **teams/functions** (Sales, Marketing,
   Service, Implementation) — the axis that makes "partners as an extension of the
   team" real. Implemented additively: `role_user` / `role_contact` pivots
   (single-role columns kept as the *primary* role for landing/back-compat) with
   permission resolution as the **union across roles**; `teams` + polymorphic
   memberships. See §3.
2. **Row-level visibility.** A shared data fabric needs per-actor scoping — a
   partner sees only their company; a PSM their book; a manager their team's books;
   CoE program-wide — via `VisibilityContext` + Policies + `ScopedToActor`. See §3.
3. **Explainable metric layer (Insight Kit).** Every number traces to the partner
   activity that produced it — `MetricSource`/`MetricRegistry` + shared components
   + a visibility-enforcing drill. See §12.

These are the same three needs the operating model implies; treat them as one
target, not three features.
