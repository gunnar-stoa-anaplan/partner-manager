@extends('layouts.vertical', [
    'pageTitle' => 'Business Plans',
    'pageSubTitle1' => 'Insights',
    'pageSubTitle2' => 'PSM Engagement',
    'pageSubTitle2Url' => url('partner-success'),
    'pageSubText' => 'The partner plan — revenue committed and the certified people to deliver it. Scoped to your partners and the filters above.',
])

@php
    $m = fn ($v) => '$'.number_format(($v ?? 0) / 1_000_000, 1).'M';
    $roleOrder = ['GTM', 'PreSales', 'Delivery'];
    // Compact money for the dense product matrix: $1.2M / $436K / —.
    $mc = function ($v) {
        $v = (int) ($v ?? 0);
        if ($v <= 0) return '—';
        if ($v >= 1_000_000) return '$'.number_format($v / 1_000_000, 1).'M';
        return '$'.number_format($v / 1_000).'K';
    };
    $lobColor = [
        'Platform' => '#1b3a5b', 'SCM' => '#0f4c54', 'FIN' => '#9b2c2c',
        'RPM' => '#8a6d1f', 'WFP' => '#4c3d8f', 'AI' => '#1f6f54', '—' => '#334155',
    ];
@endphp

@section('content')

{{-- Embedded screen agent (renders only when config('agent.enabled')). --}}
<x-agent.panel screen="business-plan" />
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

    {{-- Sales — opportunities by product (stage × product matrix) --}}
    <style>
        .ps-matrix { width: max-content; min-width: 100%; }
        .ps-matrix th, .ps-matrix td { white-space: nowrap; }
        /* Roomy product columns so nothing is squeezed; the wrapper scrolls. */
        .ps-matrix thead tr:nth-child(2) th,
        .ps-matrix tbody td:nth-child(n+5),
        .ps-matrix tfoot td:nth-child(n+5) { min-width: 92px; }
        /* Pin the sales-stage column while scrolling right. */
        .ps-matrix thead th:first-child,
        .ps-matrix tbody td:first-child,
        .ps-matrix tfoot td:first-child {
            position: sticky; left: 0; z-index: 3; min-width: 190px;
            background: var(--bs-card-bg); box-shadow: 1px 0 0 var(--bs-border-color);
        }
        .ps-matrix tfoot td:first-child { background: var(--bs-dark); }
    </style>
    <h5 class="fw-600 mt-4 mb-2">Sales <span class="fs-sm fw-normal text-muted">— opportunities by product</span></h5>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle ps-matrix">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle">Sales stage</th>
                        <th rowspan="2" class="align-middle text-end">$ sold</th>
                        <th rowspan="2" class="align-middle text-end">Opportunities</th>
                        <th rowspan="2" class="align-middle text-end">Accounts</th>
                        @foreach($plan['matrix']['groups'] as $g)
                            <th colspan="{{ count($g['products']) }}" class="text-center text-white"
                                style="background: {{ $lobColor[$g['lob']] ?? '#334155' }}">{{ $g['lob'] }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($plan['matrix']['groups'] as $g)
                            @foreach($g['products'] as $p)
                                <th class="text-center fs-xs" title="{{ $p['cap'] }}">{{ $p['abbr'] }}</th>
                            @endforeach
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($plan['matrix']['stages'] as $s)
                        <tr>
                            <td class="fw-500 text-nowrap">{{ $s['label'] }}</td>
                            <td class="text-end fw-600">{{ $m($s['sold']) }}</td>
                            <td class="text-end">{{ number_format($s['opps']) }}</td>
                            <td class="text-end">{{ number_format($s['accounts']) }}</td>
                            @foreach($plan['matrix']['groups'] as $g)
                                @foreach($g['products'] as $p)
                                    @php $c = $s['cells'][$p['cap']] ?? null; @endphp
                                    <td class="text-center">
                                        @if($c)
                                            <div class="fw-600 fs-sm">{{ $mc($c['v']) }}</div>
                                            <div class="text-muted fs-xs">{{ number_format($c['n']) }} opps</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center py-3">No opportunities in the current scope.</td></tr>
                    @endforelse
                </tbody>
                @if(! empty($plan['matrix']['stages']))
                    <tfoot class="table-dark">
                        <tr>
                            <td class="fw-700">TOTAL</td>
                            <td class="text-end fw-700">{{ $m($plan['matrix']['total']['sold']) }}</td>
                            <td class="text-end fw-700">{{ number_format($plan['matrix']['total']['opps']) }}</td>
                            <td class="text-end fw-700">{{ number_format($plan['matrix']['total']['accounts']) }}</td>
                            @foreach($plan['matrix']['groups'] as $g)
                                @foreach($g['products'] as $p)
                                    @php $c = $plan['matrix']['total']['cells'][$p['cap']] ?? null; @endphp
                                    <td class="text-center">
                                        @if($c)
                                            <div class="fw-700 fs-sm">{{ $mc($c['v']) }}</div>
                                            <div class="fs-xs opacity-75">{{ number_format($c['n']) }}</div>
                                        @else
                                            <span class="opacity-50">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            @endforeach
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
    <div class="fs-xs text-muted mt-1">Cells show $ACV sold with the opportunity count beneath; an empty cell is "—". A deal spanning several products appears in each of its columns, so columns sum higher than the row total. Scroll right for the full product set.</div>

    {{-- Practice role coverage: committed vs certified --}}
    <div class="d-flex align-items-center justify-content-between mt-4 mb-2">
        <h5 class="fw-600 mb-0">Practice role coverage</h5>
        @unless($editablePartner)
            <span class="fs-sm text-muted">Select a single partner in <strong>Filters</strong> to edit commitments.</span>
        @endunless
    </div>
    <div class="row g-3">
        @foreach($roleOrder as $role)
            @php
                $r = $plan['roles'][$role] ?? ['certified' => 0, 'committed' => 0, 'to_assign' => 0];
                $committed = $r['committed'] ?? 0;
                $certified = $r['certified'] ?? 0;
                $pct = $committed > 0 ? min(100, round($certified / $committed * 100)) : ($certified > 0 ? 100 : 0);
                $met = $committed > 0 && $certified >= $committed;
            @endphp
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-600">{{ $role }}</span>
                            <span class="badge rounded-pill {{ $met ? 'bg-success-subtle text-success' : ($committed > 0 ? 'bg-warning-subtle text-warning' : 'bg-body-secondary text-muted') }}">
                                {{ $committed > 0 ? $pct.'% covered' : 'no commit' }}
                            </span>
                        </div>
                        <div class="d-flex gap-4 mb-2">
                            <div><div class="fs-3 fw-700">{{ number_format($certified) }}</div><div class="fs-xs text-muted text-uppercase">Certified</div></div>
                            <div><div class="fs-3 fw-700">{{ number_format($committed) }}</div><div class="fs-xs text-muted text-uppercase">Committed</div></div>
                            <div><div class="fs-3 fw-700 {{ ($r['to_assign'] ?? 0) > 0 ? 'text-warning' : '' }}">{{ number_format($r['to_assign'] ?? 0) }}</div><div class="fs-xs text-muted text-uppercase">To assign</div></div>
                        </div>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar {{ $met ? 'bg-success' : 'bg-primary' }}" style="width: {{ $pct }}%"></div>
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

    {{-- People committed by line of business (LOB × role matrix) --}}
    <h5 class="fw-600 mt-4 mb-2">People committed by line of business</h5>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Line of business</th>
                        <th class="text-end">Open pipeline</th>
                        @foreach($roleOrder as $role)
                            <th class="text-center">{{ $role }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php $totals = ['GTM' => 0, 'PreSales' => 0, 'Delivery' => 0]; @endphp
                    @forelse($plan['groups'] as $g)
                        @php $lob = $g['lob']; @endphp
                        <tr>
                            <td class="fw-500">{{ $lob }}</td>
                            <td class="text-end">
                                <span class="fw-600">{{ $m($g['open_v']) }}</span>
                                <span class="text-muted fs-xs d-block">{{ number_format($g['open_n']) }} deals</span>
                            </td>
                            @foreach($roleOrder as $role)
                                @php
                                    $val = $lobCommits[$lob][$role] ?? 0;
                                    $totals[$role] += $val;
                                @endphp
                                <td class="text-center">
                                    @if($editablePartner)
                                        <form method="POST" action="{{ route('partner-success.plan-commit') }}" class="d-inline-flex">
                                            @csrf
                                            <input type="hidden" name="partner_id" value="{{ $editablePartner->id }}">
                                            <input type="hidden" name="lob" value="{{ $lob }}">
                                            <input type="hidden" name="role" value="{{ $role }}">
                                            <input type="number" min="0" name="committed"
                                                   value="{{ $editableLobCommits[$lob][$role] ?? 0 }}"
                                                   onchange="this.form.submit()"
                                                   class="form-control form-control-sm text-center mx-auto" style="max-width:72px"
                                                   aria-label="{{ $role }} committed for {{ $lob }}">
                                        </form>
                                    @else
                                        <span class="fw-600">{{ number_format($val) }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted text-center py-3">No pipeline in the current scope.</td></tr>
                    @endforelse
                </tbody>
                @if(! empty($plan['groups']))
                    <tfoot class="border-top">
                        <tr class="fw-600">
                            <td>Practice total</td>
                            <td></td>
                            @foreach($roleOrder as $role)
                                <td class="text-center">{{ number_format($totals[$role]) }}</td>
                            @endforeach
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
    @if($editablePartner)
        <div class="fs-sm text-muted mt-2">Editing commitments for <strong>{{ $editablePartner->name }}</strong>. Changes save on edit.</div>
    @endif

    {{-- Capability coverage (sales) --}}
    <h5 class="fw-600 mt-4 mb-2">Capability coverage <span class="fs-sm fw-normal text-muted">— products in the pipeline</span></h5>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Line of business</th>
                        <th class="text-end">Open deals</th>
                        <th class="text-end">Open $ACV</th>
                        <th class="text-end">Won</th>
                        <th class="text-end">Won $ACV</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plan['caps'] as $c)
                        <tr>
                            <td class="fw-500">{{ $c['cap'] }}</td>
                            <td><span class="badge bg-body-secondary text-body border fw-normal">{{ $c['group'] }}</span></td>
                            <td class="text-end">{{ number_format($c['open_n']) }}</td>
                            <td class="text-end">{{ $m($c['open_v']) }}</td>
                            <td class="text-end">{{ number_format($c['won_n']) }}</td>
                            <td class="text-end">{{ $m($c['won_v']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted text-center py-3">No products in the current scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

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
