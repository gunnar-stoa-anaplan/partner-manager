@extends('layouts.vertical', ['pageTitle' => 'Project Management Dashboard'])

@section('page-title')
    <div class="d-flex align-items-end mb-4">
        <div>
            @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Project Management', 'pageSubTitle1' => 'Insights', 'pageSubTitle2'=> 'Dashboards'])
        </div>
        <div class="ms-auto d-none d-sm-flex align-items-center">
            <div class="d-flex align-items-center">
                <div class="d-inline-flex flex-column justify-content-center me-2">
                                    <span class="fw-500 fs-xs d-block">
                                        <small>COMPLETION RATE</small>
                                    </span>
                    <span class="fw-500 fs-xl d-flex align-items-center text-success"> 80% <svg
                            class="sa-icon sa-bold sa-icon-success ms-1">
                                            <use href="/img/sprite.svg#trending-up"></use>
                                        </svg>
                                    </span>
                </div>
            </div>
            <div
                class="d-flex align-items-center border-faded border-dashed border-top-0 border-bottom-0 border-end-0 ms-3 ps-3">
                <div class="d-inline-flex flex-column justify-content-center me-2">
                                    <span class="fw-500 fs-xs d-block">
                                        <small>TASKS DUE TODAY</small>
                                    </span>
                    <span class="fw-500 fs-xl d-flex align-items-center text-danger"> 12 <svg
                            class="sa-icon sa-bold ms-1 sa-icon-danger">
                                            <use href="/img/sprite.svg#alert-circle"></use>
                                        </svg>
                                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <!-- Summary cards -->
    <div class="row sortable-off">
        <div class="col-md-6 col-lg-3 col-xl-3">
            <div class="card mb-g">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mt-0">Active Projects</h6>
                    <h2>24</h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success">+3 </span>
                        <span class="text-muted">From last month</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 col-xl-3">
            <div class="card mb-g">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mt-0">Overdue Tasks</h6>
                    <h2>17</h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger">+5 </span>
                        <span class="text-muted">Since yesterday</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 col-xl-3">
            <div class="card mb-g">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mt-0">Team Members</h6>
                    <h2>36</h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary">+2 </span>
                        <span class="text-muted">New hires</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 col-xl-3">
            <div class="card mb-g">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mt-0">Open Issues</h6>
                    <h2>42</h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning">-7 </span>
                        <span class="text-muted">Resolved today</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Development Phases Timeline -->
        <div class="col-xl-12">
            <div class="panel panel-icon" id="panel-9">
                <div class="panel-hdr">
                    <h2>Development <span class="fw-light"><i>Phases</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="chart-controls development-phases-filters d-flex justify-content-center gap-1">
                            <button class="btn btn-xs btn-outline-default" data-filter="all" type="button">All
                                Teams
                            </button>
                            <button class="btn btn-xs btn-outline-default" data-filter="Frontend Team"
                                    type="button">Frontend
                            </button>
                            <button class="btn btn-xs btn-outline-default" data-filter="Backend Team"
                                    type="button">Backend
                            </button>
                            <button class="btn btn-xs btn-outline-default" data-filter="QA Team" type="button">QA
                            </button>
                            <button class="btn btn-xs btn-outline-default" data-filter="DevOps Team"
                                    type="button">DevOps
                            </button>
                        </div>
                        <div id="development-phases-chart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Project Progress and Task Status -->
        <div class="col-xl-8">
            <div class="panel panel-icon panel-locked" id="panel-1">
                <div class="panel-hdr">
                    <h2>Project <span class="fw-light"><i>Progress</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="project-progress-chart"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>Task <span class="fw-light"><i>Status</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="task-status-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Team Performance and Weekly Activity -->
    <div class="row">
        <div class="col-xl-4">
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>Team <span class="fw-light"><i>Performance</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="team-performance-chart"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>Weekly <span class="fw-light"><i>Activity</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="weekly-activity-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Team Workload Heatmap -->
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>Team <span class="fw-light"><i>Workload</i></span></h2>
                    <div class="panel-toolbar">
                        <div class="btn-group team-workload-filters">
                            <button class="btn btn-xs btn-outline-default active" data-filter="week"
                                    type="button">Weekly
                            </button>
                            <button class="btn btn-xs btn-outline-default" data-filter="month"
                                    type="button">Monthly
                            </button>
                        </div>
                    </div>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="team-workload-heatmap"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Project Timeline Chart -->
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-8">
                <div class="panel-hdr">
                    <h2>Project <span class="fw-light"><i>Timeline</i></span></h2>
                    <div class="panel-toolbar">
                        <div class="btn-group project-timeline-filters">
                            <button class="btn btn-xs btn-outline-default active" data-timeline-view="tasks"
                                    type="button">Tasks
                            </button>
                            <button class="btn btn-xs btn-outline-default" data-timeline-view="phases"
                                    type="button">Project Phases
                            </button>
                        </div>
                    </div>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="project-timeline-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/projectmanagementdashboard.js'])
@endsection
