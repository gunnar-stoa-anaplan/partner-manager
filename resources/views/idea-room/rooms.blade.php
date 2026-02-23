@extends('layouts.vertical', ['pageTitle' => 'Idea Room — Rooms'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Idea Room',
        'pageSubTitle1' => 'Rooms',
    ])
@endsection

@section('content')
<div class="card shadow-0" x-data="roomList">
    {{-- Toolbar --}}
    <div class="card-header d-flex align-items-center justify-content-between py-3">
        <div class="d-flex align-items-center gap-3">
            <input type="text" x-model="search" placeholder="Search rooms..."
                   class="form-control form-control-sm" style="width:14rem">
            <select x-model="statusFilter" class="form-select form-select-sm" style="width:auto">
                <option value="all">All Statuses</option>
                <option value="active">Active</option>
                <option value="archived">Archived</option>
            </select>
        </div>
        <a href="{{ route('idea_room.chat') }}" class="btn btn-sm btn-warning">
            <i class="fal fa-plus me-1"></i> New Room via Chat
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
        <template x-if="!loading && filteredRooms.length === 0">
            <div class="text-center py-5">
                <div class="fs-1 mb-3 opacity-50"><i class="fal fa-door-open"></i></div>
                <h5>No rooms yet</h5>
                <p class="text-muted">Start a conversation in
                    <a href="{{ route('idea_room.chat') }}" class="text-warning">Chat</a>
                    to create your first idea room.</p>
            </div>
        </template>

        {{-- Table --}}
        <template x-if="!loading && filteredRooms.length > 0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Idea</th>
                            <th>State</th>
                            <th>Status</th>
                            <th class="text-center">Messages</th>
                            <th class="text-center">Artifacts</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="room in filteredRooms" :key="room.uuid">
                            <tr>
                                <td>
                                    <a :href="'/idea-room/rooms/' + room.uuid"
                                       class="fw-medium text-decoration-none"
                                       x-text="room.name"></a>
                                </td>
                                <td x-text="room.idea?.title || '—'" class="text-muted small"></td>
                                <td>
                                    <span class="ir-state-badge"
                                          :class="'ir-state-badge--' + (room.idea?.lifecycle_state || 'spark')"
                                          x-text="room.idea?.lifecycle_state || '—'"></span>
                                </td>
                                <td>
                                    <span class="badge"
                                          :class="room.status === 'active' ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'"
                                          x-text="room.status"></span>
                                </td>
                                <td class="text-center" x-text="room.messages_count || 0"></td>
                                <td class="text-center" x-text="room.artifacts_count || 0"></td>
                                <td class="small text-muted" x-text="room.updated_at ? new Date(room.updated_at).toLocaleDateString() : '—'"></td>
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
