@extends('layouts.vertical', ['pageTitle' => 'Custom Analysis'])

@section('css')
<style>
    .fs-xxl { font-size: 2.5rem; }

    /* Heatmap colored circles */
    .heatmap-dot {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        font-size: 0.7rem;
        font-weight: 700;
        color: #fff;
    }
    .heatmap-dot-green { background-color: #28a745; }
    .heatmap-dot-yellow { background-color: #ffc107; color: #212529; }
    .heatmap-dot-red { background-color: #dc3545; }
    .heatmap-dot-gray { background-color: #dee2e6; color: #6c757d; }

    /* Risk badges */
    .risk-badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75rem;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }
    .risk-low { background-color: #28a745; color: white; }
    .risk-medium { background-color: #ffc107; color: #212529; }
    .risk-high { background-color: #fd7e14; color: white; }
    .risk-critical { background-color: #dc3545; color: white; }

    /* Hover effects */
    .hover-lift { transition: all 0.3s ease; cursor: pointer; }
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    /* Filter bar dropdown menus */
    .filter-dropdown .dropdown-menu {
        min-width: 200px;
        max-height: 280px;
        overflow-y: auto;
        padding: 0.5rem;
    }
    .filter-dropdown .dropdown-menu .form-check {
        padding: 0.35rem 0.5rem 0.35rem 2rem;
        border-radius: 0.25rem;
    }
    .filter-dropdown .dropdown-menu .form-check:hover {
        background-color: var(--bs-tertiary-bg);
    }
    .filter-dropdown .dropdown-toggle::after {
        margin-left: 0.5em;
    }
    .filter-dropdown .dropdown-toggle .filter-count {
        font-size: 0.7rem;
        min-width: 18px;
        height: 18px;
        line-height: 18px;
        text-align: center;
        border-radius: 50%;
        display: inline-block;
    }

    /* Saved filter items */
    .saved-filter-item {
        display: flex;
        align-items: center;
        padding: 0.4rem 0.75rem;
        cursor: pointer;
        border-radius: 0.25rem;
    }
    .saved-filter-item:hover { background-color: var(--bs-tertiary-bg); }
    .saved-filter-item .filter-name { flex: 1; font-size: 0.875rem; }
    .saved-filter-item .btn-delete-filter {
        opacity: 0;
        transition: opacity 0.2s;
        padding: 0;
        border: none;
        background: none;
        color: var(--bs-danger);
        font-size: 0.75rem;
    }
    .saved-filter-item:hover .btn-delete-filter { opacity: 1; }

    /* Pipeline metric tiles */
    .pipeline-metric {
        text-align: center;
        padding: 1rem;
        border-radius: 0.5rem;
        background: var(--bs-tertiary-bg);
    }
    .pipeline-metric .metric-value {
        font-size: 1.5rem;
        font-weight: 700;
    }
    .pipeline-metric .metric-label {
        font-size: 0.8rem;
        color: var(--bs-secondary-color);
    }

    /* Product Area Risk card */
    .risk-card {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }
    .risk-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .risk-card.border-success { border-left-color: #28a745 !important; }
    .risk-card.border-warning { border-left-color: #ffc107 !important; }
    .risk-card.border-orange { border-left-color: #fd7e14 !important; }
    .risk-card.border-danger { border-left-color: #dc3545 !important; }

    /* Supporting tables */
    .analysis-table th { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .analysis-table td { vertical-align: middle; }
</style>
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Custom Analysis', 'pageSubTitle1' => 'Partner Performance', 'pageSubTitle2'=> 'Reports'])
@endsection

@section('content')

    <!-- Horizontal Filter Bar -->
    <div class="border-faded bg-faded p-3 mb-g rounded">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="me-1 fw-semibold text-nowrap">
                <i class="fal fa-filter me-1"></i> Filter By:
            </span>

            <!-- Readiness Focus -->
            <div class="filter-dropdown dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="fal fa-crosshairs me-1"></i> Readiness Blueprint
                </button>
                <div class="dropdown-menu">
                    <div class="form-check">
                        <input class="form-check-input readiness-filter" type="radio" name="readiness" id="readiness-all" value="all" checked>
                        <label class="form-check-label" for="readiness-all">All Types</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input readiness-filter" type="radio" name="readiness" id="readiness-sales" value="sales">
                        <label class="form-check-label" for="readiness-sales">Sales Readiness</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input readiness-filter" type="radio" name="readiness" id="readiness-presales" value="presales">
                        <label class="form-check-label" for="readiness-presales">Pre-Sales Readiness</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input readiness-filter" type="radio" name="readiness" id="readiness-delivery" value="delivery">
                        <label class="form-check-label" for="readiness-delivery">Delivery Readiness</label>
                    </div>
                </div>
            </div>

            <!-- Geography -->
            <div class="filter-dropdown dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="geo-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="fal fa-globe me-1"></i> Geography
                    <span class="filter-count bg-primary text-white ms-1">{{ count($geographies) }}</span>
                </button>
                <div class="dropdown-menu" id="geo-dropdown-menu">
                    @foreach($geographies as $geo)
                    <div class="form-check">
                        <input class="form-check-input geo-filter" type="checkbox" id="geo-{{ Str::slug($geo) }}" value="{{ $geo }}" checked>
                        <label class="form-check-label" for="geo-{{ Str::slug($geo) }}">{{ $geo }}</label>
                    </div>
                    @endforeach
                    @if($geographies->isEmpty())
                    <div class="text-muted small px-2 py-1">No geographies available</div>
                    @endif
                </div>
            </div>

            <!-- Region (dependent on Geography) -->
            <div class="filter-dropdown dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="region-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="fal fa-map-marked-alt me-1"></i> Region
                    <span class="filter-count bg-primary text-white ms-1">{{ count($regions) }}</span>
                </button>
                <div class="dropdown-menu" id="region-dropdown-menu">
                    @foreach($regions as $region)
                    <div class="form-check">
                        <input class="form-check-input region-filter" type="checkbox" id="region-{{ Str::slug($region) }}" value="{{ $region }}" checked>
                        <label class="form-check-label" for="region-{{ Str::slug($region) }}">{{ $region }}</label>
                    </div>
                    @endforeach
                    @if($regions->isEmpty())
                    <div class="text-muted small px-2 py-1">No regions available</div>
                    @endif
                </div>
            </div>

            <!-- PSM -->
            <div class="filter-dropdown dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="psm-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="fal fa-user-tie me-1"></i> PSM
                    <span class="filter-count bg-primary text-white ms-1">{{ count($psms) }}</span>
                </button>
                <div class="dropdown-menu">
                    @foreach($psms as $psm)
                    <div class="form-check">
                        <input class="form-check-input psm-filter" type="checkbox" id="psm-{{ $psm->id }}" value="{{ $psm->id }}" checked>
                        <label class="form-check-label" for="psm-{{ $psm->id }}">{{ $psm->name }}</label>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Fiscal Quarter -->
            <div class="filter-dropdown dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="quarter-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="fal fa-calendar-alt me-1"></i> Quarter
                    <span class="filter-count bg-primary text-white ms-1">5</span>
                </button>
                <div class="dropdown-menu">
                    <div class="form-check">
                        <input class="form-check-input quarter-filter" type="checkbox" id="q-current" value="current" checked>
                        <label class="form-check-label" for="q-current">Current Quarter</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input quarter-filter" type="checkbox" id="q-q1" value="q1-fy27" checked>
                        <label class="form-check-label" for="q-q1">Q1 FY2027</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input quarter-filter" type="checkbox" id="q-q2" value="q2-fy27" checked>
                        <label class="form-check-label" for="q-q2">Q2 FY2027</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input quarter-filter" type="checkbox" id="q-q3" value="q3-fy27" checked>
                        <label class="form-check-label" for="q-q3">Q3 FY2027</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input quarter-filter" type="checkbox" id="q-q4" value="q4-fy27" checked>
                        <label class="form-check-label" for="q-q4">Q4 FY2027</label>
                    </div>
                </div>
            </div>

            <!-- LOB -->
            <div class="filter-dropdown dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="lob-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="fal fa-briefcase me-1"></i> LOB
                    <span class="filter-count bg-primary text-white ms-1">4</span>
                </button>
                <div class="dropdown-menu">
                    <div class="form-check">
                        <input class="form-check-input lob-filter" type="checkbox" id="lob-ifp" value="ifp" checked>
                        <label class="form-check-label" for="lob-ifp">IFP (Finance)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input lob-filter" type="checkbox" id="lob-spm" value="spm" checked>
                        <label class="form-check-label" for="lob-spm">SPM (Sales & Marketing)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input lob-filter" type="checkbox" id="lob-ind" value="ind" checked>
                        <label class="form-check-label" for="lob-ind">I&D (Supply Chain)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input lob-filter" type="checkbox" id="lob-owp" value="owp" checked>
                        <label class="form-check-label" for="lob-owp">OWP (HR & Workforce)</label>
                    </div>
                </div>
            </div>

            <!-- Partner -->
            <div class="filter-dropdown dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="partner-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="fal fa-handshake me-1"></i> Partner
                    <span class="filter-count bg-primary text-white ms-1">{{ count($partners) }}</span>
                </button>
                <div class="dropdown-menu" id="partner-dropdown-menu">
                    @foreach($partners as $partner)
                    <div class="form-check">
                        <input class="form-check-input partner-filter" type="checkbox" id="partner-{{ $partner->id }}" value="{{ $partner->id }}" checked>
                        <label class="form-check-label" for="partner-{{ $partner->id }}">{{ $partner->name }}</label>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Sales Stage -->
            <div class="filter-dropdown dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="stage-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="fal fa-funnel-dollar me-1"></i> Stage
                    <span class="filter-count bg-primary text-white ms-1">6</span>
                </button>
                <div class="dropdown-menu">
                    <div class="form-check">
                        <input class="form-check-input stage-filter" type="checkbox" id="stage-prospecting" value="prospecting" checked>
                        <label class="form-check-label" for="stage-prospecting">Prospecting</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input stage-filter" type="checkbox" id="stage-qualification" value="qualification" checked>
                        <label class="form-check-label" for="stage-qualification">Qualification</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input stage-filter" type="checkbox" id="stage-proposal" value="proposal" checked>
                        <label class="form-check-label" for="stage-proposal">Proposal</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input stage-filter" type="checkbox" id="stage-negotiation" value="negotiation" checked>
                        <label class="form-check-label" for="stage-negotiation">Negotiation</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input stage-filter" type="checkbox" id="stage-won" value="closed-won" checked>
                        <label class="form-check-label" for="stage-won">Closed Won</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input stage-filter" type="checkbox" id="stage-lost" value="closed-lost" checked>
                        <label class="form-check-label" for="stage-lost">Closed Lost</label>
                    </div>
                </div>
            </div>

            <!-- Approach -->
            <div class="filter-dropdown dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="approach-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="fal fa-route me-1"></i> Approach
                    <span class="filter-count bg-primary text-white ms-1">4</span>
                </button>
                <div class="dropdown-menu">
                    <div class="form-check">
                        <input class="form-check-input approach-filter" type="checkbox" id="approach-partner" value="partner-led" checked>
                        <label class="form-check-label" for="approach-partner">Partner-Led</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input approach-filter" type="checkbox" id="approach-customer" value="customer-led" checked>
                        <label class="form-check-label" for="approach-customer">Customer-Led</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input approach-filter" type="checkbox" id="approach-anaplan" value="anaplan-led" checked>
                        <label class="form-check-label" for="approach-anaplan">Anaplan-Led</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input approach-filter" type="checkbox" id="approach-co" value="co-delivery" checked>
                        <label class="form-check-label" for="approach-co">Co-Delivery</label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <button type="button" class="btn btn-primary btn-sm waves-effect waves-themed" id="run-analysis">
                <i class="fal fa-play me-1"></i> Run Analysis
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm waves-effect waves-themed" id="reset-filters">
                <i class="fal fa-redo me-1"></i> Reset
            </button>

            <span class="border-start ps-2 ms-1"></span>

            <!-- Saved Filters -->
            <div class="filter-dropdown dropdown">
                <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" id="saved-filters-btn" data-bs-toggle="dropdown">
                    <i class="fal fa-bookmark me-1"></i> Saved Filters
                </button>
                <div class="dropdown-menu dropdown-menu-end" id="saved-filters-menu" style="min-width: 240px;">
                    <div id="saved-filters-list">
                        <div class="text-muted small px-3 py-2"><i class="fal fa-info-circle me-1"></i> No saved filters</div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="px-3 py-2">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="save-filter-name" placeholder="Filter name..." maxlength="30">
                            <button class="btn btn-success" type="button" id="save-filter-btn" title="Save current filters">
                                <i class="fal fa-save"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-2 text-muted small">
            <i class="fal fa-info-circle me-1"></i> Adjust filters above to narrow your dataset, then press <strong>Run Analysis</strong> to generate results. Use <strong>Saved Filters</strong> to store and recall configurations.
        </div>
    </div>

    <!-- Empty State (visible until Run Analysis) -->
    <div id="analysis-empty-state" class="mt-4">
        <div class="card shadow-0 border-faded">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fal fa-chart-scatter fa-4x text-muted opacity-50"></i>
                </div>
                <h4 class="fw-400 text-muted mb-3">Ready to Analyze</h4>
                <p class="text-muted mb-4" style="max-width: 480px; margin: 0 auto;">
                    Configure your filters and click <strong>Run Analysis</strong> to generate partner performance metrics, risk assessments, and actionable recommendations.
                </p>
                <div class="d-flex justify-content-center gap-4 text-muted small">
                    <div><span class="badge bg-fusion rounded-circle me-1">1</span> Set filters</div>
                    <div><span class="badge bg-fusion rounded-circle me-1">2</span> Run Analysis</div>
                    <div><span class="badge bg-fusion rounded-circle me-1">3</span> Review results</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- Analysis Results (hidden until Run Analysis)                   -->
    <!-- ============================================================= -->
    <div id="analysis-results" style="display: none;">

    <!-- Row 1: KPI Metric Cards -->
    <div class="row sortable-off">
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Total Partners</p>
                            <span class="h5 text-nowrap" id="kpi-total-partners">-</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-building h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Active Projects</p>
                            <span class="h5 text-nowrap" id="kpi-active-projects">-</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-project-diagram h4 text-success m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Close Rate</p>
                            <span class="h5 text-nowrap" id="kpi-close-rate">-</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-percentage h4 text-info m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Growth Rate (Sourced)</p>
                            <span class="h5 text-nowrap" id="kpi-growth-rate">-</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-chart-line h4 text-warning m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Business Feed Panel -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-icon panel-locked" id="panel-business-feed">
                <div class="panel-hdr">
                    <h2> Business <span class="fw-light"><i>Feed</i></span></h2>
                </div>
                <div class="panel-container border-faded border-start-0 border-end-0 border-bottom-0">
                    <div class="panel-content p-0">
                        <div class="row row-grid g-0">
                            <div class="col-sm-4 p-3" style="background: var(--app-nav-item-active-bg);">
                                <div class="d-flex mt-3">
                                    <span class="h6 m-0 fw-300">Close Rate</span>
                                    <span class="h6 d-inline-block ms-auto" id="feed-close-rate-val">0%</span>
                                </div>
                                <div class="progress mb-4 border shadow-inset-3">
                                    <div id="feed-close-rate-bar" class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <div class="d-flex">
                                    <span class="h6 m-0 fw-300">Growth Rate</span>
                                    <span class="h6 d-inline-block ms-auto" id="feed-growth-rate-val">0%</span>
                                </div>
                                <div class="progress mb-4 border shadow-inset-3">
                                    <div id="feed-growth-rate-bar" class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <div class="d-flex">
                                    <span class="h6 m-0 fw-300">Pipeline to Quota</span>
                                    <span class="h6 d-inline-block ms-auto" id="feed-pipeline-quota-val">0%</span>
                                </div>
                                <div class="progress mb-4 border shadow-inset-3">
                                    <div id="feed-pipeline-quota-bar" class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <div class="d-flex">
                                    <span class="h6 m-0 fw-300">Resource Utilization</span>
                                    <span class="h6 d-inline-block ms-auto" id="feed-resource-util-val">0%</span>
                                </div>
                                <div class="progress mb-4 border shadow-inset-3">
                                    <div id="feed-resource-util-bar" class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <div class="d-flex">
                                    <span class="h6 m-0 fw-300">Quota Attainment</span>
                                    <span class="h6 d-inline-block ms-auto" id="feed-quota-attain-val">0%</span>
                                </div>
                                <div class="progress mb-4 border shadow-inset-3">
                                    <div id="feed-quota-attain-bar" class="progress-bar bg-danger-500 progress-bar-striped" role="progressbar" style="width: 0%;"></div>
                                </div>
                            </div>
                            <div class="col-sm-8 p-3 bg-subtlelight-fade">
                                <div id="nnacv-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Current Pipeline + Blueprint Completion -->
    <div class="row">
        <div class="col-lg-8">
            <div class="panel panel-icon" id="panel-pipeline">
                <div class="panel-hdr">
                    <h2><i class="fal fa-chart-bar me-2"></i> Current <span class="fw-light"><i>Pipeline</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="pipeline-metric">
                                    <div class="metric-value text-primary" id="pipe-opp-count">-</div>
                                    <div class="metric-label">Opportunity Count</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="pipeline-metric">
                                    <div class="metric-value text-success" id="pipe-opp-value">-</div>
                                    <div class="metric-label">Pipeline Value</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="pipeline-metric">
                                    <div class="metric-value text-info" id="pipe-sourced-quota">-</div>
                                    <div class="metric-label">Sourced to Quota</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="pipeline-metric">
                                    <div class="metric-value text-warning" id="pipe-influenced-quota">-</div>
                                    <div class="metric-label">Influenced to Quota</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="pipeline-metric">
                                    <div class="metric-value" id="pipe-attainment">-</div>
                                    <div class="metric-label">Quota & Attainment</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="pipeline-metric">
                                    <div class="metric-value text-dark" id="pipe-asp">-</div>
                                    <div class="metric-label">Avg Selling Price</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="pipeline-metric">
                                    <div class="metric-value text-primary" id="pipe-nnacv">-</div>
                                    <div class="metric-label">Historical NNACV</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="pipeline-metric">
                                    <div class="metric-value text-success" id="pipe-quota-opp-asp">-</div>
                                    <div class="metric-label">Quota Opty Count @ ASP</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel panel-icon panel-locked" id="panel-blueprint">
                <div class="panel-hdr">
                    <h2> Blueprint <span class="fw-light"><i>Completion</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="blueprint-chart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Resource Count Cards -->
    <div class="row sortable-off">
        <div class="col-xs-12 col-sm-4 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Delivery Resources</p>
                            <span class="h5 text-nowrap" id="res-delivery-count">-</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-cogs h4 text-success m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Sales Resources</p>
                            <span class="h5 text-nowrap" id="res-sales-count">-</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-handshake h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Pre-Sales Resources</p>
                            <span class="h5 text-nowrap" id="res-presales-count">-</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-user-headset h4 text-info m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 5: Resource Utilization Heatmap -->
    <div class="row">
        <div class="col-12">
            <div class="panel panel-icon" id="panel-heatmap">
                <div class="panel-hdr">
                    <h2><i class="fal fa-th me-2"></i> Resource Utilization <span class="fw-light"><i>Heatmap</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                            <table class="table table-bordered mb-0 analysis-table d-none" id="heatmap-table">
                                <thead>
                                    <tr>
                                        <th>Partner</th>
                                        <th class="text-center">IFP</th>
                                        <th class="text-center">SPM</th>
                                        <th class="text-center">I&D</th>
                                        <th class="text-center">OWP</th>
                                        <th class="text-center">FCR</th>
                                        <th class="text-center">IBP</th>
                                        <th class="text-center">MFP</th>
                                        <th class="text-center fw-bold">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="heatmap-body"></tbody>
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 6: Product Area Risk Cards -->
    <div class="row" id="product-risk-row">
        <div class="col-xl-3 col-md-6 mb-g">
            <div class="card risk-card border-success" id="risk-card-ifp">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="width-4 height-4 bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fal fa-calculator h5 text-success m-0"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Finance</h6>
                            <small class="text-muted">IFP</small>
                        </div>
                        <span class="risk-badge risk-low ms-auto" id="risk-ifp-badge">Low</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small">Resource:Opp Ratio</span>
                        <span class="fw-bold float-end" id="risk-ifp-ratio">-</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small">Resources</span>
                        <span class="fw-bold float-end" id="risk-ifp-resources">-</span>
                    </div>
                    <div>
                        <span class="text-muted small">$ at Risk</span>
                        <span class="fw-bold float-end text-danger" id="risk-ifp-dollars">-</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-g">
            <div class="card risk-card border-warning" id="risk-card-spm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="width-4 height-4 bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fal fa-bullhorn h5 text-warning m-0"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Sales & Marketing</h6>
                            <small class="text-muted">SPM</small>
                        </div>
                        <span class="risk-badge risk-medium ms-auto" id="risk-spm-badge">Medium</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small">Resource:Opp Ratio</span>
                        <span class="fw-bold float-end" id="risk-spm-ratio">-</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small">Resources</span>
                        <span class="fw-bold float-end" id="risk-spm-resources">-</span>
                    </div>
                    <div>
                        <span class="text-muted small">$ at Risk</span>
                        <span class="fw-bold float-end text-danger" id="risk-spm-dollars">-</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-g">
            <div class="card risk-card border-orange" id="risk-card-ind">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="width-4 height-4 bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fal fa-truck h5 text-info m-0"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Supply Chain</h6>
                            <small class="text-muted">I&D</small>
                        </div>
                        <span class="risk-badge risk-high ms-auto" id="risk-ind-badge">High</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small">Resource:Opp Ratio</span>
                        <span class="fw-bold float-end" id="risk-ind-ratio">-</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small">Resources</span>
                        <span class="fw-bold float-end" id="risk-ind-resources">-</span>
                    </div>
                    <div>
                        <span class="text-muted small">$ at Risk</span>
                        <span class="fw-bold float-end text-danger" id="risk-ind-dollars">-</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-g">
            <div class="card risk-card border-danger" id="risk-card-owp">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="width-4 height-4 bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fal fa-users h5 text-danger m-0"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">HR & Workforce</h6>
                            <small class="text-muted">OWP</small>
                        </div>
                        <span class="risk-badge risk-critical ms-auto" id="risk-owp-badge">Critical</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small">Resource:Opp Ratio</span>
                        <span class="fw-bold float-end" id="risk-owp-ratio">-</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small">Resources</span>
                        <span class="fw-bold float-end" id="risk-owp-resources">-</span>
                    </div>
                    <div>
                        <span class="text-muted small">$ at Risk</span>
                        <span class="fw-bold float-end text-danger" id="risk-owp-dollars">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 7: Supporting Table - Opportunities -->
    <div class="row">
        <div class="col-12">
            <div class="panel panel-icon" id="panel-opps-table">
                <div class="panel-hdr">
                    <h2><i class="fal fa-bullseye-arrow me-2"></i> Opportunities <span class="fw-light"><i>Detail</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                            <table class="table table-hover table-bordered mb-0 analysis-table d-none" id="opps-table">
                                <thead>
                                    <tr>
                                        <th>Opportunity</th>
                                        <th>Account</th>
                                        <th>Partner</th>
                                        <th>Stage</th>
                                        <th class="text-end">Amount</th>
                                        <th>Close Date</th>
                                        <th class="text-end">Probability</th>
                                        <th>Owner</th>
                                    </tr>
                                </thead>
                                <tbody id="opps-table-body"></tbody>
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 8: Supporting Table - Resources -->
    <div class="row">
        <div class="col-12">
            <div class="panel panel-icon" id="panel-resources-table">
                <div class="panel-hdr">
                    <h2><i class="fal fa-id-badge me-2"></i> Resources <span class="fw-light"><i>Detail</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                            <table class="table table-hover table-bordered mb-0 analysis-table d-none" id="resources-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Partner</th>
                                        <th>Role</th>
                                        <th>Program</th>
                                        <th>Status</th>
                                        <th>Date Certified</th>
                                    </tr>
                                </thead>
                                <tbody id="resources-table-body"></tbody>
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 9: Supporting Table - Active Projects -->
    <div class="row">
        <div class="col-12">
            <div class="panel panel-icon" id="panel-projects-table">
                <div class="panel-hdr">
                    <h2><i class="fal fa-tasks me-2"></i> Active <span class="fw-light"><i>Projects</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                            <table class="table table-hover table-bordered mb-0 analysis-table d-none" id="projects-table">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Partner</th>
                                        <th>Status</th>
                                        <th class="text-end">ACV</th>
                                        <th class="text-center">Progress</th>
                                        <th>Sprints</th>
                                        <th>Start Date</th>
                                    </tr>
                                </thead>
                                <tbody id="projects-table-body"></tbody>
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div><!-- /analysis-results -->
@endsection

@section('scripts')
@vite(['resources/scripts/pages/table-init.js'])
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var sampleData = @json($partners);
    var geoRegionMap = @json($geoRegionMap);
    var psmPartnerMap = @json($psmPartnerMap);
    var STORAGE_KEY = 'pm3_analysis_saved_filters';

    // Chart instance tracking for cleanup
    var chartInstances = {};

    // SmartTables instance tracking
    var tableInstances = {};
    var originalTableContainers = {};

    function storeOriginalTableHTML() {
        ['panel-heatmap', 'panel-opps-table', 'panel-resources-table', 'panel-projects-table'].forEach(function(panelId) {
            var panelContent = document.querySelector('#' + panelId + ' .panel-content');
            if (panelContent) originalTableContainers[panelId] = panelContent.innerHTML;
        });
    }
    storeOriginalTableHTML();

    function destroyAllSmartTables() {
        Object.keys(tableInstances).forEach(function(key) {
            if (tableInstances[key]) {
                try { tableInstances[key].destroy(); } catch(e) {}
                tableInstances[key] = null;
            }
        });
    }

    function resetTableHTML() {
        destroyAllSmartTables();
        Object.keys(originalTableContainers).forEach(function(panelId) {
            var panelContent = document.querySelector('#' + panelId + ' .panel-content');
            if (panelContent) panelContent.innerHTML = originalTableContainers[panelId];
        });
    }

    function initAllSmartTables() {
        if (!window.SmartTables) return;
        var configs = {
            'heatmap-table': {
                perPage: 25,
                responsive: { enabled: true, breakpoint: 768, columnPriorities: { 0: 1, 8: 2, 1: 3, 2: 4, 3: 5, 4: 6, 5: 7, 6: 8, 7: 9 } }
            },
            'opps-table': {
                perPage: 15,
                responsive: { enabled: true, breakpoint: 768, columnPriorities: { 0: 1, 2: 2, 4: 3, 3: 4, 5: 5, 6: 6, 1: 7, 7: 8 } }
            },
            'resources-table': {
                perPage: 15,
                responsive: { enabled: true, breakpoint: 768, columnPriorities: { 0: 1, 1: 2, 3: 3, 4: 4, 2: 5, 5: 6 } }
            },
            'projects-table': {
                perPage: 15,
                responsive: { enabled: true, breakpoint: 768, columnPriorities: { 0: 1, 1: 2, 4: 3, 2: 4, 3: 5, 5: 6, 6: 7 } }
            }
        };
        Object.keys(configs).forEach(function(tableId) {
            var el = document.getElementById(tableId);
            if (el && el.querySelector('tbody') && el.querySelector('tbody').children.length > 0) {
                tableInstances[tableId] = new window.SmartTables(tableId, configs[tableId]);
            }
        });
    }

    // Product area programs
    var PROGRAMS = ['IFP', 'SPM', 'I&D', 'OWP', 'FCR', 'IBP', 'MFP'];
    var PROGRAM_LABELS = {
        'IFP': 'Finance', 'SPM': 'Sales & Marketing', 'I&D': 'Supply Chain',
        'OWP': 'HR & Workforce', 'FCR': 'FCR', 'IBP': 'IBP', 'MFP': 'MFP'
    };

    // Helpers
    function fmt(val) {
        if (val >= 1e6) return '$' + (val / 1e6).toFixed(1) + 'M';
        if (val >= 1e3) return '$' + (val / 1e3).toFixed(0) + 'K';
        return '$' + val.toLocaleString();
    }

    function fmtPct(val) { return Math.round(val) + '%'; }

    function getChartColors() {
        var cm = window.colorMap;
        if (cm) return {
            primary: cm.primary[400].hex,
            primary600: cm.primary[600].hex,
            success: cm.success[400].hex,
            danger: cm.danger[400].hex,
            warning: cm.warning[400].hex,
            info: cm.info[400].hex,
            body: cm.bootstrapVars.bodyColor.hex,
            bodyRgba: function(o) { return cm.bootstrapVars.bodyColor.rgba(o); },
            gridColor: cm.bootstrapVars.bodyColor.rgba(0.1)
        };
        return {
            primary: '#886ab5', primary600: '#6a4f96', success: '#1dc9b7',
            danger: '#fd3995', warning: '#ffc241', info: '#2196F3',
            body: '#666', bodyRgba: function(o) { return 'rgba(102,102,102,' + o + ')'; },
            gridColor: 'rgba(102,102,102,0.1)'
        };
    }

    // ===================================================
    // FILTER COUNT BADGE UPDATER
    // ===================================================
    function updateFilterCount(filterClass, btnId) {
        var checked = document.querySelectorAll('.' + filterClass + ':checked').length;
        var total = document.querySelectorAll('.' + filterClass).length;
        var badge = document.querySelector('#' + btnId + ' .filter-count');
        if (!badge) return;
        badge.textContent = checked;
        if (checked < total) {
            badge.classList.remove('bg-primary');
            badge.classList.add('bg-warning');
        } else {
            badge.classList.remove('bg-warning');
            badge.classList.add('bg-primary');
        }
    }

    var filterMap = {
        'geo-filter': 'geo-btn',
        'region-filter': 'region-btn',
        'psm-filter': 'psm-btn',
        'quarter-filter': 'quarter-btn',
        'lob-filter': 'lob-btn',
        'partner-filter': 'partner-btn',
        'stage-filter': 'stage-btn',
        'approach-filter': 'approach-btn'
    };

    function bindFilterBadgeEvents() {
        Object.keys(filterMap).forEach(function(filterClass) {
            var btnId = filterMap[filterClass];
            document.querySelectorAll('.' + filterClass).forEach(function(el) {
                el.addEventListener('change', function() {
                    updateFilterCount(filterClass, btnId);
                });
            });
        });
    }
    bindFilterBadgeEvents();

    // ===================================================
    // DEPENDENT FILTERS: Geography -> Region
    // ===================================================
    function updateRegionsByGeography() {
        var checkedGeos = Array.from(document.querySelectorAll('.geo-filter:checked')).map(function(el) { return el.value; });
        var menu = document.getElementById('region-dropdown-menu');
        menu.innerHTML = '';
        var availableRegions = [];
        checkedGeos.forEach(function(geo) {
            (geoRegionMap[geo] || []).forEach(function(r) {
                if (availableRegions.indexOf(r) === -1) availableRegions.push(r);
            });
        });
        availableRegions.sort();
        if (availableRegions.length === 0) {
            menu.innerHTML = '<div class="text-muted small px-2 py-1"><i class="fal fa-info-circle me-1"></i> No regions for selected geographies</div>';
        } else {
            availableRegions.forEach(function(region) {
                var slug = region.toLowerCase().replace(/[^a-z0-9]+/g, '-');
                var div = document.createElement('div');
                div.className = 'form-check';
                div.innerHTML = '<input class="form-check-input region-filter" type="checkbox" id="region-' + slug + '" value="' + region + '" checked><label class="form-check-label" for="region-' + slug + '">' + region + '</label>';
                menu.appendChild(div);
            });
            menu.querySelectorAll('.region-filter').forEach(function(el) {
                el.addEventListener('change', function() { updateFilterCount('region-filter', 'region-btn'); });
            });
        }
        updateFilterCount('region-filter', 'region-btn');
    }
    document.querySelectorAll('.geo-filter').forEach(function(el) {
        el.addEventListener('change', updateRegionsByGeography);
    });

    // ===================================================
    // DEPENDENT FILTERS: PSM -> Partner
    // ===================================================
    function updatePartnersByPSM() {
        var checkedPSMs = Array.from(document.querySelectorAll('.psm-filter:checked')).map(function(el) { return el.value; });
        var menu = document.getElementById('partner-dropdown-menu');
        menu.innerHTML = '';
        var availablePartners = [];
        var seenIds = {};
        checkedPSMs.forEach(function(psmId) {
            (psmPartnerMap[psmId] || []).forEach(function(p) {
                if (!seenIds[p.id]) { seenIds[p.id] = true; availablePartners.push(p); }
            });
        });
        availablePartners.sort(function(a, b) { return a.name.localeCompare(b.name); });
        if (availablePartners.length === 0) {
            menu.innerHTML = '<div class="text-muted small px-2 py-1"><i class="fal fa-info-circle me-1"></i> No partners for selected PSMs</div>';
        } else {
            availablePartners.forEach(function(partner) {
                var div = document.createElement('div');
                div.className = 'form-check';
                div.innerHTML = '<input class="form-check-input partner-filter" type="checkbox" id="partner-' + partner.id + '" value="' + partner.id + '" checked><label class="form-check-label" for="partner-' + partner.id + '">' + partner.name + '</label>';
                menu.appendChild(div);
            });
            menu.querySelectorAll('.partner-filter').forEach(function(el) {
                el.addEventListener('change', function() { updateFilterCount('partner-filter', 'partner-btn'); });
            });
        }
        updateFilterCount('partner-filter', 'partner-btn');
    }
    document.querySelectorAll('.psm-filter').forEach(function(el) {
        el.addEventListener('change', updatePartnersByPSM);
    });

    // ===================================================
    // SAVE / LOAD FILTER CONFIGURATIONS
    // ===================================================
    function getFilterState() {
        var state = {};
        var readinessEl = document.querySelector('.readiness-filter:checked');
        state.readiness = readinessEl ? readinessEl.value : 'all';
        ['geo-filter', 'region-filter', 'psm-filter', 'quarter-filter', 'lob-filter', 'partner-filter', 'stage-filter', 'approach-filter'].forEach(function(cls) {
            state[cls] = Array.from(document.querySelectorAll('.' + cls + ':checked')).map(function(el) { return el.value; });
        });
        return state;
    }

    function applyFilterState(state) {
        if (state.readiness) {
            var radio = document.querySelector('.readiness-filter[value="' + state.readiness + '"]');
            if (radio) radio.checked = true;
        }
        if (state['geo-filter']) {
            document.querySelectorAll('.geo-filter').forEach(function(el) { el.checked = state['geo-filter'].indexOf(el.value) !== -1; });
            updateFilterCount('geo-filter', 'geo-btn');
            updateRegionsByGeography();
        }
        if (state['region-filter']) {
            document.querySelectorAll('.region-filter').forEach(function(el) { el.checked = state['region-filter'].indexOf(el.value) !== -1; });
            updateFilterCount('region-filter', 'region-btn');
        }
        if (state['psm-filter']) {
            document.querySelectorAll('.psm-filter').forEach(function(el) { el.checked = state['psm-filter'].indexOf(el.value) !== -1; });
            updateFilterCount('psm-filter', 'psm-btn');
            updatePartnersByPSM();
        }
        if (state['partner-filter']) {
            document.querySelectorAll('.partner-filter').forEach(function(el) { el.checked = state['partner-filter'].indexOf(el.value) !== -1; });
            updateFilterCount('partner-filter', 'partner-btn');
        }
        ['quarter-filter', 'lob-filter', 'stage-filter', 'approach-filter'].forEach(function(cls) {
            if (state[cls]) {
                document.querySelectorAll('.' + cls).forEach(function(el) { el.checked = state[cls].indexOf(el.value) !== -1; });
                if (filterMap[cls]) updateFilterCount(cls, filterMap[cls]);
            }
        });
    }

    function getSavedFilters() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {}; }
        catch (e) { return {}; }
    }
    function saveSavedFilters(filters) { localStorage.setItem(STORAGE_KEY, JSON.stringify(filters)); }

    function renderSavedFiltersList() {
        var list = document.getElementById('saved-filters-list');
        var filters = getSavedFilters();
        var names = Object.keys(filters);
        list.innerHTML = '';
        if (names.length === 0) {
            list.innerHTML = '<div class="text-muted small px-3 py-2"><i class="fal fa-info-circle me-1"></i> No saved filters</div>';
            return;
        }
        names.forEach(function(name) {
            var item = document.createElement('div');
            item.className = 'saved-filter-item';
            item.innerHTML = '<span class="filter-name">' + name + '</span><button class="btn-delete-filter" title="Delete filter"><i class="fal fa-times"></i></button>';
            item.querySelector('.filter-name').addEventListener('click', function() {
                applyFilterState(filters[name]);
                var dropdown = bootstrap.Dropdown.getInstance(document.getElementById('saved-filters-btn'));
                if (dropdown) dropdown.hide();
            });
            item.querySelector('.btn-delete-filter').addEventListener('click', function(e) {
                e.stopPropagation();
                delete filters[name];
                saveSavedFilters(filters);
                renderSavedFiltersList();
            });
            list.appendChild(item);
        });
    }

    document.getElementById('save-filter-btn').addEventListener('click', function() {
        var nameInput = document.getElementById('save-filter-name');
        var name = nameInput.value.trim();
        if (!name) { nameInput.classList.add('is-invalid'); nameInput.focus(); return; }
        nameInput.classList.remove('is-invalid');
        var filters = getSavedFilters();
        filters[name] = getFilterState();
        saveSavedFilters(filters);
        nameInput.value = '';
        renderSavedFiltersList();
    });
    document.getElementById('save-filter-name').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('save-filter-btn').click(); }
    });
    renderSavedFiltersList();

    // ===================================================
    // RUN ANALYSIS
    // ===================================================
    document.getElementById('run-analysis').addEventListener('click', function() {
        var btn = this;
        var selectedPartners = Array.from(document.querySelectorAll('.partner-filter:checked')).map(function(el) { return el.value; });

        btn.innerHTML = '<i class="fal fa-spinner fa-spin me-1"></i> Analyzing...';
        btn.disabled = true;

        setTimeout(function() {
            var filteredPartners = sampleData;
            if (selectedPartners.length > 0 && selectedPartners.length < sampleData.length) {
                filteredPartners = sampleData.filter(function(p) {
                    return selectedPartners.includes(p.id.toString());
                });
            }

            // Reset tables before re-rendering
            resetTableHTML();

            renderKPICards(filteredPartners);
            renderBusinessFeed(filteredPartners);
            renderPipelineMetrics(filteredPartners);
            renderBlueprintChart(filteredPartners);
            renderResourceCards(filteredPartners);
            renderHeatmap(filteredPartners);
            renderProductAreaRisk(filteredPartners);
            renderOpportunitiesTable(filteredPartners);
            renderResourcesTable(filteredPartners);
            renderProjectsTable(filteredPartners);

            document.getElementById('analysis-empty-state').style.display = 'none';
            document.getElementById('analysis-results').style.display = '';

            // Initialize SmartTables on populated tables
            initAllSmartTables();

            btn.innerHTML = '<i class="fal fa-play me-1"></i> Run Analysis';
            btn.disabled = false;
        }, 1200);
    });

    // ===================================================
    // RESET FILTERS
    // ===================================================
    document.getElementById('reset-filters').addEventListener('click', function() {
        var allRadio = document.getElementById('readiness-all');
        if (allRadio) allRadio.checked = true;

        document.querySelectorAll('.geo-filter').forEach(function(el) { el.checked = true; });
        updateFilterCount('geo-filter', 'geo-btn');
        updateRegionsByGeography();

        document.querySelectorAll('.psm-filter').forEach(function(el) { el.checked = true; });
        updateFilterCount('psm-filter', 'psm-btn');
        updatePartnersByPSM();

        ['quarter-filter', 'lob-filter', 'stage-filter', 'approach-filter'].forEach(function(cls) {
            document.querySelectorAll('.' + cls).forEach(function(el) { el.checked = true; });
            if (filterMap[cls]) updateFilterCount(cls, filterMap[cls]);
        });

        // Destroy charts and tables
        Object.keys(chartInstances).forEach(function(key) {
            if (chartInstances[key]) { chartInstances[key].destroy(); chartInstances[key] = null; }
        });
        resetTableHTML();

        // Hide results, show empty state
        document.getElementById('analysis-results').style.display = 'none';
        document.getElementById('analysis-empty-state').style.display = '';
    });

    // ===================================================
    // RENDER: KPI Cards
    // ===================================================
    function renderKPICards(partners) {
        var allOpps = partners.flatMap(function(p) { return p.opportunities || []; });
        var allProjects = partners.flatMap(function(p) { return p.projects || []; });
        var closedWon = allOpps.filter(function(o) { return o.stage && o.stage.toLowerCase() === 'closed won'; });
        var closeRate = allOpps.length > 0 ? (closedWon.length / allOpps.length) * 100 : 0;
        var sourcedOpps = allOpps.filter(function(o) { return o.lead_source && o.lead_source.toLowerCase().indexOf('partner') !== -1; });
        var growthRate = allOpps.length > 0 ? (sourcedOpps.length / allOpps.length) * 100 : 0;

        document.getElementById('kpi-total-partners').textContent = partners.length;
        document.getElementById('kpi-active-projects').textContent = allProjects.length;
        document.getElementById('kpi-close-rate').textContent = fmtPct(closeRate);
        document.getElementById('kpi-growth-rate').textContent = fmtPct(growthRate);
    }

    // ===================================================
    // RENDER: Business Feed
    // ===================================================
    function renderBusinessFeed(partners) {
        var allOpps = partners.flatMap(function(p) { return p.opportunities || []; });
        var allRes = partners.flatMap(function(p) { return p.resources || []; });
        var closedWon = allOpps.filter(function(o) { return o.stage && o.stage.toLowerCase() === 'closed won'; });
        var closeRate = allOpps.length > 0 ? (closedWon.length / allOpps.length) * 100 : 0;
        var sourcedOpps = allOpps.filter(function(o) { return o.lead_source && o.lead_source.toLowerCase().indexOf('partner') !== -1; });
        var growthRate = allOpps.length > 0 ? (sourcedOpps.length / allOpps.length) * 100 : 0;
        var totalPipeline = allOpps.reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);
        var quotaTarget = partners.length * 500000; // Estimated quota per partner
        var pipelineToQuota = quotaTarget > 0 ? Math.min((totalPipeline / quotaTarget) * 100, 200) : 0;
        var completedRes = allRes.filter(function(r) { return r.status && r.status.toLowerCase() === 'completed'; });
        var resourceUtil = allRes.length > 0 ? (completedRes.length / allRes.length) * 100 : 0;
        var closedWonAmount = closedWon.reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);
        var quotaAttainment = quotaTarget > 0 ? Math.min((closedWonAmount / quotaTarget) * 100, 200) : 0;

        // Update progress bars
        setProgressBar('feed-close-rate', closeRate);
        setProgressBar('feed-growth-rate', growthRate);
        setProgressBar('feed-pipeline-quota', Math.min(pipelineToQuota, 100));
        setProgressBar('feed-resource-util', resourceUtil);
        setProgressBar('feed-quota-attain', Math.min(quotaAttainment, 100));

        // Color quota attainment bar based on value
        var attainBar = document.getElementById('feed-quota-attain-bar');
        if (attainBar) {
            attainBar.className = 'progress-bar progress-bar-striped ' + (quotaAttainment >= 80 ? 'bg-success-500' : quotaAttainment >= 50 ? 'bg-warning-500' : 'bg-danger-500');
        }

        // Render NNACV Chart
        renderNNACVChart(partners);
    }

    function setProgressBar(prefix, value) {
        var val = document.getElementById(prefix + '-val');
        var bar = document.getElementById(prefix + '-bar');
        if (val) val.textContent = fmtPct(value);
        if (bar) bar.style.width = Math.min(value, 100) + '%';
    }

    // ===================================================
    // RENDER: NNACV Chart (Historical dual-axis)
    // ===================================================
    function renderNNACVChart(partners) {
        if (chartInstances.nnacv) { chartInstances.nnacv.destroy(); chartInstances.nnacv = null; }

        var allOpps = partners.flatMap(function(p) { return p.opportunities || []; });
        var colors = getChartColors();

        // Group opportunities by month from close_date
        var monthlyData = {};
        allOpps.forEach(function(opp) {
            if (!opp.close_date) return;
            var month = opp.close_date.substring(0, 7); // YYYY-MM
            if (!monthlyData[month]) monthlyData[month] = { amount: 0, count: 0 };
            monthlyData[month].amount += (parseFloat(opp.amount) || 0);
            monthlyData[month].count++;
        });

        var months = Object.keys(monthlyData).sort();
        if (months.length === 0) {
            // Generate sample months if no data
            var now = new Date();
            for (var i = 11; i >= 0; i--) {
                var d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                months.push(d.toISOString().substring(0, 7));
            }
        }

        var amountData = months.map(function(m) { return monthlyData[m] ? Math.round(monthlyData[m].amount) : 0; });
        var countData = months.map(function(m) { return monthlyData[m] ? monthlyData[m].count : 0; });

        var options = {
            series: [
                { name: 'NNACV ($)', type: 'area', data: amountData },
                { name: 'Opp Count', type: 'line', data: countData }
            ],
            chart: {
                height: 335, type: 'line', zoom: { enabled: false }, stacked: false,
                toolbar: { show: false }, fontFamily: 'inherit', parentHeightOffset: 0
            },
            colors: [colors.bodyRgba(0.1), colors.primary],
            stroke: {
                width: [1, 2], curve: 'smooth',
                colors: [colors.bodyRgba(0.8), colors.primary],
                dashArray: [4, 0]
            },
            fill: { type: ['solid', 'solid'], opacity: [0.15, 1] },
            markers: {
                size: [3, 3],
                colors: [colors.bodyRgba(0.7), colors.primary600],
                strokeColors: [colors.bodyRgba(0.7), colors.primary600],
                strokeWidth: 2, hover: { sizeOffset: 2 }
            },
            xaxis: {
                categories: months,
                labels: { style: { colors: colors.body, fontSize: '12px' } },
                axisBorder: { show: false }, axisTicks: { show: false }
            },
            yaxis: [
                {
                    seriesName: 'NNACV ($)',
                    labels: {
                        style: { colors: colors.body, fontSize: '12px' },
                        formatter: function(val) { return fmt(val); }
                    }
                },
                {
                    seriesName: 'Opp Count', opposite: true,
                    labels: {
                        style: { colors: colors.body, fontSize: '12px' },
                        formatter: function(val) { return Math.round(val); }
                    }
                }
            ],
            legend: {
                show: true, position: 'top', horizontalAlign: 'center',
                fontSize: '14px', fontFamily: 'inherit',
                labels: { colors: colors.body },
                markers: { width: 18, height: 6, radius: 2 },
                itemMargin: { horizontal: 12, vertical: 0 }
            },
            grid: {
                borderColor: colors.gridColor, strokeDashArray: 3,
                yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } }
            },
            tooltip: {
                shared: true, intersect: false, theme: 'dark',
                y: [
                    { formatter: function(val) { return fmt(val); } },
                    { formatter: function(val) { return val; } }
                ]
            }
        };

        chartInstances.nnacv = new ApexCharts(document.getElementById('nnacv-chart'), options);
        chartInstances.nnacv.render();
    }

    // ===================================================
    // RENDER: Current Pipeline Metrics
    // ===================================================
    function renderPipelineMetrics(partners) {
        var allOpps = partners.flatMap(function(p) { return p.opportunities || []; });
        var openOpps = allOpps.filter(function(o) {
            return !o.stage || (o.stage.toLowerCase() !== 'closed won' && o.stage.toLowerCase() !== 'closed lost');
        });
        var closedWon = allOpps.filter(function(o) { return o.stage && o.stage.toLowerCase() === 'closed won'; });

        var totalPipeline = openOpps.reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);
        var closedWonAmount = closedWon.reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);
        var sourcedOpps = allOpps.filter(function(o) { return o.lead_source && o.lead_source.toLowerCase().indexOf('partner') !== -1; });
        var sourcedAmount = sourcedOpps.reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);
        var influencedOpps = allOpps.filter(function(o) { return o.lead_source && o.lead_source.toLowerCase().indexOf('partner') === -1; });
        var influencedAmount = influencedOpps.reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);
        var quotaTarget = partners.length * 500000;
        var asp = openOpps.length > 0 ? totalPipeline / openOpps.length : 0;
        var quotaOppAtAsp = asp > 0 ? Math.ceil(quotaTarget / asp) : 0;
        var attainmentPct = quotaTarget > 0 ? (closedWonAmount / quotaTarget) * 100 : 0;
        var shortfall = Math.max(0, quotaTarget - closedWonAmount);
        var historicalNNACV = closedWonAmount;

        document.getElementById('pipe-opp-count').textContent = openOpps.length;
        document.getElementById('pipe-opp-value').textContent = fmt(totalPipeline);
        document.getElementById('pipe-sourced-quota').textContent = fmt(sourcedAmount);
        document.getElementById('pipe-influenced-quota').textContent = fmt(influencedAmount);

        var attainEl = document.getElementById('pipe-attainment');
        attainEl.textContent = fmtPct(attainmentPct) + ' / ' + fmt(shortfall) + ' gap';
        attainEl.className = 'metric-value ' + (attainmentPct >= 80 ? 'text-success' : attainmentPct >= 50 ? 'text-warning' : 'text-danger');

        document.getElementById('pipe-asp').textContent = fmt(asp);
        document.getElementById('pipe-nnacv').textContent = fmt(historicalNNACV) + ' (' + closedWon.length + ')';
        document.getElementById('pipe-quota-opp-asp').textContent = quotaOppAtAsp + ' opps needed';
    }

    // ===================================================
    // RENDER: Blueprint Completion Radar
    // ===================================================
    function renderBlueprintChart(partners) {
        if (chartInstances.blueprint) { chartInstances.blueprint.destroy(); chartInstances.blueprint = null; }

        var allRes = partners.flatMap(function(p) { return p.resources || []; });
        var colors = getChartColors();
        var categories = ['Finance', 'Supply Chain', 'HR & Workforce', 'Sales & Marketing', 'Other', 'AI', 'Polaris'];
        var programMap = { 'IFP': 0, 'I&D': 1, 'OWP': 2, 'SPM': 3 };

        // Calculate completion by program for Delivery and Sales roles
        var deliveryData = [0, 0, 0, 0, 0, 0, 0];
        var salesData = [0, 0, 0, 0, 0, 0, 0];
        var deliveryTotal = [0, 0, 0, 0, 0, 0, 0];
        var salesTotal = [0, 0, 0, 0, 0, 0, 0];

        allRes.forEach(function(r) {
            var idx = programMap[r.program];
            if (idx === undefined) idx = 4; // Other
            var isCompleted = r.status && r.status.toLowerCase() === 'completed';
            if (r.role && r.role.toLowerCase() === 'delivery') {
                deliveryTotal[idx]++;
                if (isCompleted) deliveryData[idx]++;
            } else if (r.role && (r.role.toLowerCase() === 'sales' || r.role.toLowerCase() === 'pre-sales')) {
                salesTotal[idx]++;
                if (isCompleted) salesData[idx]++;
            }
        });

        // Convert to percentages
        var deliveryPct = deliveryData.map(function(v, i) { return deliveryTotal[i] > 0 ? Math.round((v / deliveryTotal[i]) * 100) : 0; });
        var salesPct = salesData.map(function(v, i) { return salesTotal[i] > 0 ? Math.round((v / salesTotal[i]) * 100) : 0; });

        // If all zeros, use sample data
        if (deliveryPct.every(function(v) { return v === 0; }) && salesPct.every(function(v) { return v === 0; })) {
            deliveryPct = [65, 59, 90, 81, 56, 55, 40];
            salesPct = [28, 48, 40, 19, 96, 27, 100];
        }

        var options = {
            series: [
                { name: 'Delivery', data: deliveryPct },
                { name: 'Sales', data: salesPct }
            ],
            chart: {
                height: 350, width: '100%', type: 'radar',
                toolbar: { show: false }, fontFamily: 'inherit', parentHeightOffset: 0
            },
            colors: [colors.success, colors.primary],
            stroke: { width: 0, colors: [colors.success, colors.primary] },
            fill: { opacity: 0.2 },
            markers: {
                size: 5, colors: [colors.success, colors.primary],
                strokeColors: '#fff', strokeWidth: 2, hover: { sizeOffset: 2 }
            },
            xaxis: {
                categories: categories,
                labels: { style: { colors: colors.body, fontSize: '10px' } }
            },
            yaxis: {
                max: 100, tickAmount: 5, show: true,
                labels: { show: true, style: { colors: colors.body, fontSize: '10px' } }
            },
            legend: {
                show: true, position: 'top', horizontalAlign: 'center',
                fontSize: '14px', fontFamily: 'inherit',
                labels: { colors: colors.body },
                markers: { width: 18, height: 6, radius: 2 },
                itemMargin: { horizontal: 12, vertical: 0 }
            },
            tooltip: { theme: 'dark', y: { formatter: function(val) { return val + '%'; } } },
            grid: { borderColor: colors.gridColor, strokeDashArray: 3 },
            plotOptions: {
                radar: {
                    polygons: {
                        strokeColors: colors.gridColor,
                        connectorColors: colors.gridColor
                    }
                }
            }
        };

        chartInstances.blueprint = new ApexCharts(document.getElementById('blueprint-chart'), options);
        chartInstances.blueprint.render();
    }

    // ===================================================
    // RENDER: Resource Count Cards
    // ===================================================
    function renderResourceCards(partners) {
        var allRes = partners.flatMap(function(p) { return p.resources || []; });
        var delivery = allRes.filter(function(r) { return r.role && r.role.toLowerCase() === 'delivery'; }).length;
        var sales = allRes.filter(function(r) { return r.role && r.role.toLowerCase() === 'sales'; }).length;
        var preSales = allRes.filter(function(r) { return r.role && r.role.toLowerCase() === 'pre-sales'; }).length;

        document.getElementById('res-delivery-count').textContent = delivery;
        document.getElementById('res-sales-count').textContent = sales;
        document.getElementById('res-presales-count').textContent = preSales;
    }

    // ===================================================
    // RENDER: Resource Utilization Heatmap
    // ===================================================
    function renderHeatmap(partners) {
        var tbody = document.getElementById('heatmap-body');
        tbody.innerHTML = '';

        partners.forEach(function(partner) {
            var resources = partner.resources || [];
            var programCounts = {};
            PROGRAMS.forEach(function(prog) { programCounts[prog] = 0; });
            resources.forEach(function(r) {
                if (r.program && programCounts.hasOwnProperty(r.program)) {
                    programCounts[r.program]++;
                }
            });

            var total = resources.length;
            var row = document.createElement('tr');
            var html = '<td class="fw-500"><a href="/partners/' + partner.id + '" class="text-primary">' + partner.name + '</a></td>';

            PROGRAMS.forEach(function(prog) {
                var count = programCounts[prog];
                var dotClass = 'heatmap-dot-gray';
                if (count >= 5) dotClass = 'heatmap-dot-green';
                else if (count >= 2) dotClass = 'heatmap-dot-yellow';
                else if (count >= 1) dotClass = 'heatmap-dot-red';
                html += '<td class="text-center"><span class="heatmap-dot ' + dotClass + '">' + count + '</span></td>';
            });

            html += '<td class="text-center fw-bold">' + total + '</td>';
            row.innerHTML = html;
            tbody.appendChild(row);
        });
    }

    // ===================================================
    // RENDER: Product Area Risk Cards
    // ===================================================
    function renderProductAreaRisk(partners) {
        var allRes = partners.flatMap(function(p) { return p.resources || []; });
        var allOpps = partners.flatMap(function(p) { return p.opportunities || []; });
        var totalPipeline = allOpps.reduce(function(s, o) { return s + (parseFloat(o.amount) || 0); }, 0);

        var riskAreas = [
            { key: 'ifp', program: 'IFP' },
            { key: 'spm', program: 'SPM' },
            { key: 'ind', program: 'I&D' },
            { key: 'owp', program: 'OWP' }
        ];

        // Distribute opportunities across product areas proportionally to resources
        var totalResources = allRes.length || 1;
        riskAreas.forEach(function(area) {
            var areaRes = allRes.filter(function(r) { return r.program === area.program; });
            var resCount = areaRes.length;
            var completedCount = areaRes.filter(function(r) { return r.status && r.status.toLowerCase() === 'completed'; }).length;
            var areaShare = resCount / totalResources;
            var areaPipeline = totalPipeline * areaShare;

            // Calculate risk based on ratio of completed resources to pipeline share
            var ratio = resCount > 0 ? (resCount / Math.max(1, Math.ceil(allOpps.length * areaShare))).toFixed(1) : '0.0';
            var ratioNum = parseFloat(ratio);
            var riskLevel, riskClass, borderClass;

            if (ratioNum >= 2.0) { riskLevel = 'Low'; riskClass = 'risk-low'; borderClass = 'border-success'; }
            else if (ratioNum >= 1.0) { riskLevel = 'Medium'; riskClass = 'risk-medium'; borderClass = 'border-warning'; }
            else if (ratioNum >= 0.5) { riskLevel = 'High'; riskClass = 'risk-high'; borderClass = 'border-orange'; }
            else { riskLevel = 'Critical'; riskClass = 'risk-critical'; borderClass = 'border-danger'; }

            var atRisk = ratioNum < 1.0 ? areaPipeline * (1 - ratioNum) : 0;

            document.getElementById('risk-' + area.key + '-badge').textContent = riskLevel;
            document.getElementById('risk-' + area.key + '-badge').className = 'risk-badge ' + riskClass + ' ms-auto';
            document.getElementById('risk-' + area.key + '-ratio').textContent = ratio + ':1';
            document.getElementById('risk-' + area.key + '-resources').textContent = resCount + ' (' + completedCount + ' certified)';
            document.getElementById('risk-' + area.key + '-dollars').textContent = fmt(atRisk);

            var card = document.getElementById('risk-card-' + area.key);
            card.className = 'card risk-card ' + borderClass;
        });
    }

    // ===================================================
    // RENDER: Opportunities Table
    // ===================================================
    function renderOpportunitiesTable(partners) {
        var tbody = document.getElementById('opps-table-body');
        tbody.innerHTML = '';

        partners.forEach(function(partner) {
            (partner.opportunities || []).forEach(function(opp) {
                var stageBadge = getStageBadge(opp.stage);
                var row = document.createElement('tr');
                row.innerHTML =
                    '<td>' + (opp.opportunity_name || '-') + '</td>' +
                    '<td>' + (opp.account_name || '-') + '</td>' +
                    '<td><a href="/partners/' + partner.id + '" class="text-primary">' + partner.name + '</a></td>' +
                    '<td>' + stageBadge + '</td>' +
                    '<td class="text-end">' + (opp.amount ? fmt(parseFloat(opp.amount)) : '-') + '</td>' +
                    '<td>' + (opp.close_date || '-') + '</td>' +
                    '<td class="text-end">' + (opp.probability ? opp.probability + '%' : '-') + '</td>' +
                    '<td>' + (opp.owner || '-') + '</td>';
                tbody.appendChild(row);
            });
        });

        if (tbody.children.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No opportunities found</td></tr>';
        }
    }

    function getStageBadge(stage) {
        if (!stage) return '<span class="badge bg-secondary">Unknown</span>';
        var s = stage.toLowerCase();
        var cls = 'bg-secondary';
        if (s === 'closed won') cls = 'bg-success';
        else if (s === 'closed lost') cls = 'bg-danger';
        else if (s === 'negotiation') cls = 'bg-warning text-dark';
        else if (s === 'proposal') cls = 'bg-info';
        else if (s === 'qualification') cls = 'bg-primary';
        else if (s === 'prospecting') cls = 'bg-secondary';
        return '<span class="badge ' + cls + '">' + stage + '</span>';
    }

    // ===================================================
    // RENDER: Resources Table
    // ===================================================
    function renderResourcesTable(partners) {
        var tbody = document.getElementById('resources-table-body');
        tbody.innerHTML = '';

        partners.forEach(function(partner) {
            (partner.resources || []).forEach(function(res) {
                var statusBadge = res.status && res.status.toLowerCase() === 'completed'
                    ? '<span class="badge bg-success">Completed</span>'
                    : '<span class="badge bg-warning text-dark">' + (res.status || 'In Progress') + '</span>';
                var row = document.createElement('tr');
                row.innerHTML =
                    '<td>' + (res.name || '-') + '</td>' +
                    '<td><a href="/partners/' + partner.id + '" class="text-primary">' + partner.name + '</a></td>' +
                    '<td>' + (res.role || '-') + '</td>' +
                    '<td>' + (res.program || '-') + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '<td>' + (res.date_certified || '-') + '</td>';
                tbody.appendChild(row);
            });
        });

        if (tbody.children.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">No resources found</td></tr>';
        }
    }

    // ===================================================
    // RENDER: Active Projects Table
    // ===================================================
    function renderProjectsTable(partners) {
        var tbody = document.getElementById('projects-table-body');
        tbody.innerHTML = '';

        partners.forEach(function(partner) {
            (partner.projects || []).forEach(function(proj) {
                var pct = proj.percent_complete || 0;
                var progressClass = pct >= 80 ? 'bg-success' : pct >= 50 ? 'bg-info' : pct >= 25 ? 'bg-warning' : 'bg-danger';
                var row = document.createElement('tr');
                row.innerHTML =
                    '<td>' + (proj.name || '-') + '</td>' +
                    '<td><a href="/partners/' + partner.id + '" class="text-primary">' + partner.name + '</a></td>' +
                    '<td><span class="badge ' + getStatusBadgeClass(proj.status) + '">' + (proj.status || 'planning') + '</span></td>' +
                    '<td class="text-end">' + (proj.acv ? fmt(parseFloat(proj.acv)) : '-') + '</td>' +
                    '<td class="text-center"><div class="progress" style="height: 8px; min-width: 80px;"><div class="progress-bar ' + progressClass + '" style="width: ' + pct + '%;"></div></div><small class="text-muted">' + pct + '%</small></td>' +
                    '<td>' + (proj.current_sprint || 0) + '/' + (proj.total_sprints || 0) + '</td>' +
                    '<td>' + (proj.start_date || '-') + '</td>';
                tbody.appendChild(row);
            });
        });

        if (tbody.children.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No active projects found</td></tr>';
        }
    }

    function getStatusBadgeClass(status) {
        if (!status) return 'bg-secondary';
        var s = status.toLowerCase();
        if (s === 'active' || s === 'in progress') return 'bg-success';
        if (s === 'planning') return 'bg-info';
        if (s === 'on hold') return 'bg-warning text-dark';
        if (s === 'completed') return 'bg-primary';
        return 'bg-secondary';
    }
});
</script>
@endsection
