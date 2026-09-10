@extends('layouts.vertical', ['pageTitle' => 'Scorecard'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Scorecard', 'pageSubTitle1' => 'Plan', 'pageSubTitle1Url' => route('plan.scorecard.index'), 'pageSubText' => $partner->name])
@endsection

@php
    $fmt = fn ($v) => $v === null ? '—' : number_format((float) $v, 1);
    $dimensions = [
        'certification_score' => 'Certification', 'pipeline_score' => 'Pipeline',
        'delivery_score' => 'Delivery', 'engagement_score' => 'Engagement',
        'growth_score' => 'Growth', 'self_sufficiency_score' => 'Self-sufficiency',
    ];
@endphp

@section('content')
    @if(! $latest)
        <div class="card shadow-0"><div class="card-body text-muted fst-italic">No scorecards recorded for {{ $partner->name }} yet.</div></div>
    @else
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card shadow-0 h-100"><div class="card-body">
                    <div class="fs-xs text-uppercase text-muted">Overall</div>
                    <div class="fs-1 fw-700">{{ $fmt($latest->overall_score) }}</div>
                    <div class="fs-sm text-muted">Tier: {{ $latest->tier ?? '—' }}</div>
                    <div class="fs-xs text-muted">Scored {{ \Illuminate\Support\Str::of((string) $latest->scored_at)->substr(0, 10) }}</div>
                </div></div>
            </div>
            @foreach($dimensions as $col => $label)
                <div class="col-md-3 col-xl-2">
                    <div class="card shadow-0 h-100"><div class="card-body">
                        <div class="fs-xs text-uppercase text-muted">{{ $label }}</div>
                        <div class="fs-3 fw-700">{{ $fmt($latest->$col) }}</div>
                    </div></div>
                </div>
            @endforeach
        </div>

        <div class="card shadow-0">
            <div class="card-header"><div class="card-title">History (last {{ $scorecards->count() }})</div></div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Scored</th><th class="text-end">Overall</th>
                            @foreach($dimensions as $label)<th class="text-end">{{ $label }}</th>@endforeach
                            <th>Tier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scorecards as $sc)
                            <tr>
                                <td>{{ \Illuminate\Support\Str::of((string) $sc->scored_at)->substr(0, 10) }}</td>
                                <td class="text-end fw-600">{{ $fmt($sc->overall_score) }}</td>
                                @foreach(array_keys($dimensions) as $col)<td class="text-end">{{ $fmt($sc->$col) }}</td>@endforeach
                                <td>{{ $sc->tier ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
