{{-- App Enablement Matrix: Product readiness across pipeline progression --}}

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 1: KPI Cards
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row mb-g">
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Trained Resources</p>
                    <span class="h3 mb-0 fw-500">{{ $totalTrained }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-user-check fa-lg text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">In Progress</p>
                    <span class="h3 mb-0 fw-500">{{ $totalInProgress }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-spinner fa-lg text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Open Pipeline</p>
                    <span class="h3 mb-0 fw-500">{{ $totalPipeline }}</span>
                    <span class="text-muted small ms-1">accounts</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-chart-line fa-lg text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Customer Live</p>
                    <span class="h3 mb-0 fw-500">{{ $totalLive }}</span>
                    <span class="text-muted small ms-1">projects</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-check-circle fa-lg text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 2: Enablement Matrix Table
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12">
        <div class="card shadow-0">
            <div class="card-header d-flex align-items-center">
                <h4 class="card-title mb-0"><i class="fal fa-th me-2 text-primary"></i>Nicole's View &mdash; App Enablement Matrix</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.8rem;">
                        <thead>
                            <tr class="bg-fusion-50">
                                <th class="text-center" style="min-width: 160px;">Product</th>
                                <th class="text-center" style="min-width: 90px;">
                                    <div class="fw-700">Total</div>
                                    <div class="text-muted fw-400 small">Trained Resources</div>
                                </th>
                                <th class="text-center text-muted" style="width: 30px;"><i class="fal fa-chevron-right"></i></th>
                                <th class="text-center" style="min-width: 140px;">
                                    <div class="fw-700">Stage 3+</div>
                                    <div class="text-muted fw-400 small">Open Pipeline</div>
                                </th>
                                <th class="text-center text-muted" style="width: 30px;"><i class="fal fa-chevron-right"></i></th>
                                <th class="text-center" style="min-width: 140px;">
                                    <div class="fw-700">Won / Kick-off</div>
                                    <div class="text-muted fw-400 small">Delivery Readiness</div>
                                </th>
                                <th class="text-center text-muted" style="width: 30px;"><i class="fal fa-chevron-right"></i></th>
                                <th class="text-center" style="min-width: 140px;">
                                    <div class="fw-700">IHS</div>
                                    <div class="text-muted fw-400 small">Current Projects</div>
                                </th>
                                <th class="text-center text-muted" style="width: 30px;"><i class="fal fa-chevron-right"></i></th>
                                <th class="text-center" style="min-width: 140px;">
                                    <div class="fw-700">CS</div>
                                    <div class="text-muted fw-400 small">Customer Live</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($productLines as $lineName => $rows)
                                {{-- Product Line Header --}}
                                <tr class="bg-fusion-25">
                                    <td colspan="10" class="fw-700 text-uppercase small py-2 ps-3">
                                        @php
                                            $lineAbbreviations = [
                                                'Finance' => 'FIN',
                                                'Supply Chain' => 'SCM',
                                                'Sales' => 'SPM',
                                                'Workforce' => 'WFP',
                                                'Platform' => 'PLT',
                                            ];
                                        @endphp
                                        <span class="badge bg-primary bg-opacity-15 text-primary me-1">{{ $lineAbbreviations[$lineName] ?? strtoupper(substr($lineName, 0, 3)) }}</span>
                                        {{ $lineName }}
                                    </td>
                                </tr>

                                @foreach ($rows as $row)
                                    @php $product = $row['product']; @endphp
                                    <tr>
                                        {{-- Product Name --}}
                                        <td class="ps-4">
                                            <span class="fw-500">{{ $product->name }}</span>
                                            @if ($product->code)
                                                <span class="text-muted ms-1 small">({{ $product->code }})</span>
                                            @endif
                                        </td>

                                        {{-- Total Trained Resources --}}
                                        <td class="text-center">
                                            @if ($row['total_resources'] > 0)
                                                <span class="fw-700 {{ $row['trained_count'] > 0 ? 'text-success' : 'text-warning' }}">{{ $row['trained_count'] }}</span>
                                                @if ($row['in_progress_count'] > 0 || $row['workshop_count'] > 0)
                                                    <br><span class="text-muted small">+{{ $row['in_progress_count'] + $row['workshop_count'] }} in prog</span>
                                                @endif
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>

                                        {{-- Chevron --}}
                                        <td class="text-center text-muted"><i class="fal fa-chevron-right small"></i></td>

                                        {{-- Stage 3+ (Open Pipeline) --}}
                                        <td>
                                            @if ($row['pipeline_opps']->isNotEmpty())
                                                @foreach ($row['pipeline_opps']->take(3) as $account)
                                                    <span class="badge bg-warning bg-opacity-15 text-warning me-1 mb-1">{{ $account }}</span>
                                                @endforeach
                                                @if ($row['pipeline_opps']->count() > 3)
                                                    <span class="text-muted small">+{{ $row['pipeline_opps']->count() - 3 }} more</span>
                                                @endif
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>

                                        {{-- Chevron --}}
                                        <td class="text-center text-muted"><i class="fal fa-chevron-right small"></i></td>

                                        {{-- Won / Kick-off --}}
                                        <td>
                                            @if ($row['kickoff_projects']->isNotEmpty())
                                                @foreach ($row['kickoff_projects']->take(3) as $account)
                                                    <span class="badge bg-info bg-opacity-15 text-info me-1 mb-1">{{ $account }}</span>
                                                @endforeach
                                                @if ($row['kickoff_projects']->count() > 3)
                                                    <span class="text-muted small">+{{ $row['kickoff_projects']->count() - 3 }} more</span>
                                                @endif
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>

                                        {{-- Chevron --}}
                                        <td class="text-center text-muted"><i class="fal fa-chevron-right small"></i></td>

                                        {{-- IHS (Implementation) --}}
                                        <td>
                                            @if ($row['implementation_projects']->isNotEmpty())
                                                @foreach ($row['implementation_projects']->take(3) as $account)
                                                    <span class="badge bg-primary bg-opacity-15 text-primary me-1 mb-1">{{ $account }}</span>
                                                @endforeach
                                                @if ($row['implementation_projects']->count() > 3)
                                                    <span class="text-muted small">+{{ $row['implementation_projects']->count() - 3 }} more</span>
                                                @endif
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>

                                        {{-- Chevron --}}
                                        <td class="text-center text-muted"><i class="fal fa-chevron-right small"></i></td>

                                        {{-- CS (Customer Live) --}}
                                        <td>
                                            @if ($row['live_projects']->isNotEmpty())
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1" style="height: 8px;">
                                                        <div class="progress-bar bg-success" role="progressbar"
                                                             style="width: {{ min(100, $row['live_projects']->count() * 25) }}%"></div>
                                                    </div>
                                                    <span class="ms-2 fw-700 text-success small">{{ $row['live_projects']->count() }}</span>
                                                </div>
                                                <div class="mt-1">
                                                    @foreach ($row['live_projects']->take(2) as $account)
                                                        <span class="badge bg-success bg-opacity-15 text-success me-1 mb-1">{{ $account }}</span>
                                                    @endforeach
                                                    @if ($row['live_projects']->count() > 2)
                                                        <span class="text-muted small">+{{ $row['live_projects']->count() - 2 }} more</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fal fa-info-circle me-1"></i> No products configured. Run the ProductSeeder to populate the catalog.
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
