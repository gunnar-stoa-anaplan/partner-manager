@extends('layouts.vertical', ['pageTitle' => $journey->name . ' — Builder'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $journey->name,
        'pageSubTitle1' => 'Journeys',
        'pageSubTitle1Url' => route('journeys.index'),
        'pageSubTitle2' => 'Builder',
        'pageSubText' => 'Drag assets onto stages to compose this journey',
    ])
@endsection

@section('content')

<style>
    .jb-root { position: relative; }
    .jb-grid { position: relative; z-index: 1; }
    .jb-connector-overlay {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }
    .jb-library-list {
        max-height: 70vh;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding-right: 4px;
    }
    .jb-stage-body {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-height: 80px;
        background: linear-gradient(180deg, rgba(0,0,0,0.015), transparent);
        border: 1px dashed transparent;
        border-radius: 6px;
        transition: border-color 0.15s, background 0.15s;
    }
    .jb-stage-body.sortable-drag-over,
    .jb-stage-body:has(.sortable-ghost) {
        border-color: var(--bs-primary, #0d6efd);
        background: rgba(13, 110, 253, 0.05);
    }
    .jb-card {
        position: relative;
        padding: 10px 12px;
        border: 1px solid #d8d8d8;
        border-radius: 6px;
        background: #fff;
        cursor: grab;
        transition: box-shadow 0.15s, transform 0.15s;
        user-select: none;
    }
    .jb-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); transform: translateY(-1px); }
    .jb-card:active { cursor: grabbing; }
    .jb-card-type {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #888;
        font-weight: 600;
        margin-bottom: 2px;
    }
    .jb-card-name { font-size: 13px; font-weight: 500; color: #222; }
    .jb-card-meta { margin-top: 4px; }
    .jb-card.d-none-search { display: none !important; }

    /* Type accents */
    .jb-card.jb-type-academy_course { border-left: 3px solid #1A8A4A; }
    .jb-card.jb-type-ilt_workshop { border-left: 3px solid #1A5276; }
    .jb-card.jb-type-specialist_engagement { border-left: 3px solid #B7540F; }
    .jb-card.jb-type-blueprint { border-left: 3px solid #6B21A8; }
    .jb-card.jb-type-reading { border-left: 3px solid #0D9488; }
    .jb-card.jb-type-video { border-left: 3px solid #B45309; }
    .jb-card.jb-type-mentor_session { border-left: 3px solid #991B1B; }

    /* SortableJS state classes */
    .jb-ghost { opacity: 0.5; background: #fffbe6; }
    .jb-chosen { box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
    .jb-drag { transform: rotate(1deg); }
    .jb-saving { opacity: 0.6; pointer-events: none; }

    /* Stage card on drop — slightly tighter, with remove button */
    .jb-stage-card .jb-remove {
        position: absolute;
        top: 6px;
        right: 6px;
        opacity: 0;
        transition: opacity 0.15s;
    }
    .jb-stage-card:hover .jb-remove { opacity: 0.7; }
    .jb-stage-card .jb-remove:hover { opacity: 1; }
</style>

@php
    $assetTypes = \App\Models\Asset::TYPES;
    $assetsByType = $assets->groupBy('type');
@endphp

<div
    class="jb-root"
    data-journey-builder
    data-journey-id="{{ $journey->id }}"
    data-attach-url="{{ route('journeys.builder.attach', $journey) }}"
    data-detach-url-template="{{ route('journeys.builder.detach', ['journey' => $journey, 'item' => '__ITEM__']) }}"
>
    {{-- SVG overlay for connectors. Sits behind cards (pointer-events:none). --}}
    <svg
        class="jb-connector-overlay"
        data-connector-overlay
        xmlns="http://www.w3.org/2000/svg"
        preserveAspectRatio="xMinYMin meet"
    ></svg>

    <div class="row g-4 jb-grid">
        {{-- ── Asset library ──────────────────────────────────────────── --}}
        <div class="col-12 col-lg-4 jb-library-col">
            <div class="card shadow-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Asset Library</h5>
                    <span class="badge text-bg-light">{{ $assets->count() }}</span>
                </div>
                <div class="card-body">
                    <input
                        type="search"
                        class="form-control form-control-sm mb-3"
                        placeholder="Search assets…"
                        data-asset-search
                        autocomplete="off"
                    >

                    <div class="d-flex flex-wrap gap-1 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary active" data-type-filter="all">
                            All
                        </button>
                        @foreach($assetTypes as $key => $label)
                            @if($assetsByType->has($key))
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-type-filter="{{ $key }}">
                                    {{ $label }}
                                </button>
                            @endif
                        @endforeach
                    </div>

                    <div class="jb-library-list" data-asset-library>
                        @foreach($assets as $asset)
                            <div
                                class="jb-card jb-library-card jb-type-{{ $asset->type }}"
                                data-library-card
                                data-item-type="asset"
                                data-item-id="{{ $asset->id }}"
                                data-asset-type="{{ $asset->type }}"
                                data-search-text="{{ $asset->name }} {{ $asset->typeLabel() }} {{ implode(' ', $asset->tags ?? []) }}"
                            >
                                <div class="jb-card-type">{{ $asset->typeLabel() }}</div>
                                <div class="jb-card-name">{{ $asset->name }}</div>
                                @if($asset->duration_minutes)
                                    <div class="jb-card-meta">
                                        <small class="text-muted">{{ $asset->duration_minutes }} min</small>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Stages ─────────────────────────────────────────────────── --}}
        <div class="col-12 col-lg-8 jb-stages-col">
            <div class="d-flex flex-column gap-3">
                @foreach($stages as $stage)
                    <div class="jb-stage card shadow-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary me-2">Stage {{ $stage['num'] }}</span>
                                <strong>{{ $stage['name'] }}</strong>
                            </div>
                            <small class="text-muted">{{ count($itemsByStage[$stage['num']] ?? []) }} items</small>
                        </div>
                        <div
                            class="card-body jb-stage-body"
                            data-stage-list
                            data-stage-number="{{ $stage['num'] }}"
                        >
                            @foreach($itemsByStage[$stage['num']] ?? [] as $item)
                                <div
                                    class="jb-card jb-stage-card jb-type-{{ $item['type'] }}"
                                    data-stage-card
                                    data-stage-item-id="{{ $item['item_id'] }}"
                                    data-item-type="{{ $item['item_type'] }}"
                                    data-item-id="{{ $item['asset_id'] }}"
                                    data-asset-type="{{ $item['type'] }}"
                                >
                                    <div class="jb-card-type">{{ $item['type_label'] }}</div>
                                    <div class="jb-card-name">{{ $item['name'] }}</div>
                                    <button type="button" class="jb-remove btn-close opacity-75" aria-label="Remove from stage"></button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite(['resources/scripts/pages/journey-builder.js'])
@endpush
