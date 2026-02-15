@extends('layouts.vertical', ['pageTitle' => 'Anaplan Workspaces'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Anaplan Workspaces', 'pageSubTitle1' => 'System', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Manage Anaplan workspace provisioning across partners. Sync workspace and model data from the Anaplan API, then assign workspaces to partners to track their provisioning status. Each workspace contains one or more models with modules and lists.</p>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show">{{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

@if(!$isConfigured)
<div class="alert alert-info">
    <strong>Anaplan API not configured.</strong> Set <code>ANAPLAN_USERNAME</code> and <code>ANAPLAN_PASSWORD</code> in your <code>.env</code> file to enable live sync.
</div>
@endif

{{-- KPI Cards --}}
<div class="row mb-3">
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center">
                <h3 class="mb-1">{{ $workspaces->count() }}</h3>
                <small class="text-muted">Total Workspaces</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center">
                <h3 class="mb-1 text-success">{{ $workspaces->count() - $unassignedCount }}</h3>
                <small class="text-muted">Assigned to Partners</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center">
                <h3 class="mb-1 {{ $unassignedCount > 0 ? 'text-warning' : '' }}">{{ $unassignedCount }}</h3>
                <small class="text-muted">Unassigned</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center">
                <h3 class="mb-1">{{ $activeModels }}</h3>
                <small class="text-muted">Active Models</small>
            </div>
        </div>
    </div>
</div>

{{-- Sync + Workspaces Table --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex align-items-center">
        <div class="card-title flex-grow-1">Workspaces</div>
        <div class="d-flex align-items-center gap-2">
            @if($lastSync)
            <small class="text-muted me-2">Last sync: {{ $lastSync->diffForHumans() }}</small>
            @endif
            <form action="{{ route('admin.anaplan-provisioning.sync') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm" {{ !$isConfigured ? 'disabled' : '' }} onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Syncing...'; this.form.submit();">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#refresh-cw"></use></svg>
                    Sync Now
                </button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover m-0" id="workspacesTable">
                <thead>
                    <tr>
                        <th>Workspace</th>
                        <th>Status</th>
                        <th>Partner</th>
                        <th class="text-center">Storage</th>
                        <th class="text-center">Models</th>
                        <th>Last Synced</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workspaces as $workspace)
                    <tr>
                        <td>
                            <strong>{{ $workspace->name }}</strong>
                            <br><small class="text-muted font-monospace">{{ $workspace->anaplan_id }}</small>
                        </td>
                        <td>
                            @if($workspace->active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.anaplan-provisioning.assign', $workspace) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <select name="partner_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 180px;">
                                    <option value="">— Unassigned —</option>
                                    @foreach($partners as $p)
                                    <option value="{{ $p->id }}" {{ $workspace->partner_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="text-center">
                            @if($workspace->size_allowance)
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px; max-width: 100px;">
                                    @php $pct = $workspace->usagePercentage() ?? 0; @endphp
                                    <div class="progress-bar {{ $pct > 80 ? 'bg-danger' : ($pct > 60 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <small class="text-muted text-nowrap">{{ $workspace->formattedCurrentSize() }} / {{ $workspace->formattedSizeAllowance() }}</small>
                            </div>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php $activeCount = $workspace->models->where('active_state', '!=', 'ARCHIVED')->count(); @endphp
                            <span class="badge bg-info">{{ $activeCount }}</span>
                            @if($workspace->models->where('active_state', 'ARCHIVED')->count() > 0)
                            <span class="badge bg-secondary">{{ $workspace->models->where('active_state', 'ARCHIVED')->count() }} archived</span>
                            @endif
                        </td>
                        <td>
                            @if($workspace->last_synced_at)
                            <small>{{ $workspace->last_synced_at->format('M d, Y H:i') }}</small>
                            @else
                            <span class="text-muted">Never</span>
                            @endif
                        </td>
                    </tr>
                    {{-- Expandable model rows --}}
                    @foreach($workspace->models as $model)
                    <tr class="table-light">
                        <td class="ps-5">
                            <svg class="sa-icon sa-icon-sm text-muted me-1"><use href="/icons/sprite.svg#database"></use></svg>
                            {{ $model->name }}
                        </td>
                        <td>
                            <span class="badge" style="background-color: {{ \App\Models\AnaplanModel::ACTIVE_STATE_COLORS[$model->active_state] ?? '#868e96' }}">
                                {{ \App\Models\AnaplanModel::ACTIVE_STATES[$model->active_state] ?? $model->active_state }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $model->category ?? '—' }}</small></td>
                        <td class="text-center"><small>{{ $model->formattedMemoryUsage() }}</small></td>
                        <td class="text-center">
                            <small class="text-muted">{{ $model->module_count ?? 0 }} modules, {{ $model->list_count ?? 0 }} lists</small>
                        </td>
                        <td>
                            @if($model->last_modified_in_anaplan_at)
                            <small>{{ $model->last_modified_in_anaplan_at->format('M d, Y') }}</small>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            @if($isConfigured)
                            No workspaces found. Click <strong>Sync Now</strong> to pull data from Anaplan.
                            @else
                            Configure Anaplan API credentials to sync workspaces.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/table-init.js'])
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.SmartTables && document.querySelector('#workspacesTable tbody tr td:not([colspan])')) {
            new window.SmartTables('workspacesTable', {
                perPage: 50,
                sortable: true,
                responsive: {
                    enabled: true,
                    breakpoint: 768,
                    columnPriorities: { 0: 1, 2: 2, 4: 3, 1: 4, 3: 5, 5: 6 }
                }
            });
        }
    });
    </script>
@endsection
