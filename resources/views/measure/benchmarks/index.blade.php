@extends('layouts.vertical', ['pageTitle' => 'Benchmarks'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Benchmarks', 'pageSubTitle1' => 'Measure', 'pageSubTitle1Url' => route('measure.index')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Benchmark Comparisons show how each partner ranks against the portfolio across key dimensions. Partners can see where they stand relative to peers without revealing individual data — helping identify strengths to build on and areas for improvement.</p>

@if(empty($benchmarks))
<div class="card shadow-0 mb-g">
    <div class="card-body text-center py-5">
        <svg class="sa-icon text-muted mb-2" style="width:48px;height:48px"><use href="/icons/sprite.svg#bar-chart"></use></svg>
        <h5 class="text-muted">No benchmark data available</h5>
        <p class="text-muted mb-0">Benchmarks require scorecard data and partner activity to compute rankings.</p>
    </div>
</div>
@else

{{-- Portfolio Overview Table --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Portfolio Benchmark Rankings</div></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th class="text-center">Overall</th>
                        @foreach($dimensions as $dimKey => $dimLabel)
                        <th class="text-center">{{ $dimLabel }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($partners as $partner)
                    @php $bm = $benchmarks[$partner->id] ?? null; @endphp
                    @if($bm)
                    <tr>
                        <td>
                            <a href="{{ route('measure.benchmarks.partner', $partner) }}" class="text-decoration-none fw-500">{{ $partner->name }}</a>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ \App\Services\BenchmarkService::QUARTILE_COLORS[$bm['overall_quartile']] }}">
                                {{ $bm['overall_percentile'] }}%
                            </span>
                        </td>
                        @foreach($dimensions as $dimKey => $dimLabel)
                            @php $d = $bm['dimensions'][$dimKey] ?? null; @endphp
                            <td class="text-center">
                                @if($d)
                                <span class="badge bg-{{ \App\Services\BenchmarkService::QUARTILE_COLORS[$d['quartile']] }}" title="{{ $d['quartile_label'] }}">
                                    {{ $d['percentile'] }}%
                                </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Quartile Legend --}}
<div class="d-flex gap-3 mb-g justify-content-center">
    @foreach(\App\Services\BenchmarkService::QUARTILE_LABELS as $q => $label)
    <span class="badge bg-{{ \App\Services\BenchmarkService::QUARTILE_COLORS[$q] }}">{{ $label }}</span>
    @endforeach
</div>

@endif
@endsection
