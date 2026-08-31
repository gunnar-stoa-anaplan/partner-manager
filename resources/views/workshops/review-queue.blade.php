@extends('layouts.vertical', ['pageTitle' => $workshop->name.' - Review Queue'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Review Queue', 'pageSubTitle1' => $workshop->name, 'pageSubTitle1Url' => route('workshops.show', $workshop), 'pageSubText' => 'Draft page and survey revisions awaiting review, in one place.'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Pages</div></div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th>Revising</th>
                            <th>Version</th>
                            <th>Opened</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingPages as $page)
                            <tr>
                                <td class="fw-medium">{{ $page->title }}</td>
                                <td>{{ $page->parentPage ? 'v'.$page->parentPage->version.' (live)' : 'New page' }}</td>
                                <td>v{{ $page->version }}</td>
                                <td class="text-muted fs-sm">{{ $page->created_at->diffForHumans() }}</td>
                                <td><a href="{{ route('workshops.pages.diff', [$workshop, $page]) }}" class="btn btn-sm btn-outline-primary">Review</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Nothing pending.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Survey templates</div></div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Kind</th>
                            <th>Revising</th>
                            <th>Version</th>
                            <th>Opened</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingSurveyTemplates as $template)
                            <tr>
                                <td class="fw-medium">{{ \App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind }}</td>
                                <td>{{ $template->parentTemplate ? 'v'.$template->parentTemplate->version.' (live)' : 'New template' }}</td>
                                <td>v{{ $template->version }}</td>
                                <td class="text-muted fs-sm">{{ $template->created_at->diffForHumans() }}</td>
                                <td><a href="{{ route('workshops.surveys.diff', $template) }}" class="btn btn-sm btn-outline-primary">Review</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Nothing pending.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
