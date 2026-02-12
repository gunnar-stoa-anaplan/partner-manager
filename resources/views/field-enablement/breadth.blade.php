@extends('layouts.vertical', ['pageTitle' => 'Field Breadth'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Field Breadth', 'pageSubTitle1' => 'Engage', 'pageSubTitle1Url' => route('welcome'), 'pageSubTitle2' => 'Field Enablement', 'pageSubTitle2Url' => route('influence_map'), 'pageSubText' => 'Contact reach across your partner portfolio'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Breadth measures how widely your partners are engaged across accounts and product areas. Use this view to see which partners have broad contact networks and where whitespace opportunities exist — helping you prioritize co-selling motions and expansion plays that fill gaps in your go-to-market footprint.</p>
    {{-- Summary Cards --}}
    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $totalContacts }}
                    </h3>
                    <span class="m-0 l-h-n">Total Contacts</span>
                </div>
                <i class="fal fa-users position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $partnerCount }}
                    </h3>
                    <span class="m-0 l-h-n">Partners Covered</span>
                </div>
                <i class="fal fa-briefcase position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $avgProjects }}
                    </h3>
                    <span class="m-0 l-h-n">Avg Projects/Contact</span>
                </div>
                <i class="fal fa-chart-line position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $withMultiple }}
                    </h3>
                    <span class="m-0 l-h-n">Multi-Project (2+)</span>
                </div>
                <i class="fal fa-layer-group position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-g">
        <div class="col-lg-6">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">Contacts by Partner</div>
                </div>
                <div class="card-body">
                    <div id="partner-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">Project Involvement Distribution</div>
                </div>
                <div class="card-body">
                    <div id="distribution-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contacts Table --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">All Contacts by Project Involvement</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="breadth-table" class="table table-bordered table-hover table-striped w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Partner</th>
                                    <th>Title</th>
                                    <th>Email</th>
                                    <th class="text-center">Projects</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contacts as $contact)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="d-inline-block rounded-circle bg-primary-100 text-primary text-center fw-500 me-2"
                                                      style="width: 30px; height: 30px; line-height: 30px; font-size: 11px;">
                                                    {{ strtoupper(substr($contact->first_name, 0, 1)) }}{{ strtoupper(substr($contact->last_name, 0, 1)) }}
                                                </span>
                                                <strong>{{ $contact->first_name }} {{ $contact->last_name }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            @if($contact->partner)
                                                <a href="{{ route('partners.show', $contact->partner_id) }}" class="text-muted">
                                                    {{ $contact->partner->name }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $contact->title ?? '-' }}</td>
                                        <td>
                                            @if($contact->email)
                                                <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php $pc = $contact->projects_count; @endphp
                                            @if($pc >= 3)
                                                <span class="badge bg-success">{{ $pc }}</span>
                                            @elseif($pc >= 2)
                                                <span class="badge bg-primary">{{ $pc }}</span>
                                            @elseif($pc === 1)
                                                <span class="badge bg-warning text-dark">{{ $pc }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="mb-3"><i class="fal fa-users fa-3x text-muted opacity-50"></i></div>
                                            <h5 class="text-muted">No contacts found</h5>
                                            <p class="text-muted mb-0">Contacts will appear here once they are imported for your partners.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/table-init.js'])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var cm = window.colorMap || {};
        var pri = (cm.primary || {});
        var bsVars = (cm.bootstrapVars || {});
        var bodyColorHex = (bsVars.bodyColor || {}).hex || '#666';
        var bodyColor01 = (bsVars.bodyColor || { rgba: function() { return 'rgba(0,0,0,0.1)'; }}).rgba(0.1);

        // SmartTables
        if (window.SmartTables && document.querySelector('#breadth-table tbody tr td:not([colspan])')) {
            new window.SmartTables('breadth-table', {
                perPage: 20,
                sortable: true,
                responsive: {
                    enabled: true,
                    breakpoint: 768,
                    columnPriorities: { 0: 1, 4: 2, 1: 3, 2: 4, 3: 5 }
                }
            });
        }

        // Contacts by Partner (Horizontal Bar)
        var partnerData = @json($contactsByPartner);
        var partnerLabels = Object.keys(partnerData);
        var partnerValues = Object.values(partnerData);

        if (partnerLabels.length > 0) {
            new ApexCharts(document.querySelector('#partner-chart'), {
                chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Contacts', data: partnerValues }],
                plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3, distributed: true } },
                colors: [
                    (pri[500] || {}).hex || '#886ab5',
                    (pri[400] || {}).hex || '#9B7FC5',
                    (pri[300] || {}).hex || '#B09AD5',
                    ((cm.info || {})[500] || {}).hex || '#33b5e5',
                    ((cm.success || {})[500] || {}).hex || '#1dc9b7',
                    ((cm.warning || {})[500] || {}).hex || '#ffc241',
                    (pri[600] || {}).hex || '#7559a0',
                    ((cm.danger || {})[500] || {}).hex || '#fd3995',
                    (pri[200] || {}).hex || '#C5B5E5',
                    (pri[100] || {}).hex || '#DAD0F5'
                ],
                xaxis: {
                    categories: partnerLabels,
                    labels: { style: { colors: bodyColorHex, fontSize: '11px' } }
                },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#fff'] } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else {
            document.querySelector('#partner-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }

        // Project Distribution (Column)
        var distData = @json($projectDistribution);
        var distLabels = Object.keys(distData);
        var distValues = Object.values(distData);

        if (distValues.some(function(v) { return v > 0; })) {
            new ApexCharts(document.querySelector('#distribution-chart'), {
                chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Contacts', data: distValues }],
                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, distributed: true } },
                colors: [
                    ((cm.danger || {})[400] || {}).hex || '#fe5da8',
                    ((cm.warning || {})[500] || {}).hex || '#ffc241',
                    (pri[400] || {}).hex || '#9B7FC5',
                    (pri[500] || {}).hex || '#886ab5',
                    ((cm.success || {})[500] || {}).hex || '#1dc9b7'
                ],
                xaxis: {
                    categories: distLabels,
                    labels: { style: { colors: bodyColorHex, fontSize: '11px' } }
                },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } }, forceNiceScale: true },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else {
            document.querySelector('#distribution-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }
    });
    </script>
@endsection
