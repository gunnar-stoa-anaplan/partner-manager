@extends('layouts.vertical', ['pageTitle' => 'System Status'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'System Status', 'pageSubTitle1' => 'System Administration', 'pageSubText' => 'Deployment, service health, and agent connectivity'])
@endsection

@section('content')

    {{-- ════════════════════════════════════════════════════════════════
         Row 1: Deployment Overview (4 summary cards)
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">v{{ $currentVersion }}</h3>
                    <span class="m-0 l-h-n">App Version</span>
                </div>
                <i class="fal fa-code-branch position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-{{ $runtime['environment'] === 'production' ? 'success' : 'warning' }}-400 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500 text-capitalize">{{ $runtime['environment'] }}</h3>
                    <span class="m-0 l-h-n">Environment</span>
                </div>
                <i class="fal fa-server position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-300 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ $agent['instance_id'] ?? env('INSTANCE_ID', '—') }}</h3>
                    <span class="m-0 l-h-n">Instance ID</span>
                </div>
                <i class="fal fa-fingerprint position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            @if($agent && $agent['is_connected'])
                <div class="p-3 bg-success-300 rounded overflow-hidden position-relative text-white">
                    <div>
                        <h3 class="display-4 d-block l-h-n m-0 fw-500">Connected</h3>
                        <span class="m-0 l-h-n">Agent Status</span>
                    </div>
                    <i class="fal fa-wifi position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
                </div>
            @elseif($agent)
                <div class="p-3 bg-warning-400 rounded overflow-hidden position-relative text-white">
                    <div>
                        <h3 class="display-4 d-block l-h-n m-0 fw-500">Stale</h3>
                        <span class="m-0 l-h-n">Agent Status</span>
                    </div>
                    <i class="fal fa-wifi position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
                </div>
            @else
                <div class="p-3 bg-secondary rounded overflow-hidden position-relative text-white">
                    <div>
                        <h3 class="display-4 d-block l-h-n m-0 fw-500">Not Connected</h3>
                        <span class="m-0 l-h-n">Agent Status</span>
                    </div>
                    <i class="fal fa-wifi-slash position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
                </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Row 2: Service Health
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-g">
        {{-- Database --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="width-4 height-4 rounded-circle bg-{{ $database['status'] === 'healthy' ? 'success' : 'danger' }} bg-opacity-10 d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                        <i class="fal fa-database fs-xl text-{{ $database['status'] === 'healthy' ? 'success' : 'danger' }}"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Database</h6>
                        <small class="text-muted">{{ $database['driver'] }} — {{ $database['host'] }}</small>
                        @if($database['status'] === 'healthy')
                            <div class="text-success fs-nano fw-500 mt-1">
                                <i class="fal fa-check-circle me-1"></i>Healthy ({{ $database['response_ms'] }}ms)
                            </div>
                        @else
                            <div class="text-danger fs-nano fw-500 mt-1">
                                <i class="fal fa-times-circle me-1"></i>Unreachable
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Cache --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="width-4 height-4 rounded-circle bg-{{ $cache['status'] === 'healthy' ? 'success' : 'warning' }} bg-opacity-10 d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                        <i class="fal fa-bolt fs-xl text-{{ $cache['status'] === 'healthy' ? 'success' : 'warning' }}"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Cache</h6>
                        <small class="text-muted">{{ $cache['driver'] }}</small>
                        <div class="text-{{ $cache['status'] === 'healthy' ? 'success' : 'warning' }} fs-nano fw-500 mt-1">
                            <i class="fal fa-check-circle me-1"></i>{{ ucfirst($cache['status']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Queue --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="width-4 height-4 rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                        <i class="fal fa-layer-group fs-xl text-info"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Queue</h6>
                        <small class="text-muted">{{ $queue['driver'] }}</small>
                        <div class="text-info fs-nano fw-500 mt-1">
                            <i class="fal fa-check-circle me-1"></i>{{ ucfirst($queue['status']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mail --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="width-4 height-4 rounded-circle bg-{{ $mail['status'] === 'configured' ? 'success' : 'secondary' }} bg-opacity-10 d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                        <i class="fal fa-envelope fs-xl text-{{ $mail['status'] === 'configured' ? 'success' : 'secondary' }}"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Mail</h6>
                        <small class="text-muted">{{ $mail['mailer'] }}{{ $mail['status'] === 'configured' ? ' — ' . $mail['host'] : '' }}</small>
                        <div class="text-{{ $mail['status'] === 'configured' ? 'success' : 'secondary' }} fs-nano fw-500 mt-1">
                            <i class="fal fa-{{ $mail['status'] === 'configured' ? 'check-circle' : 'info-circle' }} me-1"></i>{{ ucfirst($mail['status']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Row 3: Containers + Updates
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-g">
        {{-- Container Status --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center">
                    <h4 class="card-title mb-0 flex-grow-1">Container Status</h4>
                    @if($agent)
                        <span class="badge bg-{{ $agent['is_connected'] ? 'success' : 'warning' }}">
                            Agent v{{ $agent['agent_version'] ?? '?' }}
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    @if($agent && !empty($agent['containers']))
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Container</th>
                                        <th>Image</th>
                                        <th>State</th>
                                        <th>Uptime</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($agent['containers'] as $container)
                                        <tr>
                                            <td class="fw-500">{{ $container['name'] }}</td>
                                            <td><code class="fs-nano">{{ $container['image'] }}</code></td>
                                            <td>
                                                @if($container['state'] === 'running')
                                                    <span class="badge bg-success-50 text-success">{{ $container['state'] }}</span>
                                                @else
                                                    <span class="badge bg-danger-50 text-danger">{{ $container['state'] }}</span>
                                                @endif
                                            </td>
                                            <td class="text-muted">{{ $container['status'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(isset($agent['last_heartbeat']))
                            <p class="text-muted fs-nano mt-3 mb-0">
                                <i class="fal fa-clock me-1"></i>
                                Last heartbeat: {{ \Carbon\Carbon::parse($agent['last_heartbeat'])->diffForHumans() }}
                            </p>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fal fa-wifi-slash fa-3x text-muted mb-3 d-block"></i>
                            <h6 class="text-muted">Agent Not Connected</h6>
                            <p class="text-muted fs-sm mb-0">
                                Container status is reported by the PM Agent. The agent is either not installed,
                                not running, or has not yet sent its first heartbeat.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Updates & Upgrades --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0">Updates & Upgrades</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="width-4 height-4 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                            <i class="fal fa-code-branch fs-xl text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Current Version</h6>
                            <span class="text-muted">v{{ $currentVersion }}</span>
                        </div>
                    </div>

                    @if($availableUpdate)
                        <hr>
                        <div class="d-flex align-items-start mb-3">
                            <div class="width-4 height-4 rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-3 flex-shrink-0 mt-1">
                                <i class="fal fa-download fs-xl text-info"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">
                                    v{{ $availableUpdate['version'] }} Available
                                    <span class="badge bg-{{ $availableUpdate['type'] === 'upgrade' ? 'warning' : ($availableUpdate['type'] === 'patch' ? 'success' : 'info') }} ms-1">
                                        {{ ucfirst($availableUpdate['type'] ?? 'update') }}
                                    </span>
                                </h6>
                                <p class="text-muted fs-sm mb-2">{{ $availableUpdate['summary'] ?? '' }}</p>

                                @if($isUpdateAccepted)
                                    <span class="badge bg-success"><i class="fal fa-check me-1"></i>Accepted — Deploying</span>
                                @else
                                    <a href="{{ route('admin.update') }}" class="btn btn-sm btn-info">
                                        <i class="fal fa-arrow-right me-1"></i>Review Update
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        <hr>
                        <div class="d-flex align-items-center">
                            <div class="width-4 height-4 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                                <i class="fal fa-check-circle fs-xl text-success"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-success">Up to Date</h6>
                                <span class="text-muted fs-sm">You are running the latest version.</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Row 4: Runtime & Database Metrics
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-g">
        {{-- Runtime --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0">Runtime</h4>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <tbody>
                            <tr>
                                <td class="ps-3 fw-500">PHP Version</td>
                                <td class="text-end pe-3">{{ $runtime['php_version'] }}</td>
                            </tr>
                            <tr>
                                <td class="ps-3 fw-500">Framework Version</td>
                                <td class="text-end pe-3">{{ $runtime['laravel_version'] }}</td>
                            </tr>
                            <tr>
                                <td class="ps-3 fw-500">Environment</td>
                                <td class="text-end pe-3"><span class="text-capitalize">{{ $runtime['environment'] }}</span></td>
                            </tr>
                            <tr>
                                <td class="ps-3 fw-500">Debug Mode</td>
                                <td class="text-end pe-3">
                                    @if($runtime['debug'])
                                        <span class="badge bg-warning">Enabled</span>
                                    @else
                                        <span class="badge bg-success">Disabled</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-3 fw-500">Memory Usage</td>
                                <td class="text-end pe-3">{{ $runtime['memory_usage_mb'] }} MB / {{ $runtime['memory_limit'] }}</td>
                            </tr>
                            <tr>
                                <td class="ps-3 fw-500">Peak Memory</td>
                                <td class="text-end pe-3">{{ $runtime['memory_peak_mb'] }} MB</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Database Metrics --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0">Database Metrics</h4>
                </div>
                <div class="card-body p-0">
                    @if($dbMetrics)
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                <tr>
                                    <td class="ps-3 fw-500">Server Uptime</td>
                                    <td class="text-end pe-3">{{ $dbMetrics['uptime_days'] }} days</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-500">Active Connections</td>
                                    <td class="text-end pe-3">{{ $dbMetrics['threads_connected'] }} / {{ $dbMetrics['max_connections'] }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-500">Total Queries</td>
                                    <td class="text-end pe-3">{{ number_format($dbMetrics['total_queries']) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-500">Slow Queries</td>
                                    <td class="text-end pe-3">
                                        @if($dbMetrics['slow_queries'] > 0)
                                            <span class="text-warning">{{ number_format($dbMetrics['slow_queries']) }}</span>
                                        @else
                                            <span class="text-success">0</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Database metrics unavailable.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
