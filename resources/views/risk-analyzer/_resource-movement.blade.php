{{-- Resource Movement Tracking --}}
@if($movement)
<div class="row mb-3">
    <div class="col-auto">
        <span class="text-muted small">Comparing <strong>{{ \Carbon\Carbon::parse($fromDate)->format('M j') }}</strong> to <strong>{{ \Carbon\Carbon::parse($toDate)->format('M j, Y') }}</strong></span>
    </div>
</div>

<div class="row">
    @php
        $resKpis = [
            ['label' => 'New Resources', 'value' => $movement['new_resources']->count(), 'color' => 'success', 'icon' => 'user-plus'],
            ['label' => 'Departed', 'value' => $movement['departed']->count(), 'color' => 'danger', 'icon' => 'user-minus'],
            ['label' => 'New Certifications', 'value' => $movement['new_certifications']->count(), 'color' => 'info', 'icon' => 'award'],
        ];
    @endphp
    @foreach($resKpis as $kpi)
    <div class="col-xl-4 col-md-6">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-{{ $kpi['color'] }}" style="width:24px;height:24px"><use href="/icons/sprite.svg#{{ $kpi['icon'] }}"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ $kpi['value'] }}</h2>
                <small class="text-muted">{{ $kpi['label'] }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($movement['new_certifications']->isNotEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">New Certifications</div></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead><tr><th>Name</th><th>Program</th><th>Partner</th></tr></thead>
                <tbody>
                @foreach($movement['new_certifications']->take(50) as $cert)
                <tr>
                    <td>{{ $cert->name }}</td>
                    <td><span class="badge bg-success bg-opacity-25 text-success">{{ $cert->program }}</span></td>
                    <td>{{ \App\Models\Partner::find($cert->partner_id)?->name ?? 'Unknown' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($movement['new_resources']->isNotEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">New Resources</div></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead><tr><th>Name</th><th>Partner</th></tr></thead>
                <tbody>
                @foreach($movement['new_resources']->take(50) as $res)
                <tr>
                    <td>{{ $res->name }}</td>
                    <td>{{ \App\Models\Partner::find($res->partner_id)?->name ?? 'Unknown' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@else
<div class="alert alert-info">Need at least two snapshots to show resource movement.</div>
@endif
