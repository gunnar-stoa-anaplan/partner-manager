@extends('layouts.vertical', ['pageTitle' => 'My Partners'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'My Partners', 'pageSubTitle1' => 'Partner Manager', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">My Partners is your home base — showing all the partners assigned to you with their key metrics at a glance. Use the search bar to filter by name, industry, or region, then click any partner card to dive into their full profile. Select a partner to make it your active context for the partner-specific views throughout the application.</p>
<div class="row">
    <div class="col-xl-12">
        <div class="border-faded bg-faded p-3 mb-g d-flex">
            <div class="position-relative flex-grow-1">
                <input class="form-control shadow-inset-2 form-control-lg" id="js-filter-partners"
                       name="filter-partners" placeholder="Filter partners by name, industry or region"
                       type="text"/>
                <button class="btn btn-sm btn-danger position-absolute top-50 end-0 translate-middle-y me-2 d-none"
                        id="js-clear-filter" type="button">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div aria-label="Change list &amp; grid view" class="btn-group btn-group-lg hidden-lg-down ms-3"
                 role="group">
                <input checked="checked" class="btn-check" id="grid" name="partnerview" type="radio" value="grid"/>
                <label class="btn btn-outline-secondary" for="grid"><i class="fal fa-table"></i></label>
                <input class="btn-check" id="table" name="partnerview" type="radio" value="table"/>
                <label class="btn btn-outline-secondary" for="table"><i class="fal fa-th-list"></i></label>
            </div>
        </div>
        <div class="ms-auto fs-sm text-muted mb-g" id="filter-result-counter"></div>
    </div>
</div>

@if($partners->isEmpty())
    <div class="row">
        <div class="col-xl-12">
            <div class="alert alert-info mb-g">
                <strong>No Partners Assigned</strong>
                <p class="mb-0 mt-1">You don't have any partners assigned yet. Contact your administrator to get started.</p>
            </div>
        </div>
    </div>
@else
    <div class="row" id="js-partners">
        @foreach($partners as $partner)
        <div class="col-xl-4 partner-item"
             data-filter-tags="{{ strtolower($partner->name) }} {{ strtolower($partner->industry ?? '') }} {{ strtolower($partner->region ?? '') }}"
             id="p_{{ $partner->id }}">
            <div class="card border shadow-0 mb-g shadow-sm-hover">
                <div class="card-body border-faded border-top-0 border-start-0 border-end-0 rounded-top">
                    <div class="d-flex flex-row align-items-center">
                        <span class="status status-success me-3">
                            <span class="rounded-circle d-flex align-items-center justify-content-center bg-primary-100 text-primary"
                                  style="width: 42px; height: 42px;">
                                <svg class="sa-icon sa-icon-lg">
                                    <use href="/icons/sprite.svg#briefcase"></use>
                                </svg>
                            </span>
                        </span>
                        <div class="info-card-text flex-grow-1">
                            <a href="{{ route('partners.show', $partner->id) }}"
                               class="fs-xl fw-500 text-truncate text-truncate-lg d-block text-primary">
                                {{ $partner->name }}
                            </a>
                            @if($partner->industry)
                                <span class="d-block text-truncate text-truncate-lg text-muted fs-sm">{{ $partner->industry }}</span>
                            @endif
                        </div>
                        <button aria-expanded="false" class="js-expand-btn btn btn-sm btn-default d-none"
                                data-bs-target="#p_{{ $partner->id }} > .card-body + .card-body" data-bs-toggle="collapse"
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
                                <i class="fal fa-map-marker-alt text-muted me-2"></i> {{ $partner->geography ?? '' }} {{ $partner->region ? '/ ' . $partner->region : '' }}
                            </span>
                        @endif
                        <div class="row text-center mt-3 fs-sm">
                            <div class="col">
                                <div class="fw-500 text-primary">{{ $partner->projects->count() }}</div>
                                <div class="text-muted fs-xs">Projects</div>
                            </div>
                            <div class="col">
                                <div class="fw-500 text-info">{{ $partner->contacts->count() }}</div>
                                <div class="text-muted fs-xs">Contacts</div>
                            </div>
                            <div class="col">
                                <div class="fw-500 text-warning">{{ $partner->opportunities->count() }}</div>
                                <div class="text-muted fs-xs">Opps</div>
                            </div>
                            <div class="col">
                                <div class="fw-500 text-success">{{ $partner->leads->count() }}</div>
                                <div class="text-muted fs-xs">Leads</div>
                            </div>
                        </div>
                        <div class="mt-3 text-end">
                            <a href="{{ route('partners.select', $partner->id) }}"
                               class="fs-xs text-muted" title="Set as active partner">
                                <i class="fal fa-check-circle me-1"></i>Select
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
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
    @vite(['resources/scripts/optional/smartnotes.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterInput = document.getElementById('js-filter-partners');
            const clearBtn = document.getElementById('js-clear-filter');
            const partnerItems = document.querySelectorAll('.partner-item');
            const resultCounter = document.getElementById('filter-result-counter');
            const gridRadio = document.getElementById('grid');
            const tableRadio = document.getElementById('table');
            const partnersContainer = document.getElementById('js-partners');

            function filterPartners() {
                const search = filterInput.value.toLowerCase();
                let visible = 0;

                // Show/hide clear button
                if (search.length > 0) {
                    clearBtn.classList.remove('d-none');
                } else {
                    clearBtn.classList.add('d-none');
                }

                partnerItems.forEach(function(item) {
                    const tags = item.getAttribute('data-filter-tags');
                    const matches = !search || tags.includes(search);
                    item.style.display = matches ? '' : 'none';
                    if (matches) visible++;
                });

                // Update result counter
                if (search.length > 0) {
                    resultCounter.textContent = visible + ' {{ Str::plural("partner", 2) }} found';
                } else {
                    resultCounter.textContent = '';
                }
            }

            // Clear filter
            clearBtn.addEventListener('click', function() {
                filterInput.value = '';
                clearBtn.classList.add('d-none');
                filterPartners();
                filterInput.focus();
            });

            filterInput.addEventListener('keyup', filterPartners);

            // Grid/List view toggle
            if (gridRadio && tableRadio && partnersContainer) {
                gridRadio.addEventListener('change', function() {
                    partnerItems.forEach(function(item) {
                        item.className = item.className.replace(/col-xl-\d+/g, 'col-xl-4');
                    });
                    document.querySelectorAll('.js-expand-btn').forEach(function(btn) {
                        btn.classList.add('d-none');
                    });
                    document.querySelectorAll('#js-partners .card-body.collapse').forEach(function(el) {
                        el.classList.add('show');
                    });
                });

                tableRadio.addEventListener('change', function() {
                    partnerItems.forEach(function(item) {
                        item.className = item.className.replace(/col-xl-\d+/g, 'col-xl-12');
                    });
                    document.querySelectorAll('.js-expand-btn').forEach(function(btn) {
                        btn.classList.remove('d-none');
                    });
                });
            }
        });
    </script>
@endsection
