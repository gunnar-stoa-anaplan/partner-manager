# Insight Kit — design spec

A shared presentation + metric layer so every view renders metrics, charts,
tables, and filters the same way, and so the "click a value → source rows + SQL
+ provenance" **audit-drill** works everywhere — not just the PSM Engagement
dashboard.

Status: **approved design, not yet built** (2026-09). See ARCHITECTURE.md §12.

## Why this, not a framework

Filament v4 was piloted (throwaway branch, now deleted) and rejected for this
app: it force-downgrades Livewire v4→v3, pulls in ~30 packages, adds a second
chart library (Chart.js), and runs its own nav/panel/auth in parallel with the
app's custom `ModuleRegistry`/`RedirectRestrictedRoles`/RBAC — while still not
providing the audit-drill natively. It's good at the easy part (the card/chart
shell) and no help with the hard part (explainable metrics + reconciling with
the bespoke platform).

The Insight Kit is built on what the app already has: **Blade components**
(first-party, currently unused), **Alpine**, **ApexCharts** (installed),
`fetch` → JSON. No new platform, no second chart lib, no Livewire requirement.

## Layer 1 — Presentation components

Blade components under `resources/views/components/insight/`, all reading design
tokens so per-client white-labeling stays central.

| Component | Purpose |
|-----------|---------|
| `<x-insight.stat-card>` | KPI tile: title, value, sub/delta, sparkline, optional drill handle |
| `<x-insight.chart>` | One **ApexCharts** wrapper (bar/donut/line/area); per-point drill; themed once |
| `<x-insight.data-table>` | Consistent table: columns, sort, empty/loading states, optional row drill |
| `<x-insight.filter-bar>` | GEO/segment + entity (partner) selector; drives the view's data |
| `<x-insight.drill-panel>` | The audit surface: value, source rows + SQL + provenance; one component, used everywhere |
| `<x-insight.section-tabs>` | The Pipeline/Delivery/… tab pattern (optional) |

## Layer 2 — The explainable-metric contract

A metric knows how to compute itself **and** how to explain itself:

```php
interface MetricSource
{
    public function key(): string;                             // 'stage_3_4_acv'
    public function meta(): MetricMeta;                        // title, unit, description, source
    public function value(MetricFilter $f): MetricValue;       // {count, sum, unit, snapshot}
    public function drill(MetricFilter $f): MetricDrill;       // {rows, columns, sql, provenance}
    public function trend(MetricFilter $f): array;             // optional, for charts
    public function breakdown(string $dim, MetricFilter $f): array;
}
```

- DTOs: `MetricMeta`; `MetricFilter` (geo / partner / segment / date + **actor +
  visibility scope**); `MetricValue`; `MetricDrill`
  (`rows`, `columns`, `sql`, `provenance{source_file, load_run, snapshot_date, row_count}`).
- `MetricRegistry` (mirrors `ModuleRegistry`) registers sources by key.
- The warehouse `MetricService` becomes the **first adapter** — it already
  returns value/drill/trend/breakdown + provenance. App-DB metrics (workshop
  completion, partner health, …) implement the same interface later.

## Layer 3 — One endpoint + one behavior

- Routes: `GET /insights/{metric}/{value|drill|trend|breakdown}` → resolve the
  source from `MetricRegistry`, apply filters, return JSON.
- **The drill endpoint enforces row-level visibility** (the `VisibilityContext`
  from the auth plan, ARCHITECTURE.md §3). An audit drill must only ever return
  rows the current user is entitled to see. The drill layer and the row-level
  authorization work are the same effort — do it once.
- One `insights.js` (Alpine): filter store, fetch helpers, `openDrill(metricKey)`
  → `/drill` → `<x-insight.drill-panel>`, with standardized loading/empty/error
  states. Replaces the per-view hand-written `fetch` currently in the dashboard.

## Reference extraction — PSM Engagement maps on

| Today (bespoke) | Becomes |
|-----------------|---------|
| 6 hand-coded KPI cards | `<x-insight.stat-card>` × warehouse `MetricSource` |
| Hand-rolled SVG bars/donuts | `<x-insight.chart>` (ApexCharts) |
| GEO + partner selector | `<x-insight.filter-bar>` |
| Click-to-drill overlay | `<x-insight.drill-panel>` + `/insights/drill` |
| Inline `fetch` JS | shared `insights.js` |

The most advanced view becomes the reference — with less custom code than today.

## Reuse vs build

- **Reuse:** Blade components, ApexCharts, Alpine, `MetricService`, branding tokens.
- **Build (custom anywhere — the differentiator):** the `MetricSource` contract +
  drill/provenance layer.
- **Avoid:** Filament/Nova (platform collision), Chart.js (second lib), a Livewire
  requirement.

## Rollout

1. Kit + `MetricSource`/registry + endpoint + drill behavior.
2. Refactor PSM Engagement onto it (reference; removes the custom SVG).
3. Adopt view-by-view — each conversion makes a view consistent **and** drillable.
4. New views use the kit by default.

## Dependencies / sequencing

- The drill's row-level enforcement needs the **row-level authorization** layer
  (Policies + `VisibilityContext`, ARCHITECTURE.md §3). Build that first, or build
  them together — the reference extraction should not ship a drill that ignores
  visibility.
