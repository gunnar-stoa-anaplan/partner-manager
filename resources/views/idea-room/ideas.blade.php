@extends('layouts.vertical', ['pageTitle' => 'Idea Room — Ideas'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Idea Room',
        'pageSubTitle1' => 'Ideas',
    ])
@endsection

@section('content')
<div class="card shadow-0" x-data="ideaList">
    {{-- Toolbar --}}
    <div class="card-header d-flex align-items-center justify-content-between py-3">
        <div class="d-flex align-items-center gap-3">
            <input type="text" x-model="search" placeholder="Search ideas..."
                   class="form-control form-control-sm" style="width:14rem">
            <select x-model="stateFilter" class="form-select form-select-sm" style="width:auto">
                <option value="all">All States</option>
                <option value="spark">Spark</option>
                <option value="growing">Growing</option>
                <option value="developed">Developed</option>
                <option value="archived">Archived</option>
                <option value="dropped">Dropped</option>
            </select>
            <select x-model="sortBy" class="form-select form-select-sm" style="width:auto">
                <option value="last_activity_at">Last Activity</option>
                <option value="created_at">Created</option>
                <option value="title">Title</option>
            </select>
        </div>
        <a href="{{ route('idea_room.whiteboard') }}" class="btn btn-sm btn-warning">
            <i class="fal fa-lightbulb me-1"></i> Capture New Idea
        </a>
    </div>

    <div class="card-body p-0">
        {{-- Loading --}}
        <template x-if="loading">
            <div class="d-flex justify-content-center py-5">
                <div class="d-flex gap-1">
                    <span class="ir-loading-dot"></span>
                    <span class="ir-loading-dot"></span>
                    <span class="ir-loading-dot"></span>
                </div>
            </div>
        </template>

        {{-- Empty state --}}
        <template x-if="!loading && filteredIdeas.length === 0">
            <div class="text-center py-5">
                <div class="fs-1 mb-3 opacity-50"><i class="fal fa-lightbulb"></i></div>
                <h5>No ideas yet</h5>
                <p class="text-muted">Head to the
                    <a href="{{ route('idea_room.whiteboard') }}" class="text-warning">Whiteboard</a>
                    to capture your first idea.</p>
            </div>
        </template>

        {{-- Table --}}
        <template x-if="!loading && filteredIdeas.length > 0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>State</th>
                            <th>Buckets</th>
                            <th>Tags</th>
                            <th>Last Activity</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="idea in filteredIdeas" :key="idea.uuid">
                            <tr>
                                <td>
                                    <a :href="'/idea-room/ideas/' + idea.uuid + '/enter'"
                                       class="fw-medium text-decoration-none"
                                       x-text="idea.title"></a>
                                </td>
                                <td>
                                    <span class="ir-state-badge"
                                          :class="'ir-state-badge--' + idea.lifecycle_state"
                                          x-text="idea.lifecycle_state"></span>
                                </td>
                                <td>
                                    <template x-for="bucket in (idea.buckets || []).slice(0, 2)" :key="bucket.uuid || bucket.name">
                                        <span class="badge bg-body-secondary text-body me-1 small"
                                              x-text="bucket.full_path || bucket.name"></span>
                                    </template>
                                </td>
                                <td>
                                    <template x-for="tag in (idea.tags || []).slice(0, 3)" :key="tag">
                                        <span class="badge bg-warning bg-opacity-10 text-warning me-1"
                                              x-text="tag"></span>
                                    </template>
                                </td>
                                <td class="small text-muted"
                                    x-text="idea.last_activity_at ? new Date(idea.last_activity_at).toLocaleDateString() : '—'"></td>
                                <td class="small text-muted"
                                    x-text="idea.created_at ? new Date(idea.created_at).toLocaleDateString() : '—'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/idea-room/alpine-app.js'])
@endsection
