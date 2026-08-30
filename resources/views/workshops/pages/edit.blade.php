@extends('layouts.vertical', ['pageTitle' => 'Edit Page'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Edit Content Page', 'pageSubTitle1' => 'Content Pages', 'pageSubTitle1Url' => route('workshops.pages.index', $workshop), 'pageSubText' => $page->title])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <form action="{{ route('workshops.pages.update', [$workshop, $page]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" value="{{ old('title', $page->title) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="slug" value="{{ old('slug', $page->slug) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Audience <span class="text-danger">*</span></label>
                        <select class="form-select" name="audience" required>
                            @foreach(\App\Models\WorkshopPage::AUDIENCES as $value => $label)
                                <option value="{{ $value }}" @selected(old('audience', $page->audience) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Body (Markdown/HTML) <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="body" rows="12" required>{{ old('body', $page->body) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Checks (JSON, optional)</label>
                        <textarea class="form-control font-monospace" name="checks_json" rows="4">{{ old('checks_json', $page->checks_json ? json_encode($page->checks_json, JSON_PRETTY_PRINT) : '') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('workshops.pages.index', $workshop) }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
