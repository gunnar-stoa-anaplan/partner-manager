@extends('portal.layout', ['pageTitle' => 'Workspaces'])

@section('content')
<h4 class="mb-3">Anaplan Workspaces</h4>
<p class="text-muted mb-4">View your organization's Anaplan workspaces and model information.</p>

{{-- Version Warning Banners --}}
@foreach($outdatedAreas as $oa)
<div class="alert alert-warning d-flex align-items-start">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2 mt-1 flex-shrink-0"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
    <div>
        Your organization is running <strong>{{ $oa['area'] }} {{ $oa['current'] }}</strong>.
        The current version is <strong>{{ $oa['target'] }}</strong>.
        Contact your Anaplan team for upgrade information.
    </div>
</div>
@endforeach

@if($workspaces->isEmpty())
<div class="text-center py-5">
    <p class="text-muted">No Anaplan workspaces are associated with your organization.</p>
</div>
@else

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card portal-card h-100">
            <div class="card-body text-center py-3">
                <h3 class="mb-1">{{ $workspaces->count() }}</h3>
                <small class="text-muted">Workspaces</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card portal-card h-100">
            <div class="card-body text-center py-3">
                <h3 class="mb-1 text-primary">{{ $activeModelCount }}</h3>
                <small class="text-muted">Active Models</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card portal-card h-100">
            <div class="card-body text-center py-3">
                @php
                    $memGb = $totalMemory / (1024 * 1024 * 1024);
                    if ($memGb >= 1024) {
                        $memDisplay = number_format($memGb / 1024, 2) . ' TB';
                    } elseif ($memGb >= 1) {
                        $memDisplay = number_format($memGb, 1) . ' GB';
                    } else {
                        $memDisplay = number_format($totalMemory / (1024 * 1024), 0) . ' MB';
                    }
                @endphp
                <h3 class="mb-1">{{ $totalMemory > 0 ? $memDisplay : 'N/A' }}</h3>
                <small class="text-muted">Total Memory</small>
            </div>
        </div>
    </div>
</div>

{{-- Workspace Cards --}}
@foreach($workspaces as $workspace)
<div class="card portal-card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between bg-white">
        <div>
            <strong>{{ $workspace->name }}</strong>
            @if($workspace->product_area)
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $workspace->product_area }}</span>
            @endif
            @if($workspace->workspace_type)
            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ $workspace->workspace_type }}</span>
            @endif
        </div>
        @if($workspace->size_allowance)
        <div class="d-flex align-items-center gap-2">
            <div class="progress" style="height: 6px; width: 100px;">
                @php $pct = $workspace->usagePercentage() ?? 0; @endphp
                <div class="progress-bar {{ $pct > 80 ? 'bg-danger' : ($pct > 60 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $pct }}%"></div>
            </div>
            <small class="text-muted text-nowrap">{{ $workspace->formattedCurrentSize() }} / {{ $workspace->formattedSizeAllowance() }}</small>
        </div>
        @endif
    </div>
    @if($workspace->models->where('active_state', '!=', 'ARCHIVED')->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-sm m-0">
            <thead class="table-light">
                <tr>
                    <th>Model</th>
                    <th class="text-center">Version</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Memory</th>
                    <th>Last Modified</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workspace->models->where('active_state', '!=', 'ARCHIVED') as $model)
                <tr>
                    <td>{{ $model->name }}</td>
                    <td class="text-center">
                        @if($model->detected_version && $model->detected_version !== 'Other')
                        <span class="badge" style="background-color: {{ $model->versionBadgeColor() }}; {{ $model->versionBadgeColor() === '#ffc107' ? 'color: #212529;' : '' }}">
                            {{ $model->detected_version }}
                        </span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge" style="background-color: {{ \App\Models\AnaplanModel::ACTIVE_STATE_COLORS[$model->active_state] ?? '#868e96' }}">
                            {{ \App\Models\AnaplanModel::ACTIVE_STATES[$model->active_state] ?? $model->active_state }}
                        </span>
                    </td>
                    <td class="text-center"><small>{{ $model->formattedMemoryUsage() }}</small></td>
                    <td>
                        @if($model->last_modified_in_anaplan_at)
                        <small>{{ $model->last_modified_in_anaplan_at->format('M d, Y') }}</small>
                        @else
                        <small class="text-muted">—</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="card-body text-center text-muted py-3">No active models in this workspace.</div>
    @endif
</div>
@endforeach

@endif
@endsection
