@extends('layouts.vertical', ['pageTitle' => 'Guided Analysis'])

@section('css')
<style>
    /* Wizard step indicator */
    .wizard-steps { z-index: 1; }
    .wizard-line {
        position: absolute;
        top: 20px;
        left: 10%;
        right: 10%;
        height: 3px;
        background: var(--bs-border-color, #e0e0e0);
        z-index: 0;
    }
    .wizard-step {
        text-align: center;
        z-index: 1;
        flex: 1;
    }
    .wizard-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: var(--bs-tertiary-bg, #e0e0e0);
        color: var(--bs-secondary-color, #999);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s ease;
        border: 3px solid var(--bs-border-color, #e0e0e0);
    }
    .wizard-step.active .wizard-circle {
        background: var(--bs-primary);
        color: #fff;
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.2);
    }
    .wizard-step.completed .wizard-circle {
        background: #28a745;
        color: #fff;
        border-color: #28a745;
    }
    .wizard-label {
        margin-top: 8px;
        font-size: 0.75rem;
        color: var(--bs-secondary-color, #999);
        font-weight: 500;
    }
    .wizard-step.active .wizard-label,
    .wizard-step.completed .wizard-label {
        color: var(--bs-body-color);
    }
    /* Panel fade transition */
    .wizard-panel { animation: wizFadeIn 0.3s ease-in; }
    @keyframes wizFadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* Scope cards */
    .scope-btn.active { background: var(--bs-primary) !important; color: #fff !important; border-color: var(--bs-primary) !important; }

    /* Heatmap dots */
    .heatmap-dot {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: 50%;
        font-size: 0.7rem; font-weight: 700; color: #fff;
    }
    .heatmap-dot-green { background-color: #28a745; }
    .heatmap-dot-yellow { background-color: #ffc107; color: #212529; }
    .heatmap-dot-red { background-color: #dc3545; }
    .heatmap-dot-gray { background-color: #dee2e6; color: #6c757d; }

    /* Risk badges */
    .risk-badge { display: inline-block; padding: 0.35em 0.65em; font-size: 0.75rem; font-weight: 700; border-radius: 0.25rem; }
    .risk-low { background-color: #28a745; color: white; }
    .risk-medium { background-color: #ffc107; color: #212529; }
    .risk-high { background-color: #fd7e14; color: white; }
    .risk-critical { background-color: #dc3545; color: white; }

    .bg-success-50 { background-color: rgba(40, 167, 69, 0.1); }
    .bg-warning-50 { background-color: rgba(255, 193, 7, 0.1); }
    .bg-danger-50 { background-color: rgba(220, 53, 69, 0.1); }
    .bg-dark-50 { background-color: rgba(52, 58, 64, 0.1); }
    .fs-xxl { font-size: 2.5rem; }

    /* Action item cards */
    .action-item { border-left: 4px solid var(--bs-primary); transition: all 0.2s; }
    .action-item:hover { transform: translateX(4px); }
    .action-item.priority-high { border-left-color: #dc3545; }
    .action-item.priority-medium { border-left-color: #ffc107; }
    .action-item.priority-low { border-left-color: #28a745; }
</style>
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Guided Analysis', 'pageSubTitle1' => 'Analysis', 'pageSubTitle1Url' => route('analysis.index')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Guided Analysis walks you through a step-by-step workflow to answer common partner management questions — from "which partners need the most attention?" to "where are my biggest growth opportunities?" Follow the wizard to select your question, set your parameters, and get a focused analysis with clear next-step recommendations.</p>

    <!-- Wizard Step Indicator -->
    <div class="card shadow-0 mb-g">
        <div class="card-body py-3">
            <div class="wizard-steps d-flex justify-content-between align-items-center position-relative">
                <div class="wizard-line"></div>
                <div class="wizard-step active" data-step="1">
                    <div class="wizard-circle">1</div>
                    <div class="wizard-label">Select Scope</div>
                </div>
                <div class="wizard-step" data-step="2">
                    <div class="wizard-circle">2</div>
                    <div class="wizard-label">Portfolio Overview</div>
                </div>
                <div class="wizard-step" data-step="3">
                    <div class="wizard-circle">3</div>
                    <div class="wizard-label">Resources</div>
                </div>
                <div class="wizard-step" data-step="4">
                    <div class="wizard-circle">4</div>
                    <div class="wizard-label">Risk Analysis</div>
                </div>
                <div class="wizard-step" data-step="5">
                    <div class="wizard-circle">5</div>
                    <div class="wizard-label">Action Plan</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== STEP 1: SELECT SCOPE ====== -->
    <div class="wizard-panel" id="step-1">
        <div class="row">
            <div class="col-xl-6">
                <div class="panel panel-icon" id="panel-scope">
                    <div class="panel-hdr">
                        <h2><i class="fal fa-crosshairs me-2"></i> Select <span class="fw-light"><i>Partners</i></span></h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <label class="form-label fw-500 mb-2">Quick Scope</label>
                            <div class="btn-group d-flex mb-4" role="group">
                                <button type="button" class="btn btn-outline-primary scope-btn active" data-scope="all">All My Partners</button>
                                <button type="button" class="btn btn-outline-primary scope-btn" data-scope="geography">By Geography</button>
                                <button type="button" class="btn btn-outline-primary scope-btn" data-scope="custom">Custom</button>
                            </div>

                            <div class="d-none mb-3" id="geo-selector">
                                <label class="form-label fw-500">Geography</label>
                                @foreach($geographies as $geo)
                                <div class="form-check">
                                    <input class="form-check-input wizard-geo" type="checkbox" id="wgeo-{{ Str::slug($geo) }}" value="{{ $geo }}" checked>
                                    <label class="form-check-label" for="wgeo-{{ Str::slug($geo) }}">{{ $geo }}</label>
                                </div>
                                @endforeach
                            </div>

                            <div class="d-none mb-3" id="partner-selector">
                                <label class="form-label fw-500">Partners</label>
                                @foreach($partners as $partner)
                                <div class="form-check">
                                    <input class="form-check-input wizard-partner" type="checkbox" id="wp-{{ $partner->id }}" value="{{ $partner->id }}" data-geography="{{ $partner->geography }}" checked>
                                    <label class="form-check-label" for="wp-{{ $partner->id }}">{{ $partner->name }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="panel panel-icon" id="panel-filters">
                    <div class="panel-hdr">
                        <h2><i class="fal fa-sliders-h me-2"></i> Filter <span class="fw-light"><i>Options</i></span></h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <label class="form-label fw-500 mb-2">Line of Business</label>
                            <div class="mb-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input wizard-lob" type="checkbox" id="wlob-ifp" value="IFP" checked>
                                    <label class="form-check-label" for="wlob-ifp">IFP (Finance)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input wizard-lob" type="checkbox" id="wlob-spm" value="SPM" checked>
                                    <label class="form-check-label" for="wlob-spm">SPM (Sales & Marketing)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input wizard-lob" type="checkbox" id="wlob-ind" value="I&D" checked>
                                    <label class="form-check-label" for="wlob-ind">I&D (Supply Chain)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input wizard-lob" type="checkbox" id="wlob-owp" value="OWP" checked>
                                    <label class="form-check-label" for="wlob-owp">OWP (HR & Workforce)</label>
                                </div>
                            </div>

                            <label class="form-label fw-500 mb-2">Fiscal Quarter Focus</label>
                            <select class="form-select mb-4" id="wizard-quarter">
                                <option value="current" selected>Current Quarter</option>
                                <option value="next">Next Quarter</option>
                                <option value="all">All Quarters</option>
                            </select>

                            <div class="alert alert-info border-0" id="scope-summary">
                                <i class="fal fa-info-circle me-1"></i>
                                <span id="scope-summary-text">Analyzing all {{ $partners->count() }} partners across all LOBs.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== STEP 2: PORTFOLIO OVERVIEW ====== -->
    <div class="wizard-panel d-none" id="step-2">
        <div class="row sortable-off" id="step2-kpis"></div>

        <div class="row">
            <div class="col-xl-8">
                <div class="panel panel-icon" id="panel-trajectory">
                    <div class="panel-hdr">
                        <h2><i class="fal fa-chart-area me-2"></i> Pipeline <span class="fw-light"><i>Trajectory</i></span></h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div id="step2-pipeline-chart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="panel panel-icon" id="panel-stages">
                    <div class="panel-hdr">
                        <h2><i class="fal fa-funnel-dollar me-2"></i> Stage <span class="fw-light"><i>Distribution</i></span></h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div id="step2-stage-chart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="panel panel-icon" id="panel-top-partners">
                    <div class="panel-hdr">
                        <h2><i class="fal fa-trophy me-2"></i> Top Partners <span class="fw-light"><i>by Pipeline</i></span></h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Partner</th>
                                            <th class="text-end">Opportunities</th>
                                            <th class="text-end">Pipeline Value</th>
                                            <th class="text-end">Avg Deal</th>
                                            <th class="text-end">Close Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody id="step2-partner-tbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== STEP 3: RESOURCE ASSESSMENT ====== -->
    <div class="wizard-panel d-none" id="step-3">
        <div class="row sortable-off" id="step3-res-cards"></div>

        <div class="row">
            <div class="col-xl-8">
                <div class="panel panel-icon" id="panel-heatmap">
                    <div class="panel-hdr">
                        <h2><i class="fal fa-th me-2"></i> Resource Utilization <span class="fw-light"><i>Heatmap</i></span></h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Partner</th>
                                            <th class="text-center">IFP</th>
                                            <th class="text-center">SPM</th>
                                            <th class="text-center">I&D</th>
                                            <th class="text-center">OWP</th>
                                            <th class="text-center fw-bold">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="step3-heatmap-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="panel panel-icon panel-locked" id="panel-blueprint">
                    <div class="panel-hdr">
                        <h2> Blueprint <span class="fw-light"><i>Completion</i></span></h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div id="step3-blueprint-chart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== STEP 4: RISK ANALYSIS ====== -->
    <div class="wizard-panel d-none" id="step-4">
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="text-center p-3 bg-success-50 rounded">
                    <div class="fs-xxl fw-bold text-success" id="risk-low-count">-</div>
                    <div class="text-muted">Low Risk</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-3 bg-warning-50 rounded">
                    <div class="fs-xxl fw-bold text-warning" id="risk-medium-count">-</div>
                    <div class="text-muted">Medium Risk</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-3 bg-danger-50 rounded">
                    <div class="fs-xxl fw-bold text-danger" id="risk-high-count">-</div>
                    <div class="text-muted">High Risk</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-3 bg-dark-50 rounded">
                    <div class="fs-xxl fw-bold" id="risk-critical-count">-</div>
                    <div class="text-muted">Critical</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6">
                <div class="panel panel-icon" id="panel-product-risk">
                    <div class="panel-hdr">
                        <h2><i class="fal fa-shield-alt me-2"></i> Product Area <span class="fw-light"><i>Risk</i></span></h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content" id="step4-product-risk"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="panel panel-icon" id="panel-risk-ranked">
                    <div class="panel-hdr">
                        <h2><i class="fal fa-sort-amount-down me-2"></i> Risk-Ranked <span class="fw-light"><i>Partners</i></span></h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content" id="step4-risk-ranked"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== STEP 5: ACTION PLAN ====== -->
    <div class="wizard-panel d-none" id="step-5">
        <div class="row">
            <div class="col-xl-8">
                <div class="panel panel-icon" id="panel-actions">
                    <div class="panel-hdr">
                        <h2><i class="fal fa-clipboard-check me-2"></i> Prioritized <span class="fw-light"><i>Action Items</i></span></h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content" id="step5-actions"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="panel panel-icon" id="panel-snapshot">
                    <div class="panel-hdr">
                        <h2><i class="fal fa-clipboard-list me-2"></i> Key <span class="fw-light"><i>Findings</i></span></h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content" id="step5-snapshot"></div>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-outline-primary waves-effect waves-themed" onclick="window.print()">
                        <i class="fal fa-print me-1"></i> Print Summary
                    </button>
                    <button class="btn btn-outline-secondary waves-effect waves-themed" id="btn-restart">
                        <i class="fal fa-redo me-1"></i> Start Over
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="d-flex justify-content-between mt-3 mb-4">
        <button class="btn btn-outline-secondary btn-lg waves-effect waves-themed d-none" id="wizard-prev">
            <i class="fal fa-arrow-left me-1"></i> Back
        </button>
        <div class="ms-auto">
            <button class="btn btn-primary btn-lg waves-effect waves-themed" id="wizard-next">
                Next <i class="fal fa-arrow-right ms-1"></i>
            </button>
        </div>
    </div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var allPartners = @json($partners);
    var geoRegionMap = @json($geoRegionMap);
    var opportunityHistory = @json($opportunityHistory);
    var PROGRAMS = ['IFP', 'SPM', 'I&D', 'OWP'];

    var state = { currentStep: 1, totalSteps: 5 };
    var charts = {};

    function fmt(val) {
        if (val >= 1e6) return '$' + (val / 1e6).toFixed(1) + 'M';
        if (val >= 1e3) return '$' + (val / 1e3).toFixed(0) + 'K';
        return '$' + Math.round(val).toLocaleString();
    }

    // ===================================================
    // WIZARD NAVIGATION
    // ===================================================
    function goToStep(step) {
        if (step < 1 || step > state.totalSteps) return;
        state.currentStep = step;

        document.querySelectorAll('.wizard-panel').forEach(function(p) { p.classList.add('d-none'); });
        var target = document.getElementById('step-' + step);
        if (target) target.classList.remove('d-none');

        document.querySelectorAll('.wizard-step').forEach(function(el) {
            var n = parseInt(el.dataset.step);
            el.classList.remove('active', 'completed');
            if (n === step) el.classList.add('active');
            else if (n < step) el.classList.add('completed');
        });

        var prevBtn = document.getElementById('wizard-prev');
        var nextBtn = document.getElementById('wizard-next');
        prevBtn.classList.toggle('d-none', step === 1);
        nextBtn.innerHTML = step === state.totalSteps
            ? '<i class="fal fa-check me-1"></i> Finish'
            : 'Next <i class="fal fa-arrow-right ms-1"></i>';

        if (step >= 2) renderStep(step);
    }

    document.getElementById('wizard-next').addEventListener('click', function() {
        if (state.currentStep < state.totalSteps) goToStep(state.currentStep + 1);
        else goToStep(1); // Finish loops back
    });
    document.getElementById('wizard-prev').addEventListener('click', function() {
        if (state.currentStep > 1) goToStep(state.currentStep - 1);
    });
    document.getElementById('btn-restart').addEventListener('click', function() { goToStep(1); });

    // ===================================================
    // SCOPE SELECTION (Step 1)
    // ===================================================
    document.querySelectorAll('.scope-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.scope-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            var scope = this.dataset.scope;
            document.getElementById('geo-selector').classList.toggle('d-none', scope !== 'geography');
            document.getElementById('partner-selector').classList.toggle('d-none', scope !== 'custom');
            updateScopeSummary();
        });
    });

    document.querySelectorAll('.wizard-geo, .wizard-partner, .wizard-lob').forEach(function(el) {
        el.addEventListener('change', updateScopeSummary);
    });

    function updateScopeSummary() {
        var partners = getFilteredPartners();
        var lobs = getSelectedLOBs();
        document.getElementById('scope-summary-text').textContent =
            'Analyzing ' + partners.length + ' partner(s) across ' + lobs.join(', ') + '.';
    }

    function getFilteredPartners() {
        var scope = (document.querySelector('.scope-btn.active') || {}).dataset || {};
        var scopeType = scope.scope || 'all';

        if (scopeType === 'all') return allPartners;
        if (scopeType === 'geography') {
            var geos = Array.from(document.querySelectorAll('.wizard-geo:checked')).map(function(el) { return el.value; });
            return allPartners.filter(function(p) { return geos.indexOf(p.geography) !== -1; });
        }
        var ids = Array.from(document.querySelectorAll('.wizard-partner:checked')).map(function(el) { return parseInt(el.value); });
        return allPartners.filter(function(p) { return ids.indexOf(p.id) !== -1; });
    }

    function getSelectedLOBs() {
        return Array.from(document.querySelectorAll('.wizard-lob:checked')).map(function(el) { return el.value; });
    }

    // ===================================================
    // RENDER STEPS
    // ===================================================
    function renderStep(step) {
        var partners = getFilteredPartners();
        var allOpps = partners.flatMap(function(p) { return p.opportunities || []; });
        var allRes = partners.flatMap(function(p) { return p.resources || []; });
        var allProjects = partners.flatMap(function(p) { return p.projects || []; });

        if (step === 2) renderStep2(partners, allOpps, allRes);
        if (step === 3) renderStep3(partners, allRes);
        if (step === 4) renderStep4(partners, allOpps, allRes);
        if (step === 5) renderStep5(partners, allOpps, allRes, allProjects);
    }

    // ===================================================
    // STEP 2: Portfolio Overview
    // ===================================================
    function renderStep2(partners, allOpps, allRes) {
        var totalPipeline = allOpps.reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);
        var closedWon = allOpps.filter(function(o) { return o.stage && o.stage.toLowerCase() === 'closed won'; });
        var certifiedRes = allRes.filter(function(r) { return r.status && r.status.toLowerCase() === 'completed'; });

        // KPI cards
        document.getElementById('step2-kpis').innerHTML = buildKPI([
            { label: 'Partners in Scope', value: partners.length, icon: 'fad fa-building', color: 'primary' },
            { label: 'Total Opportunities', value: allOpps.length, icon: 'fad fa-bullseye-arrow', color: 'success' },
            { label: 'Pipeline Value', value: fmt(totalPipeline), icon: 'fad fa-chart-line', color: 'warning' },
            { label: 'Certified Resources', value: certifiedRes.length + '/' + allRes.length, icon: 'fad fa-user-graduate', color: 'info' }
        ]);

        // Pipeline trajectory chart
        if (charts.pipeline) charts.pipeline.destroy();
        var histDates = opportunityHistory.map(function(d) { return d.snapshot_date; });
        var histAmounts = opportunityHistory.map(function(d) { return Math.round(parseFloat(d.total_amount) / 1000) || 0; });
        if (histDates.length === 0) {
            histDates = ['No data'];
            histAmounts = [0];
        }
        charts.pipeline = new ApexCharts(document.getElementById('step2-pipeline-chart'), {
            chart: { type: 'area', height: 350, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Pipeline ($K)', data: histAmounts }],
            xaxis: { categories: histDates, labels: { rotate: -45, style: { fontSize: '11px' } } },
            yaxis: { labels: { formatter: function(v) { return '$' + v + 'K'; } } },
            colors: ['#886ab5'],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
            tooltip: { theme: 'dark' },
            grid: { strokeDashArray: 3 }
        });
        charts.pipeline.render();

        // Stage distribution donut
        if (charts.stages) charts.stages.destroy();
        var stageMap = {};
        allOpps.forEach(function(o) { var s = o.stage || 'Unknown'; stageMap[s] = (stageMap[s] || 0) + 1; });
        var stageLabels = Object.keys(stageMap);
        var stageValues = stageLabels.map(function(k) { return stageMap[k]; });
        if (stageLabels.length === 0) { stageLabels = ['No Data']; stageValues = [1]; }
        charts.stages = new ApexCharts(document.getElementById('step2-stage-chart'), {
            chart: { type: 'donut', height: 350, fontFamily: 'inherit' },
            series: stageValues,
            labels: stageLabels,
            colors: ['#886ab5', '#1dc9b7', '#ffc241', '#fd3995', '#2196F3', '#6c757d'],
            legend: { position: 'bottom', labels: { colors: 'var(--bs-body-color)' } },
            tooltip: { theme: 'dark' },
            stroke: { width: 1, colors: ['var(--bs-body-bg)'] },
            dataLabels: { enabled: false }
        });
        charts.stages.render();

        // Partner table
        var tbody = document.getElementById('step2-partner-tbody');
        tbody.innerHTML = '';
        var sorted = partners.slice().sort(function(a, b) {
            var av = (a.opportunities || []).reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);
            var bv = (b.opportunities || []).reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);
            return bv - av;
        });
        sorted.forEach(function(p) {
            var opps = p.opportunities || [];
            var pipeline = opps.reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);
            var won = opps.filter(function(o) { return o.stage && o.stage.toLowerCase() === 'closed won'; }).length;
            var cr = opps.length > 0 ? Math.round((won / opps.length) * 100) : 0;
            var avg = opps.length > 0 ? pipeline / opps.length : 0;
            tbody.innerHTML += '<tr><td><a href="/partners/' + p.id + '" class="text-primary fw-500">' + p.name + '</a></td>' +
                '<td class="text-end">' + opps.length + '</td><td class="text-end">' + fmt(pipeline) + '</td>' +
                '<td class="text-end">' + fmt(avg) + '</td><td class="text-end">' + cr + '%</td></tr>';
        });
    }

    // ===================================================
    // STEP 3: Resource Assessment
    // ===================================================
    function renderStep3(partners, allRes) {
        var delivery = allRes.filter(function(r) { return r.role && r.role.toLowerCase() === 'delivery'; });
        var sales = allRes.filter(function(r) { return r.role && r.role.toLowerCase() === 'sales'; });
        var preSales = allRes.filter(function(r) { return r.role && r.role.toLowerCase() === 'pre-sales'; });

        document.getElementById('step3-res-cards').innerHTML = buildKPI([
            { label: 'Delivery Resources', value: delivery.length, icon: 'fad fa-cogs', color: 'success' },
            { label: 'Sales Resources', value: sales.length, icon: 'fad fa-handshake', color: 'primary' },
            { label: 'Pre-Sales Resources', value: preSales.length, icon: 'fad fa-user-headset', color: 'info' },
            { label: 'Total Certified', value: allRes.filter(function(r) { return r.status && r.status.toLowerCase() === 'completed'; }).length + '/' + allRes.length, icon: 'fad fa-certificate', color: 'warning' }
        ]);

        // Heatmap
        var tbody = document.getElementById('step3-heatmap-body');
        tbody.innerHTML = '';
        partners.forEach(function(p) {
            var res = p.resources || [];
            var counts = {};
            PROGRAMS.forEach(function(pr) { counts[pr] = 0; });
            res.forEach(function(r) { if (r.program && counts.hasOwnProperty(r.program)) counts[r.program]++; });
            var total = res.length;
            var html = '<td class="fw-500">' + p.name + '</td>';
            PROGRAMS.forEach(function(pr) {
                var c = counts[pr];
                var cls = c >= 5 ? 'green' : c >= 2 ? 'yellow' : c >= 1 ? 'red' : 'gray';
                html += '<td class="text-center"><span class="heatmap-dot heatmap-dot-' + cls + '">' + c + '</span></td>';
            });
            html += '<td class="text-center fw-bold">' + total + '</td>';
            tbody.innerHTML += '<tr>' + html + '</tr>';
        });

        // Blueprint radar
        if (charts.blueprint) charts.blueprint.destroy();
        var categories = ['Finance', 'Supply Chain', 'HR & Workforce', 'Sales & Marketing'];
        var progIdx = { 'IFP': 0, 'I&D': 1, 'OWP': 2, 'SPM': 3 };
        var deliveryPct = [0, 0, 0, 0], salesPct = [0, 0, 0, 0];
        var deliveryTot = [0, 0, 0, 0], salesTot = [0, 0, 0, 0];
        allRes.forEach(function(r) {
            var idx = progIdx[r.program]; if (idx === undefined) return;
            var done = r.status && r.status.toLowerCase() === 'completed';
            if (r.role && r.role.toLowerCase() === 'delivery') { deliveryTot[idx]++; if (done) deliveryPct[idx]++; }
            else { salesTot[idx]++; if (done) salesPct[idx]++; }
        });
        var dPct = deliveryPct.map(function(v, i) { return deliveryTot[i] > 0 ? Math.round((v / deliveryTot[i]) * 100) : 0; });
        var sPct = salesPct.map(function(v, i) { return salesTot[i] > 0 ? Math.round((v / salesTot[i]) * 100) : 0; });
        if (dPct.every(function(v) { return v === 0; })) { dPct = [65, 59, 90, 81]; sPct = [28, 48, 40, 19]; }

        charts.blueprint = new ApexCharts(document.getElementById('step3-blueprint-chart'), {
            chart: { type: 'radar', height: 350, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Delivery', data: dPct }, { name: 'Sales', data: sPct }],
            xaxis: { categories: categories },
            yaxis: { max: 100, tickAmount: 5 },
            colors: ['#1dc9b7', '#886ab5'],
            stroke: { width: 0 }, fill: { opacity: 0.2 },
            markers: { size: 5, strokeColors: '#fff', strokeWidth: 2 },
            legend: { position: 'top', labels: { colors: 'var(--bs-body-color)' } },
            tooltip: { theme: 'dark', y: { formatter: function(v) { return v + '%'; } } }
        });
        charts.blueprint.render();
    }

    // ===================================================
    // STEP 4: Risk Analysis
    // ===================================================
    function renderStep4(partners, allOpps, allRes) {
        var totalPipeline = allOpps.reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);
        var totalResCount = allRes.length || 1;

        // Score each partner
        var scored = partners.map(function(p) {
            var opps = p.opportunities || [];
            var res = p.resources || [];
            var pipeline = opps.reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);
            var certified = res.filter(function(r) { return r.status && r.status.toLowerCase() === 'completed'; }).length;
            var pipeScore = Math.min(100, (pipeline / Math.max(1, 500000)) * 100);
            var resScore = res.length > 0 ? (certified / res.length) * 100 : 0;
            var overall = (pipeScore * 0.5) + (resScore * 0.5);
            var level = overall >= 70 ? 'Low' : overall >= 45 ? 'Medium' : overall >= 25 ? 'High' : 'Critical';
            return { partner: p, pipeline: pipeline, resources: res.length, certified: certified, overall: Math.round(overall), level: level };
        }).sort(function(a, b) { return a.overall - b.overall; });

        var counts = { Low: 0, Medium: 0, High: 0, Critical: 0 };
        scored.forEach(function(s) { counts[s.level]++; });
        document.getElementById('risk-low-count').textContent = counts.Low;
        document.getElementById('risk-medium-count').textContent = counts.Medium;
        document.getElementById('risk-high-count').textContent = counts.High;
        document.getElementById('risk-critical-count').textContent = counts.Critical;

        // Product area risk
        var paContainer = document.getElementById('step4-product-risk');
        paContainer.innerHTML = '';
        var paNames = { IFP: 'Finance', SPM: 'Sales & Marketing', 'I&D': 'Supply Chain', OWP: 'HR & Workforce' };
        var paIcons = { IFP: 'calculator', SPM: 'bullhorn', 'I&D': 'truck', OWP: 'users' };
        PROGRAMS.forEach(function(prog) {
            var res = allRes.filter(function(r) { return r.program === prog; });
            var share = res.length / totalResCount;
            var areaOpps = Math.max(1, Math.ceil(allOpps.length * share));
            var ratio = res.length > 0 ? (res.length / areaOpps).toFixed(1) : '0.0';
            var rn = parseFloat(ratio);
            var level = rn >= 2 ? 'Low' : rn >= 1 ? 'Medium' : rn >= 0.5 ? 'High' : 'Critical';
            var cls = rn >= 2 ? 'success' : rn >= 1 ? 'warning' : rn >= 0.5 ? 'warning' : 'danger';
            var atRisk = rn < 1 ? totalPipeline * share * (1 - rn) : 0;
            paContainer.innerHTML += '<div class="mb-3 p-3 border rounded" style="border-left: 4px solid var(--bs-' + cls + ') !important;">' +
                '<div class="d-flex align-items-center justify-content-between mb-2">' +
                '<div><i class="fal fa-' + paIcons[prog] + ' me-2 text-' + cls + '"></i><strong>' + paNames[prog] + '</strong> <small class="text-muted">(' + prog + ')</small></div>' +
                '<span class="risk-badge risk-' + level.toLowerCase() + '">' + level + '</span></div>' +
                '<div class="d-flex justify-content-between small text-muted"><span>Resources: ' + res.length + '</span><span>Ratio: ' + ratio + ':1</span><span>$ at Risk: ' + fmt(atRisk) + '</span></div></div>';
        });

        // Risk-ranked partners
        var rrContainer = document.getElementById('step4-risk-ranked');
        rrContainer.innerHTML = '';
        scored.forEach(function(s) {
            var cls = s.level === 'Low' ? 'success' : s.level === 'Medium' ? 'warning' : s.level === 'High' ? 'warning' : 'danger';
            rrContainer.innerHTML += '<div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">' +
                '<div><a href="/partners/' + s.partner.id + '" class="fw-500 text-primary">' + s.partner.name + '</a>' +
                '<div class="small text-muted">' + s.resources + ' resources, ' + fmt(s.pipeline) + ' pipeline</div></div>' +
                '<span class="risk-badge risk-' + s.level.toLowerCase() + '">' + s.level + '</span></div>';
        });
    }

    // ===================================================
    // STEP 5: Action Plan
    // ===================================================
    function renderStep5(partners, allOpps, allRes, allProjects) {
        var actions = [];
        var totalPipeline = allOpps.reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);

        // Generate data-driven actions
        partners.forEach(function(p) {
            var opps = p.opportunities || [];
            var res = p.resources || [];
            var deliveryRes = res.filter(function(r) { return r.role && r.role.toLowerCase() === 'delivery'; });
            var closingOpps = opps.filter(function(o) { return o.close_date && !o.stage?.toLowerCase().includes('closed'); });

            if (closingOpps.length > 0 && deliveryRes.length === 0) {
                actions.push({ priority: 'high', partner: p.name, id: p.id,
                    title: 'No delivery resources for ' + p.name,
                    desc: closingOpps.length + ' open opportunities but zero delivery resources. Schedule enablement review immediately.',
                    icon: 'exclamation-triangle' });
            }
            if (opps.length === 0) {
                actions.push({ priority: 'medium', partner: p.name, id: p.id,
                    title: 'Empty pipeline for ' + p.name,
                    desc: 'No active opportunities. Consider a joint prospecting session or pipeline-building workshop.',
                    icon: 'chart-line' });
            }
            var certified = res.filter(function(r) { return r.status && r.status.toLowerCase() === 'completed'; });
            if (res.length > 3 && certified.length / res.length < 0.3) {
                actions.push({ priority: 'medium', partner: p.name, id: p.id,
                    title: 'Low certification rate for ' + p.name,
                    desc: 'Only ' + Math.round((certified.length / res.length) * 100) + '% of resources are certified. Prioritize training paths.',
                    icon: 'user-graduate' });
            }
        });

        actions.push({ priority: 'low', partner: '', id: null,
            title: 'Refresh pipeline data',
            desc: 'Run a fresh Salesforce import to ensure all metrics are current.',
            icon: 'sync' });

        actions.sort(function(a, b) {
            var ord = { high: 0, medium: 1, low: 2 };
            return (ord[a.priority] || 2) - (ord[b.priority] || 2);
        });

        var actContainer = document.getElementById('step5-actions');
        actContainer.innerHTML = '';
        actions.forEach(function(a) {
            actContainer.innerHTML += '<div class="action-item priority-' + a.priority + ' p-3 mb-3 rounded bg-body-tertiary">' +
                '<div class="d-flex align-items-start">' +
                '<div class="me-3 mt-1"><i class="fal fa-' + a.icon + ' fa-lg text-' + (a.priority === 'high' ? 'danger' : a.priority === 'medium' ? 'warning' : 'success') + '"></i></div>' +
                '<div class="flex-1">' +
                '<div class="d-flex justify-content-between align-items-start mb-1">' +
                '<h6 class="mb-0 fw-700">' + a.title + '</h6>' +
                '<span class="risk-badge risk-' + (a.priority === 'high' ? 'critical' : a.priority === 'medium' ? 'medium' : 'low') + '">' + a.priority.toUpperCase() + '</span>' +
                '</div>' +
                '<p class="text-muted mb-0 small">' + a.desc + '</p>' +
                '</div></div></div>';
        });

        // Snapshot summary
        var snapshot = document.getElementById('step5-snapshot');
        var closedWon = allOpps.filter(function(o) { return o.stage && o.stage.toLowerCase() === 'closed won'; });
        var closeRate = allOpps.length > 0 ? Math.round((closedWon.length / allOpps.length) * 100) : 0;
        snapshot.innerHTML =
            '<ul class="list-unstyled mb-0">' +
            '<li class="mb-3"><i class="fal fa-building me-2 text-primary"></i> <strong>' + partners.length + '</strong> partners analyzed</li>' +
            '<li class="mb-3"><i class="fal fa-bullseye-arrow me-2 text-success"></i> <strong>' + allOpps.length + '</strong> opportunities, <strong>' + fmt(totalPipeline) + '</strong> total pipeline</li>' +
            '<li class="mb-3"><i class="fal fa-percentage me-2 text-info"></i> <strong>' + closeRate + '%</strong> close rate</li>' +
            '<li class="mb-3"><i class="fal fa-users me-2 text-warning"></i> <strong>' + allRes.length + '</strong> resources across portfolio</li>' +
            '<li class="mb-3"><i class="fal fa-tasks me-2 text-primary"></i> <strong>' + allProjects.length + '</strong> active projects</li>' +
            '<li><i class="fal fa-exclamation-circle me-2 text-danger"></i> <strong>' + actions.filter(function(a) { return a.priority === 'high'; }).length + '</strong> high-priority actions identified</li>' +
            '</ul>';
    }

    // ===================================================
    // HELPERS
    // ===================================================
    function buildKPI(items) {
        return items.map(function(item) {
            return '<div class="col-xs-12 col-sm-6 col-xl-3 mb-g"><div class="card"><div class="row">' +
                '<div class="col-6"><div class="p-3"><p class="p-0 mb-3 text-muted text-truncate">' + item.label + '</p>' +
                '<span class="h5 text-nowrap">' + item.value + '</span></div></div>' +
                '<div class="col-6"><div class="d-flex h-100 align-items-center justify-content-end pe-3">' +
                '<div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">' +
                '<i class="' + item.icon + ' h4 text-' + item.color + ' m-0"></i></div></div></div>' +
                '</div></div></div>';
        }).join('');
    }

    // Initialize
    goToStep(1);
});
</script>
@endsection
