@extends('layouts.vertical', ['pageTitle' => $role ? 'Edit Role' : 'Create Role'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $role ? 'Edit Role: ' . $role->display_name : 'Create Role',
        'pageSubTitle1' => 'Administration',
        'pageSubTitle1Url' => route('admin.dashboard'),
        'pageSubTitle2' => 'Role Management',
        'pageSubTitle2Url' => route('admin.roles.index')
    ])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">
    @if($role)
        Edit role settings and configure which modules this role can access.
        @if($role->is_protected)
            This is a protected system role — slug, type, and bypass settings cannot be changed.
        @endif
    @else
        Create a new role and configure its module permissions.
    @endif
</p>

<form action="{{ $role ? route('admin.roles.update', $role) : route('admin.roles.store') }}" method="POST">
    @csrf
    @if($role)
        @method('PUT')
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- Left Column: Role Details --}}
        <div class="col-xl-5">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Role Details</div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="display_name">Display Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('display_name') is-invalid @enderror"
                               id="display_name" name="display_name"
                               value="{{ old('display_name', $role?->display_name) }}" required>
                        @error('display_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="slug">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror"
                               id="slug" name="slug"
                               value="{{ old('slug', $role?->slug) }}"
                               required pattern="[a-z][a-z0-9_]*"
                               {{ $role?->is_protected ? 'disabled' : '' }}>
                        @if($role?->is_protected)
                            <input type="hidden" name="slug" value="{{ $role->slug }}">
                        @endif
                        <div class="form-text">Lowercase letters, numbers, and underscores only.</div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror"
                                id="type" name="type" required
                                {{ $role?->is_protected ? 'disabled' : '' }}>
                            @php $typeValue = old('type', $role?->type ?? request('type', 'internal')); @endphp
                            <option value="internal" {{ $typeValue === 'internal' ? 'selected' : '' }}>Internal</option>
                            <option value="portal" {{ $typeValue === 'portal' ? 'selected' : '' }}>Portal</option>
                        </select>
                        @if($role?->is_protected)
                            <input type="hidden" name="type" value="{{ $role->type }}">
                        @endif
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="2">{{ old('description', $role?->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="landing_route">Landing Route</label>
                        <input type="text" class="form-control @error('landing_route') is-invalid @enderror"
                               id="landing_route" name="landing_route"
                               value="{{ old('landing_route', $role?->landing_route) }}"
                               placeholder="e.g. welcome, admin.dashboard">
                        <div class="form-text">Named route for post-login redirect.</div>
                        @error('landing_route')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="dashboard-group-field" style="{{ old('type', $role?->type ?? request('type')) === 'portal' ? '' : 'display: none;' }}">
                        <label class="form-label" for="dashboard_group">Dashboard Group</label>
                        <select class="form-select @error('dashboard_group') is-invalid @enderror"
                                id="dashboard_group" name="dashboard_group">
                            <option value="">— None —</option>
                            @php $dgValue = old('dashboard_group', $role?->dashboard_group); @endphp
                            <option value="admin" {{ $dgValue === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="sales" {{ $dgValue === 'sales' ? 'selected' : '' }}>Sales</option>
                            <option value="delivery" {{ $dgValue === 'delivery' ? 'selected' : '' }}>Delivery</option>
                        </select>
                        <div class="form-text">Portal roles only — determines dashboard layout.</div>
                        @error('dashboard_group')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="sort_order">Sort Order</label>
                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                               id="sort_order" name="sort_order"
                               value="{{ old('sort_order', $role?->sort_order ?? 0) }}" min="0" max="999">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if(!$role?->is_protected)
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="hidden" name="bypass_permissions" value="0">
                            <input type="checkbox" class="form-check-input" id="bypass_permissions" name="bypass_permissions"
                                   value="1" {{ old('bypass_permissions', $role?->bypass_permissions) ? 'checked' : '' }}>
                            <label class="form-check-label" for="bypass_permissions">
                                Bypass all permissions
                            </label>
                        </div>
                        <div class="form-text">When enabled, this role has unrestricted access to all modules. The permission matrix below is ignored.</div>
                    </div>
                    @endif

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            {{ $role ? 'Update Role' : 'Create Role' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Permission Matrix --}}
        <div class="col-xl-7" id="permission-matrix" style="{{ ($role?->bypass_permissions && !$role?->is_protected) ? 'opacity: 0.4; pointer-events: none;' : '' }}">
            @if($role?->bypass_permissions)
                <div class="alert alert-warning mb-3">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#shield"></use></svg>
                    This role bypasses all permission checks. The matrix below is informational only.
                </div>
            @endif

            @foreach($moduleGroups as $groupName => $modules)
            <div class="card shadow-0 mb-g">
                <div class="card-header py-2">
                    <div class="card-title small fw-500">{{ $groupName }}</div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Module</th>
                                <th class="text-center" style="width: 80px;">None</th>
                                <th class="text-center" style="width: 80px;">Read</th>
                                <th class="text-center" style="width: 80px;">Write</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modules as $slug => $label)
                            @php
                                $currentLevel = old("permissions.{$slug}", $role?->accessLevel($slug));
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <span class="small">{{ $label }}</span>
                                    <code class="text-muted ms-1" style="font-size: 0.7rem;">{{ $slug }}</code>
                                </td>
                                <td class="text-center">
                                    <input type="radio" class="form-check-input"
                                           name="permissions[{{ $slug }}]" value=""
                                           {{ !$currentLevel ? 'checked' : '' }}>
                                </td>
                                <td class="text-center">
                                    <input type="radio" class="form-check-input"
                                           name="permissions[{{ $slug }}]" value="read"
                                           {{ $currentLevel === 'read' ? 'checked' : '' }}>
                                </td>
                                <td class="text-center">
                                    <input type="radio" class="form-check-input"
                                           name="permissions[{{ $slug }}]" value="write"
                                           {{ $currentLevel === 'write' ? 'checked' : '' }}>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle dashboard_group visibility based on type selection
    const typeSelect = document.getElementById('type');
    const dgField = document.getElementById('dashboard-group-field');
    if (typeSelect && dgField) {
        typeSelect.addEventListener('change', function() {
            dgField.style.display = this.value === 'portal' ? '' : 'none';
        });
    }

    // Toggle permission matrix opacity based on bypass checkbox
    const bypassCheck = document.getElementById('bypass_permissions');
    const matrix = document.getElementById('permission-matrix');
    if (bypassCheck && matrix) {
        bypassCheck.addEventListener('change', function() {
            matrix.style.opacity = this.checked ? '0.4' : '';
            matrix.style.pointerEvents = this.checked ? 'none' : '';
        });
    }
});
</script>
@endpush
@endsection
