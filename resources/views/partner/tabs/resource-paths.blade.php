{{-- Resource Paths Tab: Individual resource progression within this partner --}}

{{-- KPI Row --}}
<div class="row mb-g">
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Enrolled Resources</p>
                    <span class="h3 mb-0 fw-500">{{ $enrollments->count() }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-users fa-lg text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Active</p>
                    <span class="h3 mb-0 fw-500">{{ $enrollments->where('status', 'active')->count() }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-check-circle fa-lg text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Avg Days in Stage</p>
                    <span class="h3 mb-0 fw-500">
                        {{ $enrollments->where('status', 'active')->isNotEmpty() ? round($enrollments->where('status', 'active')->avg(fn($e) => $e->stage_entered_at->diffInDays(now()))) : 0 }}
                    </span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-clock fa-lg text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Stage Distribution</p>
                    <div class="d-flex flex-wrap gap-1">
                        @forelse($stageDistribution as $stageName => $count)
                        <span class="badge bg-light text-dark">{{ $stageName }}: {{ $count }}</span>
                        @empty
                        <span class="text-muted small">—</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Enrollment Table --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title">Resource Enrollments</div>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#enrollResourceForm">
            <i class="fal fa-plus me-1"></i>Enroll Resource
        </button>
    </div>

    {{-- Enroll Form (collapsed) --}}
    <div class="collapse" id="enrollResourceForm">
        <div class="card-body border-bottom bg-light">
            <form action="{{ route('plan.resource-paths.enroll') }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <input type="hidden" name="partner_id" value="{{ $partner->id }}">
                <div class="col-md-5">
                    <label class="form-label fs-sm">Resource</label>
                    <select name="contact_id" class="form-select form-select-sm" required>
                        <option value="">Select a contact...</option>
                        @foreach($contacts as $c)
                        <option value="{{ $c->id }}">{{ $c->full_name }} ({{ ucfirst($c->contact_type) }}{{ $c->resource_role ? ' — ' . (\App\Models\Contact::RESOURCE_ROLES[$c->resource_role] ?? $c->resource_role) : '' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fs-sm">Path</label>
                    <select name="resource_path_id" class="form-select form-select-sm" required>
                        @foreach($paths as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Enroll</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>Resource</th>
                    <th>Role</th>
                    <th>Path</th>
                    <th>Current Stage</th>
                    <th class="text-center">Days in Stage</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enrollment)
                <tr>
                    <td>
                        <div class="fw-500">{{ $enrollment->contact->full_name }}</div>
                        <small class="text-muted">{{ $enrollment->contact->email }}</small>
                    </td>
                    <td>
                        @if($enrollment->contact->resource_role)
                        <span class="badge bg-light text-dark">{{ \App\Models\Contact::RESOURCE_ROLES[$enrollment->contact->resource_role] ?? $enrollment->contact->resource_role }}</span>
                        @else
                        <span class="text-muted">{{ ucfirst($enrollment->contact->contact_type) }}</span>
                        @endif
                    </td>
                    <td><span class="fw-500">{{ $enrollment->path->name }}</span></td>
                    <td>
                        <span class="badge" style="background-color: {{ $enrollment->currentStage->color }}">
                            <i class="{{ $enrollment->currentStage->icon ?? 'fal fa-circle' }} me-1"></i>{{ $enrollment->currentStage->name }}
                        </span>
                    </td>
                    <td class="text-center">
                        @php $days = (int) $enrollment->stage_entered_at->diffInDays(now()); @endphp
                        <span class="{{ $days > 90 ? 'text-danger fw-bold' : ($days > 60 ? 'text-warning' : '') }}">{{ $days }}d</span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $enrollment->status === 'active' ? 'success' : ($enrollment->status === 'paused' ? 'warning' : 'secondary') }}">{{ ucfirst($enrollment->status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('plan.resource-paths.show', $enrollment) }}" class="btn btn-sm btn-outline-primary" title="View details">
                            <i class="fal fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fal fa-users fa-2x mb-2 d-block"></i>
                        No resources enrolled in paths for this partner yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
