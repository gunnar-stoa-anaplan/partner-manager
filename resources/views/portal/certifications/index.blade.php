@extends('portal.layout', ['pageTitle' => 'Certifications'])

@section('content')
<h4 class="mb-4">Certifications — {{ $partner->name }}</h4>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card portal-card text-center py-3">
            <h3 class="text-success mb-0">{{ $summary['active'] }}</h3>
            <small class="text-muted">Active</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card portal-card text-center py-3">
            <h3 class="text-warning mb-0">{{ $summary['expiring_soon'] }}</h3>
            <small class="text-muted">Expiring Soon</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card portal-card text-center py-3">
            <h3 class="text-info mb-0">{{ $summary['in_progress'] }}</h3>
            <small class="text-muted">In Progress</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card portal-card text-center py-3">
            <h3 class="text-danger mb-0">{{ $summary['expired'] }}</h3>
            <small class="text-muted">Expired</small>
        </div>
    </div>
</div>

<div class="card portal-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Contact</th><th>Type</th><th>Level</th><th>Status</th><th>Issued</th><th>Expires</th></tr></thead>
                <tbody>
                @forelse($certifications as $cert)
                    <tr class="{{ $cert->isExpiringSoon() ? 'table-warning' : '' }}">
                        <td>{{ $cert->contact?->full_name ?? '—' }}</td>
                        <td>{{ \App\Models\Certification::TYPES[$cert->type] ?? $cert->type }}</td>
                        <td>{{ \App\Models\Certification::LEVELS[$cert->level] ?? $cert->level }}</td>
                        <td>
                            @php $statusColors = ['active'=>'success','expired'=>'danger','in_progress'=>'info','scheduled'=>'secondary','revoked'=>'dark']; @endphp
                            <span class="badge bg-{{ $statusColors[$cert->status] ?? 'secondary' }}">{{ \App\Models\Certification::STATUSES[$cert->status] ?? $cert->status }}</span>
                            @if($cert->isExpiringSoon()) <small class="text-warning ms-1">Expiring soon</small> @endif
                        </td>
                        <td>{{ $cert->issued_date?->format('M j, Y') ?? '—' }}</td>
                        <td>{{ $cert->expiry_date?->format('M j, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-3">No certifications found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
