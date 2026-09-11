<?php

namespace App\Mcp\Servers;

use App\Mcp\Resources\DataDictionaryResource;
use App\Mcp\Tools\AnalyticsSummaryTool;
use App\Mcp\Tools\DeliveryReadinessTool;
use App\Mcp\Tools\IntegrationStatusTool;
use App\Mcp\Tools\PartnerListTool;
use App\Mcp\Tools\PartnerPipelineTool;
use App\Mcp\Tools\PartnerProfileTool;
use App\Mcp\Tools\PartnerScorecardTool;
use App\Mcp\Tools\SignalExplainTool;
use App\Mcp\Tools\SignalFeedTool;
use App\Mcp\Tools\StrategyPlanTool;
use Laravel\Mcp\Server;

class PartnerManagerServer extends Server
{
    protected string $name = 'Partner Manager';

    protected string $version = '1.0.0';

    // NOTE: laravel/mcp v0.5.x resolves server/tool metadata from these
    // PROPERTIES, not from #[Name]/#[Instructions]/#[Description] attributes
    // (which belong to a newer API and are silently ignored here). Keep the
    // metadata as properties so it actually reaches MCP clients.
    protected string $instructions = <<<'MARKDOWN'
        Partner Manager exposes read access to a company's partner ecosystem —
        partners (companies), their opportunity pipeline, health scorecards,
        portfolio analytics, delivery readiness, integration status, Signal
        Engine intelligence, and strategy plans.

        ## Data scope (read this first)
        Every result is already limited to the partners the calling user is
        authorized to see — their owned and assigned partners plus everyone in
        their reporting hierarchy, or all partners if they hold unrestricted
        visibility. You never receive data about partners outside that scope. An
        empty result means "none in your scope", NOT an error, and totals/counts
        are computed over the in-scope set only — never present them as
        company-wide unless the caller is known to have unrestricted visibility.
        A partner_id that is out of scope behaves as if it does not exist
        (not-found), never as "forbidden".

        ## Vocabulary
        - Partner: a partner company (the hub of the model). Numeric id.
        - Opportunity: a sales deal on a partner's pipeline (stage, amount,
          probability, product_area, fiscal_year/quarter).
        - Scorecard: a partner's health score (certification + pipeline +
          delivery + engagement).
        - Signal: a machine-generated insight (risk/opportunity/trend/anomaly/
          recommendation/benchmark) that may become an alert.
        - Product area: a solution/practice line an opportunity or certification
          maps to; used for delivery-readiness gaps.

        ## Which tool to use
        - Find or filter partners, or get their ids -> partner_list.
        - Everything about ONE partner (contacts, projects, recent deals) ->
          partner_profile; its deals only -> partner_pipeline; its health ->
          partner_scorecard.
        - Totals ACROSS all in-scope partners (counts, pipeline, project status)
          -> analytics_summary (portfolio-wide; do not use for one partner).
        - "Can we staff forecasted work? where are the capability gaps?" ->
          delivery_readiness (per product area, portfolio or one partner).
        - What needs attention right now -> signal_feed; the full "why" behind one
          signal -> signal_explain.
        - Data pipeline / connector health -> integration_status.
        - Tracking against the annual plan (targets vs actuals, budgets) ->
          strategy_plan.

        Most single-partner tools need a numeric partner_id: call partner_list
        first to resolve a name to an id. All tools are READ-ONLY. For the domain
        glossary, metric definitions, and the full enum reference, read the
        data-dictionary resource.
        MARKDOWN;

    protected array $tools = [
        PartnerListTool::class,
        PartnerProfileTool::class,
        PartnerPipelineTool::class,
        PartnerScorecardTool::class,
        AnalyticsSummaryTool::class,
        IntegrationStatusTool::class,
        SignalFeedTool::class,
        SignalExplainTool::class,
        DeliveryReadinessTool::class,
        StrategyPlanTool::class,
    ];

    protected array $resources = [
        DataDictionaryResource::class,
    ];
}
