@extends('portal.layout', ['pageTitle' => 'Goals'])

@section('content')
<h4 class="mb-4">Goals & Targets — {{ $partner->name }}</h4>

@foreach($categories as $category => $data)
<div class="card portal-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>{{ \App\Models\GoalTemplate::CATEGORIES[$category] ?? $category }}</strong>
        <span class="badge bg-primary">{{ $data['avg_attainment'] }}% avg attainment</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Goal</th><th class="text-end">Target</th><th class="text-end">Actual</th><th style="width:180px">Progress</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($data['goals'] as $g)
                    @php $pct = $g->target_value > 0 ? min(round(($g->actual_value / $g->target_value) * 100), 100) : 0; @endphp
                    <tr>
                        <td>{{ $g->name }}</td>
                        <td class="text-end">{{ $g->formatted_target ?? $g->target_value }}</td>
                        <td class="text-end">{{ $g->formatted_actual ?? $g->actual_value }}</td>
                        <td>
                            <div class="progress" style="height:14px"><div class="progress-bar bg-{{ $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') }}" style="width:{{ $pct }}%">{{ $pct }}%</div></div>
                        </td>
                        <td><span class="badge bg-{{ \App\Models\PartnerAssignedGoal::STATUS_COLORS[$g->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $g->status)) }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach

@if($goals->isEmpty())
    <div class="text-center text-muted py-5">No goals assigned yet.</div>
@endif
@endsection
