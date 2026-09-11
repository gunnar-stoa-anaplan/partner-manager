<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

/**
 * Reference context an MCP client can pull once and reason over: the domain
 * model, metric definitions, the enum reference for tool inputs, and the scope
 * contract. Static text — no per-request data, so nothing here is user-scoped.
 */
class DataDictionaryResource extends Resource
{
    protected string $name = 'data-dictionary';

    protected string $uri = 'partner-manager://data-dictionary';

    protected string $mimeType = 'text/markdown';

    protected string $description = 'Domain glossary, metric definitions, enum reference, and the data-scope contract for the Partner Manager tools.';

    public function handle(Request $request): Response
    {
        return Response::text(<<<'MARKDOWN'
        # Partner Manager — Data Dictionary

        ## Data-scope contract
        Every tool result is limited to the partners the calling user may see
        (owned + assigned + reporting-hierarchy, or all under unrestricted
        visibility). Empty results mean "none in your scope", not an error.
        Aggregates are over the in-scope set only. An out-of-scope `partner_id`
        reads as not-found. All tools are read-only.

        ## Entities
        - **Partner** — a partner company; the hub. Numeric `id`. Has region,
          geography, industry; owns contacts, opportunities, projects, resources.
        - **Contact** — a person at a partner (portal user or influencer).
        - **Opportunity** — a sales deal: `stage`, `amount` (USD), `probability`
          (0–100), `product_area`, `fiscal_year` (e.g. FY27), `fiscal_quarter`.
        - **Project** — delivery engagement with a `status`.
        - **Scorecard** — partner health snapshot (overall_score 0–100) blending
          certification, pipeline, delivery, and engagement.
        - **Signal** — machine-generated insight; may be promoted to an alert.
        - **Certification** — a contact's credential in a product area (active/…).
        - **Strategy plan** — annual targets/actuals/variance, assumptions,
          budgets, initiatives.

        ## Key metrics
        - **total_pipeline** — sum of opportunity `amount` (USD) in scope.
        - **weighted_pipeline** — sum of `amount × probability/100`.
        - **coverage_ratio** (delivery readiness) — certified_resources ÷
          forecasted_projects for a product area. Lower = more risk.
        - **resource_gap** — forecasted_projects − certified_resources (≥ 0).

        ## Enum reference (tool inputs)
        - Signal `channel`: risk, opportunity, trend, anomaly, recommendation, benchmark
        - Signal `severity`: critical, warning, info, positive
        - Signal `status`: active, acknowledged, resolved, expired, dismissed
        - Strategy plan `status`: draft, committed, active, closed
        - `region` / `geography` / `industry` / `product_area` are free-text data
          values — discover valid ones via `partner_list` / `analytics_summary`
          rather than guessing.
        - `risk_level` (delivery-readiness output): critical (<0.25 coverage),
          high (<0.5), warning (<0.75), low (<1.0), none (≥1.0).

        ## Tool selection
        - Resolve a partner name → id: `partner_list`.
        - One partner: `partner_profile` (full), `partner_pipeline` (deals),
          `partner_scorecard` (health).
        - Across all in-scope partners: `analytics_summary`.
        - Capability/staffing gaps by product area: `delivery_readiness`.
        - Attention queue: `signal_feed`; deep-dive one signal: `signal_explain`.
        - Connector/data-sync health: `integration_status`.
        - Annual plan tracking: `strategy_plan`.
        MARKDOWN);
    }
}
