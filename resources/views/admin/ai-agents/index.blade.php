@extends('layouts.vertical', ['pageTitle' => 'AI Agents'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'AI Agents', 'pageSubTitle1' => 'System Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubText' => 'Manage AI-powered agents for partner insights and content generation'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">These agents use the configured AI provider to generate insights and content across the platform. Each agent is purpose-built for a specific use case and produces structured output that integrates directly into its target module.</p>

    <div class="row">
        {{-- Partner Advisor --}}
        <div class="col-xl-4 col-lg-6 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-header bg-primary bg-opacity-10">
                    <div class="d-flex align-items-center">
                        <svg class="sa-icon sa-icon-sm me-2"><use href="/icons/sprite.svg#cpu"></use></svg>
                        <span class="fw-medium">Partner Advisor</span>
                        <span class="badge bg-success ms-auto">Active</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-sm mb-3">Analyzes partner metrics, scorecards, health signals, and activity data to produce a health assessment with actionable recommendations for the partner success manager.</p>

                    <div class="mb-3">
                        <span class="badge bg-secondary me-1">Provider: {{ ucfirst(config('ai.default_provider', 'openai')) }}</span>
                        <span class="badge bg-secondary">Max Tokens: 1024</span>
                    </div>

                    <h6 class="fs-sm fw-medium mb-2">Output Schema</h6>
                    <ul class="list-unstyled fs-sm text-muted mb-3">
                        <li><code>health_score</code> — Overall health 1-100</li>
                        <li><code>risk_level</code> — Risk assessment level</li>
                        <li><code>summary</code> — Brief narrative assessment</li>
                        <li><code>top_recommendation</code> — Highest priority action</li>
                        <li><code>second_recommendation</code> — Second priority action</li>
                        <li><code>third_recommendation</code> — Third priority action</li>
                        <li><code>key_strength</code> — Partner's primary strength</li>
                        <li><code>key_risk</code> — Partner's primary risk</li>
                    </ul>

                    <h6 class="fs-sm fw-medium mb-2">Surfaced In</h6>
                    <a href="#" class="btn btn-sm btn-outline-primary disabled">Partner Overview Tab</a>
                </div>
            </div>
        </div>

        {{-- Enablement Designer --}}
        <div class="col-xl-4 col-lg-6 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-header bg-primary bg-opacity-10">
                    <div class="d-flex align-items-center">
                        <svg class="sa-icon sa-icon-sm me-2"><use href="/icons/sprite.svg#cpu"></use></svg>
                        <span class="fw-medium">Enablement Designer</span>
                        <span class="badge bg-success ms-auto">Active</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-sm mb-3">Designs complete enablement workshops with detailed agendas, learning objectives, lab exercises, and instructor notes. Uses existing catalog data to avoid duplication.</p>

                    <div class="mb-3">
                        <span class="badge bg-secondary me-1">Provider: {{ ucfirst(config('ai.default_provider', 'openai')) }}</span>
                        <span class="badge bg-secondary me-1">Max Tokens: 4096</span>
                        <span class="badge bg-secondary">Tools: 3</span>
                    </div>

                    <h6 class="fs-sm fw-medium mb-2">Output Schema</h6>
                    <ul class="list-unstyled fs-sm text-muted mb-3">
                        <li><code>title</code>, <code>type</code>, <code>category</code>, <code>product_area</code></li>
                        <li><code>target_audience</code>, <code>duration_hours</code>, <code>max_participants</code></li>
                        <li><code>description</code>, <code>objectives</code>, <code>prerequisites</code></li>
                        <li><code>agenda</code>, <code>lab_design</code>, <code>promotion_copy</code></li>
                        <li><code>instructor_notes</code></li>
                    </ul>

                    <h6 class="fs-sm fw-medium mb-2">Surfaced In</h6>
                    <a href="{{ route('enablement-events.create') }}" class="btn btn-sm btn-outline-primary">Create Event Page</a>
                </div>
            </div>
        </div>

        {{-- Lab Designer --}}
        <div class="col-xl-4 col-lg-6 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-header bg-primary bg-opacity-10">
                    <div class="d-flex align-items-center">
                        <svg class="sa-icon sa-icon-sm me-2"><use href="/icons/sprite.svg#cpu"></use></svg>
                        <span class="fw-medium">Lab Designer</span>
                        <span class="badge bg-success ms-auto">Active</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-sm mb-3">Designs hands-on Anaplan workshop labs with progressive checkpoints, validation criteria, and scoring. References existing labs to ensure variety.</p>

                    <div class="mb-3">
                        <span class="badge bg-secondary me-1">Provider: {{ ucfirst(config('ai.default_provider', 'openai')) }}</span>
                        <span class="badge bg-secondary me-1">Max Tokens: 4096</span>
                        <span class="badge bg-secondary">Tools: 3</span>
                    </div>

                    <h6 class="fs-sm fw-medium mb-2">Output Schema</h6>
                    <ul class="list-unstyled fs-sm text-muted mb-3">
                        <li><code>name</code>, <code>description</code>, <code>instructions</code></li>
                        <li><code>tenant</code>, <code>duration_minutes</code></li>
                        <li><code>checkpoint_[1-6]_title</code>, <code>_description</code></li>
                        <li><code>checkpoint_[1-6]_type</code>, <code>_points</code>, <code>_hint</code></li>
                        <li><code>checkpoint_[1-6]_required</code></li>
                    </ul>

                    <h6 class="fs-sm fw-medium mb-2">Surfaced In</h6>
                    <a href="{{ route('labs.create') }}" class="btn btn-sm btn-outline-primary">Create Lab Page</a>
                </div>
            </div>
        </div>
    </div>

    <h5 class="mt-4 mb-1">Product-Line Experts</h5>
    <p class="text-muted fst-italic mb-4">Specialized domain agents consulted automatically by the Enablement Designer and Lab Designer via the <code>ConsultProductExpert</code> tool. Each expert provides deep product-specific guidance — Anaplan formula patterns, realistic business scenarios, and implementation best practices — that the orchestrator agents use to produce technically accurate content.</p>

    <div class="row">
        @php
            $experts = [
                ['name' => 'Finance Expert', 'area' => 'IFP', 'color' => '#2196F3', 'desc' => 'Budgeting, forecasting, consolidation, financial close, revenue planning, P&L, balance sheet, cash flow, FP&A, variance analysis.'],
                ['name' => 'Supply Chain Expert', 'area' => 'SCM', 'color' => '#4CAF50', 'desc' => 'Demand planning, supply planning, S&OP, inventory optimization, production scheduling, procurement.'],
                ['name' => 'Workforce Expert', 'area' => 'OWP', 'color' => '#FF9800', 'desc' => 'Headcount planning, skills mapping, capacity modeling, workforce allocation, attrition, compensation planning.'],
                ['name' => 'Sales Expert', 'area' => 'SPM', 'color' => '#9C27B0', 'desc' => 'Territory planning, quota management, incentive compensation, commissions, pipeline forecasting, sales capacity.'],
                ['name' => 'Polaris Expert', 'area' => 'Polaris', 'color' => '#00BCD4', 'desc' => 'Polaris UX, new model experience, Polaris formula engine, migration from classic, board design, guided planning.'],
                ['name' => 'ADO Expert', 'area' => 'ADO', 'color' => '#795548', 'desc' => 'Application design orchestration, page builder, custom views, UX design, app publishing, action workflows.'],
            ];
        @endphp

        @foreach ($experts as $expert)
        <div class="col-xl-4 col-lg-6 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-header" style="background: {{ $expert['color'] }}15;">
                    <div class="d-flex align-items-center">
                        <svg class="sa-icon sa-icon-sm me-2"><use href="/icons/sprite.svg#cpu"></use></svg>
                        <span class="fw-medium">{{ $expert['name'] }}</span>
                        <span class="badge ms-auto" style="background: {{ $expert['color'] }};">{{ $expert['area'] }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-sm mb-3">{{ $expert['desc'] }}</p>

                    <div class="mb-3">
                        <span class="badge bg-secondary me-1">Max Tokens: 2048</span>
                        <span class="badge bg-secondary me-1">Max Steps: 3</span>
                        <span class="badge bg-secondary">Temp: 0.3</span>
                    </div>

                    <h6 class="fs-sm fw-medium mb-2">Invoked By</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary me-1">Enablement Designer</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary">Lab Designer</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endsection
