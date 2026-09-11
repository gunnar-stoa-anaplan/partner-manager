# Partner Manager

Partner Manager turns a company's **partner ecosystem into a productive extension
of its own go-to-market motion** — sales, marketing, service, and implementation.
It gives internal teams a single place to run partner success and enablement, and
gives partners a branded portal onto the same data.

It is a **productized, single-tenant, white-label application**: each client runs
their own branded instance with its own database, on their infrastructure or
hosted for them. It is *not* a shared multi-tenant SaaS.

## What it does

The **Partner (company)** is the hub. A partner's detail page aggregates every
module as tabs — Pipeline, Deal Registrations, Projects, Enablement, Calendar,
Influencers, Blueprints, PSM Priorities, Resources, Resource Paths, Launch
Programs, Workspaces, and Marketplace. Each partner is owned by an internal
**Partner Success Manager (PSM)** and holds its people (contacts), opportunities,
leads, projects, funding, and resources. Nearly everything in the app is activity
on or about a partner.

Two planes of metrics sit on top of that data:

- **Operational (live):** partner health, scorecards (certification, pipeline,
  delivery, engagement), goal attainment, and signals → alerts. Drives day-to-day
  PSM action.
- **Audited (weekly warehouse snapshots, schema `wh`):** ~21 metrics across
  Pipeline / Delivery / Capability / Enablement / Catalog, each with provenance
  and drill-to-source. Drives reporting, QBRs, and the executive view.

## Who uses it

- **Internal (employees):** Admin/Super Admin (configure the instance), CoE
  (authors the program — strategy, competency paths, blueprints, benchmarks,
  knowledge base), Managers (oversight across PSMs), and PSMs (own a book of
  partners and run the day-to-day).
- **External (partners):** partner contacts sign in to a branded **portal**
  scoped to their own company — profile, enablement, launch programs, resources,
  and workspaces.

## Authorization model

Access is decided by two independent questions:

1. **Capability — what can this user *do*?** Determined by **roles**. A role
   grants `None` / `Read` / `Write` on a capability; a user may hold several
   roles and gets the most-permissive result. Roles never encode data visibility.
2. **Data scope — which partners' records can this user *operate on*?**
   Determined by the user's **relationships** to a partner (owner, assignment)
   rolled up through the reporting hierarchy — not by role. Company-wide
   visibility is a separate, explicit privilege. Enforced fail-closed at the
   query layer (`VisibilityContext` + a `ScopedToActor` global scope), so lists,
   detail, search, dashboards, aggregates, and APIs all see only in-scope rows.

Availability is gated separately again: each **module** has an on/off flag and a
feature flag, and some capabilities sit behind a training lifecycle.

See [`ARCHITECTURE.md`](ARCHITECTURE.md) §3 for the current implementation.

## AI & MCP

The app exposes its data to AI clients through a **Model Context Protocol (MCP)**
server (`app/Mcp`) with read tools for partners, pipeline, scorecards, analytics,
signals, and delivery readiness. Tools run as the authenticated user, so answers
inherit the same row-level scope. A pgvector knowledge base and `laravel/ai`
back in-app AI features.

## Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12, PHP 8.4 |
| Database | PostgreSQL (app data + `wh` analytics schema; pgvector knowledge base) |
| Frontend | Bootstrap 5 + Alpine.js (SmartAdmin theme), Livewire, compiled with Vite |
| Auth | Sanctum (API tokens); separate `web` (internal) and `portal` (partner) guards |
| API | Sanctum tokens, auto OpenAPI docs (Scramble) |
| AI | `laravel/ai`, `laravel/mcp`, pgvector |
| Local dev | Sail (Docker) |

## Getting started

```bash
# Start the Docker environment (see SAIL_QUICK_START.md for details)
./vendor/bin/sail up -d

# Install dependencies, run migrations + seeders
./vendor/bin/sail composer install
./vendor/bin/sail npm install
./vendor/bin/sail artisan migrate --seed

# Build front-end assets
./vendor/bin/sail npm run dev
```

The app serves on **http://localhost:8083**. Run the test suite with
`./vendor/bin/sail artisan test`.

## Documentation

- [`ARCHITECTURE.md`](ARCHITECTURE.md) — authoritative technical model (stack,
  data stores, authorization, modules, integrations, tenancy).
- [`OPERATING_MODEL.md`](OPERATING_MODEL.md) — personas, the loop they move
  through, and the lifecycle of the data they create.
- [`INSIGHT_KIT.md`](INSIGHT_KIT.md) — the metric and audit-drill UX layer.
- [`SECURITY_OVERVIEW.md`](SECURITY_OVERVIEW.md) — security posture.
- [`SAIL_QUICK_START.md`](SAIL_QUICK_START.md) — local development setup.
