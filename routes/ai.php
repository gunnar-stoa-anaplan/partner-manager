<?php

use App\Mcp\Servers\PartnerManagerServer;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| MCP Server Routes
|--------------------------------------------------------------------------
|
| These routes expose Model Context Protocol (MCP) endpoints that allow
| LLMs and AI clients to query partner data, pipeline analytics, and
| integration status. Authentication is handled via Sanctum bearer tokens.
|
*/

// Authenticated endpoint — requires a valid Sanctum API token
Mcp::web('/mcp/partner-manager', PartnerManagerServer::class)
    ->middleware('auth:sanctum');

// Local stdio transport for CLI-based MCP clients (e.g. Claude Code)
Mcp::local('partner-manager', PartnerManagerServer::class);
