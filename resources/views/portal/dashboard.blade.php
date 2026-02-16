@extends('portal.layout', ['pageTitle' => 'Dashboard'])

@section('content')
<h2 class="fw-bold mb-1">Welcome, {{ $contact->first_name }}</h2>
<p class="text-muted mb-4">{{ $partner->name }} — {{ \App\Models\Contact::PORTAL_ROLES[$contact->portal_role] ?? '' }} Dashboard</p>

{{-- ===== ADMIN DASHBOARD ===== --}}
@if($contact->dashboardGroup() === 'admin')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                @php $hColor = \App\Models\PartnerHealthSnapshot::RISK_COLORS[$health['risk_level']] ?? 'secondary'; @endphp
                <h1 class="display-5 fw-bold text-{{ $hColor }} mb-0">{{ $health['score'] }}</h1>
                <span class="badge bg-{{ $hColor }}">{{ \App\Models\PartnerHealthSnapshot::RISK_LEVELS[$health['risk_level']] ?? $health['risk_level'] }}</span>
                <p class="text-muted mt-2 mb-0">Health Score</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                <h1 class="display-5 fw-bold text-primary mb-0">{{ $scorecard ? $scorecard->overall_score : '—' }}</h1>
                <p class="text-muted mt-2 mb-0">Scorecard</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                <h1 class="display-5 fw-bold text-success mb-0">${{ number_format($pipeline->total ?? 0) }}</h1>
                <small class="text-muted">{{ $pipeline->count ?? 0 }} opportunities</small>
                <p class="text-muted mt-2 mb-0">Open Pipeline</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                <h1 class="display-5 fw-bold text-info mb-0">{{ $certCount }}</h1>
                <small class="text-muted">{{ $contactCount }} team members</small>
                <p class="text-muted mt-2 mb-0">Active Certifications</p>
            </div>
        </div>
    </div>
</div>

@if(!empty($health['signals']))
<div class="card portal-card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Risk Signals</h5></div>
    <div class="card-body">
        @foreach($health['signals'] as $signal)
        <div class="d-flex align-items-center mb-2">
            <span class="badge bg-{{ $hColor }} me-2">!</span>
            {{ $signal }}
        </div>
        @endforeach
    </div>
</div>
@endif

@if($goals->isNotEmpty())
<div class="card portal-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Goals Progress</h5>
        <a href="{{ route('portal.goals.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Goal</th><th>Category</th><th class="text-end">Target</th><th class="text-end">Actual</th><th style="width:150px">Progress</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($goals->take(8) as $g)
                    @php $pct = $g->target_value > 0 ? min(round(($g->actual_value / $g->target_value) * 100), 100) : 0; @endphp
                    <tr>
                        <td class="fw-500">{{ $g->name }}</td>
                        <td><span class="badge bg-secondary">{{ $g->category }}</span></td>
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
@endif

@if(isset($launchPrograms) && $launchPrograms->isNotEmpty())
<div class="card portal-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Launch Programs</h5>
        <a href="{{ route('portal.launch-programs.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($launchPrograms->take(3) as $lp)
                @php $completion = $lp->completionPercentage(); @endphp
                <div class="col-md-4 mb-2">
                    <div class="border rounded p-3">
                        <h6 class="mb-1">{{ $lp->program->name }}</h6>
                        <small class="text-muted d-block mb-2">{{ $lp->program->product?->name ?? '' }}</small>
                        <div class="progress mb-1" style="height:8px">
                            <div class="progress-bar {{ $completion >= 80 ? 'bg-success' : 'bg-primary' }}" style="width:{{ $completion }}%"></div>
                        </div>
                        <small class="text-muted">{{ $completion }}% complete</small>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="card portal-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Recent Deal Registrations</h5>
        <a href="{{ route('portal.deal-registrations.create') }}" class="btn btn-sm btn-primary">Register New Deal</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Deal Name</th><th>Customer</th><th class="text-end">Est. Value</th><th>Status</th><th>Submitted</th></tr></thead>
                <tbody>
                @forelse($dealRegs as $deal)
                    <tr>
                        <td class="fw-500">{{ $deal->deal_name }}</td>
                        <td>{{ $deal->customer_name }}</td>
                        <td class="text-end">${{ number_format($deal->estimated_value) }}</td>
                        <td><span class="badge" style="background:{{ \App\Models\DealRegistration::STATUS_COLORS[$deal->status] ?? '#868e96' }}">{{ ucfirst($deal->status) }}</span></td>
                        <td>{{ $deal->submitted_at?->format('M d') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No deal registrations yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===== SALES DASHBOARD ===== --}}
@elseif($contact->dashboardGroup() === 'sales')
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                <h1 class="display-5 fw-bold text-success mb-0">${{ number_format(($pipeline->total ?? 0) / 1000) }}K</h1>
                <small class="text-muted">{{ $pipeline->count ?? 0 }} opportunities</small>
                <p class="text-muted mt-2 mb-0">Open Pipeline</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                <h1 class="display-5 fw-bold text-primary mb-0">{{ $dealRegs->count() }}</h1>
                <p class="text-muted mt-2 mb-0">Recent Partner Registrations</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                <h1 class="display-5 fw-bold text-info mb-0">{{ $myDeals->count() }}</h1>
                <p class="text-muted mt-2 mb-0">My Submissions</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card portal-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">My Deal Registrations</h5>
                <a href="{{ route('portal.deal-registrations.create') }}" class="btn btn-sm btn-primary">Register Deal</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Deal</th><th>Customer</th><th>Value</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($myDeals as $deal)
                        <tr>
                            <td>{{ $deal->deal_name }}</td>
                            <td>{{ $deal->customer_name }}</td>
                            <td>${{ number_format($deal->estimated_value) }}</td>
                            <td><span class="badge" style="background:{{ \App\Models\DealRegistration::STATUS_COLORS[$deal->status] ?? '#868e96' }}">{{ ucfirst($deal->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center py-3">No submissions yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card portal-card">
            <div class="card-header"><h5 class="card-title mb-0">Partner Deal Registrations</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Deal</th><th>Value</th><th>Status</th><th>Submitted</th></tr></thead>
                    <tbody>
                    @forelse($dealRegs as $deal)
                        <tr>
                            <td>{{ $deal->deal_name }}</td>
                            <td>${{ number_format($deal->estimated_value) }}</td>
                            <td><span class="badge" style="background:{{ \App\Models\DealRegistration::STATUS_COLORS[$deal->status] ?? '#868e96' }}">{{ ucfirst($deal->status) }}</span></td>
                            <td>{{ $deal->submitted_at?->format('M j') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center py-3">No registrations</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ===== DELIVERY DASHBOARD ===== --}}
@elseif($contact->dashboardGroup() === 'delivery')
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                <h1 class="display-5 fw-bold text-primary mb-0">{{ $myProjects->count() }}</h1>
                <p class="text-muted mt-2 mb-0">My Assigned Projects</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                <h1 class="display-5 fw-bold text-success mb-0">{{ $myProjects->where('status', 'active')->count() }}</h1>
                <p class="text-muted mt-2 mb-0">Active Projects</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                <h1 class="display-5 fw-bold text-warning mb-0">{{ $openCases->count() }}</h1>
                <p class="text-muted mt-2 mb-0">Open Cases</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card portal-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">My Projects</h5>
                <a href="{{ route('portal.projects.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Project</th><th>Status</th><th style="width:25%">Completion</th></tr></thead>
                    <tbody>
                    @forelse($myProjects as $project)
                        @php $pct = $project->completion_percentage ?? 0; @endphp
                        <tr>
                            <td><a href="{{ route('portal.projects.show', $project) }}">{{ $project->name }}</a></td>
                            <td><span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'primary' : 'warning') }}">{{ ucfirst($project->status) }}</span></td>
                            <td>
                                <div class="progress" style="height:8px"><div class="progress-bar" style="width:{{ $pct }}%"></div></div>
                                <small class="text-muted">{{ $pct }}%</small>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center py-3">No assigned projects</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card portal-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Open Cases</h5>
                <a href="{{ route('portal.cases.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Subject</th><th>Severity</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($openCases as $case)
                        <tr>
                            <td>{{ $case->subject }}</td>
                            <td><span class="badge bg-{{ $case->severity === 'critical' ? 'danger' : ($case->severity === 'high' ? 'warning' : 'secondary') }}">{{ ucfirst($case->severity) }}</span></td>
                            <td>{{ \App\Models\SupportCase::STATUSES[$case->status] ?? $case->status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center py-3">No open cases</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
