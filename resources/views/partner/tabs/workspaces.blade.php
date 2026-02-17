@if(!$isConfigured)
<div class="alert alert-info mt-3">
    <strong>Anaplan API not configured.</strong> An admin can set <code>ANAPLAN_USERNAME</code> and <code>ANAPLAN_PASSWORD</code> in the environment to enable live sync.
</div>
@endif

@if($workspaces->isEmpty())
<div class="text-center py-5">
    <svg class="sa-icon sa-icon-xl text-muted mb-3" style="width: 48px; height: 48px;"><use href="/icons/sprite.svg#server"></use></svg>
    <p class="text-muted">No Anaplan workspaces have been assigned to this partner.</p>
    @if(auth()->user()->role === 'admin')
    <a href="{{ route('admin.provisioning-workspaces.index') }}" class="btn btn-outline-primary btn-sm">Manage Workspaces</a>
    @endif
</div>
@else

{{-- Product Area Summary Cards (always show all 4) --}}
@php
    $allModels = $workspaces->flatMap(fn ($ws) => $ws->models)->where('active_state', '!=', 'ARCHIVED');
    $productAreas = \App\Models\AnaplanWorkspace::PRODUCT_AREAS;
@endphp

<div class="row mt-3 mb-3">
    @foreach($productAreas as $area)
    @php
        $config = ($versionConfigs ?? collect())->get($area);
        $areaModels = $allModels->where('product_area', $area)
            ->filter(fn ($m) => isset(\App\Models\AnaplanModel::VERSION_RANKS[$m->detected_version]) && \App\Models\AnaplanModel::VERSION_RANKS[$m->detected_version] > 0);
        $isProvisioned = $areaModels->isNotEmpty();

        $highestVersion = null;
        $isOutdated = false;
        $diff = 0;
        $modelCount = 0;
        $areaMemory = 0;

        if ($isProvisioned) {
            $highestRank = $areaModels->max(fn ($m) => \App\Models\AnaplanModel::VERSION_RANKS[$m->detected_version] ?? 0);
            $highestVersion = collect(\App\Models\AnaplanModel::VERSION_RANKS)->filter(fn ($r) => $r === $highestRank)->keys()->first();
            $targetRank = $config ? (\App\Models\AnaplanModel::VERSION_RANKS[$config->current_version] ?? -1) : -1;
            $isOutdated = $targetRank > 0 && $highestRank < $targetRank;
            $diff = $targetRank - $highestRank;
            $modelCount = $areaModels->count();
            $areaMemory = $areaModels->sum('memory_usage');
        }
    @endphp
    <div class="col-sm-6 col-xl-3 mb-g">
        @if($isProvisioned)
        <div class="card shadow-0 h-100" style="border-left: 4px solid {{ $isOutdated ? '#ffc107' : '#28a745' }};">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="m-0 fw-bold">{{ $area }}</h6>
                    @if($isOutdated)
                    <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Update Available</span>
                    @else
                    <span class="badge bg-success" style="font-size: 0.65rem;">Current</span>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="text-center">
                        <div class="fw-bold fs-5" style="color: {{ $isOutdated ? ($diff > 1 ? '#dc3545' : '#ffc107') : '#28a745' }};">{{ $highestVersion }}</div>
                        <small class="text-muted" style="font-size: 0.7rem;">In Use</small>
                    </div>
                    @if($config)
                    <div class="text-muted px-1">
                        <svg class="sa-icon" style="width: 14px; height: 14px;"><use href="/icons/sprite.svg#arrow-right"></use></svg>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold fs-5 text-success">{{ $config->current_version }}</div>
                        <small class="text-muted" style="font-size: 0.7rem;">Current</small>
                    </div>
                    @endif
                </div>

                <div class="text-muted" style="font-size: 0.75rem;">
                    {{ $modelCount }} {{ Str::plural('model', $modelCount) }}
                    @if($areaMemory > 0)
                    @php
                        $sMemGb = $areaMemory / (1024 * 1024 * 1024);
                        $sMemDisplay = $sMemGb >= 1 ? number_format($sMemGb, 1) . ' GB' : number_format($areaMemory / (1024 * 1024), 0) . ' MB';
                    @endphp
                    &middot; {{ $sMemDisplay }}
                    @endif
                </div>
            </div>
        </div>
        @else
        <div class="card shadow-0 h-100" style="border-left: 4px solid #dee2e6;">
            <div class="card-body py-3">
                <h6 class="m-0 fw-bold mb-2">{{ $area }}</h6>
                <div class="text-muted small fst-italic">Not yet provisioned</div>
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>

{{-- KPI Row --}}
<div class="row mb-3">
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center py-3">
                <h3 class="fw-300 m-0">{{ $workspaces->count() }}</h3>
                <small class="text-muted">Workspaces</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center py-3">
                <h3 class="fw-300 m-0 text-success">{{ $activeModels }}</h3>
                <small class="text-muted">Active Models</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center py-3">
                @php
                    $memGb = $totalMemory / (1024 * 1024 * 1024);
                    $memDisplay = $memGb >= 1 ? round($memGb, 2) . ' GB' : round($totalMemory / (1024 * 1024), 1) . ' MB';
                @endphp
                <h3 class="fw-300 m-0">{{ $totalMemory > 0 ? $memDisplay : 'N/A' }}</h3>
                <small class="text-muted">Total Memory Usage</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center py-3">
                <h3 class="fw-300 m-0">{{ $lastSync ? $lastSync->diffForHumans() : 'Never' }}</h3>
                <small class="text-muted">Last Synced</small>
            </div>
        </div>
    </div>
</div>

{{-- Model Activity Distribution --}}
@php
    $hasActivityData = array_sum($modelActivity) > 0;
@endphp
@if($hasActivityData)
<div class="row mb-3">
    <div class="col-xl-6 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-header py-2">
                <div class="card-title fs-sm">Model Activity Distribution</div>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 250px;">
                <div id="model-activity-chart" style="width: 100%;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-header py-2">
                <div class="card-title fs-sm">Activity Breakdown</div>
            </div>
            <div class="card-body">
                <table class="table table-sm m-0">
                    <tbody>
                        <tr>
                            <td><span class="badge" style="background-color: #28a745;">&#9679;</span> Modified within 1 month</td>
                            <td class="text-end fw-bold">{{ $modelActivity['under_1_month'] }}</td>
                        </tr>
                        <tr>
                            <td><span class="badge" style="background-color: #33b5e5;">&#9679;</span> 1–6 months ago</td>
                            <td class="text-end fw-bold">{{ $modelActivity['1_to_6_months'] }}</td>
                        </tr>
                        <tr>
                            <td><span class="badge" style="background-color: #ffc107; color: #212529;">&#9679;</span> 6–12 months ago</td>
                            <td class="text-end fw-bold">{{ $modelActivity['6_to_12_months'] }}</td>
                        </tr>
                        <tr>
                            <td><span class="badge" style="background-color: #dc3545;">&#9679;</span> Over 12 months ago</td>
                            <td class="text-end fw-bold">{{ $modelActivity['over_12_months'] }}</td>
                        </tr>
                        @if($modelActivity['never'] > 0)
                        <tr>
                            <td><span class="badge bg-secondary">&#9679;</span> No data</td>
                            <td class="text-end fw-bold">{{ $modelActivity['never'] }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var activityData = @json($modelActivity);
    var el = document.querySelector('#model-activity-chart');
    if (!el) return;

    var series = [activityData.under_1_month, activityData['1_to_6_months'], activityData['6_to_12_months'], activityData.over_12_months];
    var labels = ['< 1 month', '1–6 months', '6–12 months', '> 12 months'];
    var colors = ['#28a745', '#33b5e5', '#ffc107', '#dc3545'];

    if (activityData.never > 0) {
        series.push(activityData.never);
        labels.push('No data');
        colors.push('#868e96');
    }

    new ApexCharts(el, {
        chart: { type: 'donut', height: 240, fontFamily: 'inherit' },
        series: series,
        labels: labels,
        colors: colors,
        plotOptions: { pie: { donut: { size: '55%', labels: {
            show: true,
            total: { show: true, label: 'Models', formatter: function(w) {
                return w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
            }}
        }}}},
        legend: { position: 'bottom', fontSize: '11px' },
        dataLabels: { enabled: true, formatter: function(val) { return val.toFixed(0) + '%'; } },
        stroke: { width: 2 }
    }).render();
});
</script>
@endif

{{-- Per Workspace Cards --}}
@foreach($workspaces as $workspace)
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex align-items-center">
        <div class="card-title flex-grow-1">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#server"></use></svg>
            {{ $workspace->name }}
            @if($workspace->active)
            <span class="badge bg-success ms-2">Active</span>
            @else
            <span class="badge bg-secondary ms-2">Inactive</span>
            @endif
            @if($workspace->product_area)
            <span class="badge bg-info bg-opacity-25 text-info ms-1">{{ $workspace->product_area }}</span>
            @endif
            @if($workspace->workspace_type)
            <span class="badge bg-secondary bg-opacity-25 text-secondary ms-1">{{ $workspace->workspace_type }}</span>
            @endif
        </div>
        @if($workspace->size_allowance)
        <div class="d-flex align-items-center gap-2">
            <div class="progress" style="height: 8px; width: 120px;">
                @php $pct = $workspace->usagePercentage() ?? 0; @endphp
                <div class="progress-bar {{ $pct > 80 ? 'bg-danger' : ($pct > 60 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $pct }}%"></div>
            </div>
            <small class="text-muted text-nowrap">{{ $workspace->formattedCurrentSize() }} / {{ $workspace->formattedSizeAllowance() }}</small>
        </div>
        @endif
    </div>
    <div class="card-body p-0">
        @if($workspace->models->isEmpty())
        <div class="text-center py-3 text-muted">No models in this workspace.</div>
        @else
        <table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>Model</th>
                    <th class="text-center">Version</th>
                    <th class="text-center">Status</th>
                    <th>Category</th>
                    <th class="text-center">Memory</th>
                    <th class="text-center">Modules</th>
                    <th class="text-center">Lists</th>
                    <th>Last Modified</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($workspace->models as $model)
                <tr class="cursor-pointer" data-bs-toggle="collapse" data-bs-target="#model-detail-{{ $model->id }}">
                    <td>
                        <svg class="sa-icon sa-icon-sm text-muted me-1"><use href="/icons/sprite.svg#database"></use></svg>
                        <strong>{{ $model->name }}</strong>
                    </td>
                    <td class="text-center">
                        @if($model->detected_version && $model->detected_version !== 'Other')
                        <span class="badge" style="background-color: {{ $model->versionBadgeColor() }}; {{ $model->versionBadgeColor() === '#ffc107' ? 'color: #212529;' : '' }}">
                            {{ $model->detected_version }}
                        </span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge" style="background-color: {{ \App\Models\AnaplanModel::ACTIVE_STATE_COLORS[$model->active_state] ?? '#868e96' }}">
                            {{ \App\Models\AnaplanModel::ACTIVE_STATES[$model->active_state] ?? $model->active_state }}
                        </span>
                    </td>
                    <td>{{ $model->category ?? '—' }}</td>
                    <td class="text-center">{{ $model->formattedMemoryUsage() }}</td>
                    <td class="text-center"><span class="badge bg-info bg-opacity-25 text-info">{{ $model->module_count ?? 0 }}</span></td>
                    <td class="text-center"><span class="badge bg-primary bg-opacity-25 text-primary">{{ $model->list_count ?? 0 }}</span></td>
                    <td>
                        @if($model->last_modified_in_anaplan_at)
                        {{ $model->last_modified_in_anaplan_at->format('M d, Y H:i') }}
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($model->model_url)
                        <a href="{{ $model->model_url }}" target="_blank" class="btn btn-xs btn-outline-info" title="Open in Anaplan" onclick="event.stopPropagation();">
                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#external-link"></use></svg>
                        </a>
                        @endif
                    </td>
                </tr>
                {{-- Expandable detail row --}}
                <tr class="collapse" id="model-detail-{{ $model->id }}">
                    <td colspan="9" class="bg-light p-3">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2">Modules ({{ $model->modules->count() }})</h6>
                                @if($model->modules->isEmpty())
                                <p class="text-muted small">No modules synced.</p>
                                @else
                                <ul class="list-unstyled mb-0">
                                    @foreach($model->modules as $module)
                                    <li class="py-1 border-bottom">
                                        <svg class="sa-icon sa-icon-xs text-muted me-1"><use href="/icons/sprite.svg#grid"></use></svg>
                                        {{ $module->name }}
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2">Lists / Dimensions ({{ $model->lists->count() }})</h6>
                                @if($model->lists->isEmpty())
                                <p class="text-muted small">No lists synced.</p>
                                @else
                                <ul class="list-unstyled mb-0">
                                    @foreach($model->lists as $list)
                                    <li class="py-1 border-bottom d-flex justify-content-between">
                                        <span>
                                            <svg class="sa-icon sa-icon-xs text-muted me-1"><use href="/icons/sprite.svg#list"></use></svg>
                                            {{ $list->name }}
                                        </span>
                                        @if($list->item_count !== null)
                                        <small class="text-muted">{{ number_format($list->item_count) }} items</small>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                        </div>
                        @if($model->created_in_anaplan_at)
                        <div class="mt-2">
                            <small class="text-muted">Created: {{ $model->created_in_anaplan_at->format('M d, Y') }}</small>
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endforeach

@endif
