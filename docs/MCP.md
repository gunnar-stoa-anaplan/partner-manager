# MCP Server — operator & developer guide

Partner Manager ships a **Model Context Protocol (MCP)** server so AI clients can
query the partner ecosystem. The *primary consumers are MCP clients* (LLM
agents), so the tool/resource metadata **is** the documentation — see the
conventions below. This file is for the humans who connect and maintain it.

## Endpoints

Defined in `routes/ai.php`:

- **HTTP (remote clients):** `POST /mcp/partner-manager`, protected by
  `auth:sanctum`. Clients authenticate with a Sanctum API token.
- **Local (stdio):** `Mcp::local('partner-manager', …)` for CLI clients.

Server class: `App\Mcp\Servers\PartnerManagerServer`.

## Connecting a client

1. Issue the user a Sanctum token (`/settings` or `createToken`).
2. Point the client at `https://<host>/mcp/partner-manager` with
   `Authorization: Bearer <token>`.
3. On connect, the client reads the server **instructions**, the **tool** list
   (name + description + input schema), and the **data-dictionary** resource.

## Data-scope guarantee

Every tool runs **as the authenticated user** and inherits the app's row-level
visibility (`VisibilityContext`): results are limited to the caller's authorized
partners, aggregates are computed over that set only, and an out-of-scope
`partner_id` reads as not-found. There is no "see everything" path except a user
who holds unrestricted visibility. (This depends on `VisibilityContext` resolving
the `sanctum` guard — see the authorization work; a regression there would
un-scope the whole MCP surface.)

## Tools (all read-only)

| Tool | Purpose |
|------|---------|
| `partner_list` | Search/filter partners in scope; resolve name → id |
| `partner_profile` | One partner's contacts, projects, recent deals |
| `partner_pipeline` | One partner's pipeline (total/weighted/by-stage) |
| `partner_scorecard` | One partner's health scorecard |
| `analytics_summary` | Portfolio-wide totals across all in-scope partners |
| `delivery_readiness` | Capability/staffing gaps by product area |
| `signal_feed` | Prioritized attention queue from the Signal Engine |
| `signal_explain` | Full context behind one signal |
| `integration_status` | Connector / data-sync health |
| `strategy_plan` | Targets vs actuals, budgets, initiatives |

## Resources

| Resource | URI | Purpose |
|----------|-----|---------|
| `data-dictionary` | `partner-manager://data-dictionary` | Glossary, metric definitions, enum reference, scope contract |

## Developer conventions (so new tools stay client-legible)

**Metadata is via PROPERTIES, not attributes.** `laravel/mcp` v0.5.x resolves
`name`, `description` (tools/resources) and `name`/`version`/`instructions`
(server) from `protected` properties. The `#[Name]` / `#[Description]` /
`#[Instructions]` attributes belong to a newer API and are **silently ignored**
in this version — do not use them; they produce generic headline names/descriptions
at the client. (A test, `tests/Feature/Mcp/McpServerDocsTest.php`, guards this.)

When adding a tool:

- Set `protected string $name` to a stable `snake_case` name and register it in
  `PartnerManagerServer::$tools`. Names are an API contract for agents — keep
  them stable; document deprecations.
- Write a `description` that says **when to use it, when not to (vs neighbours),
  and what it returns**. Disambiguation prevents wrong-tool calls.
- Give every schema parameter a `->description(...)`; use `->enum([...])` for
  constrained values and `->required()` where mandatory. Do not hard-enum
  free-text data values (region, industry, product_area) — document them instead.
- Return consistent, self-describing JSON: explicit field names, units in the
  name where ambiguous (`amount` is USD), ids alongside human labels. Never leak
  data outside the caller's scope — rely on model scopes / `VisibilityContext`,
  and treat any raw/`withoutGlobalScope` query as a reviewed exception.
- Add the tool + expected name to the docs-completeness test.
- **Write tools** (none today): a mutating tool must state its side effects,
  require the appropriate capability at `Write` level, enforce data scope, and
  follow a propose-then-confirm pattern for consequential actions.

Add the tool to the server instructions' "Which tool to use" guide and to the
`data-dictionary` resource so clients can discover it.
