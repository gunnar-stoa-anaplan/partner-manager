@extends('layouts.vertical', ['pageTitle' => 'Admin Dashboard'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Admin Dashboard', 'pageSubTitle1' => 'Administration', 'pageSubText' => 'System-wide metrics, health & utilization for ' . $currentFY])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The Admin Dashboard gives you a system-wide view of program health — partner counts, user activity, import status, enablement utilization, and pipeline coverage. Use it to quickly assess whether the platform is being used effectively and to spot operational issues before they become problems.</p>

    {{-- ════════════════════════════════════════════════════════════════
         Section 1: Summary Cards (2 rows of 4)
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($totalPartners) }}</h3>
                    <span class="m-0 l-h-n">Partners</span>
                </div>
                <i class="fal fa-briefcase position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-400 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($totalUsers) }}</h3>
                    <span class="m-0 l-h-n">Users</span>
                </div>
                <i class="fal fa-users position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-400 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        ${{ $totalPipelineValue >= 1000000 ? number_format($totalPipelineValue / 1000000, 1) . 'M' : number_format($totalPipelineValue / 1000, 0) . 'k' }}
                    </h3>
                    <span class="m-0 l-h-n">Total Pipeline</span>
                </div>
                <i class="fal fa-dollar-sign position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-400 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($totalOpportunities) }}</h3>
                    <span class="m-0 l-h-n">Opportunities</span>
                </div>
                <i class="fal fa-chart-bar position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
    </div>
    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-500 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($totalProjects) }}</h3>
                    <span class="m-0 l-h-n">Projects</span>
                </div>
                <i class="fal fa-clipboard position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-300 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($totalContacts) }}</h3>
                    <span class="m-0 l-h-n">Contacts</span>
                </div>
                <i class="fal fa-address-book position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-300 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($totalLeads) }}</h3>
                    <span class="m-0 l-h-n">Leads</span>
                </div>
                <i class="fal fa-user-plus position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-300 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($totalImports) }}</h3>
                    <span class="m-0 l-h-n">Imports</span>
                </div>
                <i class="fal fa-upload position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 2: Performance
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#trending-up"></use></svg>
                Performance
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Pipeline by Quarter</div></div>
                <div class="card-body"><div id="chart-pipeline-quarter" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Pipeline by Stage</div></div>
                <div class="card-body"><div id="chart-pipeline-stage" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Project Status</div></div>
                <div class="card-body"><div id="chart-project-status" style="height:280px"></div></div>
            </div>
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-lg-8">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Goals vs Actual ({{ $currentFY }})</div></div>
                <div class="card-body"><div id="chart-goals-actual" style="height:300px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Avg Project Completion</div></div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div id="chart-avg-completion" style="height:260px; width:100%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 3: PSM Utilization
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#users"></use></svg>
                PSM Utilization
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>

    @if($unassignedPartners > 0)
    <div class="row mb-g">
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-center mb-0">
                <i class="fal fa-exclamation-triangle me-2 fs-4"></i>
                <strong>{{ $unassignedPartners }}</strong>&nbsp;partner{{ $unassignedPartners > 1 ? 's' : '' }} without a PSM assignment
            </div>
        </div>
    </div>
    @endif

    <div class="row mb-g">
        <div class="col-lg-5">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Partners per PSM</div></div>
                <div class="card-body"><div id="chart-psm-partners" style="height:320px"></div></div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">PSM Pipeline</div></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="table-psm-pipeline" class="table table-bordered table-hover table-striped w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>PSM</th>
                                    <th class="text-end">Pipeline</th>
                                    <th class="text-center">Opps</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pipelinePerPsm as $psm)
                                <tr>
                                    <td>{{ $psm->name }}</td>
                                    <td class="text-end">${{ number_format($psm->total_pipeline, 0) }}</td>
                                    <td class="text-center">{{ $psm->opp_count }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted">No pipeline data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 4: Data Health
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#shield"></use></svg>
                Data Health
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-lg-5">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Partner Field Completeness</div></div>
                <div class="card-body"><div id="chart-field-completeness" style="height:320px"></div></div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Data Quality Issues</div></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Issue</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">Severity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dataIssues as $issue)
                                <tr>
                                    <td>{{ $issue['issue'] }}</td>
                                    <td class="text-center fw-500">{{ number_format($issue['count']) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $issue['severity'] }}">{{ ucfirst($issue['severity']) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted">No data quality issues found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 5: Import History
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#upload"></use></svg>
                Import History
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Import Status</div></div>
                <div class="card-body"><div id="chart-import-status" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Import Timeline (12 Weeks)</div></div>
                <div class="card-body"><div id="chart-import-timeline" style="height:280px"></div></div>
            </div>
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header"><div class="card-title">Recent Imports</div></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="table-imports" class="table table-bordered table-hover table-striped w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th class="text-end">Total Rows</th>
                                    <th class="text-end">Successful</th>
                                    <th class="text-end">Failed</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentImports as $import)
                                <tr>
                                    <td class="text-nowrap">{{ $import->created_at->format('M j, Y g:i A') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $import->type ?? '-')) }}</td>
                                    <td>{{ $import->user->name ?? 'System' }}</td>
                                    <td>
                                        @php
                                            $importStatusColor = match($import->status ?? '') {
                                                'completed' => 'success',
                                                'failed' => 'danger',
                                                'processing' => 'warning',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $importStatusColor }}">{{ ucfirst($import->status ?? 'unknown') }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format($import->total_rows ?? 0) }}</td>
                                    <td class="text-end text-success">{{ number_format($import->successful_rows ?? 0) }}</td>
                                    <td class="text-end text-danger">{{ number_format($import->failed_rows ?? 0) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-4 text-muted">No imports yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 6: User Access
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#user-check"></use></svg>
                User Access
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Users by Role</div></div>
                <div class="card-body"><div id="chart-users-role" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">All Users</div>
                    @if($recentUsers->count() > 0)
                    <span class="badge bg-success ms-2">{{ $recentUsers->count() }} new (30 days)</span>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="table-users" class="table table-bordered table-hover table-striped w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th class="text-center">Partners</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allUsers as $u)
                                <tr>
                                    <td class="d-flex align-items-center">
                                        <span class="d-inline-block rounded-circle bg-primary-100 text-primary text-center fw-500 me-2"
                                              style="width:32px;height:32px;line-height:32px;font-size:12px">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </span>
                                        {{ $u->name }}
                                    </td>
                                    <td>{{ $u->email }}</td>
                                    <td>
                                        @php
                                            $roleColor = match($u->role) {
                                                'admin' => 'danger',
                                                'manager' => 'warning',
                                                'psm' => 'primary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $roleColor }}">{{ strtoupper($u->role) }}</span>
                                    </td>
                                    <td class="text-center">{{ $u->partners_count }}</td>
                                    <td class="text-nowrap">{{ $u->created_at->format('M j, Y') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">No users</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 7: Enablement
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#award"></use></svg>
                Enablement
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-100 rounded text-center">
                <h3 class="display-4 fw-500 m-0 text-primary">{{ $enablementEventsByStatus->sum() }}</h3>
                <span class="text-muted">Total Events</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-100 rounded text-center">
                <h3 class="display-4 fw-500 m-0 text-info">{{ $upcomingSessions->count() }}</h3>
                <span class="text-muted">Upcoming Sessions</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-100 rounded text-center">
                <h3 class="display-4 fw-500 m-0 text-success">{{ $registrationsByStatus->sum() }}</h3>
                <span class="text-muted">Total Registrations</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-100 rounded text-center">
                <h3 class="display-4 fw-500 m-0 text-warning">{{ $blueprintAssignmentStats['total'] }}</h3>
                <span class="text-muted">Blueprint Assignments</span>
            </div>
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-lg-6">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Event Status</div></div>
                <div class="card-body"><div id="chart-event-status" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Registration Status</div></div>
                <div class="card-body"><div id="chart-reg-status" style="height:280px"></div></div>
            </div>
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-lg-6">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Blueprint Progress</div></div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <span class="badge bg-success fs-sm">{{ $blueprintAssignmentStats['completed'] }}</span>
                            <small class="d-block text-muted mt-1">Completed</small>
                        </div>
                        <div class="col-4">
                            <span class="badge bg-primary fs-sm">{{ $blueprintAssignmentStats['active'] }}</span>
                            <small class="d-block text-muted mt-1">Active</small>
                        </div>
                        <div class="col-4">
                            <span class="badge bg-warning fs-sm">{{ $blueprintAssignmentStats['paused'] }}</span>
                            <small class="d-block text-muted mt-1">Paused</small>
                        </div>
                    </div>
                    @if($totalEnrollments > 0)
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted fs-sm">Enrollment Completion</span>
                            <span class="fw-500">{{ $completedEnrollments }}/{{ $totalEnrollments }}</span>
                        </div>
                        <div class="progress" style="height:12px">
                            @php $enrollPct = round(($completedEnrollments / $totalEnrollments) * 100); @endphp
                            <div class="progress-bar bg-success" style="width:{{ $enrollPct }}%">{{ $enrollPct }}%</div>
                        </div>
                    </div>
                    @else
                    <p class="text-muted text-center mt-3 mb-0">No enrollments yet</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Upcoming Sessions (30 days)</div></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Session</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingSessions as $session)
                                <tr>
                                    <td>{{ $session->title ?? $session->event->title ?? '-' }}</td>
                                    <td>{{ $session->event->title ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $session->start_at->format('M j, Y') }}</td>
                                    <td><span class="badge bg-{{ $session->status === 'confirmed' ? 'success' : 'secondary' }}">{{ ucfirst($session->status) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">No upcoming sessions</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 8: Distribution
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#globe"></use></svg>
                Distribution
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Partners by Geography</div></div>
                <div class="card-body"><div id="chart-geo" style="height:300px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Partners by Industry</div></div>
                <div class="card-body"><div id="chart-industry" style="height:300px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Pipeline by Geography</div></div>
                <div class="card-body"><div id="chart-pipeline-geo" style="height:300px"></div></div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 9: Database & System Health
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#server"></use></svg>
                Database & System Health
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>

    {{-- MySQL + PHP Summary Cards --}}
    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-100 rounded text-center">
                <h3 class="display-4 fw-500 m-0 text-primary">{{ $dbTotalSizeMb }} <small class="fs-md">MB</small></h3>
                <span class="text-muted">Database Size</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-100 rounded text-center">
                <h3 class="display-4 fw-500 m-0 text-info">{{ number_format($dbTotalRows) }}</h3>
                <span class="text-muted">Total Rows</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-100 rounded text-center">
                <h3 class="display-4 fw-500 m-0 text-success">{{ $bufferHitRate }}%</h3>
                <span class="text-muted">Buffer Pool Hit Rate</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-100 rounded text-center">
                <h3 class="display-4 fw-500 m-0 text-warning">{{ $mysqlUptimeDays }} <small class="fs-md">days</small></h3>
                <span class="text-muted">MySQL Uptime</span>
            </div>
        </div>
    </div>

    <div class="row mb-g">
        {{-- MySQL Stats --}}
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">MySQL Server</div></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-7 mb-2 text-muted">Database</dt>
                        <dd class="col-5 mb-2 fw-500 text-end"><code>{{ $dbName }}</code></dd>

                        <dt class="col-7 mb-2 text-muted">Connections</dt>
                        <dd class="col-5 mb-2 fw-500 text-end">{{ $threadsConnected }} / {{ $maxConnections }}</dd>

                        <dt class="col-7 mb-2 text-muted">Active Threads</dt>
                        <dd class="col-5 mb-2 fw-500 text-end">{{ $threadsRunning }}</dd>

                        <dt class="col-7 mb-2 text-muted">Total Queries</dt>
                        <dd class="col-5 mb-2 fw-500 text-end">{{ number_format($totalQueries) }}</dd>

                        <dt class="col-7 mb-2 text-muted">Slow Queries</dt>
                        <dd class="col-5 mb-2 fw-500 text-end">
                            <span class="{{ $slowQueries > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($slowQueries) }}</span>
                        </dd>

                        <dt class="col-7 mb-2 text-muted">Slow Query Threshold</dt>
                        <dd class="col-5 mb-2 fw-500 text-end">{{ $longQueryTime }}s</dd>

                        <dt class="col-7 mb-2 text-muted">Buffer Pool Size</dt>
                        <dd class="col-5 mb-2 fw-500 text-end">{{ $bufferPoolSizeMb }} MB</dd>

                        <dt class="col-7 mb-2 text-muted">Total Connections</dt>
                        <dd class="col-5 mb-0 fw-500 text-end">{{ number_format($totalConnections) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- PHP/App Stats --}}
        <div class="col-lg-3">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">PHP Runtime</div></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-7 mb-2 text-muted">Version</dt>
                        <dd class="col-5 mb-2 fw-500 text-end">{{ PHP_VERSION }}</dd>

                        <dt class="col-7 mb-2 text-muted">Memory Used</dt>
                        <dd class="col-5 mb-2 fw-500 text-end">{{ $phpMemoryUsage }} MB</dd>

                        <dt class="col-7 mb-2 text-muted">Peak Memory</dt>
                        <dd class="col-5 mb-2 fw-500 text-end">{{ $phpMemoryPeak }} MB</dd>

                        <dt class="col-7 mb-2 text-muted">Memory Limit</dt>
                        <dd class="col-5 mb-0 fw-500 text-end">{{ $phpMemoryLimit }}</dd>
                    </dl>
                    <hr class="my-3">
                    <div class="text-center">
                        <a href="/pulse" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fal fa-heartbeat me-1"></i> Open Laravel Pulse
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Size Chart --}}
        <div class="col-lg-5">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Table Sizes (Top 10)</div></div>
                <div class="card-body"><div id="chart-table-sizes" style="height:320px"></div></div>
            </div>
        </div>
    </div>

    {{-- Table Stats --}}
    <div class="row mb-g">
        <div class="col-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header"><div class="card-title">All Tables — {{ $dbName }}</div></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="table-db-tables" class="table table-bordered table-hover table-striped w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th class="text-end">Rows</th>
                                    <th class="text-end">Data (MB)</th>
                                    <th class="text-end">Index (MB)</th>
                                    <th class="text-end">Total (MB)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tableStats as $tbl)
                                <tr>
                                    <td><code>{{ $tbl->table_name }}</code></td>
                                    <td class="text-end">{{ number_format($tbl->row_count) }}</td>
                                    <td class="text-end">{{ $tbl->data_mb }}</td>
                                    <td class="text-end">{{ $tbl->index_mb }}</td>
                                    <td class="text-end fw-500">{{ $tbl->size_mb }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-700">
                                    <td>Total</td>
                                    <td class="text-end">{{ number_format($dbTotalRows) }}</td>
                                    <td class="text-end">{{ round($tableStats->sum('data_mb'), 2) }}</td>
                                    <td class="text-end">{{ round($tableStats->sum('index_mb'), 2) }}</td>
                                    <td class="text-end">{{ $dbTotalSizeMb }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    @vite(['resources/scripts/pages/table-init.js'])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var cm = window.colorMap || {};
        var pri = cm.primary || {};
        var suc = cm.success || {};
        var inf = cm.info || {};
        var wrn = cm.warning || {};
        var dng = cm.danger || {};
        var bsVars = cm.bootstrapVars || {};
        var bodyColorHex = (bsVars.bodyColor || {}).hex || '#666';
        var bodyColor01 = (bsVars.bodyColor || { rgba: function(){ return 'rgba(0,0,0,0.1)'; }}).rgba(0.1);

        function fmtCurrency(val) {
            if (val >= 1000000) return '$' + (val / 1000000).toFixed(1) + 'M';
            if (val >= 1000) return '$' + Math.round(val / 1000) + 'k';
            return '$' + val;
        }

        var chartColors = [
            (pri[500] || {}).hex || '#886ab5',
            (suc[500] || {}).hex || '#1dc9b7',
            (inf[500] || {}).hex || '#33b5e5',
            (wrn[500] || {}).hex || '#ffc241',
            (dng[500] || {}).hex || '#fd3995',
            (pri[300] || {}).hex || '#B09AD5',
            (suc[300] || {}).hex || '#5EDBC9',
            (inf[300] || {}).hex || '#70CCF0',
            (wrn[300] || {}).hex || '#FFD980',
            (pri[700] || {}).hex || '#5E4A80'
        ];

        // Helper: empty-state fallback
        function emptyState(el) {
            document.querySelector(el).innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }

        // SmartTables initialization
        var tables = ['table-psm-pipeline', 'table-imports', 'table-users', 'table-db-tables'];
        tables.forEach(function(id) {
            var el = document.querySelector('#' + id);
            if (window.SmartTables && el && el.querySelector('tbody tr td:not([colspan])')) {
                new window.SmartTables(id, { perPage: 10, sortable: true });
            }
        });

        // ═══════════════════════════════════════════════════════════════
        // Section 2: Performance Charts
        // ═══════════════════════════════════════════════════════════════

        // Pipeline by Quarter (Column)
        var pqData = @json($pipelineByQuarter);
        var pqLabels = Object.keys(pqData);
        var pqValues = Object.values(pqData).map(Number);
        if (pqLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-pipeline-quarter'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Pipeline', data: pqValues }],
                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, distributed: true } },
                colors: chartColors,
                xaxis: { categories: pqLabels, labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                yaxis: { labels: { style: { colors: bodyColorHex }, formatter: fmtCurrency } },
                legend: { show: false },
                dataLabels: { enabled: true, formatter: fmtCurrency, style: { fontSize: '11px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark', y: { formatter: function(v){ return '$' + Math.round(v).toLocaleString(); } } }
            }).render();
        } else { emptyState('#chart-pipeline-quarter'); }

        // Pipeline by Stage (Horizontal Bar)
        var psData = @json($pipelineByStage);
        var psLabels = Object.keys(psData);
        var psValues = Object.values(psData).map(Number);
        if (psLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-pipeline-stage'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Pipeline', data: psValues }],
                plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3, distributed: true } },
                colors: chartColors,
                xaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' }, formatter: fmtCurrency } },
                yaxis: { categories: psLabels, labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                legend: { show: false },
                dataLabels: { enabled: true, formatter: fmtCurrency, style: { fontSize: '10px', colors: ['#fff'] } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark', y: { formatter: function(v){ return '$' + Math.round(v).toLocaleString(); } } }
            }).render();
        } else { emptyState('#chart-pipeline-stage'); }

        // Project Status (Donut)
        var projData = @json($projectStatusDist);
        var projLabels = Object.keys(projData);
        var projValues = Object.values(projData).map(Number);
        if (projLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-project-status'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: projValues,
                labels: projLabels.map(function(s){ return s.charAt(0).toUpperCase() + s.slice(1); }),
                colors: chartColors.slice(0, projLabels.length),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-project-status'); }

        // Goals vs Actual (Grouped Bar)
        var goals = @json($goalsVsActual);
        if (goals.sourced_target > 0 || goals.influenced_target > 0 || goals.sourced_actual > 0 || goals.influenced_actual > 0) {
            new ApexCharts(document.querySelector('#chart-goals-actual'), {
                chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [
                    { name: 'Target', data: [goals.sourced_target, goals.influenced_target] },
                    { name: 'Actual', data: [goals.sourced_actual, goals.influenced_actual] }
                ],
                plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
                colors: [(pri[300] || {}).hex || '#B09AD5', (suc[500] || {}).hex || '#1dc9b7'],
                xaxis: { categories: ['Sourced NNACV', 'Influenced NNACV'], labels: { style: { colors: bodyColorHex } } },
                yaxis: { labels: { style: { colors: bodyColorHex }, formatter: fmtCurrency } },
                legend: { position: 'top', labels: { colors: bodyColorHex } },
                dataLabels: { enabled: true, formatter: fmtCurrency, style: { fontSize: '11px' } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark', y: { formatter: function(v){ return '$' + Math.round(v).toLocaleString(); } } }
            }).render();
        } else { emptyState('#chart-goals-actual'); }

        // Avg Project Completion (Radial)
        var avgPct = {{ $avgProjectCompletion }};
        new ApexCharts(document.querySelector('#chart-avg-completion'), {
            chart: { type: 'radialBar', height: 260, fontFamily: 'inherit' },
            series: [avgPct],
            plotOptions: {
                radialBar: {
                    hollow: { size: '60%' },
                    dataLabels: {
                        name: { show: true, fontSize: '14px', color: bodyColorHex, offsetY: -10 },
                        value: { show: true, fontSize: '28px', fontWeight: 600, offsetY: 5, formatter: function(v){ return v + '%'; } }
                    },
                    track: { background: bodyColor01 }
                }
            },
            colors: [avgPct >= 75 ? ((suc[500] || {}).hex || '#1dc9b7') : avgPct >= 40 ? ((pri[500] || {}).hex || '#886ab5') : ((wrn[500] || {}).hex || '#ffc241')],
            labels: ['Completion']
        }).render();

        // ═══════════════════════════════════════════════════════════════
        // Section 3: PSM Utilization
        // ═══════════════════════════════════════════════════════════════

        var psmData = @json($psmUtilization->pluck('partners_count', 'name'));
        var psmLabels = Object.keys(psmData);
        var psmValues = Object.values(psmData).map(Number);
        if (psmLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-psm-partners'), {
                chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Partners', data: psmValues }],
                plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 3, distributed: true } },
                colors: chartColors,
                xaxis: { labels: { style: { colors: bodyColorHex } } },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else { emptyState('#chart-psm-partners'); }

        // ═══════════════════════════════════════════════════════════════
        // Section 4: Data Health
        // ═══════════════════════════════════════════════════════════════

        var fcData = @json($partnerFieldCompleteness);
        var fcLabels = Object.keys(fcData);
        var fcValues = Object.values(fcData).map(Number);
        if (fcLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-field-completeness'), {
                chart: { type: 'radialBar', height: 320, fontFamily: 'inherit' },
                series: fcValues,
                labels: fcLabels,
                plotOptions: {
                    radialBar: {
                        hollow: { size: '30%' },
                        dataLabels: {
                            name: { fontSize: '12px' },
                            value: { fontSize: '14px', formatter: function(v){ return v + '%'; } },
                            total: { show: true, label: 'Avg', formatter: function(w){ var s=w.globals.seriesTotals.reduce(function(a,b){return a+b},0); return Math.round(s/w.globals.series.length)+'%'; } }
                        },
                        track: { background: bodyColor01 }
                    }
                },
                colors: chartColors.slice(0, fcLabels.length),
                legend: { show: true, position: 'bottom', labels: { colors: bodyColorHex }, fontSize: '11px' }
            }).render();
        } else { emptyState('#chart-field-completeness'); }

        // ═══════════════════════════════════════════════════════════════
        // Section 5: Import History
        // ═══════════════════════════════════════════════════════════════

        // Import Status (Donut)
        var isData = @json($importStats);
        var isLabels = Object.keys(isData);
        var isValues = Object.values(isData).map(Number);
        if (isLabels.length > 0) {
            var isColorMap = { completed: (suc[500]||{}).hex||'#1dc9b7', failed: (dng[500]||{}).hex||'#fd3995', processing: (wrn[500]||{}).hex||'#ffc241' };
            new ApexCharts(document.querySelector('#chart-import-status'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: isValues,
                labels: isLabels.map(function(s){ return s.charAt(0).toUpperCase() + s.slice(1); }),
                colors: isLabels.map(function(s){ return isColorMap[s] || (pri[500]||{}).hex || '#886ab5'; }),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-import-status'); }

        // Import Timeline (Area)
        var itData = @json($importTimeline);
        if (itData.length > 0) {
            new ApexCharts(document.querySelector('#chart-import-timeline'), {
                chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [
                    { name: 'Successful Rows', data: itData.map(function(r){ return r.successful_rows || 0; }) },
                    { name: 'Failed Rows', data: itData.map(function(r){ return r.failed_rows || 0; }) }
                ],
                colors: [(suc[500]||{}).hex||'#1dc9b7', (dng[500]||{}).hex||'#fd3995'],
                xaxis: { categories: itData.map(function(r){ return r.week_start; }), labels: { style: { colors: bodyColorHex, fontSize: '10px' }, rotate: -45 } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                legend: { position: 'top', labels: { colors: bodyColorHex } },
                stroke: { curve: 'smooth', width: 2 },
                fill: { opacity: 0.15 },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' },
                dataLabels: { enabled: false }
            }).render();
        } else { emptyState('#chart-import-timeline'); }

        // ═══════════════════════════════════════════════════════════════
        // Section 6: User Access
        // ═══════════════════════════════════════════════════════════════

        var urData = @json($usersByRole);
        var urLabels = Object.keys(urData);
        var urValues = Object.values(urData).map(Number);
        var roleColorMap = { admin: (dng[500]||{}).hex||'#fd3995', manager: (wrn[500]||{}).hex||'#ffc241', psm: (pri[500]||{}).hex||'#886ab5' };
        if (urLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-users-role'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: urValues,
                labels: urLabels.map(function(s){ return s.toUpperCase(); }),
                colors: urLabels.map(function(s){ return roleColorMap[s] || (inf[500]||{}).hex || '#33b5e5'; }),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-users-role'); }

        // ═══════════════════════════════════════════════════════════════
        // Section 7: Enablement
        // ═══════════════════════════════════════════════════════════════

        // Event Status (Donut)
        var esData = @json($enablementEventsByStatus);
        var esLabels = Object.keys(esData);
        var esValues = Object.values(esData).map(Number);
        if (esLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-event-status'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: esValues,
                labels: esLabels.map(function(s){ return s.charAt(0).toUpperCase() + s.slice(1); }),
                colors: chartColors.slice(0, esLabels.length),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-event-status'); }

        // Registration Status (Donut)
        var rsData = @json($registrationsByStatus);
        var rsLabels = Object.keys(rsData);
        var rsValues = Object.values(rsData).map(Number);
        if (rsLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-reg-status'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: rsValues,
                labels: rsLabels.map(function(s){ return s.charAt(0).toUpperCase() + s.slice(1); }),
                colors: chartColors.slice(0, rsLabels.length),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-reg-status'); }

        // ═══════════════════════════════════════════════════════════════
        // Section 8: Distribution
        // ═══════════════════════════════════════════════════════════════

        // Partners by Geography (Donut)
        var geoData = @json($partnersByGeo);
        var geoLabels = Object.keys(geoData);
        var geoValues = Object.values(geoData).map(Number);
        if (geoLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-geo'), {
                chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
                series: geoValues,
                labels: geoLabels,
                colors: chartColors.slice(0, geoLabels.length),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '50%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-geo'); }

        // Partners by Industry (Bar)
        var indData = @json($partnersByIndustry);
        var indLabels = Object.keys(indData);
        var indValues = Object.values(indData).map(Number);
        if (indLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-industry'), {
                chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Partners', data: indValues }],
                plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3, distributed: true } },
                colors: chartColors,
                xaxis: { labels: { style: { colors: bodyColorHex } } },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else { emptyState('#chart-industry'); }

        // Pipeline by Geography (Bar)
        var pgData = @json($pipelineByGeo);
        var pgLabels = Object.keys(pgData);
        var pgValues = Object.values(pgData).map(Number);
        if (pgLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-pipeline-geo'), {
                chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Pipeline', data: pgValues }],
                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, distributed: true } },
                colors: chartColors,
                xaxis: { categories: pgLabels, labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                yaxis: { labels: { style: { colors: bodyColorHex }, formatter: fmtCurrency } },
                legend: { show: false },
                dataLabels: { enabled: true, formatter: fmtCurrency, style: { fontSize: '11px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark', y: { formatter: function(v){ return '$' + Math.round(v).toLocaleString(); } } }
            }).render();
        } else { emptyState('#chart-pipeline-geo'); }

        // ═══════════════════════════════════════════════════════════════
        // Section 9: Database & System Health
        // ═══════════════════════════════════════════════════════════════

        var tbData = @json($tableStats->take(10)->pluck('size_mb', 'table_name'));
        var tbLabels = Object.keys(tbData);
        var tbValues = Object.values(tbData).map(Number);
        if (tbLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-table-sizes'), {
                chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Size (MB)', data: tbValues }],
                plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 3, distributed: true } },
                colors: chartColors,
                xaxis: { labels: { style: { colors: bodyColorHex }, formatter: function(v){ return v + ' MB'; } } },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '10px' } } },
                legend: { show: false },
                dataLabels: { enabled: true, formatter: function(v){ return v + ' MB'; }, style: { fontSize: '10px', colors: ['#fff'] } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark', y: { formatter: function(v){ return v + ' MB'; } } }
            }).render();
        } else { emptyState('#chart-table-sizes'); }

    });
    </script>
@endsection
