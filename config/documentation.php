<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Documentation Versions
    |--------------------------------------------------------------------------
    |
    | All available documentation versions. The first version in the array
    | is used as the default when no version is specified.
    |
    */

    'versions' => ['1.0'],

    'default' => '1.0',

    /*
    |--------------------------------------------------------------------------
    | Content Path
    |--------------------------------------------------------------------------
    |
    | The base directory where documentation markdown files are stored.
    |
    */

    'path' => base_path('docs'),

    /*
    |--------------------------------------------------------------------------
    | Landing Page
    |--------------------------------------------------------------------------
    |
    | The default page slug to show when visiting /docs or /docs/{version}.
    |
    */

    'landing' => 'overview',

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (minutes)
    |--------------------------------------------------------------------------
    |
    | How long to cache rendered documentation pages. Set to 0 to disable
    | caching (useful during development).
    |
    */

    'cache_ttl' => env('DOCS_CACHE_TTL', 0),

    /*
    |--------------------------------------------------------------------------
    | Navigation Structure
    |--------------------------------------------------------------------------
    |
    | Defines the sidebar navigation. Each section maps display names to
    | markdown file slugs. Slugs correspond to: docs/{version}/{slug}.md
    |
    */

    'navigation' => [
        'Introduction' => [
            'Overview' => 'overview',
            'Getting Started' => 'getting-started',
            'Roles & Permissions' => 'roles-and-permissions',
        ],
        'Partner Management' => [
            'Partner Dashboard' => 'partner-dashboard',
            'Idea Room' => 'idea-room',
            'Business Development' => 'business-development',
            'Lead Assignment' => 'lead-assignment',
            'Deal Registration' => 'deal-registration',
        ],
        'Plan' => [
            'Partner Paths' => 'partner-paths',
            'Task Board' => 'task-board',
            'Action Plans' => 'action-plans',
            'Goals & Targets' => 'goals-and-targets',
            'Scorecard' => 'scorecard',
            'Competency Matrix' => 'competency-matrix',
        ],
        'Engage' => [
            'Project Health' => 'project-health',
            'Field Enablement' => 'field-enablement',
            'Solutions Lifecycle' => 'solutions-lifecycle',
            'QBR Workbench' => 'qbr-workbench',
            'Enablement Events' => 'enablement-events',
            'Blueprints' => 'blueprints',
        ],
        'Measure' => [
            'Portfolio Analytics' => 'portfolio-analytics',
            'Delivery Metrics' => 'delivery-metrics',
            'Revenue Analysis' => 'revenue-analysis',
            'Benchmarking' => 'benchmarking',
        ],
        'Act' => [
            'Health Dashboard' => 'health-dashboard',
            'Alerts' => 'alerts',
            'Path Reviews' => 'path-reviews',
        ],
        'Strategy & CoE' => [
            'Strategy Workbench' => 'strategy-workbench',
            'Knowledge Base' => 'knowledge-base',
            'AI Agents' => 'ai-agents',
            'Workshops & Labs' => 'workshops',
            'Launch Programs' => 'launch-programs',
        ],
        'Integration' => [
            'Integration Hub' => 'integration-hub',
        ],
        'Portal' => [
            'Portal Overview' => 'portal-overview',
            'Portal Roles' => 'portal-roles',
        ],
        'Administration' => [
            'Feature Activation' => 'feature-activation',
            'User Management' => 'user-management',
            'Team Management' => 'team-management',
            'Directory Sync' => 'directory-sync',
            'Configuration' => 'configuration',
            'Branding' => 'branding',
            'Blueprints Admin' => 'blueprints-admin',
            'Enablement Calendar' => 'enablement-calendar',
            'Provisioning' => 'provisioning',
        ],
        'Operations' => [
            'Architecture' => 'architecture',
            'Database Support' => 'database-support',
            'Deployment' => 'deployment',
            'Deployment Options' => 'deployment-options',
            'Updates & Upgrades' => 'updates-and-upgrades',
            'Licensing' => 'licensing',
        ],
        'API' => [
            'API Overview' => 'api-overview',
            'Authentication' => 'api-authentication',
            'Endpoints' => 'api-endpoints',
        ],
    ],

];
