@extends('layouts.vertical', ['pageTitle' => 'Idea Room — ' . $room->name, 'extraClass' => 'layout-trimmed'])

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Rock+Salt&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="row" style="height: calc(100vh - var(--app-header-height)); overflow: hidden;" x-data="ideaRoom({
    roomUuid: '{{ $room->uuid }}',
    ideaTitle: {{ Js::from($room->idea?->title) }},
    ideaState: {{ Js::from($room->idea?->lifecycle_state) }},
    ideaUuid: {{ Js::from($room->idea?->uuid) }},
    ideaText: {{ Js::from($room->idea?->original_text) }},
    objective: {{ Js::from($room->agent_config['session_objective'] ?? null) }},
    buckets: {{ Js::from($room->idea?->buckets?->map(fn($b) => ['name' => $b->name, 'full_path' => $b->fullPath(), 'color' => $b->color])) }},
    tags: {{ Js::from($room->idea?->tags?->pluck('name')) }},
})">
    {{-- Chat Area --}}
    <div class="col-lg-8 d-flex flex-column" style="min-height: 0;">
        <div class="card shadow-0 flex-grow-1 d-flex flex-column" style="min-height: 0;">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
                <div>
                    <h6 class="mb-0" x-text="ideaTitle"></h6>
                    <span class="ir-state-badge" :class="'ir-state-badge--' + ideaState" x-text="ideaState"></span>
                </div>
                <a href="{{ route('idea_room.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                    Back to Gallery
                </a>
            </div>

            <div class="card-body ir-chat-container p-0">
                <div id="ir-room-messages" class="ir-chat-messages">
                    <div class="mx-auto" style="max-width:48rem">
                        {{-- Messages --}}
                        <template x-for="(msg, index) in messages" :key="msg.uuid || index">
                            <div class="mb-3">
                                <template x-if="msg.sender_type === 'user'">
                                    <div class="d-flex justify-content-end">
                                        <div class="ir-msg-user rounded-3 bg-warning bg-opacity-75 text-dark px-3 py-2 small">
                                            <p class="mb-0" x-text="msg.content"></p>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="msg.sender_type === 'agent'">
                                    <div class="d-flex justify-content-start">
                                        <div class="ir-msg-agent">
                                            <template x-if="msg.metadata?.actions?.length > 0">
                                                <div class="mb-1">
                                                    <template x-for="(action, ai) in formatActions(msg.metadata.actions)" :key="ai">
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

                        <template x-if="error">
                            <div class="alert alert-danger small" x-text="error"></div>
                        </template>
                    </div>
                </div>

                {{-- Input --}}
                <div class="border-top p-3">
                    <form @submit.prevent="send" class="d-flex gap-2 mx-auto" style="max-width:48rem">
                        <input type="text" x-model="message"
                               placeholder="Challenge this idea, ask questions, or brainstorm..."
                               class="form-control" :disabled="loading" autofocus>
                        <button type="submit" class="btn btn-warning" :disabled="loading || !message.trim()">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4" style="overflow-y: auto; min-height: 0;">
        {{-- Objective --}}
        <div class="card shadow-0 mb-g" x-show="objective">
            <div class="card-header py-2">
                <h6 class="mb-0 small text-uppercase text-muted fw-bold">Session Objective</h6>
            </div>
            <div class="card-body py-2">
                <p class="mb-0 small" x-text="objective"></p>
            </div>
        </div>

        {{-- Idea Context --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header py-2">
                <h6 class="mb-0 small text-uppercase text-muted fw-bold">Idea Context</h6>
            </div>
            <div class="card-body py-2">
                <p class="small text-muted mb-2" x-text="ideaText"></p>
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <template x-for="bucket in buckets" :key="bucket.name">
                        <span class="badge bg-body-secondary text-body" x-text="bucket.full_path || bucket.name"></span>
                    </template>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <template x-for="tag in tags" :key="tag">
                        <span class="badge bg-warning bg-opacity-10 text-warning" x-text="tag"></span>
                    </template>
                </div>
            </div>
        </div>

        {{-- Memories --}}
        <div class="card shadow-0 mb-g" x-show="memories.length > 0">
            <div class="card-header py-2">
                <h6 class="mb-0 small text-uppercase text-muted fw-bold">Agent Memories</h6>
            </div>
            <div class="card-body py-2">
                <template x-for="memory in memories" :key="memory.id">
                    <div class="d-flex align-items-start gap-2 mb-2 small">
                        <div class="flex-grow-1">
                            <span class="badge bg-body-secondary text-body me-1" x-text="memory.memory_type"></span>
                            <span class="text-muted" x-text="memory.content"></span>
                        </div>
                        <button @click="deactivateMemory(memory.id)" class="btn btn-sm btn-link text-danger p-0" title="Deactivate">
                            &times;
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/idea-room/alpine-app.js'])
@endsection
