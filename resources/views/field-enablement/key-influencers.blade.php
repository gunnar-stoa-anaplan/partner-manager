@extends('layouts.vertical', ['pageTitle' => 'Key Influencers'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Key Influencers', 'pageSubTitle1' => 'Engage', 'pageSubTitle1Url' => route('welcome'), 'pageSubTitle2' => 'Field Enablement', 'pageSubTitle2Url' => route('influence_map'), 'pageSubText' => $partner->name])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Key Influencers highlights the individual contacts within this partner organization who drive the most impact — whether through deal origination, delivery leadership, or executive sponsorship. Use this view to know who to engage for specific initiatives and to ensure your most valuable relationships stay active and well-supported.</p>
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
                        {{ $withProjects }}
                    </h3>
                    <span class="m-0 l-h-n">On Projects</span>
                </div>
                <i class="fal fa-clipboard position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
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
                <i class="fal fa-chart-bar position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $topInfluencers }}
                    </h3>
                    <span class="m-0 l-h-n">Top Influencers (3+)</span>
                </div>
                <i class="fal fa-star position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-g">
        <div class="col-lg-6">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">Project Involvement</div>
                </div>
                <div class="card-body">
                    <div id="involvement-chart" style="height: 280px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">Contacts by Role</div>
                </div>
                <div class="card-body">
                    <div id="role-chart" style="height: 280px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- View Toggle + Filter --}}
    <div class="row mb-g">
        <div class="col-12">
            <div class="card shadow-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <input type="text" id="contact-filter" class="form-control" placeholder="Filter contacts by name, title, or email...">
                    <div class="btn-group flex-shrink-0" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-view="grid" title="Grid view">
                            <i class="fal fa-th-large"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-view="table" title="Table view">
                            <i class="fal fa-th-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid View --}}
    <div id="grid-view" class="row">
        @forelse($contacts as $contact)
            <div class="col-xl-4 col-lg-6 contact-item" data-filter="{{ strtolower($contact->first_name . ' ' . $contact->last_name . ' ' . $contact->title . ' ' . $contact->email) }}">
                <div class="card shadow-0 mb-g">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="d-inline-block rounded-circle bg-primary-100 text-primary text-center fw-500 me-3 flex-shrink-0"
                                  style="width: 48px; height: 48px; line-height: 48px; font-size: 16px;">
                                {{ strtoupper(substr($contact->first_name, 0, 1)) }}{{ strtoupper(substr($contact->last_name, 0, 1)) }}
                            </span>
                            <div class="flex-grow-1 min-width-0">
                                <h5 class="mb-0 text-truncate">{{ $contact->first_name }} {{ $contact->last_name }}</h5>
                                <small class="text-muted text-truncate d-block">{{ $contact->title ?: 'No title' }}</small>
                            </div>
                            @if($contact->projects_count >= 3)
                                <span class="badge bg-warning text-dark ms-2 flex-shrink-0" title="Top Influencer">
                                    <i class="fal fa-star me-1"></i>{{ $contact->projects_count }}
                                </span>
                            @elseif($contact->projects_count > 0)
                                <span class="badge bg-primary-100 text-primary ms-2 flex-shrink-0">
                                    {{ $contact->projects_count }} {{ $contact->projects_count === 1 ? 'project' : 'projects' }}
                                </span>
                            @endif
                        </div>
                        <div class="ps-0">
                            @if($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="d-block fs-sm text-muted mb-1 text-truncate">
                                    <i class="fal fa-envelope me-2 text-primary"></i>{{ $contact->email }}
                                </a>
                            @endif
                            @if($contact->phone)
                                <a href="tel:{{ $contact->phone }}" class="d-block fs-sm text-muted mb-1">
                                    <i class="fal fa-phone me-2 text-primary"></i>{{ $contact->phone }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-0 mb-g">
                    <div class="card-body text-center py-5">
                        <i class="fal fa-users fa-3x text-muted opacity-50 mb-3"></i>
                        <h5 class="text-muted">No contacts found</h5>
                        <p class="text-muted mb-0">No contacts have been added for {{ $partner->name }} yet.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Table View (hidden by default) --}}
    <div id="table-view" class="row d-none">
        <div class="col-12">
            <div class="card shadow-0 mb-g">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="influencers-table" class="table table-bordered table-hover table-striped w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Title</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th class="text-center">Projects</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contacts as $contact)
                                    <tr class="contact-row" data-filter="{{ strtolower($contact->first_name . ' ' . $contact->last_name . ' ' . $contact->title . ' ' . $contact->email) }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="d-inline-block rounded-circle bg-primary-100 text-primary text-center fw-500 me-2"
                                                      style="width: 30px; height: 30px; line-height: 30px; font-size: 11px;">
                                                    {{ strtoupper(substr($contact->first_name, 0, 1)) }}{{ strtoupper(substr($contact->last_name, 0, 1)) }}
                                                </span>
                                                <strong>{{ $contact->first_name }} {{ $contact->last_name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $contact->title ?? '-' }}</td>
                                        <td>
                                            @if($contact->email)
                                                <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $contact->phone ?? '-' }}</td>
                                        <td class="text-center">
                                            @if($contact->projects_count >= 3)
                                                <span class="badge bg-warning text-dark">{{ $contact->projects_count }}</span>
                                            @elseif($contact->projects_count > 0)
                                                <span class="badge bg-primary">{{ $contact->projects_count }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
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
    @vite(['resources/scripts/pages/table-init.js'])
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var cm = window.colorMap || {};
        var pri = (cm.primary || {});
        var bsVars = (cm.bootstrapVars || {});
        var bodyColorHex = (bsVars.bodyColor || {}).hex || '#666';
        var bodyColor01 = (bsVars.bodyColor || { rgba: function() { return 'rgba(0,0,0,0.1)'; }}).rgba(0.1);

        // SmartTables for table view
        if (window.SmartTables && document.querySelector('#influencers-table tbody tr')) {
            new window.SmartTables('influencers-table', {
                perPage: 15,
                sortable: true,
                responsive: {
                    enabled: true,
                    breakpoint: 768,
                    columnPriorities: { 0: 1, 4: 2, 1: 3, 2: 4, 3: 5 }
                }
            });
        }

        // View toggle
        document.querySelectorAll('[data-view]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('[data-view]').forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                if (this.dataset.view === 'grid') {
                    document.getElementById('grid-view').classList.remove('d-none');
                    document.getElementById('table-view').classList.add('d-none');
                } else {
                    document.getElementById('grid-view').classList.add('d-none');
                    document.getElementById('table-view').classList.remove('d-none');
                }
            });
        });

        // Filter
        document.getElementById('contact-filter').addEventListener('input', function() {
            var val = this.value.toLowerCase();
            document.querySelectorAll('.contact-item').forEach(function(el) {
                el.style.display = el.dataset.filter.includes(val) ? '' : 'none';
            });
            document.querySelectorAll('.contact-row').forEach(function(el) {
                el.style.display = el.dataset.filter.includes(val) ? '' : 'none';
            });
        });

        // Involvement Chart (Column)
        var involvementData = @json($involvementBuckets);
        var invLabels = Object.keys(involvementData);
        var invValues = Object.values(involvementData);

        if (invValues.some(function(v) { return v > 0; })) {
            new ApexCharts(document.querySelector('#involvement-chart'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Contacts', data: invValues }],
                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, distributed: true } },
                colors: [
                    ((cm.danger || {})[500] || {}).hex || '#fd3995',
                    ((cm.warning || {})[500] || {}).hex || '#ffc241',
                    (pri[500] || {}).hex || '#886ab5',
                    ((cm.success || {})[500] || {}).hex || '#1dc9b7'
                ],
                xaxis: {
                    categories: invLabels.map(function(l) { return l + ' projects'; }),
                    labels: { style: { colors: bodyColorHex, fontSize: '11px' } }
                },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } }, forceNiceScale: true },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else {
            document.querySelector('#involvement-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }

        // Role Chart (Horizontal Bar)
        var roleData = @json($byTitle);
        var roleLabels = Object.keys(roleData);
        var roleValues = Object.values(roleData);

        if (roleLabels.length > 0) {
            new ApexCharts(document.querySelector('#role-chart'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Contacts', data: roleValues }],
                plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3, distributed: true } },
                colors: [
                    (pri[500] || {}).hex || '#886ab5',
                    (pri[400] || {}).hex || '#9B7FC5',
                    (pri[300] || {}).hex || '#B09AD5',
                    ((cm.info || {})[500] || {}).hex || '#33b5e5',
                    ((cm.success || {})[500] || {}).hex || '#1dc9b7',
                    ((cm.warning || {})[500] || {}).hex || '#ffc241',
                    (pri[600] || {}).hex || '#7559a0',
                    ((cm.danger || {})[500] || {}).hex || '#fd3995'
                ],
                xaxis: {
                    categories: roleLabels,
                    labels: { style: { colors: bodyColorHex, fontSize: '11px' } }
                },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#fff'] } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else {
            document.querySelector('#role-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }
    });
    </script>
@endsection
