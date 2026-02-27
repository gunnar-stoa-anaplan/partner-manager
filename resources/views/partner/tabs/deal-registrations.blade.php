{{-- Deal Registrations Tab: Partner-specific deal registration metrics --}}

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 1: KPI Cards
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row mb-g">
    @php
        $kpis = [
            ['label' => 'Total Deals', 'value' => $totalDeals, 'sub' => $pendingDeals . ' pending review', 'icon' => 'file-text', 'color' => 'primary'],
            ['label' => 'Approved', 'value' => $approvedDeals, 'sub' => $rejectedDeals . ' rejected', 'icon' => 'check-circle', 'color' => 'success'],
            ['label' => 'Pipeline Value', 'value' => '$' . number_format($totalValue), 'sub' => '$' . number_format($approvedValue) . ' approved', 'icon' => 'trending-up', 'color' => 'info'],
            ['label' => 'Avg Deal Size', 'value' => $totalDeals > 0 ? '$' . number_format($totalValue / $totalDeals) : '$0', 'sub' => 'across active deals', 'icon' => 'bar-chart-2', 'color' => 'warning'],
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

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 2: Charts — Status Donut + Value by Product Area
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-lg-5">
        <div class="panel panel-icon" id="panel-dr-status">
            <div class="panel-hdr">
                <h2>Deals by <span class="fw-light"><i>Status</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="dr-status-chart" style="height: 280px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="panel panel-icon" id="panel-dr-value-product">
            <div class="panel-hdr">
                <h2>Pipeline Value by <span class="fw-light"><i>Product Area</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="dr-value-product-chart" style="height: 280px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 3: Deal Registrations Table
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">Deal Registrations ({{ $totalDeals }})</div>
                <a href="{{ route('engage.deal-registrations.create') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fal fa-plus me-1"></i> New Deal
                </a>
            </div>
            <div class="card-body p-0">
                @if($deals->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fal fa-file-text fa-2x mb-2 opacity-50"></i>
                        <p>No deal registrations for this partner yet.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table id="dr-deals-table" class="table table-bordered table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Deal Name</th>
                                    <th>Customer</th>
                                    <th class="text-end">Est. Value</th>
                                    <th>Product Area</th>
                                    <th>Status</th>
                                    <th>Close Date</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deals as $deal)
                                <tr>
                                    <td>
                                        <a href="{{ route('engage.deal-registrations.show', $deal) }}" class="fw-500 text-decoration-none">
                                            {{ $deal->deal_name }}
                                        </a>
                                    </td>
                                    <td>{{ $deal->customer_name }}</td>
                                    <td class="text-end text-nowrap">${{ number_format($deal->estimated_value) }}</td>
                                    <td>{{ $deal->product_area ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ \App\Models\DealRegistration::STATUS_COLORS[$deal->status] ?? 'secondary' }}">
                                            {{ \App\Models\DealRegistration::STATUSES[$deal->status] ?? $deal->status }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">{{ $deal->expected_close_date?->format('M j, Y') ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $deal->submitted_at?->format('M j, Y') ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     Chart Scripts
     ══════════════════════════════════════════════════════════════════════ --}}
<script>
(function() {
    var cm = window.colorMap || {};
    var bv = (cm.bootstrapVars && cm.bootstrapVars.bodyColor) || null;
    var bodyColorHex = bv ? bv.hex : '#6c757d';

    var statusColors = @json(\App\Models\DealRegistration::STATUS_COLORS);
    var bsColorMap = {
        'secondary': '#868e96',
        'primary': '#0d6efd',
        'info': '#17a2b8',
        'success': '#28a745',
        'danger': '#dc3545',
        'warning': '#fd7e14'
    };
    var productColors = ['#0d6efd', '#17a2b8', '#28a745', '#fd7e14', '#886ab5'];

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
        new ApexCharts(document.querySelector('#dr-status-chart'), {
            chart: { type: 'donut', height: 280, toolbar: { show: false } },
            series: statusValues,
            labels: statusLabels,
            colors: statusChartColors,
            legend: { position: 'bottom', labels: { colors: bodyColorHex } },
            plotOptions: { pie: { donut: { size: '55%' } } },
            dataLabels: { enabled: true }
        }).render();
    } else {
        document.querySelector('#dr-status-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No deal data</p></div>';
    }

    // ── Pipeline Value by Product Area (bar) ──
    var valueData = @json($valueByProduct);
    var valueLabels = Object.keys(valueData);
    var valueValues = Object.values(valueData).map(Number);

    if (valueLabels.length > 0) {
        new ApexCharts(document.querySelector('#dr-value-product-chart'), {
            chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Pipeline Value', data: valueValues }],
            plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, distributed: true } },
            colors: productColors,
            xaxis: { categories: valueLabels, labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
            yaxis: { labels: { style: { colors: bodyColorHex }, formatter: function(val) { return '$' + (val / 1000).toFixed(0) + 'K'; } } },
            legend: { show: false },
            tooltip: { y: { formatter: function(val) { return '$' + val.toLocaleString(); } } }
        }).render();
    } else {
        document.querySelector('#dr-value-product-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No pipeline data</p></div>';
    }

    // ── SmartTables ──
    var el = document.querySelector('#dr-deals-table');
    if (window.SmartTables && el && el.querySelector('tbody tr td:not([colspan])')) {
        new window.SmartTables('dr-deals-table', {
            perPage: 10,
            sortable: true,
            searchable: true,
            export: true,
            plugins: window.ImportExportPlugin ? [window.ImportExportPlugin] : []
        });
    }
})();
</script>
