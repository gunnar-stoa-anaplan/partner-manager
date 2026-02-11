@extends('layouts.vertical', ['pageTitle' => 'Welcome'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Welcome', 'pageSubTitle1' => 'Partner Manager'])
@endsection

@section('content')

    <h1 class="fw-500 mb-5 mt-3 text-center">
        Welcome to Partner Manager, {{ Auth::user()->name }}
        <small class="d-block mt-2 fs-6 text-muted">Track, analyze, and collaborate with strategic partners across business units and engagements.</small>
    </h1>

    <!-- Getting Started -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-0 mb-g">
                <div class="card-body p-4 position-relative">
                    <span class="badge bg-primary fw-300 position-absolute top-0 end-0 me-4 mt-4 d-none d-md-inline-block">STEP 1</span>
                    <div class="d-flex align-items-center mb-g">
                        <div class="me-3" style="font-size: 2.5rem;">
                            <i class="fal fa-rocket text-primary"></i>
                        </div>
                        <div>
                            <h2 class="fw-300 m-0">
                                <span>Getting Started</span>
                            </h2>
                            <small class="text-muted">A few simple steps to set up your environment</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <ul class="list-spaced">
                                <li><strong>Connect to Salesforce</strong> – Make sure you create and validate your Salesforce credentials. Your partners will automatically show up here.</li>
                                <li><strong>Select a Partner</strong> – Use the top-left dropdown menu to activate the partner-specific dashboard.</li>
                                <li>Navigate through the sidebar to access key metrics, business development leads, and project delivery details.</li>
                            </ul>
                        </div>
                        <div class="col-lg-6">
                            <ul class="list-spaced">
                                <li>Each partner view includes project history, performance analytics, and field engagement tools.</li>
                                <li>Use the Configuration section to manage Salesforce, Slack, and LinkedIn integrations.</li>
                                <li>Breadcrumbs and tooltips are provided throughout the app to guide you.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pro Tip -->
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info fs-lg mb-g">
                <div class="d-flex align-items-start w-100">
                    <div class="me-3 d-none d-md-block">
                        <span class="icon-stack icon-stack-lg">
                            <i class="base-14 icon-stack-3x color-primary-400"></i>
                            <i class="base-14 icon-stack-2x color-primary-600 opacity-70"></i>
                            <i class="fal fa-robot icon-stack-1x text-white opacity-90"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <span class="h5">Pro tip!</span>
                        You can interact with an <strong>automated partner analyst</strong> that evaluates partner goals, objectives, and KPIs — and suggests strategic actions based on current performance data.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- App Features Overview -->
    <div class="row">
        <div class="col-xl-6 mb-g">
            <div class="card shadow-0 h-100 rounded overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-g">
                        <div class="icon-stack me-3" style="font-size: 50px;">
                            <i class="base base-7 icon-stack-3x opacity-100 color-primary-500"></i>
                            <i class="base base-7 icon-stack-2x opacity-100 color-primary-300"></i>
                            <i class="fal fa-chart-bar icon-stack-1x opacity-100 color-white"></i>
                        </div>
                        <div>
                            <h3 class="fw-300 m-0">
                                Key Metrics
                            </h3>
                            <small class="text-muted">Revenue trends, growth, and project performance</small>
                        </div>
                    </div>
                    <p class="mb-0">
                        Analyze high-level partner performance across business units. Visualize growth trends, ACV, and project velocity using interactive charts.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-g">
            <div class="card shadow-0 h-100 rounded overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-g">
                        <div class="icon-stack me-3" style="font-size: 50px;">
                            <i class="base base-1 icon-stack-3x opacity-100 color-success-500"></i>
                            <i class="base base-1 icon-stack-2x opacity-100 color-success-300"></i>
                            <i class="fal fa-project-diagram icon-stack-1x opacity-100 color-white"></i>
                        </div>
                        <div>
                            <h3 class="fw-300 m-0">
                                Engagements
                            </h3>
                            <small class="text-muted">Projects, delivery teams, and execution status</small>
                        </div>
                    </div>
                    <p class="mb-0">
                        View all active and past projects. Track delivery teams, completion rate, and upcoming milestones. Sync contacts and roles from Salesforce or enter them manually.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-g">
            <div class="card shadow-0 h-100 rounded overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-g">
                        <div class="icon-stack me-3" style="font-size: 50px;">
                            <i class="base base-7 icon-stack-3x opacity-100 color-danger-500"></i>
                            <i class="base base-7 icon-stack-2x opacity-100 color-danger-300"></i>
                            <i class="fal fa-bullhorn icon-stack-1x opacity-100 color-white"></i>
                        </div>
                        <div>
                            <h3 class="fw-300 m-0">
                                Field Enablement
                            </h3>
                            <small class="text-muted">Events, calendars, and influencer tracking</small>
                        </div>
                    </div>
                    <p class="mb-0">
                        Log field engagements like workshops, briefings, and demos. Track breadth and depth of influence across your partner ecosystem.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-g">
            <div class="card shadow-0 h-100 rounded overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-g">
                        <div class="icon-stack me-3" style="font-size: 50px;">
                            <i class="base base-7 icon-stack-3x opacity-100 color-info-500"></i>
                            <i class="base base-7 icon-stack-2x opacity-100 color-info-300"></i>
                            <i class="fal fa-cogs icon-stack-1x opacity-100 color-white"></i>
                        </div>
                        <div>
                            <h3 class="fw-300 m-0">
                                Configuration
                            </h3>
                            <small class="text-muted">Integrations, objectives, and access</small>
                        </div>
                    </div>
                    <p class="mb-0">
                        Connect Salesforce, Slack, and LinkedIn. Assign goals and objectives per partner. Configure access and team roles.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Message -->
    <div class="row">
        <div class="col-12">
            <div class="mt-3 mb-5 text-center text-muted h5 fst-italic">
                <i class="fal fa-check text-success me-1"></i> You're viewing the Welcome page. Once a partner is selected, dashboards and analytics will be available.
            </div>
        </div>
    </div>

@endsection
