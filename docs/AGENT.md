# Screen Agent (Phase 1)

An embedded, screen-aware assistant: it offers proactive insights about the
current screen and answers the user's questions about that screen's data — always
grounded in the caller's **row-level-scoped** data, so it can only ever surface
what the user is already allowed to see.

**Status:** Phase 1 — the engine (provider strategy, scoped grounding, endpoints,
reusable panel). Disabled by default (`AGENT_ENABLED=false`); additive and inert
until enabled.

## Architecture

| Piece | Role |
|---|---|
| `config/agent.php` | `enabled` flag + `provider` selection |
| `App\Services\Agent\ScreenContext` | value object: screen key + filters + a scoped **facts** snapshot |
| `App\Services\Agent\AgentProvider` | strategy interface: `insights()`, `answer()` |
| `App\Services\Agent\SimulatedProvider` | default: deterministic, templated analysis over the real scoped facts (no external calls) |
| `App\Services\Agent\ScreenAgentService` | builds the scoped facts per screen, resolves the provider |
| `App\Http\Controllers\AgentController` | `POST /agent/insights`, `POST /agent/ask` (auth; 404 unless enabled) |
| `resources/views/components/agent/panel.blade.php` | `<x-agent.panel screen="…" />` offcanvas; renders only when enabled |

## Why it is safe (grounding = scoping)

`ScreenAgentService` builds every fact from the row-level-scoped Eloquent models
(`Opportunity`, `Partner`, …). Running as the authenticated user, those queries
are already limited to the caller's visible partners — so the agent's numbers are
scoped by construction. The LLM (when added) never writes SQL; it receives the
pre-scoped facts (and, later, calls the scoped MCP tools). This is exactly why the
authorization/scoping work is the prerequisite: the agent turns any scoping gap
into a spoken answer.

## Enabling

```
AGENT_ENABLED=true
AGENT_PROVIDER=simulated   # swap to a real LLM provider later
```

Include the panel on a view: `<x-agent.panel screen="pipeline" />` (already on the
Pipeline screen). Add a screen by giving `ScreenAgentService::facts()` a builder
for that screen key.

## Roadmap

- **Phase 2 — real LLM provider.** Add an `AnthropicProvider` (via `laravel/ai`)
  bound in `ScreenAgentService::provider()`; same `ScreenContext`, same facts,
  richer language. Provided by the user's API key. No UI/grounding change.
- **Reuse the MCP tools** as the agent's tool layer (one scoped surface for both
  the in-app agent and external MCP clients).
- **Phase 3 — write actions.** Let the agent create/update records (e.g. "create
  a workshop session and enroll the top 10 AMER partners"). Each write tool must:
  enforce the relevant capability at **Write** level, enforce data scope (only
  act on records the user can see), and **propose-then-confirm** for consequential
  actions — no silent execution.
