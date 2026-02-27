@extends('layouts.vertical', ['pageTitle' => 'Admin Dashboard'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Admin Dashboard', 'pageSubTitle1' => 'Administration', 'pageSubText' => 'Import history, user access & system health'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The Admin Dashboard gives you a system-wide view of import status, user access, and database & system health. Use it to quickly assess platform operations and spot issues before they become problems.</p>

    {{-- ════════════════════════════════════════════════════════════════
         Summary Card
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-300 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($totalImports) }}</h3>
                    <span class="m-0 l-h-n">Imports</span>
                </div>
                <i class="fal fa-upload position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-400 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ $allUsers->count() }}</h3>
                    <span class="m-0 l-h-n">Users</span>
                </div>
                <i class="fal fa-users position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ $dbTotalSizeMb }} <small class="fs-md">MB</small></h3>
                    <span class="m-0 l-h-n">Database Size</span>
                </div>
                <i class="fal fa-database position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-400 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ $mysqlUptimeDays }} <small class="fs-md">days</small></h3>
                    <span class="m-0 l-h-n">MySQL Uptime</span>
                </div>
                <i class="fal fa-server position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 1: Import History
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
         Section 2: User Access
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
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">All Users</div>
                    @if($recentUsers->count() > 0)
                    <span class="badge bg-success me-2">{{ $recentUsers->count() }} new (30 days)</span>
                    @endif
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#settings"></use></svg>
                        Manage Users
                    </a>
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
                                    <th class="text-center">Actions</th>
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
                                            $roleColor = match($u->role?->slug) {
                                                'admin', 'super_admin' => 'danger',
                                                'manager' => 'warning',
                                                'psm' => 'primary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $roleColor }}">{{ strtoupper($u->role?->slug ?? 'none') }}</span>
                                    </td>
                                    <td class="text-center">{{ $u->partners_count }}</td>
                                    <td class="text-nowrap">{{ $u->created_at->format('M j, Y') }}</td>
                                    <td class="text-center text-nowrap">
                                        @if($u->id !== auth()->id())
                                            <form action="{{ route('impersonate.start', $u) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-info" title="Login as {{ $u->name }}">
                                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#log-in"></use></svg>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit"></use></svg>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">No users</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 3: Database & System Health
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
        var tables = ['table-imports', 'table-users', 'table-db-tables'];
        tables.forEach(function(id) {
            var el = document.querySelector('#' + id);
            if (window.SmartTables && el && el.querySelector('tbody tr td:not([colspan])')) {
                new window.SmartTables(id, { perPage: 10, sortable: true });
            }
        });

        // ═══════════════════════════════════════════════════════════════
        // Section 1: Import History
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
        // Section 2: User Access
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
        // Section 3: Database & System Health
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
                xaxis: { categories: tbLabels, labels: { style: { colors: bodyColorHex, fontSize: '10px' } } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                legend: { show: false },
                dataLabels: { enabled: true, formatter: function(v){ return v + ' MB'; }, style: { fontSize: '10px', colors: ['#fff'] } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark', y: { formatter: function(v){ return v + ' MB'; } } }
            }).render();
        } else { emptyState('#chart-table-sizes'); }

    });
    </script>
@endsection
