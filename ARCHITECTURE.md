# Partner Manager — Architecture Reference

Authoritative model of the application. Keep this current when architecture
changes. It is the starting point for any deep work and the backbone of the
security-review pack.

_Last deep pass: 2026-09-06 (Laravel 12, PHP 8.2+)._

---

## 1. Stack at a glance

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 (PHP ^8.2), Sail/Docker for local dev |
| App database | **PostgreSQL** (migrated from MySQL; `DB_CONNECTION=pgsql`) |
| Analytics warehouse | **PostgreSQL** schema `wh` — audited weekly snapshots (Partner Success / PSM Engagement) |
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

1. **App DB (pgsql, `partner_manager`)** — all transactional data: users, roles,
   permissions, partners, opportunities, workshops, blueprints, activation,
   signals, etc. Session/cache/queue tables live here too.
2. **Warehouse (`warehouse` connection → schema `wh`)** — append-only weekly
   snapshots for the Partner Success / PSM Engagement dashboards. Every row
   carries `snapshot_date`, `load_run_id`, `source_file`, `source_id`
   (provenance). Metric definitions live in `wh.metric_definition`; the
   dashboard derives every number from that catalog. Fed by the Python ingest
   pipeline in the separate `partner-enablement-db` repo (zip of weekly CSVs).
   **Read-only from the app.**
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
- **Two audiences:** internal **users** (`users.role_id → roles`) and external
  **portal contacts** (guarded separately by `PortalAuth`/`PortalRole`/`PortalCan`).
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
| **Dashboard charts** | PSM Engagement uses hand-rolled inline SVG bars/donuts | **Use ApexCharts** (already installed) — consistent with the rest of the app, interactivity for free. *Follow-up to refactor.* |
| **Feature flags** | Custom Activation + ModuleRegistry, **and** Pennant installed | Consolidate on **Pennant** as the flag primitive; keep the training/activation layer on top of it rather than a parallel system |
| **Audit logging** | Custom `audit_logs` + observer | Consider **owen-it/laravel-auditing** or **spatie/activitylog** for model-level audit trails (less custom code, standard format) |
| **RBAC** | Custom roles/`role_permissions` | Works well and is tailored; migrating to **spatie/laravel-permission** is optional (only if you want its ecosystem). Not urgent. |
| **MFA / SSO** | None (email+password) | **laravel/fortify** for 2FA/TOTP; **laravel/socialite** or SAML for SSO — *security priority* |
| **Queue ops** | database driver, no dashboard | **Horizon** (needs Redis) for a queue dashboard + retries/metrics as volume grows |
| **Full-text search** | Custom (KB is pgvector; global search is bespoke) | KB is appropriately pgvector; for entity search consider **Laravel Scout** (DB/Meilisearch driver) if it grows |
| **Realtime** | `BROADCAST=log` (none) | **Laravel Reverb** (websockets) if you want live dashboards / Zoom chat / notifications |
| **Mail** | `MAIL_MAILER=log` | Configure a real mailer (SES/Postmark/etc.) before any user-facing email |
| **Authorization** | Middleware + custom checks, no Policies | Fine, but **Laravel Policies/Gates** would make per-model authorization testable and centralized |
| **Excel/Office/PDF** | maatwebsite/excel, phpoffice, pdfparser | ✅ Already using libraries — good |
| **API docs** | Scramble | ✅ Good |

Leftover: `config/telescope.php` exists but **laravel/telescope is not
installed** — dead config; remove or install intentionally.

---

## 9. Known risks / gaps (security-review inputs)

- Seeded default password (`password`) for all users — dev only.
- **PSM Engagement dashboard is behind plain `auth`** — any authenticated
  internal user sees all partners' pipeline/ACV/Salesforce links. Decide if it
  should be scoped per-PSM.
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
