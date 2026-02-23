<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * MCP Server module service provider.
 *
 * The MCP server is registered via routes/ai.php (auto-loaded by
 * Laravel MCP's package service provider). This provider handles
 * module-level gating — when the mcp_server module is disabled,
 * the routes file still loads but the middleware on the server
 * endpoint ensures no access.
 *
 * MCP tools provided by PartnerManagerServer:
 * - partner_list       — Search and filter partners
 * - partner_profile    — Full partner profile with relationships
 * - partner_pipeline   — Opportunity pipeline and revenue breakdown
 * - partner_scorecard  — Health scorecard and goal progress
 * - analytics_summary  — Portfolio-level analytics
 * - integration_status — Integration Hub connection and job health
 */
class McpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
