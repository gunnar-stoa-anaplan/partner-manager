<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enablement Goal Groups & Product Lines
    |--------------------------------------------------------------------------
    |
    | Groups define the visual sections on the Enablement Goals panel.
    |
    | "aggregate" groups (Sales, Pre-Sales) have NO product lines — they show
    | a single row whose actual is the total across all programs for that role.
    |
    | "line-level" groups (Delivery-Innovation, Delivery-Apps) list individual
    | products.  Each line maps to a role + program in resource_snapshots.
    | Lines with program => null are display-only (actuals = 0 until future
    | snapshot imports include the product code).
    |
    | category key format: {group} for aggregates, {group}_{product} for lines.
    |
    */

    'groups' => [
        'sales' => [
            'label' => 'Sales',
            'role' => 'Sales',
            'aggregate' => true,
            'lines' => [],
        ],
        'pre_sales' => [
            'label' => 'Pre-Sales',
            'role' => 'Pre-Sales',
            'aggregate' => true,
            'lines' => [],
        ],
        'delivery_innovation' => [
            'label' => 'Delivery - Innovation',
            'role' => 'Delivery',
            'lines' => [
                'delinno_polaris' => ['label' => 'Polaris', 'program' => null],
                'delinno_ado' => ['label' => 'ADO', 'program' => null],
                'delinno_forecaster' => ['label' => 'Forecaster', 'program' => null],
                'delinno_comodeler' => ['label' => 'CoModeler', 'program' => null],
                'delinno_agentstudio' => ['label' => 'Agent Studio', 'program' => null],
            ],
        ],
        'delivery_apps' => [
            'label' => 'Delivery - Apps',
            'role' => 'Delivery',
            'lines' => [
                'delapp_fcr' => ['label' => 'FCR', 'program' => 'FCR'],
                'delapp_ifp' => ['label' => 'IFP', 'program' => 'IFP'],
                'delapp_id' => ['label' => 'I&D', 'program' => 'I&D'],
                'delapp_owp' => ['label' => 'OWP', 'program' => 'OWP'],
                'delapp_spm' => ['label' => 'SPM', 'program' => 'SPM'],
                'delapp_ibp' => ['label' => 'IBP', 'program' => 'IBP'],
                'delapp_mfp' => ['label' => 'MFP', 'program' => 'MFP'],
            ],
        ],
    ],

];
