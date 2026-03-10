<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product Modules — Seed Source
    |--------------------------------------------------------------------------
    |
    | This file is the VERSION-CONTROLLED SEED SOURCE for the `modules` table.
    | At runtime, module definitions are loaded from the database (not this file).
    | ModuleSeeder reads this config to populate the database via updateOrCreate.
    |
    | To add or modify products:
    |   1. Edit this file
    |   2. Run: php artisan db:seed --class=ModuleSeeder
    |
    | Structure:
    |   - sections: nav groups with items; each section maps to a role permission
    |   - extends:  contributions to other features' nav or partner detail tabs
    |   - group:    UI grouping for the Feature Activation dashboard
    |
    | Permission model: role_permissions use "product.section" dot notation.
    | e.g. "launch_programs.programs" => "write"
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Core
    |--------------------------------------------------------------------------
    */

    'partner_management' => [
        'name' => 'Partner Management',
        'description' => 'Partner operations, detail views, calendars, analytics, provisioning, and administrative management.',
        'icon' => 'briefcase',
        'group' => 'core',
        'enabled' => env('MODULE_PARTNER_MANAGEMENT', true),
        'sections' => [
            'management' => [
                'label' => 'Partner Management',
                'icon' => 'briefcase',
                'items' => [
                    ['label' => 'State of Business', 'route' => 'state-of-business', 'icon' => 'activity'],
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
                    ['label' => 'My Partners', 'route' => 'partners.my', 'icon' => 'users'],
                    ['label' => 'Calendar', 'route' => 'calendar.portfolio', 'icon' => 'calendar'],
                ],
            ],
            'analytics' => [
                'label' => 'Analytics',
                'icon' => 'trending-up',
                'items' => [
                    ['label' => 'Custom Filters', 'route' => 'custom-filters.index', 'icon' => 'filter'],
                    ['label' => 'Wizard', 'route' => 'wizard.index', 'icon' => 'compass'],
                    ['label' => 'Coach', 'route' => 'coach.index', 'icon' => 'message-circle'],
                ],
            ],
            'provisioning' => [
                'label' => 'Provisioning',
                'icon' => 'server',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'admin.provisioning-summary', 'icon' => 'home'],
                    ['label' => 'Workspaces', 'route' => 'admin.provisioning-workspaces.index', 'icon' => 'server'],
                ],
            ],
            'priorities' => [
                'label' => 'Priorities & Progress',
                'icon' => 'target',
                'items' => [
                    ['label' => 'Portfolio Priorities', 'route' => 'priorities.index', 'icon' => 'trending-up'],
                    ['label' => 'Weekly Focus', 'route' => 'priorities.weekly', 'icon' => 'check-square'],
                    ['label' => 'Progress Timeline', 'route' => 'priorities.timeline', 'icon' => 'bar-chart'],
                    ['label' => 'Team Overview', 'route' => 'priorities.team', 'icon' => 'users'],
                ],
            ],
            'administration' => [
                'label' => 'Partner Administration',
                'icon' => 'settings',
                'items' => [
                    ['label' => 'Partner Assignments', 'route' => 'partners.index', 'icon' => 'link'],
                    ['label' => 'Reporting Hierarchy', 'route' => 'reporting-hierarchy.index', 'icon' => 'git-merge'],
                ],
            ],
        ],
        'extends' => [
            'partner_detail' => [
                'tabs' => [
                    ['label' => 'Overview', 'slug' => 'overview', 'icon' => 'tachometer-alt', 'partial' => 'partner.tabs.overview', 'section' => 'management', 'sort' => 0, 'server_rendered' => true],
                    ['label' => 'PSM Priorities', 'slug' => 'psm-priorities', 'icon' => 'target', 'partial' => 'partner.tabs.psm-priorities', 'section' => 'priorities', 'sort' => 65],
                    ['label' => 'Resources', 'slug' => 'resources', 'icon' => 'user-hard-hat', 'partial' => 'partner.tabs.resources', 'section' => 'management', 'sort' => 70],
                    ['label' => 'Workspaces', 'slug' => 'workspaces', 'icon' => 'server', 'partial' => 'partner.tabs.workspaces', 'section' => 'provisioning', 'sort' => 80, 'badge_relation' => 'anaplanWorkspaces'],
                ],
            ],
        ],
    ],

    'competency' => [
        'name' => 'Competency',
        'description' => 'Partner paths, scorecard, competency matrix, and path reviews.',
        'icon' => 'grid',
        'group' => 'core',
        'enabled' => env('MODULE_COMPETENCY', true),
        'sections' => [
            'paths' => [
                'label' => 'Partner Paths',
                'icon' => 'map',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'plan.paths.index', 'icon' => 'home'],
                    ['label' => 'Scorecard', 'route' => 'plan.scorecard.index', 'icon' => 'bar-chart-2'],
                    ['label' => 'Competency Matrix', 'route' => 'plan.competency-matrix.index', 'icon' => 'grid'],
                ],
            ],
            'administration' => [
                'label' => 'Competency Administration',
                'icon' => 'settings',
                'items' => [
                    ['label' => 'Path Configuration', 'route' => 'admin.paths.index', 'icon' => 'sliders'],
                    ['label' => 'Task Templates', 'route' => 'admin.task-templates.index', 'icon' => 'file-text'],
                    ['label' => 'Scorecard Weights', 'route' => 'admin.scorecard-weights.index', 'icon' => 'bar-chart-2'],
                    ['label' => 'Path Reviews', 'route' => 'act.path-reviews.index', 'icon' => 'check-circle'],
                ],
            ],
        ],
        'extends' => [
            'partner_management.management' => [
                'items' => [
                    ['label' => 'Partner Paths', 'route' => 'plan.paths.index', 'icon' => 'map'],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enablement
    |--------------------------------------------------------------------------
    */

    'enablement' => [
        'name' => 'Enablement',
        'description' => 'Enablement events, calendar, field enablement, blueprints, workshops, zoom chat, and administration.',
        'icon' => 'layers',
        'group' => 'enablement',
        'enabled' => env('MODULE_ENABLEMENT', true),
        'sections' => [
            'calendar' => [
                'label' => 'Enablement',
                'icon' => 'calendar',
                'items' => [
                    ['label' => 'Calendar', 'route' => 'enablement.calendar', 'icon' => 'calendar'],
                    ['label' => 'My Nominations', 'route' => 'enablement.my-nominations', 'icon' => 'user-check'],
                ],
            ],
            'field' => [
                'label' => 'Field Enablement',
                'icon' => 'users',
                'items' => [
                    ['label' => 'Key Influencers', 'route' => 'field.key-influencers', 'icon' => 'users'],
                    ['label' => 'Breadth', 'route' => 'field.breadth', 'icon' => 'bar-chart'],
                    ['label' => 'Depth', 'route' => 'field.depth', 'icon' => 'layers'],
                    ['label' => 'Influence Map', 'route' => 'influence_map', 'icon' => 'share-2'],
                ],
            ],
            'events' => [
                'label' => 'Enablement Events',
                'icon' => 'award',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'admin.enablement-dashboard', 'icon' => 'home'],
                    ['label' => 'Events', 'route' => 'enablement-events.index', 'icon' => 'calendar'],
                    ['label' => 'Calendar', 'route' => 'enablement-calendar', 'icon' => 'calendar'],
                ],
            ],
            'blueprints' => [
                'label' => 'Blueprints',
                'icon' => 'layers',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'admin.blueprint-dashboard', 'icon' => 'home'],
                    ['label' => 'Blueprints', 'route' => 'blueprints.index', 'icon' => 'layers'],
                    ['label' => 'Blueprint Assignments', 'route' => 'blueprints.catalog', 'icon' => 'clipboard'],
                ],
            ],
            'workshops' => [
                'label' => 'Workshops',
                'icon' => 'check-square',
                'items' => [
                    ['label' => 'Labs', 'route' => 'labs.index', 'icon' => 'check-square'],
                ],
            ],
            'workshop_admin' => [
                'label' => 'Workshop Administration',
                'icon' => 'settings',
                'items' => [
                    ['label' => 'Lab Designer', 'route' => 'labs.create', 'icon' => 'edit'],
                ],
            ],
            'zoom_chat' => [
                'label' => 'Zoom Chat',
                'icon' => 'video',
                'items' => [
                    ['label' => 'Responders', 'route' => 'zoom_chat.responders.index', 'icon' => 'message-circle'],
                    ['label' => 'Messages', 'route' => 'zoom_chat.messages.index', 'icon' => 'mail'],
                ],
            ],
            'administration' => [
                'label' => 'Enablement Administration',
                'icon' => 'settings',
                'items' => [
                    ['label' => 'Workshop Guides', 'route' => 'admin.workshop-guides.index', 'icon' => 'book'],
                ],
            ],
        ],
        'extends' => [
            'partner_detail' => [
                'tabs' => [
                    ['label' => 'Enablement', 'slug' => 'enablement', 'icon' => 'award', 'partial' => 'partner.tabs.enablement', 'section' => 'calendar', 'sort' => 40],
                    ['label' => 'Calendar', 'slug' => 'calendar', 'icon' => 'calendar', 'partial' => 'partner.tabs.calendar', 'section' => 'calendar', 'sort' => 45],
                    ['label' => 'Influencers', 'slug' => 'influencers', 'icon' => 'users', 'partial' => 'partner.tabs.influencers', 'section' => 'field', 'sort' => 50],
                    ['label' => 'Blueprints', 'slug' => 'blueprints', 'icon' => 'layers', 'partial' => 'partner.tabs.blueprints', 'section' => 'blueprints', 'sort' => 60, 'badge_relation' => 'blueprintAssignments'],
                    ['label' => "Nicole's View", 'slug' => 'app-enablement', 'icon' => 'th', 'partial' => 'partner.tabs.app-enablement', 'section' => 'calendar', 'sort' => 65],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Engagement
    |--------------------------------------------------------------------------
    */

    'launch_programs' => [
        'name' => 'Launch Programs',
        'description' => 'Launch programs, milestones, action plans, task board, project health, and partner nominations.',
        'icon' => 'zap',
        'group' => 'engagement',
        'enabled' => env('MODULE_LAUNCH_PROGRAMS', true),
        'sections' => [
            'programs' => [
                'label' => 'Launch Programs',
                'icon' => 'zap',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'admin.launch-programs.dashboard', 'icon' => 'home'],
                    ['label' => 'Programs', 'route' => 'admin.launch-programs.index', 'icon' => 'list'],
                ],
            ],
            'action_plans' => [
                'label' => 'Action Plans',
                'icon' => 'list',
                'items' => [
                    ['label' => 'Action Plans', 'route' => 'plan.action-plans.index', 'icon' => 'list'],
                    ['label' => 'Task Board', 'route' => 'plan.tasks.index', 'icon' => 'trello'],
                ],
            ],
            'projects' => [
                'label' => 'Project Health',
                'icon' => 'clipboard',
                'items' => [
                    ['label' => 'Projects', 'route' => 'projects.index', 'icon' => 'folder'],
                    ['label' => 'Risks', 'route' => 'projects.risks', 'icon' => 'alert-triangle'],
                ],
            ],
            'administration' => [
                'label' => 'Launch Program Administration',
                'icon' => 'settings',
                'items' => [
                    ['label' => 'Create Program', 'route' => 'admin.launch-programs.create', 'icon' => 'plus'],
                ],
            ],
        ],
        'extends' => [
            'partner_management.management' => [
                'items' => [
                    ['label' => 'Launch Programs', 'route' => 'admin.launch-programs.dashboard', 'icon' => 'zap'],
                ],
            ],
            'partner_detail' => [
                'tabs' => [
                    ['label' => 'Launch Programs', 'slug' => 'launch-programs', 'icon' => 'rocket', 'partial' => 'partner.tabs.launch-programs', 'section' => 'programs', 'sort' => 75],
                    ['label' => 'Projects', 'slug' => 'projects', 'icon' => 'project-diagram', 'partial' => 'partner.tabs.projects', 'section' => 'projects', 'sort' => 20, 'badge_relation' => 'projects'],
                ],
            ],
        ],
    ],

    'deal_registration' => [
        'name' => 'Deal Registration',
        'description' => 'Deal registration, lead assignment, business development, and marketplace.',
        'icon' => 'file-text',
        'group' => 'engagement',
        'enabled' => env('MODULE_DEAL_REGISTRATION', true),
        'sections' => [
            'deals' => [
                'label' => 'Deal Registration',
                'icon' => 'file-text',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'engage.deal-registrations.dashboard', 'icon' => 'home'],
                    ['label' => 'Deal List', 'route' => 'engage.deal-registrations.index', 'icon' => 'list'],
                ],
            ],
            'business_development' => [
                'label' => 'Business Development',
                'icon' => 'dollar-sign',
                'items' => [
                    ['label' => 'Sourced Leads', 'route' => 'leads.sourced', 'icon' => 'trending-up'],
                    ['label' => 'Influenced Leads', 'route' => 'leads.influenced', 'icon' => 'share-2'],
                    ['label' => 'Development Funding', 'route' => 'funding.development', 'icon' => 'dollar-sign'],
                ],
            ],
            'lead_assignment' => [
                'label' => 'Lead Assignment',
                'icon' => 'user-check',
                'items' => [
                    ['label' => 'New Leads', 'route' => 'leads.unassigned', 'icon' => 'inbox'],
                    ['label' => 'Assigned Leads', 'route' => 'leads.assigned', 'icon' => 'user-check'],
                    ['label' => 'Rejected Leads', 'route' => 'leads.rejected', 'icon' => 'x-circle'],
                ],
            ],
            'marketplace' => [
                'label' => 'Marketplace',
                'icon' => 'shopping-bag',
                'items' => [
                    ['label' => 'Submissions', 'route' => 'engage.marketplace-submissions.index', 'icon' => 'shopping-bag'],
                ],
            ],
        ],
        'extends' => [
            'partner_detail' => [
                'tabs' => [
                    ['label' => 'Pipeline', 'slug' => 'pipeline', 'icon' => 'chart-line', 'partial' => 'partner.tabs.pipeline', 'section' => 'deals', 'sort' => 10, 'badge_relation' => 'opportunities'],
                    ['label' => 'Deal Registrations', 'slug' => 'deal-registrations', 'icon' => 'file-text', 'partial' => 'partner.tabs.deal-registrations', 'section' => 'deals', 'sort' => 15, 'badge_relation' => 'dealRegistrations'],
                    ['label' => 'Marketplace', 'slug' => 'marketplace', 'icon' => 'store', 'partial' => 'partner.tabs.marketplace', 'section' => 'marketplace', 'sort' => 95],
                ],
            ],
        ],
    ],

    'goals_targets' => [
        'name' => 'Goals & Targets',
        'description' => 'Goal templates, target configuration, assignment, and progress tracking.',
        'icon' => 'target',
        'group' => 'engagement',
        'enabled' => env('MODULE_GOALS_TARGETS', true),
        'sections' => [
            'goals' => [
                'label' => 'Goals & Targets',
                'icon' => 'target',
                'items' => [
                    ['label' => 'Partner Goals', 'route' => 'partner-goals.index', 'icon' => 'target'],
                    ['label' => 'Goals', 'route' => 'plan.goals.index', 'icon' => 'check-circle'],
                ],
            ],
            'administration' => [
                'label' => 'Goals Administration',
                'icon' => 'settings',
                'items' => [
                    ['label' => 'Goal Templates', 'route' => 'admin.goal-templates.index', 'icon' => 'copy'],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Measurement
    |--------------------------------------------------------------------------
    */

    'measurement' => [
        'name' => 'Measurement',
        'description' => 'Portfolio analytics, benchmarks, partner health, alerts, and QBR workbench.',
        'icon' => 'bar-chart-2',
        'group' => 'measurement',
        'enabled' => env('MODULE_MEASUREMENT', true),
        'sections' => [
            'analytics' => [
                'label' => 'Measurement',
                'icon' => 'bar-chart-2',
                'items' => [
                    ['label' => 'At a Glance', 'route' => 'measure.index', 'icon' => 'eye'],
                ],
            ],
            'benchmarks' => [
                'label' => 'Benchmarks',
                'icon' => 'award',
                'items' => [
                    ['label' => 'Benchmarks', 'route' => 'measure.benchmarks.index', 'icon' => 'award'],
                ],
            ],
            'health' => [
                'label' => 'Partner Health',
                'icon' => 'activity',
                'items' => [
                    ['label' => 'Health Dashboard', 'route' => 'act.health.index', 'icon' => 'activity'],
                    ['label' => 'Alerts', 'route' => 'act.alerts.index', 'icon' => 'bell'],
                ],
            ],
            'qbr' => [
                'label' => 'QBR Workbench',
                'icon' => 'clipboard',
                'items' => [
                    ['label' => 'QBR Sessions', 'route' => 'act.qbr.index', 'icon' => 'clipboard'],
                ],
            ],
            'risk_analyzer' => [
                'label' => 'Risk Analyzer',
                'icon' => 'alert-triangle',
                'items' => [
                    ['label' => 'Risk Analyzer', 'route' => 'measure.risk-analyzer.index', 'icon' => 'alert-triangle'],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Intelligence
    |--------------------------------------------------------------------------
    */

    'strategy' => [
        'name' => 'Strategy Workbench',
        'description' => 'Strategic planning, scenario modeling, and program governance for the CoE.',
        'icon' => 'compass',
        'group' => 'intelligence',
        'enabled' => env('MODULE_STRATEGY', true),
        'sections' => [
            'strategy' => [
                'label' => 'Strategy Workbench',
                'icon' => 'compass',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'strategy.dashboard', 'icon' => 'home'],
                    ['label' => 'Plans', 'route' => 'strategy.plans.index', 'icon' => 'file-text'],
                    ['label' => 'Scenarios', 'route' => 'strategy.scenarios.index', 'icon' => 'git-branch'],
                    ['label' => 'Benchmarks', 'route' => 'strategy.benchmarks', 'icon' => 'bar-chart-2'],
                ],
            ],
        ],
    ],

    'ai_knowledge' => [
        'name' => 'AI & Knowledge',
        'description' => 'AI-powered agents and knowledge base document management.',
        'icon' => 'cpu',
        'group' => 'intelligence',
        'enabled' => env('MODULE_AI_KNOWLEDGE', true),
        'sections' => [
            'agents' => [
                'label' => 'AI Agents',
                'icon' => 'cpu',
                'items' => [
                    ['label' => 'AI Agents', 'route' => 'admin.ai-agents.index', 'icon' => 'cpu'],
                ],
            ],
            'knowledge_base' => [
                'label' => 'Knowledge Base',
                'icon' => 'book-open',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'knowledge_base.dashboard', 'icon' => 'home'],
                    ['label' => 'Documents', 'route' => 'knowledge_base.documents.index', 'icon' => 'file'],
                    ['label' => 'Ingest', 'route' => 'knowledge_base.ingest.create', 'icon' => 'upload'],
                ],
            ],
        ],
    ],

    'idea_room' => [
        'name' => 'Idea Room',
        'description' => 'AI-powered idea management with gallery wall, chat rooms, and constellation graph.',
        'icon' => 'star',
        'group' => 'intelligence',
        'enabled' => env('MODULE_IDEA_ROOM', true),
        'sections' => [
            'room' => [
                'label' => 'Idea Room',
                'icon' => 'star',
                'items' => [
                    ['label' => 'Gallery', 'route' => 'idea_room.dashboard', 'icon' => 'image'],
                    ['label' => 'Idea Rooms', 'route' => 'idea_room.rooms', 'icon' => 'message-circle'],
                    ['label' => 'Ideas', 'route' => 'idea_room.ideas', 'icon' => 'zap'],
                    ['label' => 'Whiteboard', 'route' => 'idea_room.whiteboard', 'icon' => 'edit-3'],
                    ['label' => 'Constellation', 'route' => 'idea_room.constellation', 'icon' => 'git-branch'],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Infrastructure
    |--------------------------------------------------------------------------
    */

    'integration_hub' => [
        'name' => 'Integration Hub',
        'description' => 'Data connectors, field mapping, import/export jobs, MCP server, API tokens, and directory sync.',
        'icon' => 'refresh-cw',
        'group' => 'infrastructure',
        'enabled' => env('MODULE_INTEGRATION_HUB', true),
        'sections' => [
            'hub' => [
                'label' => 'Integration Hub',
                'icon' => 'refresh-cw',
                'items' => [
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
            'directory_sync' => [
                'label' => 'Directory Sync',
                'icon' => 'upload-cloud',
                'items' => [
                    ['label' => 'Directory Sync', 'route' => 'directory-sync.index', 'icon' => 'upload-cloud'],
                ],
            ],
            'mcp' => [
                'label' => 'MCP Server',
                'icon' => 'cpu',
                'items' => [
                    ['label' => 'MCP Overview', 'route' => 'mcp.dashboard', 'icon' => 'cpu'],
                ],
            ],
            'api_tokens' => [
                'label' => 'API Tokens',
                'icon' => 'key',
                'items' => [
                    ['label' => 'Token Management', 'route' => 'admin.api-tokens', 'icon' => 'key'],
                ],
            ],
        ],
    ],

    'portal' => [
        'name' => 'Partner Portal',
        'description' => 'Portal configuration, branding, portal user management, and partner-side experience.',
        'icon' => 'globe',
        'group' => 'infrastructure',
        'enabled' => env('MODULE_PORTAL', true),
        'sections' => [
            'management' => [
                'label' => 'Portal Management',
                'icon' => 'globe',
                'items' => [
                    ['label' => 'Branding', 'route' => 'admin.branding', 'icon' => 'edit'],
                ],
            ],
            'partner' => [
                'label' => 'Partner Portal',
                'icon' => 'globe',
                'items' => [],
            ],
        ],
        'extends' => [
            'partner_detail' => [
                'tabs' => [
                    ['label' => 'Portal Users', 'slug' => 'portal-users', 'icon' => 'user-shield', 'partial' => 'partner.tabs.portal-users', 'section' => 'management', 'sort' => 85, 'badge_relation' => 'portalContacts'],
                ],
            ],
        ],
    ],

    'system_administration' => [
        'name' => 'System Administration',
        'description' => 'Admin dashboard, configuration, system status, roles, and notification templates.',
        'icon' => 'settings',
        'group' => 'infrastructure',
        'enabled' => env('MODULE_SYSTEM_ADMINISTRATION', true),
        'sections' => [
            'admin' => [
                'label' => 'System Administration',
                'icon' => 'settings',
                'items' => [
                    ['label' => 'Admin Dashboard', 'route' => 'admin.dashboard', 'icon' => 'monitor'],
                    ['label' => 'Configuration', 'route' => 'configuration.index', 'icon' => 'settings'],
                    ['label' => 'Roles & Permissions', 'route' => 'admin.roles.index', 'icon' => 'shield'],
                    ['label' => 'System Status', 'route' => 'admin.system-status', 'icon' => 'server'],
                    ['label' => 'Notification Templates', 'route' => 'admin.notification-templates.index', 'icon' => 'bell'],
                ],
            ],
        ],
    ],

];
