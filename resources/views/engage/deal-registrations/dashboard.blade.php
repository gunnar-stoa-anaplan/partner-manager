@extends('layouts.vertical', ['pageTitle' => 'Deal Registration Dashboard'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Deal Registration Dashboard', 'pageSubTitle1' => 'Business Development', 'pageSubTitle1Url' => route('welcome'), 'pageSubText' => 'Pipeline analytics, deal status tracking, and partner activity'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Track deal registration submissions, approvals, pipeline value, and partner activity across all product areas.</p>

    {{-- KPI Cards --}}
    <div class="row mb-g">
        @php
            $kpis = [
                ['label' => 'Total Deals', 'value' => $totalDeals, 'sub' => $pendingDeals . ' pending review', 'icon' => 'file-text', 'color' => 'primary'],
                ['label' => 'Approved', 'value' => $approvedDeals, 'sub' => $rejectedDeals . ' rejected', 'icon' => 'check-circle', 'color' => 'success'],
                ['label' => 'Pipeline Value', 'value' => '$' . number_format($totalPipelineValue), 'sub' => '$' . number_format($approvedValue) . ' approved', 'icon' => 'trending-up', 'color' => 'info'],
                ['label' => 'Avg Deal Size', 'value' => '$' . number_format($avgDealSize), 'sub' => 'across all deals', 'icon' => 'bar-chart-2', 'color' => 'warning'],
            ];
        @endphp
        @foreach($kpis as $kpi)
        <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
            <div class="card shadow-0">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 small">{{ $kpi['label'] }}</p>
                        <span class="h3 mb-0 fw-500">{{ $kpi['value'] }}</span>
                        <small class="d-block text-muted">{{ $kpi['sub'] }}</small>
                    </div>
                    <div class="width-4 height-4 bg-{{ $kpi['color'] }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fal fa-{{ $kpi['icon'] }} fa-lg text-{{ $kpi['color'] }}"></i>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Charts Row 1 --}}
    <div class="row">
        {{-- Deals by Status --}}
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-deal-status">
                <div class="panel-hdr">
                    <h2>Deals by <span class="fw-light"><i>Status</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-deal-status" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Deals by Product Area --}}
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-deal-product">
                <div class="panel-hdr">
                    <h2>Deals by <span class="fw-light"><i>Product Area</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-deal-product" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pipeline Value by Product Area --}}
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-value-product">
                <div class="panel-hdr">
                    <h2>Pipeline Value by <span class="fw-light"><i>Product Area</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-value-product" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="row">
        {{-- Deals by Month --}}
        <div class="col-lg-6">
            <div class="panel panel-icon" id="panel-deals-month">
                <div class="panel-hdr">
                    <h2>Submissions by <span class="fw-light"><i>Month</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-deals-month" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Deals by Partner --}}
        <div class="col-lg-6">
            <div class="panel panel-icon" id="panel-deals-by-partner">
                <div class="panel-hdr">
                    <h2>Deals by <span class="fw-light"><i>Partner</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-deals-by-partner" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Deals Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0">Recent Deal Registrations</div>
                    <a href="{{ route('engage.deal-registrations.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="table-recent-deals" class="table table-bordered table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Deal Name</th>
                                    <th>Partner</th>
                                    <th>Customer</th>
                                    <th class="text-end">Est. Value</th>
                                    <th>Product Area</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentDeals as $deal)
                                <tr>
                                    <td>
                                        <a href="{{ route('engage.deal-registrations.show', $deal) }}" class="fw-500 text-decoration-none">
                                            {{ $deal->deal_name }}
                                        </a>
                                    </td>
                                    <td>{{ $deal->partner->name ?? '-' }}</td>
                                    <td>{{ $deal->customer_name }}</td>
                                    <td class="text-end">${{ number_format($deal->estimated_value) }}</td>
                                    <td>{{ $deal->product_area ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ \App\Models\DealRegistration::STATUS_COLORS[$deal->status] ?? 'secondary' }}">
                                            {{ \App\Models\DealRegistration::STATUSES[$deal->status] ?? $deal->status }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">{{ $deal->submitted_at?->format('M j, Y') ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
    @vite(['resources/scripts/pages/table-init.js'])
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var cm = window.colorMap || {};
        var bv = (cm.bootstrapVars && cm.bootstrapVars.bodyColor) || null;
        var bodyColorHex = bv ? bv.hex : '#6c757d';

        var dlToolbar = { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } };

        function addCopyMenuItem(chartContext) {
            var menuEl = chartContext.el.querySelector('.apexcharts-menu');
            if (!menuEl || menuEl.querySelector('.copy-clipboard-item')) return;
            var copyItem = document.createElement('div');
            copyItem.className = 'apexcharts-menu-item copy-clipboard-item';
            copyItem.textContent = 'Copy';
            copyItem.addEventListener('click', function() {
                chartContext.dataURI({ scale: 2 }).then(function(uri) {
                    fetch(uri.imgURI)
                        .then(function(res) { return res.blob(); })
                        .then(function(blob) {
                            return navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
                        })
                        .then(function() {
                            copyItem.textContent = 'Copied!';
                            setTimeout(function() { copyItem.textContent = 'Copy'; }, 1500);
                        });
                });
                menuEl.classList.remove('apexcharts-menu-open');
            });
            menuEl.appendChild(copyItem);
        }

        var statusColors = @json(\App\Models\DealRegistration::STATUS_COLORS);
        var bsColorMap = {
            'secondary': '#868e96',
            'primary': '#0d6efd',
            'info': '#17a2b8',
            'success': '#28a745',
            'danger': '#dc3545',
            'warning': '#fd7e14'
        };

        // ── Deals by Status (donut) ──
        var statusData = @json($dealsByStatus);
        var statusLabels = Object.keys(statusData).map(function(s) {
            var labels = @json(\App\Models\DealRegistration::STATUSES);
            return labels[s] || s;
        });
        var statusValues = Object.values(statusData).map(Number);
        var statusChartColors = Object.keys(statusData).map(function(s) {
            return bsColorMap[statusColors[s]] || '#868e96';
        });
        if (statusLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-deal-status'), {
                chart: { type: 'donut', height: 300, toolbar: dlToolbar, events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: statusValues,
                labels: statusLabels,
                colors: statusChartColors,
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%' } } },
                dataLabels: { enabled: true }
            }).render();
        } else {
            document.querySelector('#chart-deal-status').innerHTML = '<div class="text-center text-muted py-5"><p>No deal data</p></div>';
        }

        // ── Deals by Product Area (bar) ──
        var productData = @json($dealsByProductArea);
        var productLabels = Object.keys(productData);
        var productValues = Object.values(productData).map(Number);
        var productColors = ['#0d6efd', '#17a2b8', '#28a745', '#fd7e14', '#886ab5'];
        if (productLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-deal-product'), {
                chart: { type: 'bar', height: 300, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: [{ name: 'Deals', data: productValues }],
                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, distributed: true } },
                colors: productColors,
                xaxis: { categories: productLabels, labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                legend: { show: false },
                tooltip: { theme: 'dark' }
            }).render();
        } else {
            document.querySelector('#chart-deal-product').innerHTML = '<div class="text-center text-muted py-5"><p>No product area data</p></div>';
        }

        // ── Pipeline Value by Product Area (bar) ──
        var valueData = @json($valueByProductArea);
        var valueLabels = Object.keys(valueData);
        var valueValues = Object.values(valueData).map(Number);
        if (valueLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-value-product'), {
                chart: { type: 'bar', height: 300, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: [{ name: 'Pipeline Value', data: valueValues }],
                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, distributed: true } },
                colors: productColors,
                xaxis: { categories: valueLabels, labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                yaxis: { labels: { style: { colors: bodyColorHex }, formatter: function(val) { return '$' + (val / 1000).toFixed(0) + 'K'; } } },
                legend: { show: false },
                tooltip: { y: { formatter: function(val) { return '$' + val.toLocaleString(); } } }
            }).render();
        } else {
            document.querySelector('#chart-value-product').innerHTML = '<div class="text-center text-muted py-5"><p>No pipeline data</p></div>';
        }

        // ── Deals by Month (area) ──
        var monthData = @json($dealsByMonth);
        var monthLabels = Object.keys(monthData);
        var monthValues = Object.values(monthData).map(Number);
        if (monthLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-deals-month'), {
                chart: { type: 'area', height: 350, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: [{ name: 'Submissions', data: monthValues }],
                colors: ['#0d6efd'],
                xaxis: { categories: monthLabels, labels: { style: { colors: bodyColorHex, fontSize: '10px' }, rotate: -45 } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
                tooltip: { theme: 'dark' }
            }).render();
        } else {
            document.querySelector('#chart-deals-month').innerHTML = '<div class="text-center text-muted py-5"><p>No submission data</p></div>';
        }

        // ── Deals by Partner (dual-axis column: count + value) ──
        var partnersData = @json($topPartners);
        if (partnersData.length > 0) {
            new ApexCharts(document.querySelector('#chart-deals-by-partner'), {
                chart: { type: 'bar', height: 350, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: [
                    { name: 'Deals', data: partnersData.map(function(p) { return p.count; }) },
                    { name: 'Value', data: partnersData.map(function(p) { return Number(p.value); }) }
                ],
                plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
                colors: ['#886ab5', '#0d6efd'],
                xaxis: { categories: partnersData.map(function(p) { return p.name; }), labels: { style: { colors: bodyColorHex, fontSize: '10px' }, rotate: -45, rotateAlways: partnersData.length > 5 } },
                yaxis: [
                    { title: { text: '# Deals' }, labels: { style: { colors: bodyColorHex } } },
                    { opposite: true, title: { text: 'Value ($)' }, labels: { style: { colors: bodyColorHex }, formatter: function(val) { return '$' + (val / 1000).toFixed(0) + 'K'; } } }
                ],
                tooltip: { theme: 'dark', shared: true, intersect: false, y: { formatter: function(val, opts) { return opts.seriesIndex === 1 ? '$' + val.toLocaleString() : val; } } },
                legend: { position: 'top', labels: { colors: bodyColorHex } },
                grid: { borderColor: 'rgba(108,117,125,0.08)', strokeDashArray: 5 }
            }).render();
        } else {
            document.querySelector('#chart-deals-by-partner').innerHTML = '<div class="text-center text-muted py-5"><p>No partner data</p></div>';
        }

        // ── SmartTables ──
        var el = document.querySelector('#table-recent-deals');
        if (window.SmartTables && el && el.querySelector('tbody tr td:not([colspan])')) {
            new window.SmartTables('table-recent-deals', {
                perPage: 10,
                sortable: true,
                searchable: true,
                export: true,
                plugins: window.ImportExportPlugin ? [window.ImportExportPlugin] : []
            });
        }
    });
    </script>
@endsection
