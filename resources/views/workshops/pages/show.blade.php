@extends('layouts.vertical', ['pageTitle' => $page->title])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $page->title, 'pageSubTitle1' => 'Content Pages', 'pageSubTitle1Url' => route('workshops.pages.index', $workshop)])
@endsection

@section('content')
<style>
    .workshop-content-body h2 { margin-top: 1.5rem; font-size: 1.3rem; }
    .workshop-content-body h2:first-child { margin-top: 0; }
    .workshop-content-body .callout-tip,
    .workshop-content-body .callout-info,
    .workshop-content-body .script-block { background: #eff6ff; border-left: 4px solid #0066cc; border-radius: 0 6px 6px 0; padding: 0.85rem 1.1rem; margin: 1rem 0; }
    .workshop-content-body .callout-warning { background: #fff7ed; border-left: 4px solid #c2410c; border-radius: 0 6px 6px 0; padding: 0.85rem 1.1rem; margin: 1rem 0; }
    .workshop-content-body .callout-label { display: block; font-weight: 600; margin-bottom: 0.35rem; }
    .workshop-content-body .click-instruction { background: #fffbeb; border-left: 3px solid #f59e0b; padding: 0.5rem 0.75rem; font-size: 0.9rem; border-radius: 0 4px 4px 0; margin: 0.5rem 0; }
    .workshop-content-body .facilitator-note { background: #f0fdf4; border-left: 3px solid #22c55e; padding: 0.5rem 0.75rem; font-size: 0.9rem; border-radius: 0 4px 4px 0; margin: 0.5rem 0; }
    .workshop-content-body .day-banner { background: linear-gradient(135deg, #1e3a5f 0%, #0066cc 100%); color: white; padding: 1rem 1.5rem; border-radius: 8px; margin: 2rem 0 1rem 0; font-weight: 600; }
    .workshop-content-body .timing-badge { display: inline-block; background: #e0e7ff; color: #3730a3; font-size: 0.72rem; font-weight: 600; padding: 0.15rem 0.5rem; border-radius: 20px; margin-left: 0.5rem; }
    .workshop-content-body .content-badge,
    .workshop-content-body .badge-row span { display: inline-block; background: #e5e7eb; color: #374151; border-radius: 4px; padding: 0.15rem 0.6rem; font-size: 0.8rem; margin-right: 0.35rem; }
    .workshop-content-body .table-wrap { overflow-x: auto; margin: 1rem 0; }
    .workshop-content-body table { width: 100%; border-collapse: collapse; }
    .workshop-content-body th, .workshop-content-body td { border: 1px solid #e5e7eb; padding: 0.5rem; text-align: left; }
    .workshop-content-body .checklist { list-style: none; padding: 0; }
    .workshop-content-body .checklist li { padding: 0.35rem 0; border-bottom: 1px solid #f0f0f0; }
    .workshop-content-body .checklist li::before { content: "\2610  "; }
    .workshop-content-body .lab-check { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 1rem; margin: 1rem 0; }
    .workshop-content-body .lab-check-button, .workshop-content-body .lab-check-results { display: none; }
    .workshop-content-body code { background: #f3f4f6; padding: 0.1rem 0.35rem; border-radius: 3px; }
    .workshop-content-body hr.section-divider { margin: 2rem 0; }
</style>

<div class="row">
    <div class="col-xl-9">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">
                    {{ $page->title }}
                    @if($page->audience === 'facilitator')
                        <span class="badge bg-warning text-dark ms-2">Facilitator Only</span>
                    @endif
                </div>
                <a href="{{ route('workshops.pages.edit', [$workshop, $page]) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
            </div>
            <div class="card-body">
                <div class="workshop-content-body">{!! $page->body !!}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">{{ $workshop->name }} Pages</div></div>
            <ul class="list-group list-group-flush">
                @foreach($workshop->pages as $navPage)
                    <li class="list-group-item {{ $navPage->id === $page->id ? 'active' : '' }}">
                        <a href="{{ route('workshops.pages.show', [$workshop, $navPage]) }}" class="{{ $navPage->id === $page->id ? 'text-white' : '' }}">{{ $navPage->title }}</a>
                        @if($navPage->audience === 'facilitator')
                            <span class="badge bg-warning text-dark ms-1">F</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
