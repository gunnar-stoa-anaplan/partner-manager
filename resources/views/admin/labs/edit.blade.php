@extends('layouts.vertical', ['pageTitle' => 'Edit Lab'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Edit Lab', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubTitle2' => 'Workshop Labs', 'pageSubTitle2Url' => route('labs.index'), 'pageSubText' => $lab->name])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-8 offset-xl-2">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Edit Lab Details</div>
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

                    <form action="{{ route('labs.update', $lab) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="name">Lab Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $lab->name) }}" required>
                        </div>

                        @if($lab->isDraft())
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label" for="enablement_session_id">Enablement Session <span class="text-danger">*</span></label>
                                <select class="form-select" id="enablement_session_id" name="enablement_session_id" required>
                                    <option value="">Select Session...</option>
                                    @foreach($sessions as $session)
                                        <option value="{{ $session->id }}" {{ old('enablement_session_id', $lab->enablement_session_id) == $session->id ? 'selected' : '' }}>
                                            {{ $session->display_title }} — {{ $session->start_at->format('M j, Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="duration_minutes">Duration (minutes)</label>
                                <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $lab->duration_minutes) }}" min="1">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label class="form-label" for="golden_model_id">Golden Model <span class="text-danger">*</span></label>
                                <select class="form-select" id="golden_model_id" name="golden_model_id" required>
                                    <option value="">Select Anaplan Model...</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ old('golden_model_id', $lab->golden_model_id) == $model->id ? 'selected' : '' }}>
                                            {{ $model->name }} {{ $model->workspace ? '('.$model->workspace->name.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="tenant">Tenant <span class="text-danger">*</span></label>
                                <select class="form-select" id="tenant" name="tenant" required>
                                    @foreach(\App\Models\Lab::TENANTS as $value => $label)
                                        <option value="{{ $value }}" {{ old('tenant', $lab->tenant) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @else
                        <div class="mb-3">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" name="duration_minutes" value="{{ old('duration_minutes', $lab->duration_minutes) }}" min="1">
                        </div>
                        <div class="alert alert-info">
                            <i class="fal fa-info-circle me-1"></i> Session and golden model cannot be changed after publishing.
                        </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $lab->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="instructions">Instructions</label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="4">{{ old('instructions', $lab->instructions) }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Lab</button>
                            <a href="{{ route('labs.show', $lab) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
