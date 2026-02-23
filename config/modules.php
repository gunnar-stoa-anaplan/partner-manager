<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Registered Modules
    |--------------------------------------------------------------------------
    |
    | Each entry defines a module that can be activated per-instance. Modules
    | add screens, routes, and API surfaces to the base Partner Manager
    | application. The 'enabled' flag defaults to the env var shown; in
    | production the control-plane license determines which modules are
    | active via the shared license-status.json file.
    |
    | Keys used here must match the feature slugs returned in the license
    | response (e.g. "integration_hub", "mcp_server").
    |
    */

    'integration_hub' => [
        'name' => 'Integration Hub',
        'description' => 'Data ingestion, export, field mapping, and external connections.',
        'icon' => 'refresh-cw',
        'enabled' => env('MODULE_INTEGRATION_HUB', true),
        'nav_section' => 'Integrations',
        'nav_items' => [
            ['label' => 'Dashboard', 'route' => 'integration-hub.dashboard', 'icon' => 'home'],
            ['label' => 'Connectors', 'route' => 'integration-hub.connectors.catalog', 'icon' => 'package'],
            ['label' => 'Connections', 'route' => 'integration-hub.connections.index', 'icon' => 'link-2'],
            ['label' => 'Field Mappings', 'route' => 'integration-hub.mappings.index', 'icon' => 'shuffle'],
            ['label' => 'Templates', 'route' => 'integration-hub.templates.index', 'icon' => 'copy'],
            ['label' => 'Import Jobs', 'route' => 'integration-hub.jobs.index', 'icon' => 'download', 'params' => ['direction' => 'import']],
            ['label' => 'Export Jobs', 'route' => 'integration-hub.jobs.index', 'icon' => 'upload', 'params' => ['direction' => 'export']],
            ['label' => 'Data Imports', 'route' => 'imports.index', 'icon' => 'upload-cloud'],
        ],
    ],

    'mcp_server' => [
        'name' => 'MCP Server',
        'description' => 'Model Context Protocol server for LLM access to partner data.',
        'icon' => 'cpu',
        'enabled' => env('MODULE_MCP_SERVER', false),
        'nav_section' => 'Integrations',
        'nav_items' => [
            ['label' => 'MCP Overview', 'route' => 'mcp.dashboard', 'icon' => 'cpu'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature-Gated Modules (navigation handled in static template)
    |--------------------------------------------------------------------------
    */

    'analytics' => [
        'name' => 'Analytics Suite',
        'description' => 'Portfolio analytics, revenue analysis, benchmarking, and custom filters.',
        'icon' => 'trending-up',
        'enabled' => env('MODULE_ANALYTICS', true),
    ],

    'competency' => [
        'name' => 'Competency Module',
        'description' => 'Competency matrix, scorecard, goals, partner paths, and action plans.',
        'icon' => 'grid',
        'enabled' => env('MODULE_COMPETENCY', true),
    ],

    'strategy' => [
        'name' => 'Strategy Workbench',
        'description' => 'Strategic planning, scenario modeling, and program governance for the CoE.',
        'icon' => 'compass',
        'enabled' => env('MODULE_STRATEGY', true),
    ],

    'idea_room' => [
        'name' => 'Idea Room',
        'description' => 'AI-powered idea management with gallery wall, chat rooms, and constellation graph.',
        'icon' => 'star',
        'enabled' => env('MODULE_IDEA_ROOM', true),
        'nav_section' => 'Intelligence',
        'nav_items' => [
            ['label' => 'Gallery', 'route' => 'idea_room.dashboard', 'icon' => 'image'],
            ['label' => 'Capture', 'route' => 'idea_room.chat', 'icon' => 'message-circle'],
            ['label' => 'Constellation', 'route' => 'idea_room.constellation', 'icon' => 'git-branch'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Paired Features — Administration & End-User Access
    |--------------------------------------------------------------------------
    |
    | Each capability has two feature flags: one to gate the administration
    | and setup screens (admin role), and one to gate end-user access (PSM
    | and other roles). This allows progressive activation where admins
    | configure a feature before it is exposed to end users.
    |
    */

    'enablement_admin' => [
        'name' => 'Enablement Administration',
        'description' => 'Admin setup for enablement events, blueprints, labs, and calendars.',
        'icon' => 'layers',
        'enabled' => env('MODULE_ENABLEMENT_ADMIN', true),
    ],

    'enablement_user' => [
        'name' => 'Field Enablement End Users',
        'description' => 'End-user access to field enablement, influence maps, breadth and depth views.',
        'icon' => 'users',
        'enabled' => env('MODULE_ENABLEMENT_USER', true),
    ],

    'deal_registration_admin' => [
        'name' => 'Deal Registration Administration',
        'description' => 'Admin setup for deal registration workflows and lead assignment.',
        'icon' => 'file-text',
        'enabled' => env('MODULE_DEAL_REGISTRATION_ADMIN', true),
    ],

    'deal_registration_user' => [
        'name' => 'Deal Registration End User',
        'description' => 'End-user access to submit and track deal registrations.',
        'icon' => 'file-text',
        'enabled' => env('MODULE_DEAL_REGISTRATION_USER', true),
    ],

    'launch_programs_admin' => [
        'name' => 'Launch Program Administration',
        'description' => 'Admin setup for launch programs, milestones, and partner nominations.',
        'icon' => 'zap',
        'enabled' => env('MODULE_LAUNCH_PROGRAMS_ADMIN', true),
    ],

    'launch_programs_user' => [
        'name' => 'Launch Program End User',
        'description' => 'End-user access to view and participate in launch programs.',
        'icon' => 'zap',
        'enabled' => env('MODULE_LAUNCH_PROGRAMS_USER', true),
    ],

    'project_health_admin' => [
        'name' => 'Project Health Administration',
        'description' => 'Admin setup for project tracking, risk categories, and delivery metrics.',
        'icon' => 'clipboard',
        'enabled' => env('MODULE_PROJECT_HEALTH_ADMIN', true),
    ],

    'project_health_user' => [
        'name' => 'Project Health End User',
        'description' => 'End-user access to project health dashboards and risk tracking.',
        'icon' => 'clipboard',
        'enabled' => env('MODULE_PROJECT_HEALTH_USER', true),
    ],

    'qbr_admin' => [
        'name' => 'QBR Workbench Administration',
        'description' => 'Admin setup for QBR templates, session configuration, and review workflows.',
        'icon' => 'clipboard',
        'enabled' => env('MODULE_QBR_ADMIN', true),
    ],

    'qbr_user' => [
        'name' => 'QBR Workbench End User',
        'description' => 'End-user access to conduct and complete quarterly business reviews.',
        'icon' => 'clipboard',
        'enabled' => env('MODULE_QBR_USER', true),
    ],

    'provisioning_admin' => [
        'name' => 'Provisioning Administration',
        'description' => 'Admin setup for workspace provisioning, version configuration, and partner assignment.',
        'icon' => 'server',
        'enabled' => env('MODULE_PROVISIONING_ADMIN', true),
    ],

    'provisioning_user' => [
        'name' => 'Provisioning End User',
        'description' => 'End-user access to view provisioned workspaces and workspace status.',
        'icon' => 'server',
        'enabled' => env('MODULE_PROVISIONING_USER', true),
    ],

    'goals_targets_admin' => [
        'name' => 'Goals & Targets Administration',
        'description' => 'Admin setup for goal templates, target configuration, and assignment rules.',
        'icon' => 'target',
        'enabled' => env('MODULE_GOALS_TARGETS_ADMIN', true),
    ],

    'goals_targets_user' => [
        'name' => 'Goals & Targets End User',
        'description' => 'End-user access to view, track, and manage partner goals and targets.',
        'icon' => 'target',
        'enabled' => env('MODULE_GOALS_TARGETS_USER', true),
    ],

    'benchmark_admin' => [
        'name' => 'Benchmark Administration',
        'description' => 'Admin setup for benchmark definitions, peer groups, and scoring criteria.',
        'icon' => 'award',
        'enabled' => env('MODULE_BENCHMARK_ADMIN', true),
    ],

    'benchmark_user' => [
        'name' => 'Benchmark End User',
        'description' => 'End-user access to partner benchmarking, peer comparisons, and ranking views.',
        'icon' => 'award',
        'enabled' => env('MODULE_BENCHMARK_USER', true),
    ],

    'action_plans_user' => [
        'name' => 'Action Plans End User',
        'description' => 'End-user access to create, manage, and track partner action plans.',
        'icon' => 'list',
        'enabled' => env('MODULE_ACTION_PLANS_USER', true),
    ],

];
