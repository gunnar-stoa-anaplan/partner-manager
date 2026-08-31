@extends('layouts.vertical', ['pageTitle' => $page->title.' - version history'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Version history', 'pageSubTitle1' => 'Content Pages', 'pageSubTitle1Url' => route('workshops.pages.index', $workshop), 'pageSubText' => $workshop->name.' - '.$page->title])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <p class="text-muted fs-sm mb-0">Editing a page that already has check attempts or discussion threads creates a new version instead of changing the one those records point to - so a completed session's history stays tied to the content that was actually shown.</p>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Check attempts</th>
                            <th>Threads</th>
                            <th>Updated</th>
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
                                <td>{{ $version->check_attempts_count }}</td>
                                <td>{{ $version->threads_count }}</td>
                                <td class="text-muted fs-sm">{{ $version->updated_at?->format('Y-m-d H:i:s') }}</td>
                                <td class="text-nowrap">
                                    @if($version->isCurrent())
                                        <a href="{{ route('workshops.pages.edit', [$workshop, $version]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    @endif
                                    <a href="{{ route('workshops.pages.show', [$workshop, $version]) }}" class="btn btn-sm btn-outline-secondary">View</a>
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
