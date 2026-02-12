@extends('layouts.vertical', ['pageTitle' => 'Competency Matrix'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Competency Matrix', 'pageSubTitle1' => 'Plan', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The Competency Matrix tracks certification coverage across your partners and product areas. Use the grid to see at a glance which partners are well-certified and where gaps exist, then check the expiring certifications table to stay ahead of renewals. Keeping certifications current directly impacts your partners' ability to sell, deliver, and advance along their path.</p>

{{-- Summary Cards --}}
<div class="row">
    <div class="col-xl col-md-4 col-6"><div class="card shadow-0 mb-g"><div class="card-body text-center py-3"><h2 class="fw-300 m-0 text-primary">{{ $stats['total_active'] }}</h2><small class="text-muted">Active Certifications</small></div></div></div>
    <div class="col-xl col-md-4 col-6"><div class="card shadow-0 mb-g"><div class="card-body text-center py-3"><h2 class="fw-300 m-0 text-warning">{{ $stats['expiring_30'] }}</h2><small class="text-muted">Expiring 30d</small></div></div></div>
    <div class="col-xl col-md-4 col-6"><div class="card shadow-0 mb-g"><div class="card-body text-center py-3"><h2 class="fw-300 m-0 text-danger">{{ $stats['expiring_90'] }}</h2><small class="text-muted">Expiring 90d</small></div></div></div>
    <div class="col-xl col-md-4 col-6"><div class="card shadow-0 mb-g"><div class="card-body text-center py-3"><h2 class="fw-300 m-0 text-info">{{ $stats['in_progress'] }}</h2><small class="text-muted">In Progress</small></div></div></div>
    <div class="col-xl col-md-4 col-6"><div class="card shadow-0 mb-g"><div class="card-body text-center py-3"><h2 class="fw-300 m-0 text-success">{{ $stats['product_areas'] }}</h2><small class="text-muted">Product Areas</small></div></div></div>
</div>

{{-- Matrix Grid --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Certification Matrix — Partner × Product Area</div></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm m-0">
                <thead>
                    <tr><th>Partner</th>
                    @foreach($productAreas as $area)<th class="text-center">{{ $area }}</th>@endforeach
                    <th class="text-center">Total</th></tr>
                </thead>
                <tbody>
                @foreach($partners as $partner)
                    @php $partnerMatrix = $matrix->get($partner->id, collect()); $total = $partnerMatrix->sum('cert_count'); @endphp
                    <tr>
                        <td>{{ $partner->name }}</td>
                        @foreach($productAreas as $area)
                            @php $count = $partnerMatrix->firstWhere('product_area', $area)?->cert_count ?? 0; @endphp
                            <td class="text-center">
                                @if($count === 0)<span class="text-muted">—</span>
                                @elseif($count >= 4)<span class="badge bg-primary">{{ $count }}</span>
                                @elseif($count >= 2)<span class="badge bg-success">{{ $count }}</span>
                                @else<span class="badge bg-warning">{{ $count }}</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-center fw-500">{{ $total }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Expiring Soon --}}
@if($expiring->isNotEmpty())
<div class="card shadow-0 mb-g border-start border-3 border-warning">
    <div class="card-header"><div class="card-title">Expiring Certifications (Next 90 Days)</div></div>
    <div class="card-body">
        <table class="table table-sm m-0" id="expiring-table">
            <thead><tr><th>Contact</th><th>Partner</th><th>Type</th><th>Product Area</th><th>Expiry Date</th><th>Days Left</th></tr></thead>
            <tbody>
            @foreach($expiring as $cert)
            <tr>
                <td>{{ $cert->contact ? $cert->contact->first_name.' '.$cert->contact->last_name : '—' }}</td>
                <td>{{ $cert->partner->name }}</td>
                <td><span class="badge bg-info">{{ str_replace('_',' ',ucfirst($cert->type)) }}</span></td>
                <td>{{ $cert->product_area ?? '—' }}</td>
                <td>{{ $cert->expiry_date->format('M j, Y') }}</td>
                <td>
                    @php $d = now()->diffInDays($cert->expiry_date); @endphp
                    <span class="badge bg-{{ $d <= 7 ? 'danger' : ($d <= 30 ? 'warning' : 'info') }}">{{ $d }}d</span>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    if (document.getElementById('expiring-table')) new SmartTable('#expiring-table', { sortable: true, perPage: 10 });
});
</script>
@endsection
