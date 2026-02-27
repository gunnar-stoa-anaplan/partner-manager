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
    | Role permissions (read/write) control what each user can do within
    | an enabled module. A single Pennant flag gates the entire capability.
    |
    */

    'integration_hub' => [
        'name' => 'Integration Hub',
        'description' => 'Data ingestion, export, field mapping, and external connections.',
        'icon' => 'refresh-cw',
        'enabled' => env('MODULE_INTEGRATION_HUB', true),
        'nav_section' => 'Integrations',
        'nav_items' => [
            ['label' => 'Dashboard', 'route' => 'integration_hub.dashboard', 'icon' => 'home'],
            ['label' => 'Connectors', 'route' => 'integration_hub.connectors.catalog', 'icon' => 'package'],
            ['label' => 'Connections', 'route' => 'integration_hub.connections.index', 'icon' => 'link-2'],
            ['label' => 'Field Mappings', 'route' => 'integration_hub.mappings.index', 'icon' => 'shuffle'],
            ['label' => 'Templates', 'route' => 'integration_hub.templates.index', 'icon' => 'copy'],
            ['label' => 'Import Jobs', 'route' => 'integration_hub.jobs.index', 'icon' => 'download', 'params' => ['direction' => 'import']],
            ['label' => 'Export Jobs', 'route' => 'integration_hub.jobs.index', 'icon' => 'upload', 'params' => ['direction' => 'export']],
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
    | Unified Feature Modules
    |--------------------------------------------------------------------------
    |
    | Each module has a single Pennant feature flag. Role permissions
    | (read/write per module_slug) control what each user role can do
    | within the module.
    |
    */

    'enablement' => [
        'name' => 'Enablement',
        'description' => 'Enablement events, blueprints, labs, calendars, influence maps, and field enablement.',
        'icon' => 'layers',
        'enabled' => env('MODULE_ENABLEMENT', true),
    ],

    'deal_registration' => [
        'name' => 'Deal Registration',
        'description' => 'Deal registration workflows, lead assignment, and deal tracking.',
        'icon' => 'file-text',
        'enabled' => env('MODULE_DEAL_REGISTRATION', true),
    ],

    'launch_programs' => [
        'name' => 'Launch Programs',
        'description' => 'Launch programs, milestones, partner nominations, and participant tracking.',
        'icon' => 'zap',
        'enabled' => env('MODULE_LAUNCH_PROGRAMS', true),
    ],

    'project_health' => [
        'name' => 'Project Health',
        'description' => 'Project tracking, risk categories, delivery metrics, and health dashboards.',
        'icon' => 'clipboard',
        'enabled' => env('MODULE_PROJECT_HEALTH', true),
    ],

    'qbr' => [
        'name' => 'QBR Workbench',
        'description' => 'QBR templates, session configuration, review workflows, and quarterly reviews.',
        'icon' => 'clipboard',
        'enabled' => env('MODULE_QBR', true),
    ],

    'provisioning' => [
        'name' => 'Provisioning',
        'description' => 'Workspace provisioning, version configuration, and partner assignment.',
        'icon' => 'server',
        'enabled' => env('MODULE_PROVISIONING', true),
    ],

    'goals_targets' => [
        'name' => 'Goals & Targets',
        'description' => 'Goal templates, target configuration, assignment rules, and progress tracking.',
        'icon' => 'target',
        'enabled' => env('MODULE_GOALS_TARGETS', true),
    ],

    'benchmark' => [
        'name' => 'Benchmarking',
        'description' => 'Benchmark definitions, peer groups, scoring criteria, and partner comparisons.',
        'icon' => 'award',
        'enabled' => env('MODULE_BENCHMARK', true),
    ],

    'action_plans' => [
        'name' => 'Action Plans',
        'description' => 'Action plan templates, categories, assignment rules, and task board.',
        'icon' => 'list',
        'enabled' => env('MODULE_ACTION_PLANS', true),
    ],

    'partner_portal' => [
        'name' => 'Partner Portal',
        'description' => 'Partner portal configuration, branding, access policies, and portal user management.',
        'icon' => 'globe',
        'enabled' => env('MODULE_PARTNER_PORTAL', true),
    ],

    'partner_portal_partner' => [
        'name' => 'Partner Portal (Partner Side)',
        'description' => 'Partner-side portal access, team settings, and portal experience.',
        'icon' => 'globe',
        'enabled' => env('MODULE_PARTNER_PORTAL_PARTNER', true),
    ],

    'partner_management' => [
        'name' => 'Partner Management',
        'description' => 'Partner management, assignments, CRUD, dashboard, and partner details.',
        'icon' => 'briefcase',
        'enabled' => env('MODULE_PARTNER_MANAGEMENT', true),
    ],

    'feature_activation_coe' => [
        'name' => 'Feature Activation (CoE)',
        'description' => 'CoE access to feature flags, activation paths, and progression dashboard.',
        'icon' => 'unlock',
        'enabled' => env('MODULE_FEATURE_ACTIVATION_COE', true),
    ],

    'ai_agents' => [
        'name' => 'AI Agents',
        'description' => 'AI-powered agents for partner insights, enablement design, lab generation, and content creation.',
        'icon' => 'cpu',
        'enabled' => env('MODULE_AI_AGENTS', true),
    ],

    'enablement_events' => [
        'name' => 'Enablement Events',
        'description' => 'Enablement event catalog, sessions, registrations, and calendar.',
        'icon' => 'layers',
        'enabled' => env('MODULE_ENABLEMENT_EVENTS', true),
    ],

    'blueprints' => [
        'name' => 'Blueprints',
        'description' => 'Blueprint catalog, assignments, enrollments, and progress tracking.',
        'icon' => 'layers',
        'enabled' => env('MODULE_BLUEPRINTS', true),
    ],

    'knowledge_base' => [
        'name' => 'Knowledge Base',
        'description' => 'Document ingestion, embedding management, knowledge base configuration, and search.',
        'icon' => 'book-open',
        'enabled' => env('MODULE_KNOWLEDGE_BASE', true),
    ],

    'zoom_chat' => [
        'name' => 'Zoom Chat Responder',
        'description' => 'AI-powered chat responders for Zoom meetings and webcasts.',
        'icon' => 'video',
        'enabled' => env('MODULE_ZOOM_CHAT', true),
    ],

    'workshop_delivery' => [
        'name' => 'Workshop Delivery',
        'description' => 'Workshops via embedded Zoom with breakout rooms, attendance tracking, and AI chat.',
        'icon' => 'video',
        'enabled' => env('MODULE_WORKSHOP_DELIVERY', true),
    ],

    'workshop_labs' => [
        'name' => 'Workshop Labs',
        'description' => 'Workshop labs, checkpoints, participants, and checkpoint exercises.',
        'icon' => 'check-square',
        'enabled' => env('MODULE_WORKSHOP_LABS', true),
    ],

    'directory_sync_admin' => [
        'name' => 'Directory Sync Administration',
        'description' => 'CSV directory import for automated user provisioning and manager hierarchy sync.',
        'icon' => 'upload-cloud',
        'enabled' => env('MODULE_DIRECTORY_SYNC_ADMIN', true),
    ],

];
