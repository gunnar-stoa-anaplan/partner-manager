@extends('layouts.vertical', ['pageTitle' => 'Partner Assignments'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Partner Assignments', 'pageSubTitle1' => 'Partner Admin', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Partner Assignments maps each partner to their assigned PSM. Use this view to reassign partners between team members, balance workloads, and ensure every partner has a clear owner. Changes here immediately update what each PSM sees in their My Partners view.</p>
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Partner Assignments</div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="alert alert-info mb-3">
                    View and manage partner-to-PSM assignments. Click the edit button to change a partner's assigned PSM.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100" id="partner-assignments-table">
                        <thead>
                            <tr>
                                <th>Partner Name</th>
                                <th>Assigned PSM</th>
                                <th>Geography</th>
                                <th>Region</th>
                                <th>Industry</th>
                                <th class="text-center" style="width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($partners as $partner)
                            <tr>
                                <td><strong>{{ $partner->name }}</strong></td>
                                <td>
                                    @if($partner->user)
                                        <span class="badge bg-success">{{ $partner->user->name }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Unassigned</span>
                                    @endif
                                </td>
                                <td>{{ $partner->geography ?? '-' }}</td>
                                <td>{{ $partner->region ?? '-' }}</td>
                                <td>{{ $partner->industry ?? '-' }}</td>
                                <td class="text-center text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-psm-btn"
                                            data-partner-id="{{ $partner->id }}"
                                            data-partner-name="{{ $partner->name }}"
                                            data-current-user-id="{{ $partner->user_id }}"
                                            title="Edit PSM Assignment">
                                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit-2"></use></svg>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No partners found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- PSM Assignment Modal --}}
<div class="modal fade" id="editPsmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editPsmForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit PSM Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Assign a PSM for <strong id="modalPartnerName"></strong>:</p>
                    <div class="mb-3">
                        <label class="form-label" for="modal_user_id">Assigned PSM</label>
                        <select class="form-select" id="modal_user_id" name="user_id">
                            <option value="">-- Unassigned --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role?->display_name ?? 'Unknown' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#save"></use></svg>
                        Save Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/table-init.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // SmartTable initialization
            if (window.SmartTables && document.querySelector('#partner-assignments-table tbody tr td:not([colspan])')) {
                new window.SmartTables('partner-assignments-table', {
                    perPage: 25,
                    responsive: {
                        enabled: true,
                        breakpoint: 768,
                        columnPriorities: { 0: 1, 1: 2, 5: 3, 2: 4, 3: 5, 4: 6 }
                    }
                });
            }

            // PSM edit modal
            var modal = new bootstrap.Modal(document.getElementById('editPsmModal'));
            var form = document.getElementById('editPsmForm');
            var partnerNameEl = document.getElementById('modalPartnerName');
            var userSelect = document.getElementById('modal_user_id');

            // Use event delegation so clicks work after SmartTables re-renders rows
            document.getElementById('partner-assignments-table').addEventListener('click', function(e) {
                var btn = e.target.closest('.edit-psm-btn');
                if (!btn) return;

                var partnerId = btn.getAttribute('data-partner-id');
                var partnerName = btn.getAttribute('data-partner-name');
                var currentUserId = btn.getAttribute('data-current-user-id');

                partnerNameEl.textContent = partnerName;
                userSelect.value = currentUserId || '';
                form.action = '/partners/' + partnerId;

                modal.show();
            });
        });
    </script>
@endsection
