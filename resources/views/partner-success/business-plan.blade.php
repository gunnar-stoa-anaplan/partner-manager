@extends('layouts.vertical', [
    'pageTitle' => 'Business Plans',
    'pageSubTitle1' => 'Insights',
    'pageSubTitle2' => 'PSM Engagement',
    'pageSubTitle2Url' => url('partner-success'),
    'pageSubText' => 'The partner plan — revenue committed and the certified people to deliver it. Scoped to your partners and the filters above.',
])

@php $m = fn ($v) => '$'.number_format(($v ?? 0) / 1_000_000, 1).'M'; @endphp

@section('content')
<div class="container-fluid py-3">

    @include('partner-success.partials.scope-filter')

    {{-- Revenue + delivery KPIs --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-xl-2"><x-insight.stat-card title="Partner Sourced" :value="$m($plan['sourced']['v'])" :sub="number_format($plan['sourced']['n']).' open deals'" /></div>
        <div class="col-6 col-md-4 col-xl-2"><x-insight.stat-card title="Partner Influenced" :value="$m($plan['influenced']['v'])" :sub="number_format($plan['influenced']['n']).' co-sold'" /></div>
        <div class="col-6 col-md-4 col-xl-2"><x-insight.stat-card title="Total Pipeline" :value="$m($plan['pipeline']['v'])" :sub="number_format($plan['pipeline']['n']).' open deals'" /></div>
        <div class="col-6 col-md-4 col-xl-2"><x-insight.stat-card title="Closed Won" :value="$m($plan['won']['v'])" :sub="number_format($plan['won']['n']).' won'" /></div>
        <div class="col-6 col-md-4 col-xl-2"><x-insight.stat-card title="Delivered" :value="number_format($plan['delivered'])" sub="past go-live" /></div>
        <div class="col-6 col-md-4 col-xl-2"><x-insight.stat-card title="In Progress" :value="number_format($plan['in_progress'])" sub="to go-live" /></div>
    </div>

    {{-- Headcount plan: certified vs committed by role --}}
    <h5 class="fw-600 mt-4 mb-2">Headcount plan</h5>
    <div class="row g-3">
        @foreach($plan['roles'] as $role => $r)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="fw-600 mb-2">{{ $role }}</div>
                        <div class="d-flex gap-4">
                            <div><div class="fs-3 fw-700">{{ number_format($r['certified']) }}</div><div class="fs-xs text-muted text-uppercase">Certified</div></div>
                            <div><div class="fs-3 fw-700">{{ number_format($r['committed']) }}</div><div class="fs-xs text-muted text-uppercase">Committed</div></div>
                            <div><div class="fs-3 fw-700 {{ $r['to_assign'] > 0 ? 'text-warning' : '' }}">{{ number_format($r['to_assign']) }}</div><div class="fs-xs text-muted text-uppercase">To assign</div></div>
                        </div>
                        @if($editablePartner)
                            <form method="POST" action="{{ route('partner-success.plan-commit') }}" class="d-flex gap-1 align-items-center mt-3">
                                @csrf
                                <input type="hidden" name="partner_id" value="{{ $editablePartner->id }}">
                                <input type="hidden" name="role" value="{{ $role }}">
                                <input type="number" min="0" name="committed" value="{{ $editableCommits[$role] ?? 0 }}"
                                       class="form-control form-control-sm" style="max-width:90px" aria-label="Committed {{ $role }}">
                                <button class="btn btn-sm btn-outline-primary">Set commit</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @unless($editablePartner)
        <div class="fs-sm text-muted mt-2">Select a single partner in <strong>Filters</strong> to edit the committed headcount.</div>
    @endunless

    {{-- Revenue by line of business --}}
    <h5 class="fw-600 mt-4 mb-2">Revenue by line of business</h5>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Line of business</th>
                        <th class="text-end">Open deals</th>
                        <th class="text-end">Open $ACV</th>
                        <th class="text-end">Won</th>
                        <th class="text-end">Won $ACV</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plan['lob'] as $l)
                        <tr>
                            <td>{{ $l['lob'] }}</td>
                            <td class="text-end">{{ number_format($l['open_n']) }}</td>
                            <td class="text-end">{{ $m($l['open_v']) }}</td>
                            <td class="text-end">{{ number_format($l['won_n']) }}</td>
                            <td class="text-end">{{ $m($l['won_v']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted text-center py-3">No pipeline in the current scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
