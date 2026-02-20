@extends('layouts.vertical', ['pageTitle' => 'Provisioning Summary'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Provisioning Summary', 'pageSubTitle1' => 'Provisioning', 'pageSubTitle1Url' => route('admin.provisioning-summary')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Monitor Anaplan application version adoption across partners. Set the current version for each product area, view version distribution, and identify partners running outdated versions.</p>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Global KPI Cards --}}
<div class="row mb-3">
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center">
                <h3 class="mb-1">{{ $totalPartners }}</h3>
                <small class="text-muted">Partners Synced</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center">
                <h3 class="mb-1 text-info">{{ $totalModels }}</h3>
                <small class="text-muted">Active Models</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center">
                @php
                    $memGb = $totalMemory / (1024 * 1024 * 1024);
                    $memDisplay = $memGb >= 1 ? number_format($memGb, 2) . ' TB' : number_format($totalMemory / (1024 * 1024), 0) . ' MB';
                    if ($memGb >= 1024) {
                        $memDisplay = number_format($memGb / 1024, 2) . ' TB';
                    } elseif ($memGb >= 1) {
                        $memDisplay = number_format($memGb, 1) . ' GB';
                    }
                @endphp
                <h3 class="mb-1">{{ $totalMemory > 0 ? $memDisplay : 'N/A' }}</h3>
                <small class="text-muted">Total Memory</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center">
                <h3 class="mb-1">{{ $lastSync ? $lastSync->diffForHumans() : 'Never' }}</h3>
                <small class="text-muted">Last Sync</small>
            </div>
        </div>
    </div>
</div>

{{-- Application Versions --}}
<h5 class="fw-bold mb-3">
    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#pie-chart"></use></svg>
    Application Versions
</h5>
<div class="row mb-3">
    @foreach($productAreas as $area)
    @php $stats = $productAreaStats[$area]; @endphp
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-header py-2">
                <div class="card-title fs-sm">{{ $area }}</div>
            </div>
            <div class="card-body text-center px-2 py-3">
                <div id="version-chart-{{ Str::slug($area) }}" style="min-height:180px"></div>

                <form action="{{ route('admin.provisioning-summary.update-version') }}" method="POST" class="d-flex align-items-center gap-2 mt-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="product_area" value="{{ $area }}">
                    <select name="current_version" class="form-select form-select-sm">
                        @foreach(\App\Models\AnaplanModel::VERSIONS as $version)
                            @if(!in_array($version, ['Other', 'Demo', 'Base']))
                            <option value="{{ $version }}" {{ (isset($versionConfigs[$area]) && $versionConfigs[$area]->current_version === $version) ? 'selected' : '' }}>
                                {{ $version }}
                            </option>
                            @endif
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">Save</button>
                </form>
                @if(isset($versionConfigs[$area]))
                <small class="text-muted d-block mt-1">Updated {{ $versionConfigs[$area]->updated_at->diffForHumans() }}
                    @if($versionConfigs[$area]->updatedByUser) by {{ $versionConfigs[$area]->updatedByUser->name }} @endif
                </small>
                @else
                <small class="text-muted d-block mt-1">Not configured</small>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Product Area Tabs --}}
<ul class="nav nav-tabs" role="tablist">
    @foreach($productAreas as $i => $area)
    <li class="nav-item">
        <a class="nav-link {{ $i === 0 ? 'active' : '' }}" data-bs-toggle="tab" href="#area-{{ Str::slug($area) }}" role="tab">
            {{ $area }}
            @if(($productAreaStats[$area]['outdated_count'] ?? 0) > 0)
            <span class="badge bg-danger ms-1">{{ $productAreaStats[$area]['outdated_count'] }} outdated</span>
            @endif
        </a>
    </li>
    @endforeach
</ul>

<div class="tab-content">
    @foreach($productAreas as $i => $area)
    @php $stats = $productAreaStats[$area]; @endphp
    <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="area-{{ Str::slug($area) }}" role="tabpanel">

        {{-- Area Summary Cards --}}
        <div class="row mt-3 mb-3">
            <div class="col-sm-6 col-xl-3 mb-g">
                <div class="card shadow-0 h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="mb-0">{{ $stats['total_partners'] }}</h4>
                        <small class="text-muted">Partners</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3 mb-g">
                <div class="card shadow-0 h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="mb-0 text-info">{{ $stats['total_models'] }}</h4>
                        <small class="text-muted">Active Models</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3 mb-g">
                <div class="card shadow-0 h-100">
                    <div class="card-body text-center py-3">
                        @php
                            $aMemGb = $stats['total_memory'] / (1024 * 1024 * 1024);
                            if ($aMemGb >= 1024) {
                                $aMemDisplay = number_format($aMemGb / 1024, 2) . ' TB';
                            } elseif ($aMemGb >= 1) {
                                $aMemDisplay = number_format($aMemGb, 1) . ' GB';
                            } else {
                                $aMemDisplay = number_format($stats['total_memory'] / (1024 * 1024), 0) . ' MB';
                            }
                        @endphp
                        <h4 class="mb-0">{{ $stats['total_memory'] > 0 ? $aMemDisplay : 'N/A' }}</h4>
                        <small class="text-muted">Total Memory</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3 mb-g">
                <div class="card shadow-0 h-100">
                    <div class="card-body text-center py-3">
                        <h4 class="mb-0 {{ $stats['outdated_count'] > 0 ? 'text-danger' : 'text-success' }}">{{ $stats['outdated_count'] }}</h4>
                        <small class="text-muted">Partners Needing Upgrade</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Version Distribution --}}
        @if($stats['version_distribution']->isNotEmpty())
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Version Distribution</div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($stats['version_distribution'] as $version => $count)
                    @php
                        $vRank = \App\Models\AnaplanModel::VERSION_RANKS[$version] ?? -1;
                        $tRank = isset($versionConfigs[$area]) ? (\App\Models\AnaplanModel::VERSION_RANKS[$versionConfigs[$area]->current_version] ?? -1) : -1;
                        $diff = $tRank - $vRank;
                        $badgeClass = $diff <= 0 ? 'bg-success' : ($diff === 1 ? 'bg-warning text-dark' : 'bg-danger');
                    @endphp
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge {{ $badgeClass }}" style="font-size: 0.85em;">{{ $version }}</span>
                        <span class="fw-bold">{{ $count }}</span>
                        <small class="text-muted">{{ Str::plural('partner', $count) }}</small>
                    </div>
                    @endforeach
                </div>

                {{-- Simple bar chart --}}
                @php $maxCount = $stats['version_distribution']->max() ?: 1; @endphp
                @foreach($stats['version_distribution'] as $version => $count)
                @php
                    $vRank = \App\Models\AnaplanModel::VERSION_RANKS[$version] ?? -1;
                    $tRank = isset($versionConfigs[$area]) ? (\App\Models\AnaplanModel::VERSION_RANKS[$versionConfigs[$area]->current_version] ?? -1) : -1;
                    $diff = $tRank - $vRank;
                    $barColor = $diff <= 0 ? '#28a745' : ($diff === 1 ? '#ffc107' : '#dc3545');
                @endphp
                <div class="d-flex align-items-center mb-2">
                    <div style="width: 60px;" class="fw-bold small">{{ $version }}</div>
                    <div class="flex-grow-1 me-2">
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" style="width: {{ ($count / $maxCount) * 100 }}%; background-color: {{ $barColor }};">
                                {{ $count }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Partner Table --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Partners — {{ $area }}</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover m-0" id="partners-{{ Str::slug($area) }}">
                        <thead>
                            <tr>
                                <th>Partner</th>
                                <th>PSM</th>
                                <th class="text-center">Latest Version</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Models</th>
                                <th class="text-center">Memory</th>
                                <th>Last Synced</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['partners'] as $data)
                            @php $partner = $data['partner']; @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('partners.show', $partner) }}">
                                        <strong>{{ $partner->name }}</strong>
                                    </a>
                                </td>
                                <td>
                                    @if($partner->user)
                                    <small>{{ $partner->user->name }}</small>
                                    @else
                                    <small class="text-muted">Unassigned</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $vRank = \App\Models\AnaplanModel::VERSION_RANKS[$data['latest_version']] ?? -1;
                                        $tRank = isset($versionConfigs[$area]) ? (\App\Models\AnaplanModel::VERSION_RANKS[$versionConfigs[$area]->current_version] ?? -1) : -1;
                                        $diff = $tRank - $vRank;
                                        $badgeColor = $diff <= 0 ? '#28a745' : ($diff === 1 ? '#ffc107' : '#dc3545');
                                    @endphp
                                    <span class="badge" style="background-color: {{ $badgeColor }}; {{ $diff === 1 ? 'color: #212529;' : '' }}">
                                        {{ $data['latest_version'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($data['is_outdated'])
                                    <span class="badge bg-danger">Outdated</span>
                                    @else
                                    <span class="badge bg-success">Current</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $data['model_count'] }}</td>
                                <td class="text-center">
                                    @php
                                        $pMemGb = $data['total_memory'] / (1024 * 1024 * 1024);
                                        $pMemDisplay = $pMemGb >= 1 ? number_format($pMemGb, 1) . ' GB' : number_format($data['total_memory'] / (1024 * 1024), 0) . ' MB';
                                    @endphp
                                    {{ $data['total_memory'] > 0 ? $pMemDisplay : 'N/A' }}
                                </td>
                                <td>
                                    @if($data['last_synced'])
                                    <small>{{ $data['last_synced']->format('M d, Y H:i') }}</small>
                                    @else
                                    <small class="text-muted">Never</small>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No partners with {{ $area }} workspaces assigned.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    @endforeach
</div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/table-init.js'])
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Application Versions donut charts
        @php
            $versionChartData = [];
            foreach ($productAreas as $a) {
                $slug = \Illuminate\Support\Str::slug($a);
                $versionChartData[$slug] = [
                    'current' => $productAreaStats[$a]['total_partners'] - $productAreaStats[$a]['outdated_count'],
                    'outdated' => $productAreaStats[$a]['outdated_count'],
                    'total' => $productAreaStats[$a]['total_partners'],
                ];
            }
        @endphp
        var versionData = @json($versionChartData);

        Object.keys(versionData).forEach(function(slug) {
            var d = versionData[slug];
            var el = document.querySelector('#version-chart-' + slug);
            if (!el) return;
            if (d.total === 0) {
                el.innerHTML = '<div class="text-muted py-4">No data</div>';
                return;
            }
            new ApexCharts(el, {
                chart: { type: 'donut', height: 180, fontFamily: 'inherit' },
                series: [d.current, d.outdated],
                labels: ['Current', 'Not Current'],
                colors: ['#28a745', '#dc3545'],
                plotOptions: { pie: { donut: { size: '60%', labels: {
                    show: true,
                    total: { show: true, label: 'Partners', formatter: function(w) {
                        return w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
                    }}
                }}}},
                legend: { position: 'bottom', fontSize: '11px' },
                dataLabels: { enabled: true, formatter: function(val) { return val.toFixed(0) + '%'; } },
                stroke: { width: 2 }
            }).render();
        });

        // SmartTables for partner tables
        @foreach($productAreas as $area)
        if (window.SmartTables && document.querySelector('#partners-{{ Str::slug($area) }} tbody tr td:not([colspan])')) {
            new window.SmartTables('partners-{{ Str::slug($area) }}', {
                perPage: 25,
                sortable: true,
                responsive: {
                    enabled: true,
                    breakpoint: 768,
                    columnPriorities: { 0: 1, 2: 2, 3: 3, 1: 4, 4: 5, 5: 6, 6: 7 }
                }
            });
        }
        @endforeach
    });
    </script>
@endsection
