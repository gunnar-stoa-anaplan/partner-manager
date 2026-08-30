@extends('layouts.vertical', ['pageTitle' => 'New Page'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'New Content Page', 'pageSubTitle1' => 'Content Pages', 'pageSubTitle1Url' => route('workshops.pages.index', $workshop)])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <form action="{{ route('workshops.pages.store', $workshop) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="slug" value="{{ old('slug') }}" placeholder="e.g. 01-overview" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Audience <span class="text-danger">*</span></label>
                        <select class="form-select" name="audience" required>
                            @foreach(\App\Models\WorkshopPage::AUDIENCES as $value => $label)
                                <option value="{{ $value }}" @selected(old('audience', 'participant') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">"Facilitator Only" pages never appear in the participant portal - use them for run-of-show scripts, launch checklists, and internal reference.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Body (Markdown/HTML) <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="body" rows="12" required>{{ old('body') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Checks (JSON, optional)</label>
                        <textarea class="form-control font-monospace" name="checks_json" rows="4">{{ old('checks_json', '[
  {"id": "check-1", "label": "I have generated the model successfully"}
]') }}</textarea>
                        <div class="form-text">Each check is a self-reported checklist item the participant marks pass/fail as they work through the page.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Page</button>
                    <a href="{{ route('workshops.pages.index', $workshop) }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
