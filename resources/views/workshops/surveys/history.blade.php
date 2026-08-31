@extends('layouts.vertical', ['pageTitle' => (\App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind).' survey - version history'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Version history', 'pageSubTitle1' => 'Survey templates', 'pageSubTitle1Url' => route('workshops.surveys.index', $template->workshop), 'pageSubText' => $template->workshop->name.' - '.(\App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind)])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <p class="text-muted fs-sm mb-0">Editing a template that already has responses creates a new version instead of changing the one those responses point to - so completed sessions keep reflecting the questions participants actually saw.</p>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Questions</th>
                            <th>Responses</th>
                            <th>Updated</th>
                            <th>Updated by</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($versions as $version)
                            <tr>
                                <td class="fw-medium">v{{ $version->version }}</td>
                                <td>
                                    @if($version->isCurrent())
                                        <span class="badge bg-success bg-opacity-25 text-success">Current</span>
                                    @else
                                        <span class="badge bg-light text-dark">Superseded {{ $version->superseded_at?->format('Y-m-d') }}</span>
                                    @endif
                                </td>
                                <td>{{ count($version->questions_json ?? []) }}</td>
                                <td>{{ $version->responses_count }}</td>
                                <td class="text-muted fs-sm">{{ $version->updated_at?->format('Y-m-d H:i:s') }}</td>
                                <td class="text-muted fs-sm">{{ $version->updatedByUser?->name ?? '—' }}</td>
                                <td class="text-nowrap">
                                    @if($version->isCurrent())
                                        <a href="{{ route('workshops.surveys.edit', $version) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    @endif
                                    <a href="{{ route('workshops.surveys.preview', $version) }}" class="btn btn-sm btn-outline-secondary" target="_blank">Preview ↗</a>
                                    <a href="{{ route('workshops.surveys.results', $version) }}" class="btn btn-sm btn-outline-primary">Results</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
