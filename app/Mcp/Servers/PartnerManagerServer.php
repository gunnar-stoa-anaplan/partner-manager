<?php

namespace App\Mcp\Servers;

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
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Partner Manager')]
#[Version('1.0.0')]
#[Instructions(<<<'MARKDOWN'
    Partner Manager MCP Server — provides read access to partner
    relationship data, opportunity pipelines, scorecards, portfolio
    analytics, integration status, and Signal Engine intelligence.

    Available tools:
    - partner_list: Search and filter partners by name, region, or industry
    - partner_profile: Get full partner profile with contacts and projects
    - partner_pipeline: View a partner's opportunity pipeline and revenue
    - partner_scorecard: Get partner health scorecard and goal progress
    - analytics_summary: Portfolio-level analytics across all partners
    - integration_status: Integration Hub connection health and job history
    - signal_feed: Query active signals by channel, severity, partner, or type
    - signal_explain: Get full context for a specific signal with correlated signals
    - delivery_readiness: Assess delivery capability gaps per product area
    - strategy_plan: Query strategy plan targets, actuals, variance, and budgets
    MARKDOWN)]
class PartnerManagerServer extends Server
{
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
}
