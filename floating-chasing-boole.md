# Feature System Redesign — Config-Driven Features with Sections & Extensions

## Context

The app has 27 fine-grained Pennant features, but ~40% of web routes and 100% of API routes lack feature gating. Nav is hardcoded in Blade (~780 lines). Partner detail tabs are hardcoded with inline feature checks. The goal is:

1. **Config-driven features** with sections — each section is independently assignable to roles
2. **Extensions** — a feature can inject nav items into other features' sections and add partner detail tabs
3. **Complete gating** — inactive feature = no nav, no routes, no APIs, no partner tabs
4. **Dynamic nav** from config — replaces hardcoded Blade navigation
5. **Dynamic partner detail tabs** — contributed by active features via `extends`

## Architecture

### Config Structure

Each feature defines its sections and optional extensions in `config/modules.php`:

```php
'launch_programs' => [
    'name'    => 'Launch Programs',
    'icon'    => 'zap',
    'group'   => 'engagement',
    'enabled' => env('MODULE_LAUNCH_PROGRAMS', true),

    'sections' => [
        'programs' => [
            'label' => 'Launch Programs',
            'icon'  => 'zap',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.launch-programs.dashboard', 'icon' => 'home'],
                ['label' => 'Program List', 'route' => 'admin.launch-programs.index', 'icon' => 'list'],
            ],
        ],
        'administration' => [
            'label' => 'Launch Program Administration',
            'icon'  => 'settings',
            'items' => [
                ['label' => 'Program Design', 'route' => 'admin.launch-programs.create', 'icon' => 'edit'],
            ],
        ],
    ],

    'extends' => [
        // Inject nav item into Partner Management
        'partner_management.management' => [
            'items' => [
                ['label' => 'Launch Programs', 'route' => '...', 'icon' => 'zap'],
            ],
        ],
        // Inject tab into Partner Detail page
        'partner_detail' => [
            'tabs' => [
                ['label' => 'Launch Programs', 'slug' => 'launch-programs', 'icon' => 'rocket',
                 'partial' => 'partner.tabs.launch-programs', 'section' => 'programs'],
            ],
        ],
    ],
],
```

### Permission Model

Role permissions use `feature.section` dot notation:

```php
// CoE — both sections
'launch_programs.programs'       => 'write',
'launch_programs.administration' => 'write',

// PSM — programs only, no administration
'launch_programs.programs'       => 'write',
```

### Middleware

`module:launch_programs.programs` checks:
1. Is `launch_programs` active? (Pennant)
2. Does user have `launch_programs.programs` permission? (role_permissions)
3. For mutating requests, does user have `write`?

`module:launch_programs` (no section) checks only Pennant + any permission for that feature.

### Extension Visibility

A contributed nav item or partner detail tab shows when:
1. The contributing feature is active
2. The user has permission for the referenced `section`

### Nav Rendering

Fully dynamic from config — replaces hardcoded `generated-navigation.blade.php`.

### Partner Detail Tabs

Dynamic from config `extends.partner_detail.tabs` — replaces hardcoded tab list. Each tab references its Blade partial for AJAX loading.

---

## Feature Mapping (21 features)

### Always-On (no feature flag)

| Routes | Notes |
|--------|-------|
| `/welcome`, `/program-dashboard` | Landing pages |
| `/profile` | User profile |
| `/training/*` | Activation path training |
| `/impersonate*` | Role switching |
| `/api/search`, `/api/chat` | Global search & AI chat |
| `/api/filter-data/*` | Cross-feature filter endpoints |
| Feature Activation (`/activation/*`) | Gated by `can:manage-activation` |

---

### 1. Partner Management
**Group:** core

| Section | Label | Key Routes |
|---------|-------|------------|
| `management` | Partner Management | Dashboard, My Partners, Partner Detail (show/summary/insights/tabs), Calendars, Full Summary/Insights |
| `administration` | Partner Administration | Partner CRUD, Partner Assignments, Reporting Hierarchy |

**Partner Detail base tabs** (owned): Overview, Resources
**API (web):** `/api/events/*`, `/api/partners/{partner_id}/contacts`
**API (routes/api.php):** `GET /partners`, `GET /partners/{id}`, etc.

---

### 2. Analytics
**Group:** core
**Extends:** `partner_management.management` → Coach, Wizard, Custom Filters nav items

| Section | Label | Key Routes |
|---------|-------|------------|
| `analysis` | Analytics | Coach, Wizard, Custom Filters |

---

### 3. Competency
**Group:** core
**Extends:** `partner_management.management` → Partner Paths nav items

| Section | Label | Key Routes |
|---------|-------|------------|
| `paths` | Competency | Partner Paths (enrollment/show/advance), Scorecard, Competency Matrix |
| `administration` | Competency Administration | Path Configuration, Task Templates, Scorecard Weights, Path Reviews |

---

### 4. Enablement
**Group:** enablement
**Absorbs:** `enablement` + `enablement_events`
**Extends:** `partner_detail` → tabs: Enablement, Calendar, Influencers, Nicole's View

| Section | Label | Key Routes |
|---------|-------|------------|
| `calendar` | Enablement | Enablement Calendar, My Nominations, Partner Enablement |
| `field` | Field Enablement | Key Influencers, Breadth, Depth, Influence Map, Contacts/Influencers |
| `events` | Enablement Events | Events CRUD, Sessions, Registrations, Enablement Calendar (admin) |
| `administration` | Enablement Administration | Enablement Dashboard, Workshop Guides Admin |

**API (web):** `/api/enablement/sessions/*`, `/api/enablement/registrations/*`
**API (routes/api.php):** `GET /enablement/*`, `POST /enablement/designer`, etc.

---

### 5. Blueprints
**Group:** enablement
**Extends:** `partner_detail` → tab: Blueprints

| Section | Label | Key Routes |
|---------|-------|------------|
| `catalog` | Blueprints | Blueprint Catalog, Assignments, Enrollments, Progress, Blueprint Owner Edit |
| `administration` | Blueprint Administration | Blueprint CRUD, Dashboard, Outcomes, Export/Duplicate |

**API (web):** `/api/blueprint-progress/toggle`

---

### 6. Workshops
**Group:** enablement
**Absorbs:** `workshop_labs` + `workshop_delivery`

| Section | Label | Key Routes |
|---------|-------|------------|
| `labs` | Workshops | Lab Dashboard, Lab Facilitator |
| `administration` | Workshop Administration | Lab CRUD, Checkpoints, Participants, Generate |

---

### 7. Launch Programs
**Group:** engagement
**Extends:** `partner_management.management` → nav item, `partner_detail` → tab: Launch Programs

| Section | Label | Key Routes |
|---------|-------|------------|
| `programs` | Launch Programs | Dashboard, Program List |
| `administration` | Launch Program Administration | Program CRUD, Milestones, Admin Nomination |

---

### 8. Deal Registration
**Group:** engagement
**Extends:** `partner_detail` → tabs: Pipeline, Deal Registrations, Marketplace

| Section | Label | Key Routes |
|---------|-------|------------|
| `deals` | Deal Registration | Dashboard, Deal List, Create/Show/Review |
| `business_development` | Business Development | Sourced Leads, Influenced Leads, Development Funding |
| `lead_assignment` | Lead Assignment | New Leads, Assigned Leads, Rejected Leads |
| `marketplace` | Marketplace | Marketplace Submissions |

---

### 9. Goals & Targets
**Group:** engagement

| Section | Label | Key Routes |
|---------|-------|------------|
| `goals` | Goals & Targets | Partner Goals, Goals CRUD, Refresh Actuals |
| `administration` | Goals Administration | Goal Templates, Assignment |

---

### 10. Action Plans
**Group:** engagement

| Section | Label | Key Routes |
|---------|-------|------------|
| `plans` | Action Plans | Action Plans CRUD, Task Board |

---

### 11. Project Health
**Group:** engagement
**Extends:** `partner_detail` → tab: Projects

| Section | Label | Key Routes |
|---------|-------|------------|
| `projects` | Project Health | Projects CRUD, Risks |

---

### 12. Measurement
**Group:** measurement
**Absorbs:** `benchmark` + `qbr` + health/alerts

| Section | Label | Key Routes |
|---------|-------|------------|
| `analytics` | Measurement | Measure tabs (At a Glance, Pipeline, Lead Flow, Funding, Revenue, Enablement, Delivery) |
| `benchmarks` | Benchmarks | Benchmark Index, Partner Benchmarks |
| `health` | Partner Health | Health Dashboard, Partner Health Detail, Alerts |
| `qbr` | QBR Workbench | QBR Sessions, Sections, Complete |

---

### 13. Strategy
**Group:** intelligence

| Section | Label | Key Routes |
|---------|-------|------------|
| `strategy` | Strategy Workbench | Dashboard, Plans, Scenarios, Benchmarks |

---

### 14. AI & Knowledge
**Group:** intelligence
**Absorbs:** `ai_agents` + `knowledge_base`

| Section | Label | Key Routes |
|---------|-------|------------|
| `agents` | AI Agents | Agent Overview |
| `knowledge_base` | Knowledge Base | Dashboard, Documents, Ingest |

---

### 15. Idea Room
**Group:** intelligence

| Section | Label | Key Routes |
|---------|-------|------------|
| `room` | Idea Room | Gallery, Rooms, Ideas, Whiteboard, Constellation |

---

### 16. Zoom Chat
**Group:** intelligence

| Section | Label | Key Routes |
|---------|-------|------------|
| `chat` | Zoom Chat | Configuration, Webhook |

---

### 17. Integration Hub
**Group:** infrastructure
**Absorbs:** `integration_hub` + `mcp_server`

| Section | Label | Key Routes |
|---------|-------|------------|
| `hub` | Integration Hub | Dashboard, Connectors, Connections, Mappings, Templates, Import/Export Jobs, Data Imports |
| `mcp` | MCP Server | MCP Overview |
| `api_tokens` | API Tokens | Token Management |

**API (routes/api.php):** `POST /imports/*`

---

### 18. Directory Sync
**Group:** infrastructure

| Section | Label | Key Routes |
|---------|-------|------------|
| `sync` | Directory Sync | CSV Upload, Sync Management |

---

### 19. Portal
**Group:** infrastructure
**Absorbs:** `partner_portal` + `partner_portal_partner`
**Extends:** `partner_detail` → tab: Portal Users

| Section | Label | Key Routes |
|---------|-------|------------|
| `management` | Portal Management | Portal Configuration, Portal User Management, Branding |
| `partner` | Partner Portal | Partner-side portal experience (routes/portal.php) |

---

### 20. Provisioning
**Group:** infrastructure
**Extends:** `partner_detail` → tab: Workspaces

| Section | Label | Key Routes |
|---------|-------|------------|
| `provisioning` | Provisioning | Dashboard, Workspaces, Version Config |

---

### 21. System Administration
**Group:** infrastructure

| Section | Label | Key Routes |
|---------|-------|------------|
| `admin` | System Administration | Admin Dashboard, Configuration, System Status, Roles & Permissions, Notification Templates |

---

## Feature Groups (for Feature Activation UI)

| Group | Features |
|-------|----------|
| **Core** | Partner Management, Analytics, Competency |
| **Enablement** | Enablement, Blueprints, Workshops |
| **Engagement** | Deal Registration, Launch Programs, Action Plans, Goals & Targets, Project Health |
| **Measurement** | Measurement |
| **Intelligence** | Strategy, AI & Knowledge, Idea Room, Zoom Chat |
| **Infrastructure** | Integration Hub, Directory Sync, Portal, Provisioning, System Administration |

---

## Summary

| | Before | After |
|---|--------|-------|
| Features | 27 | 21 |
| Feature classes | 27 | 21 |
| Config entries | 27 (flat) | 21 (with sections + extends) |
| Nav rendering | Hardcoded Blade (~780 lines) | Dynamic from config |
| Partner detail tabs | Hardcoded with inline checks | Dynamic from `extends` config |
| Ungated routes | ~40% of web, 100% of API | 0% — all business routes gated |
| Permission model | `module_slug => read/write` | `feature.section => read/write` |
| Feature grouping | Flat list | 6 groups |

---

## Implementation Approach (high level)

### Phase 1: Infrastructure
1. Update `config/modules.php` with new structure (sections, extends, groups)
2. Update `EnsureModuleEnabled` middleware to parse `feature.section` format
3. Update `ModuleRegistry` with methods: `getAccessibleSections()`, `getPartnerDetailTabs()`, `getExtendedItems()`
4. Update `role_permissions` table to use `feature.section` slugs
5. Create/delete Feature classes to match new 21 features

### Phase 2: Route Gating
6. Add `module:feature.section` middleware to all ungated routes in `routes/web.php`
7. Add `module:feature` middleware to API routes in `routes/api.php`
8. Update module route files in `bootstrap/app.php` to use new feature slugs

### Phase 3: Dynamic Nav
9. Build dynamic nav renderer (new Blade partial)
10. Replace hardcoded `generated-navigation.blade.php`

### Phase 4: Dynamic Partner Detail
11. Build dynamic tab renderer for `partner/show.blade.php`
12. Replace hardcoded tab list with config-driven tabs

### Phase 5: Permissions & Seeders
13. Update `RoleSeeder` with `feature.section` permissions
14. Update `ActivationPathSeeder` for new feature slugs
15. Migration to update existing `role_permissions` and `features` DB records

### Phase 6: Tests & Verification
16. Update all test files referencing old feature slugs
17. Run full test suite
18. Manual verification per feature toggle
