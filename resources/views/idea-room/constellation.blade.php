@extends('layouts.vertical', ['pageTitle' => 'Idea Room — Constellation', 'extraClass' => 'layout-trimmed'])

@section('content')
<div class="card shadow-0 d-flex flex-column" style="height: calc(100vh - var(--app-header-height)); overflow: hidden;">
    {{-- Toolbar --}}
    <div class="card-header d-flex align-items-center justify-content-end py-2">
        <div class="d-flex align-items-center gap-2">
            <select id="ir-filter-bucket" class="form-select form-select-sm" style="width:auto">
                <option value="">All Buckets</option>
            </select>
            <select id="ir-filter-state" class="form-select form-select-sm" style="width:auto">
                <option value="">All States</option>
                <option value="spark">Spark</option>
                <option value="growing">Growing</option>
                <option value="developed">Developed</option>
                <option value="archived">Archived</option>
                <option value="dropped">Dropped</option>
            </select>
        </div>
    </div>

    <div class="card-body p-0 flex-grow-1" style="min-height: 0; overflow: hidden;">
        <div class="d-flex h-100">
            {{-- Graph --}}
            <div class="flex-grow-1 position-relative ir-constellation-container" id="ir-graph-container">
                <div id="ir-loading-indicator" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                    <div class="d-flex gap-1">
                        <span class="ir-loading-dot"></span>
                        <span class="ir-loading-dot"></span>
                        <span class="ir-loading-dot"></span>
                    </div>
                </div>
                <div id="ir-empty-state" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column align-items-center justify-content-center text-center">
                    <div class="fs-1 mb-3">&#x2728;</div>
                    <h4>No ideas yet</h4>
                    <p class="text-muted">Head to the <a href="{{ route('idea_room.whiteboard') }}" class="text-warning">Whiteboard</a> to capture some ideas and see them here.</p>
                </div>
            </div>

            {{-- Detail Panel --}}
            <aside id="ir-detail-panel" class="d-none border-start bg-body-secondary" style="width:24rem; flex-shrink:0; overflow-y:auto">
                <div class="p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span id="ir-detail-state" class="ir-state-badge"></span>
                        <button id="ir-detail-close" class="btn btn-sm btn-outline-secondary">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <h5 id="ir-detail-title" class="mb-2"></h5>
                    <p id="ir-detail-text" class="text-muted small"></p>

                    <div class="mt-3">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">Buckets</h6>
                        <div id="ir-detail-buckets" class="d-flex flex-wrap"></div>
                    </div>
                    <div class="mt-3">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">Tags</h6>
                        <div id="ir-detail-tags" class="d-flex flex-wrap"></div>
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Created</span>
                            <span id="ir-detail-created"></span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Last Activity</span>
                            <span id="ir-detail-activity"></span>
                        </div>
                    </div>
                    <div id="ir-detail-connected" class="mt-3 pt-3 border-top d-none">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">Connected Ideas</h6>
                        <div id="ir-detail-connected-list"></div>
                    </div>
                    <div class="mt-4">
                        <a id="ir-detail-enter-room" href="#" class="btn btn-warning w-100">Enter Idea Room</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/idea-room/constellation.js'])
@endsection
