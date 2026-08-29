@extends('layouts.vertical', ['pageTitle' => 'Edit Survey Template'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Edit Survey Template', 'pageSubTitle1' => 'Survey Templates', 'pageSubTitle1Url' => route('workshops.surveys.index'), 'pageSubText' => $template->workshop->name.' - '.(\App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind)])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <form action="{{ route('workshops.surveys.update', $template) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Intro Text</label>
                        <textarea class="form-control" name="intro" rows="2">{{ old('intro', $template->intro) }}</textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Passing Score (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="passing_score" value="{{ old('passing_score', $template->passing_score) }}" min="0" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Attempts <span class="text-muted">(blank = unlimited)</span></label>
                            <input type="number" class="form-control" name="max_attempts" value="{{ old('max_attempts', $template->max_attempts) }}" min="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Questions (JSON) <span class="text-danger">*</span></label>
                        <textarea class="form-control font-monospace" name="questions_json" rows="14" required>{{ old('questions_json', json_encode($template->questions_json, JSON_PRETTY_PRINT)) }}</textarea>
                        @error('questions_json')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('workshops.surveys.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
