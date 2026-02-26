@extends('layouts.vertical', ['pageTitle' => 'Reporting Hierarchy'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Reporting Hierarchy', 'pageSubTitle1' => 'Team Management'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Define who reports to whom across the partner team. Changes here determine the management chain used for visibility, approvals, and team-level reporting.</p>
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Reporting Hierarchy</div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="alert alert-info mb-3">
                    View and manage the reporting hierarchy. Click the edit button to change a user's manager.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100" id="hierarchy-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Manager</th>
                                <th>Direct Reports</th>
                                <th class="text-center" style="width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td><span class="badge bg-secondary">{{ $user->role?->display_name ?? 'No Role' }}</span></td>
                                <td>
                                    @if($user->manager)
                                        <span class="badge bg-success">{{ $user->manager->name }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">None</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->directReports->count() > 0)
                                        <span class="badge bg-info">{{ $user->directReports->count() }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-manager-btn"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            data-current-manager-id="{{ $user->manager_id }}"
                                            title="Edit Manager">
                                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit-2"></use></svg>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Manager Assignment Modal --}}
<div class="modal fade" id="editManagerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editManagerForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Manager</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Assign a manager for <strong id="modalUserName"></strong>:</p>
                    <div class="mb-3">
                        <label class="form-label" for="modal_manager_id">Manager</label>
                        <select class="form-select" id="modal_manager_id" name="manager_id">
                            <option value="">-- None --</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}">{{ $manager->name }} ({{ $manager->role?->display_name ?? 'No Role' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#save"></use></svg>
                        Save
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
            if (window.SmartTables && document.querySelector('#hierarchy-table tbody tr td:not([colspan])')) {
                new window.SmartTables('hierarchy-table', {
                    perPage: 25,
                    responsive: {
                        enabled: true,
                        breakpoint: 768,
                        columnPriorities: { 0: 1, 1: 2, 2: 3, 4: 4, 3: 5 }
                    }
                });
            }

            // Manager edit modal
            var modal = new bootstrap.Modal(document.getElementById('editManagerModal'));
            var form = document.getElementById('editManagerForm');
            var userNameEl = document.getElementById('modalUserName');
            var managerSelect = document.getElementById('modal_manager_id');
            var currentUserId = null;

            // Use event delegation so clicks work after SmartTables re-renders rows
            document.getElementById('hierarchy-table').addEventListener('click', function(e) {
                var btn = e.target.closest('.edit-manager-btn');
                if (!btn) return;

                currentUserId = btn.getAttribute('data-user-id');
                var userName = btn.getAttribute('data-user-name');
                var currentManagerId = btn.getAttribute('data-current-manager-id');

                userNameEl.textContent = userName;

                // Enable all options, then disable self-assignment
                Array.from(managerSelect.options).forEach(function(opt) {
                    opt.disabled = false;
                    opt.hidden = false;
                });
                var selfOption = managerSelect.querySelector('option[value="' + currentUserId + '"]');
                if (selfOption) {
                    selfOption.disabled = true;
                    selfOption.hidden = true;
                }

                managerSelect.value = currentManagerId || '';
                form.action = '/reporting-hierarchy/' + currentUserId;

                modal.show();
            });
        });
    </script>
@endsection
