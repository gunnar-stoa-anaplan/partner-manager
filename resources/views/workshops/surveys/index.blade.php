@extends('layouts.vertical', ['pageTitle' => 'Survey Templates'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Survey Templates', 'pageSubTitle1' => 'Workshops', 'pageSubTitle1Url' => route('workshops.dashboard'), 'pageSubText' => 'Pre/post knowledge checks per workshop'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1"><i class="fal fa-clipboard me-2"></i> Survey Templates</div>
                <a href="{{ route('workshops.surveys.create') }}" class="btn btn-primary btn-sm">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                    New Template
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Workshop</th>
                            <th>Kind</th>
                            <th>Passing Score</th>
                            <th>Responses</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>{{ $template->workshop->name }}</td>
                                <td>{{ \App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind }}</td>
                                <td>{{ $template->passing_score }}%</td>
                                <td>{{ $template->responses_count }}</td>
                                <td>
                                    <a href="{{ route('workshops.surveys.edit', $template) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <a href="{{ route('workshops.surveys.results', $template) }}" class="btn btn-sm btn-outline-primary">Results</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No survey templates yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
