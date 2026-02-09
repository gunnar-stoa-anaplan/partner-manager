@extends('layouts.vertical', ['pageTitle' => 'My Nominations'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'My Nominations', 'pageSubTitle1' => 'Partner Enablement', 'pageSubText' => 'Track all contacts you\'ve nominated for enablement sessions'])
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        <i class="fal fa-user-check me-2"></i> My Nominations
                    </div>
                    <a href="{{ route('enablement.calendar') }}" class="btn btn-outline-primary btn-sm">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#calendar"></use></svg>
                        Enablement Calendar
                    </a>
                </div>
                <div class="card-body">
                    <div id="nominations-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Loading nominations...</p>
                    </div>

                    <div id="nominations-empty" style="display:none;" class="text-center py-5">
                        <div class="mb-3">
                            <i class="fal fa-user-check fa-3x text-muted opacity-50"></i>
                        </div>
                        <h5 class="fw-400 text-muted">No nominations yet</h5>
                        <p class="text-muted mb-3">Visit the Enablement Calendar to nominate contacts for upcoming sessions.</p>
                        <a href="{{ route('enablement.calendar') }}" class="btn btn-primary btn-sm">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#calendar"></use></svg>
                            Browse Sessions
                        </a>
                    </div>

                    <div id="nominations-table-container" style="display:none;">
                        <table class="table table-bordered table-hover table-striped w-100" id="nominations-table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Session Date</th>
                                    <th>Contact</th>
                                    <th>Partner</th>
                                    <th>Status</th>
                                    <th>Nominated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="nominations-tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100" id="toast-container"></div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/table-init.js'])
    <script>
        (function() {
            var apiBaseUrl = '{{ url("/api") }}';
            var csrfToken = '{{ csrf_token() }}';

            function loadNominations() {
                fetch(apiBaseUrl + '/enablement/my-nominations', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    document.getElementById('nominations-loading').style.display = 'none';

                    if (!data || data.length === 0) {
                        document.getElementById('nominations-empty').style.display = 'block';
                        return;
                    }

                    document.getElementById('nominations-table-container').style.display = 'block';
                    var tbody = document.getElementById('nominations-tbody');
                    tbody.innerHTML = '';

                    data.forEach(function(n) {
                        var dateStr = n.session_start ? new Date(n.session_start).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
                        var nominatedStr = n.nominated_at ? new Date(n.nominated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';

                        var actionsHtml = '';
                        if (n.can_remove) {
                            actionsHtml = '<button class="btn btn-sm btn-outline-danger remove-nomination" data-id="' + n.id + '" title="Remove Nomination">' +
                                '<svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#x"></use></svg>' +
                                '</button>';
                        }

                        var row = '<tr>' +
                            '<td><strong>' + (n.event_title || '') + '</strong><br><small class="text-muted">' + (n.session_location || '') + '</small></td>' +
                            '<td class="text-nowrap">' + dateStr + '</td>' +
                            '<td>' + (n.contact_name || '') + '<br><small class="text-muted">' + (n.contact_email || '') + '</small></td>' +
                            '<td>' + (n.partner_name || '') + '</td>' +
                            '<td><span class="badge bg-' + (n.status_color || 'secondary') + '">' + (n.status_label || n.status) + '</span></td>' +
                            '<td class="text-nowrap">' + nominatedStr + '</td>' +
                            '<td class="text-nowrap">' + actionsHtml + '</td>' +
                            '</tr>';
                        tbody.insertAdjacentHTML('beforeend', row);
                    });

                    // Init SmartTables
                    if (window.SmartTables) {
                        new window.SmartTables('nominations-table', {
                            perPage: 15,
                            responsive: {
                                enabled: true,
                                breakpoint: 768,
                                columnPriorities: { 0: 1, 4: 2, 2: 3, 3: 4, 1: 5, 5: 6, 6: 7 }
                            }
                        });
                    }

                    // Remove nomination handlers
                    document.querySelectorAll('.remove-nomination').forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            if (!confirm('Remove this nomination?')) return;
                            var id = this.getAttribute('data-id');
                            fetch(apiBaseUrl + '/enablement/registrations/' + id, {
                                method: 'DELETE',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                            })
                            .then(function(r) { return r.json(); })
                            .then(function() { location.reload(); })
                            .catch(function(err) { alert('Failed to remove nomination.'); });
                        });
                    });
                })
                .catch(function(err) {
                    document.getElementById('nominations-loading').innerHTML = '<p class="text-danger">Failed to load nominations.</p>';
                });
            }

            loadNominations();
        })();
    </script>
@endsection
