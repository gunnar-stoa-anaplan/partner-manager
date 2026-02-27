@extends('layouts.vertical', ['pageTitle' => 'Idea Room — Chat'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Idea Room',
        'pageSubTitle1' => 'Chat',
    ])
@endsection

@section('content')
<div class="row" x-data="chat">
    {{-- Sidebar --}}
    <div class="col-lg-3 d-none d-lg-block">
        <div class="card shadow-0 h-100">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
                <h6 class="mb-0">Navigation</h6>
            </div>
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <a href="{{ route('idea_room.dashboard') }}" class="btn btn-sm btn-outline-secondary w-100 mb-2">Gallery</a>
                    <a href="{{ route('idea_room.constellation') }}" class="btn btn-sm btn-outline-secondary w-100">Constellation</a>
                </div>
                <div class="p-3">
                    <h6 class="text-uppercase text-muted small fw-bold mb-2">Recent Ideas</h6>
                    <template x-if="ideas.length === 0">
                        <p class="text-muted small">No ideas yet. Start chatting!</p>
                    </template>
                    <template x-for="idea in ideas" :key="idea.uuid">
                        <a :href="'/idea-room/ideas/' + idea.uuid + '/enter'"
                           class="d-block rounded p-2 mb-1 text-decoration-none bg-body-secondary bg-opacity-50">
                            <p class="mb-0 small fw-medium text-body text-truncate" x-text="idea.title"></p>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="ir-state-badge"
                                      :class="'ir-state-badge--' + idea.lifecycle_state"
                                      x-text="idea.lifecycle_state"></span>
                                <template x-if="idea.buckets && idea.buckets.length">
                                    <template x-for="bucket in idea.buckets.slice(0, 2)" :key="bucket.uuid">
                                        <span class="text-muted" style="font-size:10px" x-text="bucket.full_path || bucket.name"></span>
                                    </template>
                                </template>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Chat --}}
    <div class="col-lg-9">
        <div class="card shadow-0 position-relative">
            {{-- Triage Overlay --}}
            <div x-show="triageActive" x-cloak x-transition class="ir-triage-overlay d-flex flex-column rounded">
                <div class="d-flex align-items-center justify-content-between border-bottom p-4">
                    <div>
                        <h5 class="mb-1">Route Your Ideas</h5>
                        <p class="text-muted small mb-0">
                            <span x-show="!triageSelectedCard && triageAllRouted" class="text-success">All ideas have a destination — hit Confirm!</span>
                            <span x-show="!triageSelectedCard && !triageAllRouted">Review suggestions, click unrouted cards to assign them</span>
                            <span x-show="triageSelectedCard" class="text-warning">Now click a target to route it there</span>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button @click="confirmAllTriage" class="btn btn-warning" :disabled="triageConfirming">
                            <span x-show="!triageConfirming" x-text="triageAllRouted ? 'Confirm All' : 'Confirm & Create Rooms'"></span>
                            <span x-show="triageConfirming" class="d-flex align-items-center gap-2">
                                <span class="spinner-border spinner-border-sm"></span> Routing...
                            </span>
                        </button>
                        <button @click="skipTriage" class="btn btn-outline-secondary">Skip</button>
                    </div>
                </div>
                <div class="flex-grow-1 overflow-auto p-4">
                    <h6 class="text-uppercase text-muted small fw-bold mb-3">New Ideas</h6>
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <template x-for="card in triageCards" :key="card.idea_uuid">
                            <div class="ir-triage-card"
                                 :class="{
                                     'ir-triage-card--routed': triageRouted[card.idea_uuid],
                                     'ir-triage-card--selected': triageSelectedCard === card.idea_uuid,
                                 }"
                                 draggable="true"
                                 @dragstart="onDragStart($event, card.idea_uuid)"
                                 @dragend="onDragEnd"
                                 @click="toggleSelectCard(card.idea_uuid)">
                                <div class="fs-1 mb-2" x-text="card.emoji"></div>
                                <p class="small fw-medium mb-2" x-text="card.idea_title"></p>
                                <template x-if="triageRouted[card.idea_uuid]">
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        &#10003; <span x-text="triageRouted[card.idea_uuid].target_name"></span>
                                    </span>
                                </template>
                                <template x-if="!triageRouted[card.idea_uuid]">
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Needs routing</span>
                                </template>
                            </div>
                        </template>
                    </div>

                    <h6 class="text-uppercase text-muted small fw-bold mb-3">Destinations</h6>
                    <div class="row g-3">
                        <template x-for="target in triageRoomTargets" :key="'room-' + target.room_uuid">
                            <div class="col-sm-6 col-lg-4">
                                <div class="ir-triage-target"
                                     :class="triageHoveredTarget === 'room-' + target.room_uuid ? 'ir-triage-target--hovered' : ''"
                                     @dragover.prevent="onDragOver('room-' + target.room_uuid)"
                                     @dragleave="onDragLeave"
                                     @drop.prevent="onDrop('room', target.room_uuid, target.room_name)"
                                     @click="onTargetClick('room', target.room_uuid, target.room_name)">
                                    <p class="fw-medium mb-1 text-truncate" x-text="target.room_name"></p>
                                    <p class="text-muted small mb-0 text-truncate" x-text="target.idea_title"></p>
                                </div>
                            </div>
                        </template>
                        <template x-for="target in triageBucketTargets" :key="'bucket-' + target.bucket_uuid">
                            <div class="col-sm-6 col-lg-4">
                                <div class="ir-triage-target"
                                     :class="triageHoveredTarget === 'bucket-' + target.bucket_uuid ? 'ir-triage-target--hovered' : ''"
                                     @dragover.prevent="onDragOver('bucket-' + target.bucket_uuid)"
                                     @dragleave="onDragLeave"
                                     @drop.prevent="onDrop('bucket', target.bucket_uuid, target.name)"
                                     @click="onTargetClick('bucket', target.bucket_uuid, target.name)">
                                    <p class="fw-medium mb-1 text-truncate" x-text="target.name"></p>
                                    <p class="text-muted small mb-0" x-text="target.idea_count + ' idea(s)'"></p>
                                </div>
                            </div>
                        </template>
                        <div class="col-sm-6 col-lg-4">
                            <div class="ir-triage-target"
                                 :class="triageHoveredTarget === 'new-room' ? 'ir-triage-target--hovered' : ''"
                                 @dragover.prevent="onDragOver('new-room')"
                                 @dragleave="onDragLeave"
                                 @drop.prevent="onDrop('new', null, 'New Room')"
                                 @click="onTargetClick('new', null, 'New Room')">
                                <p class="fw-medium mb-1 text-success">+ New Room</p>
                                <p class="text-muted small mb-0">Create a fresh workspace</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chat Messages --}}
            <div class="card-body ir-chat-container p-0">
                <div id="ir-messages" class="ir-chat-messages">
                    <div class="mx-auto" style="max-width:48rem">
                        {{-- Welcome --}}
                        <template x-if="messages.length === 0">
                            <div class="text-center py-5">
                                <div class="fs-1 mb-3">&#x1f4a1;</div>
                                <h4>Welcome to Idea Room</h4>
                                <p class="text-muted">Share an idea, a half-formed thought, or a wild hunch. I'll capture it, categorize it, and help you develop it over time.</p>
                            </div>
                        </template>

                        {{-- Messages --}}
                        <template x-for="(msg, index) in messages" :key="index">
                            <div class="mb-3">
                                <template x-if="msg.role === 'user'">
                                    <div class="d-flex justify-content-end">
                                        <div class="ir-msg-user rounded-3 bg-warning bg-opacity-75 text-dark px-3 py-2 small">
                                            <p class="mb-0" x-text="msg.content"></p>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="msg.role === 'assistant'">
                                    <div class="d-flex justify-content-start">
                                        <div class="ir-msg-agent">
                                            <template x-if="msg.actions && msg.actions.length > 0">
                                                <div class="mb-1">
                                                    <template x-for="(action, ai) in formatActions(msg.actions)" :key="ai">
                                                        <div class="d-flex align-items-center gap-1 text-warning small">
                                                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                            <span x-text="action"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <div class="rounded-3 bg-body-secondary px-3 py-2 small">
                                                <p class="mb-0 white-space-pre-wrap" x-text="msg.content"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Loading --}}
                        <template x-if="loading">
                            <div class="d-flex justify-content-start">
                                <div class="rounded-3 bg-body-secondary px-3 py-2">
                                    <div class="d-flex gap-1">
                                        <span class="ir-loading-dot"></span>
                                        <span class="ir-loading-dot"></span>
                                        <span class="ir-loading-dot"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Error --}}
                        <template x-if="error">
                            <div class="alert alert-danger small" x-text="error"></div>
                        </template>
                    </div>
                </div>

                {{-- Input --}}
                <div class="border-top p-3">
                    <form @submit.prevent="send" class="d-flex gap-2 mx-auto" style="max-width:48rem">
                        <input type="text" x-model="message" x-ref="chatInput"
                               placeholder="Share an idea, ask a question, or add to an existing thought..."
                               class="form-control" :disabled="loading" autofocus>
                        <button type="submit" class="btn btn-warning" :disabled="loading || !message.trim()">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/idea-room/alpine-app.js'])
@endsection
