@extends('layouts.vertical', ['pageTitle' => 'Idea Room — Whiteboard', 'extraClass' => 'layout-trimmed'])

@section('content')
<div x-data="whiteboard" x-init="$nextTick(() => $refs.whiteboardInput.focus())">

    {{-- Floating toolbar --}}
    <div class="position-fixed d-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-dark bg-opacity-50 text-white" style="bottom: 1.5rem; left: 50%; transform: translateX(-50%); z-index: 100; backdrop-filter: blur(8px);">
        <a href="{{ route('idea_room.dashboard') }}" class="btn btn-sm btn-outline-light border-0 rounded-pill">
            <i class="fal fa-arrow-left me-1"></i> Gallery
        </a>
        <div class="vr mx-1 opacity-50"></div>

        {{-- Feedback inline --}}
        <template x-if="feedback === 'success'">
            <span class="small text-warning fw-semibold d-flex align-items-center gap-1">
                <span class="ir-whiteboard-spark">&#10024;</span> Saved!
            </span>
        </template>
        <template x-if="feedback === 'error'">
            <span class="small text-danger" x-text="errorMessage"></span>
        </template>

        <span class="small opacity-75" x-show="captureCount > 0 && !feedback" x-text="captureCount + ' saved'"></span>

        <button type="button" class="btn btn-sm btn-warning rounded-pill"
                @click="submit()"
                :disabled="submitting || !text.trim()"
                x-show="!feedback">
            <template x-if="!submitting">
                <span><i class="fal fa-paper-plane me-1"></i> Save</span>
            </template>
            <template x-if="submitting">
                <span class="d-flex align-items-center gap-1">
                    <span class="spinner-border spinner-border-sm"></span> Saving...
                </span>
            </template>
        </button>

        <span class="small opacity-50 ms-1" x-show="!feedback">Enter to save</span>
    </div>

    {{-- Whiteboard surface --}}
    <div class="ir-whiteboard-viewport" @click="$refs.whiteboardInput.focus()">
        <div class="ir-whiteboard-surface" style="--ar-w:{{ $arW }};--ar-h:{{ $arH }}">
            {{-- Background image --}}
            @if($whiteboardImageUrl)
                <img src="{{ $whiteboardImageUrl }}" alt="" class="ir-whiteboard-bg">
            @else
                <div class="ir-whiteboard-bg ir-whiteboard-bg--default"></div>
            @endif

            {{-- Textarea directly on the whiteboard --}}
            <textarea x-ref="whiteboardInput"
                      x-model="text"
                      @keydown="handleKeydown($event)"
                      placeholder="Start writing..."
                      class="ir-whiteboard-textarea"
                      style="top:{{ $writingArea['top'] }}%;left:{{ $writingArea['left'] }}%;width:{{ $writingArea['width'] }}%;height:{{ $writingArea['height'] }}%"
                      :disabled="submitting"></textarea>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/idea-room/alpine-app.js'])
@endsection
