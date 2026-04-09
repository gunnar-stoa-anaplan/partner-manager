@extends('layouts.vertical', ['pageTitle' => 'Resource Path Configuration'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Resource Path Configuration', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubText' => 'Define resource progression paths with stages, gates, and task templates.'])
@endsection

@section('content')

{{-- Summary KPIs --}}
<div class="row mb-g">
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Total Paths</p>
                    <span class="h3 mb-0 fw-500">{{ $paths->count() }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-map-signs fa-lg text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Total Enrollments</p>
                    <span class="h3 mb-0 fw-500">{{ $paths->sum('enrollments_count') }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-users fa-lg text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">By Role Type</p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-info">Delivery: {{ $paths->filter(fn($p) => ($pathRoleTypes[$p->id] ?? '') === 'delivery')->count() }}</span>
                        <span class="badge bg-success">Sales: {{ $paths->filter(fn($p) => ($pathRoleTypes[$p->id] ?? '') === 'sales')->count() }}</span>
                        <span class="badge bg-warning text-dark">Pre-Sales: {{ $paths->filter(fn($p) => ($pathRoleTypes[$p->id] ?? '') === 'pre-sales')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Task Templates</p>
                    <span class="h3 mb-0 fw-500">{{ $paths->sum('task_templates_count') }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-tasks fa-lg text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Role Type Filter --}}
<div class="card shadow-0 mb-g">
    <div class="card-body py-2">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 fw-500 text-muted">Role Type:</label>
                <div class="btn-group btn-group-sm" id="roleTypeFilter">
                    <button class="btn btn-outline-secondary active" data-role="all">All</button>
                    <button class="btn btn-outline-info" data-role="delivery"><i class="fal fa-project-diagram me-1"></i>Delivery</button>
                    <button class="btn btn-outline-success" data-role="sales"><i class="fal fa-chart-line me-1"></i>Sales</button>
                    <button class="btn btn-outline-warning" data-role="pre-sales"><i class="fal fa-chalkboard-teacher me-1"></i>Pre-Sales</button>
                </div>
            </div>
            <div>
                <input type="text" id="pathSearch" class="form-control form-control-sm" placeholder="Search paths..." style="width: 220px;">
            </div>
        </div>
    </div>
</div>

{{-- Category Tabs --}}
<ul class="nav nav-tabs mb-g" id="categoryTabs">
    <li class="nav-item">
        <a class="nav-link active" href="#tab-general" data-bs-toggle="tab">
            General <span class="badge bg-secondary ms-1">{{ $categorized['general']->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#tab-platform" data-bs-toggle="tab">
            Platform <span class="badge bg-secondary ms-1">{{ $categorized['platform']->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#tab-product-group" data-bs-toggle="tab">
            Product Groups <span class="badge bg-secondary ms-1">{{ $categorized['product_group']->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#tab-applications" data-bs-toggle="tab">
            Applications <span class="badge bg-secondary ms-1">{{ $categorized['application']->count() }}</span>
        </a>
    </li>
</ul>

<div class="tab-content">
    {{-- General Tab --}}
    <div class="tab-pane fade show active" id="tab-general">
        <div class="row">
            {{-- Technical --}}
            <div class="col-xl-6 mb-g">
                <div class="card shadow-0 h-100">
                    <div class="card-header"><div class="card-title">General Technical</div></div>
                    <div class="card-body p-0">
                        @include('admin.resource-path-configuration._path-table', ['tablePaths' => $categorized['general']->filter(fn($p) => in_array($p->name, ['Model Builder', 'Solution Architect', 'Master Anaplanner']))])
                    </div>
                </div>
            </div>
            {{-- Non-Technical --}}
            <div class="col-xl-6 mb-g">
                <div class="card shadow-0 h-100">
                    <div class="card-header"><div class="card-title">General Non-Technical</div></div>
                    <div class="card-body p-0">
                        @include('admin.resource-path-configuration._path-table', ['tablePaths' => $categorized['general']->filter(fn($p) => in_array($p->name, ['Sales', 'Pre-Sales', 'Champion']))])
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Platform Tab --}}
    <div class="tab-pane fade" id="tab-platform">
        @php
            $platformGroups = $categorized['platform']->groupBy(fn($p) => explode(':', $p->name)[0]);
        @endphp
        <div class="row">
            @foreach($platformGroups as $techName => $techPaths)
            <div class="col-xl-6 col-xxl-4 mb-g">
                <div class="card shadow-0 h-100">
                    <div class="card-header"><div class="card-title">{{ trim($techName) }}</div></div>
                    <div class="card-body p-0">
                        @include('admin.resource-path-configuration._path-table', ['tablePaths' => $techPaths->sortBy('name')])
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Product Groups Tab --}}
    <div class="tab-pane fade" id="tab-product-group">
        <div class="row">
            @php
                $pgGroups = $categorized['product_group']->groupBy(fn($p) => explode(':', $p->name)[0]);
            @endphp
            @foreach($pgGroups as $groupName => $groupPaths)
            <div class="col-xl-6 col-xxl-3 mb-g">
                <div class="card shadow-0 h-100">
                    <div class="card-header"><div class="card-title">{{ trim($groupName) }}</div></div>
                    <div class="card-body p-0">
                        @include('admin.resource-path-configuration._path-table', ['tablePaths' => $groupPaths->sortBy('name')])
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Applications Tab --}}
    <div class="tab-pane fade" id="tab-applications">
        @foreach($appsByGroup as $groupName => $groupApps)
        @php
            $appGroups = $groupApps->groupBy(fn($p) => explode(':', $p->name)[0]);
        @endphp
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center justify-content-between py-2 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#appGroup-{{ Str::slug($groupName) }}">
                <div class="card-title mb-0">
                    <i class="fal fa-chevron-down me-1 collapse-icon"></i>{{ $groupName }}
                    <span class="badge bg-primary ms-2">{{ $groupApps->count() }} paths</span>
                    <span class="badge bg-light text-dark ms-1">{{ $appGroups->count() }} apps</span>
                </div>
            </div>
            <div class="collapse show" id="appGroup-{{ Str::slug($groupName) }}">
                <div class="card-body p-0">
                    @foreach($appGroups as $appName => $appPaths)
                    <div class="border-bottom path-app-group" data-app-name="{{ strtolower(trim($appName)) }}">
                        <div class="px-3 py-2 bg-light fw-500 small d-flex justify-content-between align-items-center">
                            <span>{{ trim($appName) }}</span>
                            <span class="badge bg-secondary">{{ $appPaths->count() }} paths</span>
                        </div>
                        @include('admin.resource-path-configuration._path-table', ['tablePaths' => $appPaths->sortBy('name'), 'compact' => true])
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Create New Path --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Create New Resource Path</div></div>
    <div class="card-body">
        <form action="{{ route('admin.resource-paths.store') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label fs-sm">Path Name</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g., Demand Planning: Delivery" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fs-sm">Description</label>
                <input type="text" name="description" class="form-control form-control-sm" placeholder="Optional description">
            </div>
            <div class="col-md-2">
                <div class="form-check mt-3">
                    <input type="checkbox" name="is_default" value="1" class="form-check-input" id="isDefault">
                    <label class="form-check-label fs-sm" for="isDefault">Default</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Create Path</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Role type filter
    document.querySelectorAll('#roleTypeFilter .btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#roleTypeFilter .btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            var role = this.dataset.role;
            document.querySelectorAll('tr[data-role-type]').forEach(function(row) {
                row.style.display = (role === 'all' || row.dataset.roleType === role) ? '' : 'none';
            });
        });
    });

    // Search filter
    var searchInput = document.getElementById('pathSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var query = this.value.toLowerCase();
            document.querySelectorAll('tr[data-path-name]').forEach(function(row) {
                row.style.display = row.dataset.pathName.includes(query) ? '' : 'none';
            });
            // Also filter app groups
            document.querySelectorAll('.path-app-group').forEach(function(group) {
                var visibleRows = group.querySelectorAll('tr[data-path-name]:not([style*="display: none"])');
                group.style.display = visibleRows.length > 0 || group.dataset.appName.includes(query) ? '' : 'none';
            });
        });
    }
});
</script>
@endsection
