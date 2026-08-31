@extends('layouts.vertical', ['pageTitle' => $workshop->name.' - Survey Templates'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Survey Templates', 'pageSubTitle1' => $workshop->name, 'pageSubTitle1Url' => route('workshops.show', $workshop), 'pageSubText' => 'One template per kind - every session in this workshop uses the same set, so results are measured consistently.'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card shadow-0 mb-g">
            @if($pendingCount > 0)
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1"></div>
                    <a href="{{ route('workshops.review-queue', $workshop) }}" class="btn btn-outline-warning btn-sm">{{ $pendingCount }} pending review</a>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Kind</th>
                            <th>Status</th>
                            <th>Questions</th>
                            <th>Responses</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $template)
                            <tr>
                                <td class="fw-medium">
                                    {{ \App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind }}
                                    @if($template->version > 1)
                                        <span class="badge bg-light text-dark ms-1">v{{ $template->version }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($template->questions_json))
                                        <span class="badge bg-primary bg-opacity-25 text-primary">Active</span>
                                    @else
                                        <span class="badge bg-light text-dark">Empty</span>
                                    @endif
                                </td>
                                <td>{{ count($template->questions_json ?? []) }}</td>
                                <td>{{ $template->responses_count }}</td>
                                <td class="text-muted fs-sm">{{ $template->updated_at?->format('Y-m-d H:i:s') }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('workshops.surveys.edit', $template) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <a href="{{ route('workshops.surveys.preview', $template) }}" class="btn btn-sm btn-outline-secondary" target="_blank">Preview ↗</a>
                                    <a href="{{ route('workshops.surveys.results', $template) }}" class="btn btn-sm btn-outline-primary">Results</a>
                                    <a href="{{ route('workshops.surveys.history', $template) }}" class="btn btn-sm btn-outline-secondary">History</a>
                                    @if($otherWorkshops->isNotEmpty())
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#cloneModal{{ $template->id }}">Clone to…</button>
                                    @endif
                                </td>
                            </tr>

                            @if($otherWorkshops->isNotEmpty())
                            <div class="modal fade" id="cloneModal{{ $template->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('workshops.surveys.clone-into', $template) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Clone {{ \App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind }} template</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label class="form-label">Target workshop</label>
                                                <select name="target_workshop_id" class="form-select" required>
                                                    @foreach($otherWorkshops as $option)
                                                        <option value="{{ $option->id }}">{{ $option->name }}</option>
                                                    @endforeach
                                                </select>
                                                <p class="text-muted fs-sm mt-2 mb-0">If the target workshop already has a {{ strtolower(\App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind) }} template, this lands as a draft for review there rather than replacing it outright.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Clone</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
