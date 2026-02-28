@extends('layouts.vertical', ['pageTitle' => 'Create Lab'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Create Lab', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubTitle2' => 'Workshop Labs', 'pageSubTitle2Url' => route('labs.index'), 'pageSubText' => 'Set up a new workshop lab for checkpoint verification'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Create a new workshop lab by selecting an enablement session and a golden Anaplan model. Once created, you can add checkpoints and enroll participants from the detail page.</p>

    @if(app(\App\Services\ModuleRegistry::class)->isEnabled('workshops.labs'))
    {{-- AI Generate Panel --}}
    <div id="ai-generate-lab-panel" data-generate-url="{{ route('labs.generate') }}">
        <div class="row mb-3">
            <div class="col-xl-8 offset-xl-2">
                <div class="card shadow-0 border-primary border-opacity-25 mb-0">
                    <div class="card-header bg-primary bg-opacity-10 cursor-pointer" role="button"
                         data-bs-toggle="collapse" data-bs-target="#aiLabCollapse">
                        <div class="d-flex align-items-center">
                            <svg class="sa-icon sa-icon-sm me-2"><use href="/icons/sprite.svg#cpu"></use></svg>
                            <span class="fw-medium">Generate with AI</span>
                            <i class="fal fa-chevron-down ms-auto"></i>
                        </div>
                    </div>
                    <div class="collapse" id="aiLabCollapse">
                        <div class="card-body">
                            <p class="text-muted fs-sm mb-3">
                                Select a tenant and describe the topic. AI will design a complete lab with checkpoints.
                                You can review and edit all fields before saving.
                            </p>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label fs-sm">Tenant <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" name="ai_tenant">
                                        <option value="">Select...</option>
                                        @foreach(\App\Models\Lab::TENANTS as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fs-sm">Topic <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="ai_topic" placeholder="e.g. Building a revenue forecast module">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-sm">Duration <span class="text-muted">(optional)</span></label>
                                    <input type="number" class="form-control form-control-sm" name="ai_duration_minutes" placeholder="45" min="15" max="120">
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-primary" id="ai-generate-lab-btn">
                                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#cpu"></use></svg>
                                    Generate
                                </button>
                                <div class="ai-generate-spinner d-none">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <span class="text-muted fs-sm ms-1">Designing lab (10-30s)...</span>
                                </div>
                            </div>
                            <div class="ai-generate-error text-danger fs-sm mt-2 d-none"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-xl-8 offset-xl-2">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">New Workshop Lab</div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('labs.store') }}" method="POST">
                        @csrf
                        <input type="hidden" id="checkpoints_json" name="checkpoints_json" value="">

                        <div class="mb-3">
                            <label class="form-label" for="name">Lab Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. FP&A Model Build Lab">
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label" for="enablement_session_id">Enablement Session <span class="text-danger">*</span></label>
                                <select class="form-select" id="enablement_session_id" name="enablement_session_id" required>
                                    <option value="">Select Session...</option>
                                    @foreach($sessions as $session)
                                        <option value="{{ $session->id }}" {{ old('enablement_session_id') == $session->id ? 'selected' : '' }}>
                                            {{ $session->display_title }} — {{ $session->start_at->format('M j, Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="duration_minutes">Duration (minutes)</label>
                                <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes') }}" min="1" placeholder="e.g. 90">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label class="form-label" for="golden_model_id">Golden Model <span class="text-danger">*</span></label>
                                <select class="form-select" id="golden_model_id" name="golden_model_id" required>
                                    <option value="">Select Anaplan Model...</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ old('golden_model_id') == $model->id ? 'selected' : '' }}>
                                            {{ $model->name }} {{ $model->workspace ? '('.$model->workspace->name.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">The reference model that participant work will be validated against.</small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="tenant">Tenant <span class="text-danger">*</span></label>
                                <select class="form-select" id="tenant" name="tenant" required>
                                    @foreach(\App\Models\Lab::TENANTS as $value => $label)
                                        <option value="{{ $value }}" {{ old('tenant', 'owp') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">API credentials used for validation.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Briefly describe what participants will build in this lab...">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="instructions">Instructions</label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="4" placeholder="Step-by-step instructions shown to participants...">{{ old('instructions') }}</textarea>
                        </div>

                        {{-- AI-generated checkpoint preview --}}
                        <div id="ai-checkpoint-preview" class="d-none mb-3">
                            <label class="form-label">AI-Generated Checkpoints</label>
                            <div class="checkpoint-cards"></div>
                            <small class="text-muted">These checkpoints will be created with the lab. You can configure validation rules on the lab detail page after creation.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Create Lab</button>
                            <a href="{{ route('labs.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/ai-generate-lab.js'])
@endsection
