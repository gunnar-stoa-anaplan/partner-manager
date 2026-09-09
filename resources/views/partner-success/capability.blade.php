@extends('layouts.vertical', [
    'pageTitle' => 'Capability',
    'pageSubTitle1' => 'Insights',
    'pageSubTitle2' => 'PSM Engagement',
    'pageSubTitle2Url' => url('partner-success'),
    'pageSubText' => 'Practice capability coverage — certified people by role and product. Scoped to your partners and the filters above.',
])

@php $roleOrder = ['GTM', 'PreSales', 'Delivery']; @endphp

@section('content')
<div class="container-fluid py-3">

    @include('partner-success.partials.scope-filter')

    {{-- Totals --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3"><x-insight.stat-card title="People" :value="number_format($cap['totals']['people'])" sub="in your partners" /></div>
        <div class="col-6 col-xl-3"><x-insight.stat-card title="Certified" :value="number_format($cap['totals']['certified_any'])" sub="hold ≥1 certification" /></div>
        <div class="col-6 col-xl-3"><x-insight.stat-card title="In progress" :value="number_format($cap['totals']['inprogress_any'])" sub="on an academy path" /></div>
        <div class="col-6 col-xl-3"><x-insight.stat-card title="Certifications" :value="number_format($cap['totals']['distinct_certs'])" sub="distinct across practice" /></div>
    </div>

    {{-- Role coverage --}}
    <h5 class="fw-600 mt-4 mb-2">Coverage by role</h5>
    <div class="row g-3">
        @foreach($roleOrder as $role)
            @php
                $r = $cap['roles'][$role] ?? ['total' => 0, 'certified' => 0];
                $pct = $r['total'] > 0 ? round($r['certified'] / $r['total'] * 100) : 0;
            @endphp
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-600">{{ $role }}</span>
                        <span class="badge rounded-pill bg-body-secondary text-body border">{{ $pct }}% certified</span>
                    </div>
                    <div class="d-flex gap-4 mb-2">
                        <div><div class="fs-3 fw-700">{{ number_format($r['certified']) }}</div><div class="fs-xs text-muted text-uppercase">Certified</div></div>
                        <div><div class="fs-3 fw-700">{{ number_format($r['total']) }}</div><div class="fs-xs text-muted text-uppercase">People in role</div></div>
                    </div>
                    <div class="progress" style="height:6px"><div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div></div>
                </div></div>
            </div>
        @endforeach
    </div>

    {{-- Certifications across the practice --}}
    <h5 class="fw-600 mt-4 mb-2">Certifications across the practice</h5>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead><tr><th>Certification</th><th class="text-end">Certified</th><th style="width:40%">Coverage</th><th class="text-end">In progress</th></tr></thead>
                <tbody>
                    @php $maxCert = max(1, collect($cap['certs'])->max('certified') ?? 1); @endphp
                    @forelse($cap['certs'] as $c)
                        <tr>
                            <td class="fw-500">{{ $c['name'] }}</td>
                            <td class="text-end">{{ number_format($c['certified']) }}</td>
                            <td><div class="progress" style="height:8px"><div class="progress-bar bg-primary" style="width: {{ round($c['certified'] / $maxCert * 100) }}%"></div></div></td>
                            <td class="text-end">{{ $c['in_progress'] ? number_format($c['in_progress']) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center py-3">No certifications in the current scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Partner × certification grid --}}
    <h5 class="fw-600 mt-4 mb-2">Certified people by partner</h5>
    <style>
        .cap-grid { width: max-content; min-width: 100%; }
        .cap-grid th, .cap-grid td { white-space: nowrap; text-align: center; }
        .cap-grid td.num { min-width: 60px; }
        .cap-grid thead th:first-child, .cap-grid tbody td:first-child {
            position: sticky; left: 0; z-index: 3; min-width: 220px; max-width: 260px; text-align: left;
            white-space: normal; background: var(--bs-card-bg); box-shadow: 1px 0 0 var(--bs-border-color);
        }
        .cap-grid td.on { background: rgba(255,97,0,.08); font-weight: 600; }
    </style>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle cap-grid">
                <thead>
                    <tr>
                        <th>Partner</th>
                        @foreach($cap['grid']['certs'] as $cn)
                            <th class="num fs-xs" title="{{ $cn }}">{{ $cn }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($cap['grid']['partners'] as $partner)
                        <tr>
                            <td title="{{ $partner }}">{{ $partner }}</td>
                            @foreach($cap['grid']['certs'] as $cn)
                                @php $n = $cap['grid']['cells'][$partner][$cn] ?? 0; @endphp
                                <td class="num {{ $n ? 'on' : '' }}">{{ $n ?: '—' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($cap['grid']['certs']) + 1 }}" class="text-muted text-center py-3">No people in the current scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
