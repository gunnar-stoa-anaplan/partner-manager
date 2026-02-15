@extends('layouts.vertical', ['pageTitle' => $lab->name])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $lab->name, 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubTitle2' => 'Workshop Labs', 'pageSubTitle2Url' => route('labs.index'), 'pageSubText' => \App\Models\Lab::STATUSES[$lab->status] ?? ''])
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Lab Info Card --}}
    <div class="row mb-g">
        <div class="col-lg-12">
            <div class="card shadow-0">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        {{ $lab->name }}
                        @if($lab->duration_minutes)
                            <small class="text-muted ms-2">{{ $lab->duration_minutes }} min</small>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        @if($lab->isDraft())
                            <form action="{{ route('labs.publish', $lab) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Publishing will capture a snapshot of the golden model. Continue?')">
                                    <i class="fal fa-rocket me-1"></i> Publish
                                </button>
                            </form>
                        @endif
                        @if($lab->isPublished() && $lab->session)
                            <a href="{{ route('sessions.lab-dashboard', $lab->session) }}" class="btn btn-sm btn-info">
                                <i class="fal fa-chart-bar me-1"></i> Facilitator Dashboard
                            </a>
                        @endif
                        <a href="{{ route('labs.edit', $lab) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fal fa-pencil me-1"></i> Edit
                        </a>
                        @if($lab->isDraft())
                            <form action="{{ route('labs.destroy', $lab) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this draft lab?')">
                                    <i class="fal fa-trash me-1"></i> Delete
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('labs.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fal fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge" style="background-color: {{ \App\Models\Lab::STATUS_COLORS[$lab->status] ?? '#868e96' }}">
                                {{ \App\Models\Lab::STATUSES[$lab->status] ?? $lab->status }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Session</small>
                            @if($lab->session)
                                <strong>{{ $lab->session->display_title }}</strong>
                                <br><small class="text-muted">{{ $lab->session->start_at->format('M j, Y') }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Golden Model</small>
                            @if($lab->goldenModel)
                                <strong>{{ Str::limit($lab->goldenModel->name, 40) }}</strong>
                                @if($lab->goldenModel->workspace)
                                    <br><small class="text-muted">{{ $lab->goldenModel->workspace->name }}</small>
                                @endif
                            @endif
                        </div>
                        <div class="col-md-1">
                            <small class="text-muted d-block">Tenant</small>
                            <span class="badge bg-dark">{{ strtoupper($lab->tenant ?? 'owp') }}</span>
                        </div>
                        <div class="col-md-1">
                            <small class="text-muted d-block">Checkpoints</small>
                            <strong>{{ $lab->checkpoints->count() }}</strong>
                            <small class="text-muted">({{ $lab->totalPoints() }} pts)</small>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Participants</small>
                            @php $stats = $lab->completionStats(); @endphp
                            <strong>{{ $stats['total'] }}</strong>
                            @if($stats['completed'] > 0)
                                <span class="badge bg-success bg-opacity-25 text-success ms-1">{{ $stats['completed'] }} done</span>
                            @endif
                        </div>
                    </div>
                    @if($lab->description)
                        <div class="mt-3">
                            <small class="text-muted d-block">Description</small>
                            <p class="mb-0">{{ $lab->description }}</p>
                        </div>
                    @endif
                    @if($lab->instructions)
                        <div class="mt-2">
                            <small class="text-muted d-block">Instructions</small>
                            <p class="mb-0">{!! nl2br(e($lab->instructions)) !!}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Checkpoints --}}
    <div class="row mb-g">
        <div class="col-lg-12">
            <div class="card shadow-0">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Checkpoints ({{ $lab->checkpoints->count() }})</div>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCheckpointModal">
                        <i class="fal fa-plus me-1"></i> Add Checkpoint
                    </button>
                </div>
                <div class="card-body p-0">
                    @if($lab->checkpoints->isEmpty())
                        <div class="p-4 text-muted text-center">No checkpoints defined. Add checkpoints to define what participants must validate.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:40px">#</th>
                                        <th>Checkpoint</th>
                                        <th>Type</th>
                                        <th class="text-center">Points</th>
                                        <th class="text-center">Required</th>
                                        <th class="text-center">Pass Rate</th>
                                        <th class="text-center">Avg Attempts</th>
                                        <th style="width:80px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lab->checkpoints as $checkpoint)
                                        @php $cpStats = $checkpointStats[$checkpoint->id] ?? []; @endphp
                                        <tr>
                                            <td>{{ $checkpoint->sort_order }}</td>
                                            <td>
                                                <strong>{{ $checkpoint->title }}</strong>
                                                @if($checkpoint->description)
                                                    <br><small class="text-muted">{{ Str::limit($checkpoint->description, 80) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: {{ \App\Models\LabCheckpoint::TYPE_COLORS[$checkpoint->checkpoint_type] ?? '#868e96' }}">
                                                    <i class="fal fa-{{ \App\Models\LabCheckpoint::TYPE_ICONS[$checkpoint->checkpoint_type] ?? 'check' }} me-1"></i>
                                                    {{ \App\Models\LabCheckpoint::CHECKPOINT_TYPES[$checkpoint->checkpoint_type] ?? $checkpoint->checkpoint_type }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $checkpoint->points }}</td>
                                            <td class="text-center">
                                                @if($checkpoint->is_required)
                                                    <i class="fas fa-check text-success"></i>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(($cpStats['total_attempts'] ?? 0) > 0)
                                                    <span class="badge {{ ($cpStats['pass_rate'] ?? 0) >= 75 ? 'bg-success' : (($cpStats['pass_rate'] ?? 0) >= 50 ? 'bg-warning' : 'bg-danger') }} bg-opacity-25 {{ ($cpStats['pass_rate'] ?? 0) >= 75 ? 'text-success' : (($cpStats['pass_rate'] ?? 0) >= 50 ? 'text-warning' : 'text-danger') }}">
                                                        {{ $cpStats['pass_rate'] }}%
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ ($cpStats['avg_attempts'] ?? 0) > 0 ? $cpStats['avg_attempts'] : '—' }}
                                            </td>
                                            <td class="text-nowrap">
                                                <button type="button" class="btn btn-xs btn-outline-primary me-1"
                                                    onclick="openEditCheckpointModal(this)"
                                                    data-id="{{ $checkpoint->id }}"
                                                    data-title="{{ e($checkpoint->title) }}"
                                                    data-description="{{ e($checkpoint->description) }}"
                                                    data-type="{{ $checkpoint->checkpoint_type }}"
                                                    data-config="{{ json_encode($checkpoint->validation_config) }}"
                                                    data-required="{{ $checkpoint->is_required ? '1' : '0' }}"
                                                    data-points="{{ $checkpoint->points }}"
                                                    data-hint="{{ e($checkpoint->hint) }}"
                                                    data-sort="{{ $checkpoint->sort_order }}">
                                                    <i class="fal fa-pencil"></i>
                                                </button>
                                                <form action="{{ route('lab-checkpoints.destroy', $checkpoint) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger" onclick="return confirm('Delete this checkpoint?')">
                                                        <i class="fal fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
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

    {{-- Golden Model Browser --}}
    @if($lab->goldenModel)
    <div class="row mb-g">
        <div class="col-lg-12">
            <div class="card shadow-0">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Golden Model Browser</div>
                    <button class="btn btn-sm btn-outline-info" onclick="loadModules()">
                        <i class="fal fa-sync me-1"></i> Load Modules
                    </button>
                    <button class="btn btn-sm btn-outline-info ms-2" onclick="loadLists()">
                        <i class="fal fa-list me-1"></i> Load Lists
                    </button>
                </div>
                <div class="card-body">
                    <div id="model-browser-loading" class="text-center py-3 d-none">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="ms-2 text-muted">Loading from Anaplan API...</span>
                    </div>
                    <div id="model-browser-content">
                        <p class="text-muted text-center mb-0">Click "Load Modules" or "Load Lists" to browse the golden model via the Anaplan API. Use the data to configure checkpoint validation.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Participants --}}
    <div class="row mb-g">
        <div class="col-lg-12">
            <div class="card shadow-0">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Participants ({{ $lab->participants->count() }})</div>
                    @if($availableContacts->isNotEmpty())
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addParticipantModal">
                            <i class="fal fa-user-plus me-1"></i> Enroll Participant
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($lab->participants->isEmpty())
                        <div class="p-4 text-muted text-center">No participants enrolled yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Participant</th>
                                        <th>Partner</th>
                                        <th>Assigned Model</th>
                                        <th>Status</th>
                                        <th class="text-center">Progress</th>
                                        <th class="text-center">Points</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lab->participants as $participant)
                                        <tr>
                                            <td>
                                                <strong>{{ $participant->contact->full_name }}</strong>
                                                <br><small class="text-muted">{{ $participant->contact->email }}</small>
                                            </td>
                                            <td>{{ $participant->partner->name ?? '—' }}</td>
                                            <td>
                                                @if($participant->assignedModel)
                                                    <small>{{ Str::limit($participant->assignedModel->name, 30) }}</small>
                                                @else
                                                    <span class="text-danger"><i class="fal fa-exclamation-triangle me-1"></i>Not assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: {{ \App\Models\LabParticipant::STATUS_COLORS[$participant->status] ?? '#868e96' }}">
                                                    {{ \App\Models\LabParticipant::STATUSES[$participant->status] ?? $participant->status }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $pct = $participant->completionPercentage(); @endphp
                                                <div class="progress" style="height: 6px; min-width: 60px;">
                                                    <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $pct }}%"></div>
                                                </div>
                                                <small>{{ $pct }}%</small>
                                            </td>
                                            <td class="text-center">
                                                {{ $participant->total_points_earned }}/{{ $participant->total_points_possible }}
                                            </td>
                                            <td class="text-end">
                                                <form action="{{ route('lab-participants.destroy', $participant) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger" onclick="return confirm('Remove this participant?')">
                                                        <i class="fal fa-user-minus"></i>
                                                    </button>
                                                </form>
                                            </td>
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

    {{-- Add Checkpoint Modal --}}
    <div class="modal fade" id="addCheckpointModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('labs.checkpoints.store', $lab) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Checkpoint</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" required placeholder="e.g. Verify Total Sales formula">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="checkpoint_type" required>
                                    @foreach(\App\Models\LabCheckpoint::CHECKPOINT_TYPES as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Points</label>
                                <input type="number" class="form-control" name="points" value="1" min="1">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Required</label>
                                <select class="form-select" name="is_required">
                                    <option value="1" selected>Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Validation Config (JSON) <span class="text-danger">*</span></label>
                            <textarea class="form-control font-monospace" name="validation_config" rows="6" required placeholder='{"module_name": "Sales Reporting", "line_item_name": "Total Sales", "expected_formula": "SUM(Sales Data.Revenue)"}'></textarea>
                            <small class="text-muted">JSON configuration that defines what to validate. Use the Model Browser above to find Anaplan IDs.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hint (shown on failure)</label>
                            <textarea class="form-control" name="hint" rows="2" placeholder="e.g. Check that you're using SUM and referencing the correct module..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Checkpoint</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Checkpoint Modal --}}
    <div class="modal fade" id="editCheckpointModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editCheckpointForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Checkpoint</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="editCpTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editCpDescription" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="checkpoint_type" id="editCpType">
                                    @foreach(\App\Models\LabCheckpoint::CHECKPOINT_TYPES as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Points</label>
                                <input type="number" class="form-control" name="points" id="editCpPoints" min="1">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Required</label>
                                <select class="form-select" name="is_required" id="editCpRequired">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" id="editCpSort" min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Validation Config (JSON) <span class="text-danger">*</span></label>
                            <textarea class="form-control font-monospace" name="validation_config" id="editCpConfig" rows="6" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hint (shown on failure)</label>
                            <textarea class="form-control" name="hint" id="editCpHint" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add Participant Modal --}}
    @if($availableContacts->isNotEmpty())
    <div class="modal fade" id="addParticipantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('labs.participants.store', $lab) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Enroll Participant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Contact <span class="text-danger">*</span></label>
                            <select class="form-select" name="contact_id" required>
                                <option value="">Select Contact...</option>
                                @foreach($availableContacts as $contact)
                                    <option value="{{ $contact->id }}">{{ $contact->full_name }} ({{ $contact->partner->name ?? '—' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assigned Anaplan Model</label>
                            <select class="form-select" name="assigned_model_id">
                                <option value="">Assign later...</option>
                                @foreach($availableModels as $model)
                                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">The participant's own Anaplan model that will be validated against the golden model.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Enroll</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('scripts')
<script>
    const checkpointBaseUrl = "{{ url('/lab-checkpoints') }}";

    function openEditCheckpointModal(btn) {
        const form = document.getElementById('editCheckpointForm');
        form.action = checkpointBaseUrl + '/' + btn.dataset.id;

        document.getElementById('editCpTitle').value = btn.dataset.title || '';
        document.getElementById('editCpDescription').value = btn.dataset.description || '';
        document.getElementById('editCpType').value = btn.dataset.type || 'formula';
        document.getElementById('editCpPoints').value = btn.dataset.points || '1';
        document.getElementById('editCpRequired').value = btn.dataset.required || '1';
        document.getElementById('editCpSort').value = btn.dataset.sort || '0';
        document.getElementById('editCpHint').value = btn.dataset.hint || '';

        // Pretty-print config JSON
        try {
            const config = JSON.parse(btn.dataset.config);
            document.getElementById('editCpConfig').value = JSON.stringify(config, null, 2);
        } catch (e) {
            document.getElementById('editCpConfig').value = btn.dataset.config || '{}';
        }

        new bootstrap.Modal(document.getElementById('editCheckpointModal')).show();
    }

    // Golden Model Browser
    function loadModules() {
        const loading = document.getElementById('model-browser-loading');
        const content = document.getElementById('model-browser-content');
        loading.classList.remove('d-none');
        content.innerHTML = '';

        fetch("{{ route('labs.browse-model', $lab) }}")
            .then(r => r.json())
            .then(data => {
                loading.classList.add('d-none');
                if (!data.modules || data.modules.length === 0) {
                    content.innerHTML = '<p class="text-muted text-center">No modules found.</p>';
                    return;
                }
                let html = '<div class="accordion" id="modulesAccordion">';
                data.modules.forEach((mod, i) => {
                    html += `
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#mod${i}" onclick="loadLineItems('${mod.id}', 'modBody${i}')">
                                    <i class="fal fa-cube me-2 text-primary"></i>
                                    <strong>${mod.name}</strong>
                                    <code class="ms-2 small text-muted">${mod.id}</code>
                                </button>
                            </h2>
                            <div id="mod${i}" class="accordion-collapse collapse">
                                <div class="accordion-body p-2" id="modBody${i}">
                                    <div class="text-center py-2"><div class="spinner-border spinner-border-sm"></div></div>
                                </div>
                            </div>
                        </div>`;
                });
                html += '</div>';
                content.innerHTML = html;
            })
            .catch(() => {
                loading.classList.add('d-none');
                content.innerHTML = '<div class="alert alert-danger">Failed to load modules from Anaplan API.</div>';
            });
    }

    function loadLineItems(moduleId, targetId) {
        const target = document.getElementById(targetId);
        if (target.dataset.loaded) return;
        target.dataset.loaded = '1';

        fetch("{{ url('/labs/'.$lab->id.'/browse-module') }}/" + moduleId)
            .then(r => r.json())
            .then(data => {
                if (!data.lineItems || data.lineItems.length === 0) {
                    target.innerHTML = '<p class="text-muted small">No line items.</p>';
                    return;
                }
                let html = '<table class="table table-sm table-bordered mb-0"><thead><tr><th>Line Item</th><th>ID</th><th>Format</th><th>Formula</th></tr></thead><tbody>';
                data.lineItems.forEach(li => {
                    html += `<tr>
                        <td>${li.name}</td>
                        <td><code class="small">${li.id}</code></td>
                        <td><small>${li.format || '—'}</small></td>
                        <td><code class="small">${li.formula || '—'}</code></td>
                    </tr>`;
                });
                html += '</tbody></table>';
                target.innerHTML = html;
            })
            .catch(() => {
                target.innerHTML = '<div class="alert alert-danger alert-sm">Failed to load line items.</div>';
            });
    }

    function loadLists() {
        const loading = document.getElementById('model-browser-loading');
        const content = document.getElementById('model-browser-content');
        loading.classList.remove('d-none');
        content.innerHTML = '';

        fetch("{{ route('labs.browse-lists', $lab) }}")
            .then(r => r.json())
            .then(data => {
                loading.classList.add('d-none');
                if (!data.lists || data.lists.length === 0) {
                    content.innerHTML = '<p class="text-muted text-center">No lists found.</p>';
                    return;
                }
                let html = '<table class="table table-bordered table-hover"><thead><tr><th>List Name</th><th>ID</th></tr></thead><tbody>';
                data.lists.forEach(l => {
                    html += `<tr><td>${l.name}</td><td><code class="small">${l.id}</code></td></tr>`;
                });
                html += '</tbody></table>';
                content.innerHTML = html;
            })
            .catch(() => {
                loading.classList.add('d-none');
                content.innerHTML = '<div class="alert alert-danger">Failed to load lists from Anaplan API.</div>';
            });
    }
</script>
@endsection
