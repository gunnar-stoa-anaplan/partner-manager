@extends('layouts.vertical', ['pageTitle' => 'New Survey Template'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'New Survey Template', 'pageSubTitle1' => 'Survey Templates', 'pageSubTitle1Url' => route('workshops.surveys.index')])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <form action="{{ route('workshops.surveys.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Workshop <span class="text-danger">*</span></label>
                            <select class="form-select" name="workshop_id" required>
                                @foreach($workshops as $workshop)
                                    <option value="{{ $workshop->id }}">{{ $workshop->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kind <span class="text-danger">*</span></label>
                            <select class="form-select" name="kind" required>
                                @foreach(\App\Models\WorkshopSurveyTemplate::KINDS as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Intro Text</label>
                        <textarea class="form-control" name="intro" rows="2">{{ old('intro') }}</textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Passing Score (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="passing_score" value="{{ old('passing_score', 70) }}" min="0" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Attempts <span class="text-muted">(blank = unlimited)</span></label>
                            <input type="number" class="form-control" name="max_attempts" value="{{ old('max_attempts') }}" min="1">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Questions Per Attempt <span class="text-muted">(blank = show all)</span></label>
                            <input type="number" class="form-control" name="questions_per_attempt" value="{{ old('questions_per_attempt') }}" min="1">
                            <div class="form-text">Draws a random subset each attempt instead of showing every question.</div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="shuffle_options" name="shuffle_options" value="1" @checked(old('shuffle_options', true))>
                                <label class="form-check-label" for="shuffle_options">Shuffle answer options</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Questions (JSON) <span class="text-danger">*</span></label>
                        <textarea class="form-control font-monospace" name="questions_json" rows="12" required>{{ old('questions_json', '[
  {
    "id": "q1",
    "text": "Which module holds transactional detail data?",
    "type": "single",
    "options": ["Line Item", "List", "Module", "Dashboard"],
    "correct": ["Module"]
  },
  {
    "id": "q2",
    "text": "What did you find most useful today? (not scored)",
    "type": "text"
  }
]') }}</textarea>
                        @error('questions_json')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Each question needs an id/text/type ("single", "multiple", or "text"). Include "options" for single/multiple, and "correct" (array of correct option values) to have it scored automatically.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Template</button>
                    <a href="{{ route('workshops.surveys.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
