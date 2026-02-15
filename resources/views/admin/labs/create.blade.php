@extends('layouts.vertical', ['pageTitle' => 'Create Lab'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Create Lab', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubTitle2' => 'Workshop Labs', 'pageSubTitle2Url' => route('labs.index'), 'pageSubText' => 'Set up a new workshop lab for checkpoint verification'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Create a new workshop lab by selecting an enablement session and a golden Anaplan model. Once created, you can add checkpoints and enroll participants from the detail page.</p>

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
@endsection
