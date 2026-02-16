@extends('layouts.vertical', ['pageTitle' => 'Scorecard Weights'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Scorecard Weights', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Scorecard Weights control how the six scoring dimensions — certification, pipeline, delivery, engagement, growth, and self-sufficiency — combine into each partner's overall health score. Adjust the weights here to reflect your program's current priorities. Changes take effect immediately across all partner scorecards.</p>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Dimension Weights</div></div>
            <div class="card-body">
                <p class="text-muted fs-sm mb-3">Adjust the weights for each scorecard dimension. Weights must sum to 100%. These weights determine how individual dimension scores combine into the overall partner score.</p>

                <form action="{{ route('admin.scorecard-weights.update') }}" method="POST">
                    @csrf @method('PUT')
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>Dimension</th>
                                <th>Description</th>
                                <th class="text-center" style="width:120px">Weight (%)</th>
                                <th class="text-center" style="width:80px">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($weights as $weight)
                            <tr>
                                <td>
                                    <strong>{{ \App\Models\ScorecardWeight::DIMENSIONS[$weight->dimension] ?? ucfirst($weight->dimension) }}</strong>
                                    <input type="hidden" name="dimensions[]" value="{{ $weight->dimension }}">
                                </td>
                                <td>
                                    <small class="text-muted">
                                        @switch($weight->dimension)
                                            @case('certification') Measures partner team readiness via active certifications @break
                                            @case('pipeline') Evaluates pipeline health, coverage, and deal progression @break
                                            @case('delivery') Assesses project delivery quality, CSAT, and on-time completion @break
                                            @case('engagement') Tracks engagement frequency and participation depth @break
                                            @case('growth') Monitors revenue trajectory and expansion patterns @break
                                            @case('self_sufficiency') Gauges partner independence in operations and support @break
                                        @endswitch
                                    </small>
                                </td>
                                <td class="text-center">
                                    <input type="number" name="weights[]" class="form-control form-control-sm text-center weight-input"
                                           value="{{ round($weight->weight * 100) }}" min="0" max="100" step="1" required>
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" name="active[{{ $weight->dimension }}]" value="1"
                                           class="form-check-input" {{ $weight->is_active ? 'checked' : '' }}>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-500">
                                <td colspan="2" class="text-end">Total:</td>
                                <td class="text-center"><span id="weightTotal">{{ round($weights->sum('weight') * 100) }}</span>%</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted" id="weightWarning"></small>
                        <button type="submit" class="btn btn-primary btn-sm" id="saveBtn">Save Weights</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Weight Distribution</div></div>
            <div class="card-body">
                <div id="weights-chart" style="height: 300px;"></div>
            </div>
        </div>

        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Guidelines</div></div>
            <div class="card-body">
                <ul class="list-unstyled fs-sm text-muted">
                    <li class="mb-2">Weights must sum to <strong>100%</strong></li>
                    <li class="mb-2">Changes take effect on the next scorecard recalculation</li>
                    <li class="mb-2">Disabling a dimension sets its effective weight to 0 and redistributes proportionally</li>
                    <li class="mb-2">Default weights: Certification 15%, Pipeline 20%, Delivery 20%, Engagement 15%, Growth 20%, Self-Sufficiency 10%</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var inputs = document.querySelectorAll('.weight-input');
    var totalEl = document.getElementById('weightTotal');
    var warningEl = document.getElementById('weightWarning');
    var saveBtn = document.getElementById('saveBtn');

    function updateTotal() {
        var sum = 0;
        inputs.forEach(function(input) { sum += parseInt(input.value) || 0; });
        totalEl.textContent = sum;
        if (sum !== 100) {
            warningEl.textContent = 'Weights must total 100% (currently ' + sum + '%)';
            warningEl.className = 'text-danger fs-sm';
            saveBtn.disabled = true;
        } else {
            warningEl.textContent = '';
            saveBtn.disabled = false;
        }
        updateChart();
    }

    inputs.forEach(function(input) { input.addEventListener('input', updateTotal); });

    // Donut chart
    var chart;
    function updateChart() {
        var labels = [];
        var series = [];
        document.querySelectorAll('input[name="dimensions[]"]').forEach(function(dim, i) {
            labels.push(dim.value.charAt(0).toUpperCase() + dim.value.slice(1).replace('_', ' '));
            series.push(parseInt(inputs[i].value) || 0);
        });
        if (chart) chart.destroy();
        chart = new ApexCharts(document.querySelector('#weights-chart'), {
            chart: { type: 'donut', height: 300 },
            series: series,
            labels: labels,
            colors: ['#17a2b8','#198754','#ffc107','#0d6efd','#28a745','#6c757d']
        });
        chart.render();
    }
    updateChart();
});
</script>
@endsection
