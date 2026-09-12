@extends('layouts.vertical', ['pageTitle' => 'My Partners'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'My Partners', 'pageSubTitle1' => 'Partner Manager', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="border-faded bg-faded p-3 mb-g d-flex">
                <div class="position-relative flex-grow-1">
                    <input class="form-control shadow-inset-2 form-control-lg" id="js-filter-contacts"
                           name="filter-contacts" placeholder="Filter partners by name, industry or region"
                           type="text"/>
                    <button class="btn btn-sm btn-danger position-absolute top-50 end-0 translate-middle-y me-2 d-none"
                            id="js-clear-filter" type="button">
                        <i class="fal fa-times"></i>
                    </button>
                </div>
                <div aria-label="Change list &amp; grid view" class="btn-group btn-group-lg hidden-lg-down ms-3"
                     role="group">
                    <input checked="checked" class="btn-check" id="grid" name="contactview" type="radio" value="grid"/>
                    <label class="btn btn-outline-secondary" for="grid"><i class="fal fa-table"></i></label>
                    <input class="btn-check" id="table" name="contactview" type="radio" value="table"/>
                    <label class="btn btn-outline-secondary" for="table"><i class="fal fa-th-list"></i></label>
                </div>
            </div>
            <div class="ms-auto fs-sm text-muted mb-g" id="filter-result-counter"></div>
        </div>
    </div>
    <div class="row" id="js-contacts">
        @foreach($partners as $partner)
        <div class="col-xl-4">
            <div class="card border shadow-0 mb-g shadow-sm-hover"
                 data-filter-tags="{{ strtolower($partner->name) }} {{ strtolower($partner->industry ?? '') }} {{ strtolower($partner->region ?? '') }}" id="c_{{ $partner->id }}">
                <div class="card-body border-faded border-top-0 border-start-0 border-end-0 rounded-top">
                    <div class="d-flex flex-row align-items-center">
<span class="status status-success me-3">
<span class="rounded-circle profile-image d-block bg-primary-100 d-flex align-items-center justify-content-center"
      style="width: 42px; height: 42px;">
    <i class="fal fa-briefcase text-primary"></i>
</span>
</span>
                        <div class="info-card-text flex-grow-1">
                            <a href="{{ route('partners.show', $partner->id) }}"
                               class="fs-xl fw-500 text-truncate text-truncate-lg d-block text-primary">
                                {{ $partner->name }}
                            </a>
                            <span class="d-block text-truncate text-truncate-lg text-muted fs-sm">{{ $partner->industry ?? 'No industry specified' }}</span>
                        </div>
                        <button aria-expanded="false" class="js-expand-btn btn btn-sm btn-default d-none"
                                data-bs-target="#c_{{ $partner->id }} > .card-body + .card-body" data-bs-toggle="collapse"
                                type="button">
                            <span class="collapsed-hidden">+</span>
                            <span class="collapsed-reveal">-</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 collapse show">
                    <div class="p-3">
                        @if($partner->region)
                            <span class="mt-1 d-block fs-sm fw-400">
                                <i class="fal fa-map-marker-alt text-muted me-2"></i> {{ $partner->geography ?? '' }}{{ $partner->region ? ' / ' . $partner->region : '' }}
                            </span>
                        @endif
                        <div class="d-flex justify-content-between mt-3 fs-sm">
                            <span class="text-muted">
                                <i class="fal fa-project-diagram me-1"></i> {{ $partner->projects->count() }} {{ Str::plural('Project', $partner->projects->count()) }}
                            </span>
                            <span class="text-muted">
                                <i class="fal fa-users me-1"></i> {{ $partner->contacts->count() }} {{ Str::plural('Contact', $partner->contacts->count()) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mt-1 fs-sm">
                            <span class="text-muted">
                                <i class="fal fa-bullseye-arrow me-1"></i> {{ $partner->opportunities->count() }} {{ Str::plural('Opp', $partner->opportunities->count()) }}
                            </span>
                            <span class="text-muted">
                                <i class="fal fa-user-plus me-1"></i> {{ $partner->leads->count() }} {{ Str::plural('Lead', $partner->leads->count()) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

{{-- Embedded screen agent (renders only when config('agent.enabled')). --}}
<x-agent.panel screen="my-partners" />
@endsection

@section('extra-content')
    <div class="content-wrapper-right">
        <div class="right-content bg-faded d-flex flex-column h-100">
            <div class="flex-grow-1 p-3">
                <style>
                    #note-content:empty:before {
                        content: 'Start typing your note...';
                        color: #999;
                        font-style: italic;
                        display: block;
                        pointer-events: none;
                    }

                    .note-item {
                        transition: all 0.2s ease;
                    }

                    .note-item .btn-outline-danger {
                        opacity: 0.7;
                        transition: opacity 0.2s ease;
                    }

                    .note-item:hover .btn-outline-danger {
                        opacity: 1;
                    }
                </style>

                <div id="notes-list-view">

                    <div id="notes-container" class="list-group"></div>
                    <div class="d-flex justify-content-center gap-2 align-items-center mb-3 mt-3">
                        <button type="button" id="clear-all-notes" class="btn btn-outline-danger btn-sm">
                            Clear Notes
                        </button>
                        <button type="button" id="create-new-note" class="btn btn-outline-secondary btn-sm">
                            Create New Note
                        </button>
                    </div>
                </div>

                <div id="note-edit-view" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <button type="button" id="show-notes-list" class="btn btn-outline-success btn-sm">
                            Back
                        </button>

                        <div class="btn-group btn-group-xs">
                            <button type="button" class="btn btn-outline-secondary btn-xs formatting-btn"
                                    data-format="bold" id="format-text-bold" title="Bold (Ctrl+B)">
                                <i class="fal fa-bold"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-xs formatting-btn"
                                    data-format="italic" id="format-text-italic" title="Italic (Ctrl+I)">
                                <i class="fal fa-italic"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-xs formatting-btn"
                                    data-format="underline" id="format-text-underline"
                                    title="Underline (Ctrl+U)">
                                <i class="fal fa-underline"></i>
                            </button>
                        </div>
                    </div>
                    <div class="editor-container mb-3">
                        <div id="note-content" class="info-container mb-3 border-faded p-3 rounded text-break"
                             contenteditable="true" placeholder="Start typing your note..."></div>
                        <div class="fs-nano text-muted"><i class="fal fa-check text-success"></i> Auto save every 5
                            seconds
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/optional/smartnotes.js','resources/scripts/pages/usercontact.js'])
@endsection
