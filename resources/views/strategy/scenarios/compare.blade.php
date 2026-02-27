@extends('layouts.vertical', ['pageTitle' => 'Scenario Comparison'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Scenario Comparison', 'pageSubTitle1' => 'Strategy', 'pageSubTitle1Url' => route('strategy.dashboard'), 'pageSubTitle2' => 'Scenarios', 'pageSubTitle2Url' => route('strategy.scenarios.index')])
@endsection

@section('content')
<div class="mt-3 mb-4">
    <p class="text-muted fst-italic">Compare scenarios side-by-side to evaluate different strategic approaches. Select scenarios to include in the comparison.</p>
</div>

{{-- Scenario Selector --}}
<div class="card shadow-0 mb-g">
    <div class="card-body">
        <form method="GET" action="{{ route('strategy.scenarios.compare') }}" class="d-flex gap-2 align-items-end flex-wrap">
            @foreach($allScenarios as $s)
            <label class="btn {{ $scenarios->contains('id', $s->id) ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm">
                <input type="checkbox" name="scenarios[]" value="{{ $s->id }}" class="d-none" {{ $scenarios->contains('id', $s->id) ? 'checked' : '' }}>
                {{ $s->name }}
            </label>
            @endforeach
            <button type="submit" class="btn btn-dark btn-sm">Update Comparison</button>
        </form>
    </div>
</div>

@if($scenarios->isEmpty())
    <div class="text-center text-muted py-5">Select at least one scenario to compare.</div>
@else
{{-- Target Comparison --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title mb-0">Target Comparison</div></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Metric</th>
                        <th class="text-end">Base Plan</th>
                        @foreach($scenarios as $scenario)
                        <th class="text-end">{{ $scenario->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($comparisonTargets as $row)
                    <tr>
                        <td><span class="badge bg-{{ \App\Models\StrategyPlanTarget::CATEGORY_COLORS[$row['category']] ?? 'secondary' }}">{{ \App\Models\StrategyPlanTarget::CATEGORIES[$row['category']] ?? $row['category'] }}</span></td>
                        <td class="fw-500">{{ $row['metric_label'] }}</td>
                        <td class="text-end text-muted">
                            @if($row['unit'] === 'currency')
                                ${{ number_format($row['base_value']) }}
                            @elseif($row['unit'] === 'percentage')
                                {{ number_format($row['base_value'], 1) }}%
                            @else
                                {{ number_format($row['base_value']) }}
                            @endif
                        </td>
                        @foreach($scenarios as $scenario)
                        @php $sv = $row['scenarios'][$scenario->id] ?? null; @endphp
                        <td class="text-end {{ $sv && $sv['has_override'] ? 'fw-500' : '' }}">
                            @if($sv)
                                @php
                                    $val = $sv['value'];
                                    $delta = $val - $row['base_value'];
                                @endphp
                                @if($row['unit'] === 'currency')
                                    ${{ number_format($val) }}
                                @elseif($row['unit'] === 'percentage')
                                    {{ number_format($val, 1) }}%
                                @else
                                    {{ number_format($val) }}
                                @endif
                                @if($sv['has_override'] && $delta != 0)
                                    <br><small class="text-{{ $delta > 0 ? 'success' : 'danger' }}">{{ $delta > 0 ? '+' : '' }}{{ $row['unit'] === 'currency' ? '$' . number_format($delta) : number_format($delta, 1) }}</small>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<script>
document.querySelectorAll('input[name="scenarios[]"]').forEach(cb => {
    cb.addEventListener('change', function() {
        this.closest('label').classList.toggle('btn-primary', this.checked);
        this.closest('label').classList.toggle('btn-outline-secondary', !this.checked);
    });
});
</script>
@endsection
