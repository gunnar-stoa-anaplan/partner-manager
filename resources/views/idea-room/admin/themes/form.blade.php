@extends('layouts.vertical', ['pageTitle' => ($theme ? 'Edit' : 'Create') . ' Gallery Theme'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => ($theme ? 'Edit' : 'New') . ' Gallery Theme',
        'pageSubTitle1' => 'Idea Room',
        'pageSubTitle1Url' => route('idea_room.dashboard'),
        'pageSubTitle2' => 'Themes',
        'pageSubTitle2Url' => route('idea_room.admin.themes.index'),
    ])
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ $theme ? route('idea_room.admin.themes.update', $theme) : route('idea_room.admin.themes.store') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    @if($theme)
        @method('PUT')
    @endif

    <div class="row">
        {{-- Left column --}}
        <div class="col-xl-8">

            {{-- Basic Info --}}
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Basic Info</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   value="{{ old('name', $theme?->name) }}"
                                   placeholder="e.g. Modern Loft"
                                   oninput="document.getElementById('slug').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                   id="slug" name="slug"
                                   value="{{ old('slug', $theme?->slug) }}"
                                   placeholder="auto-generated">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-muted small">(optional)</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="2"
                                  placeholder="Brief description of this theme">{{ old('description', $theme?->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Images --}}
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Images</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="background_image" class="form-label">
                                Background Image
                                @if(!$theme) <span class="text-danger">*</span> @endif
                            </label>
                            @if($theme?->background_image_url)
                                <div class="mb-2">
                                    <img src="{{ $theme->background_image_url }}" alt="Current background"
                                         class="rounded border" style="max-height: 100px; max-width: 100%;">
                                    <small class="d-block text-muted mt-1">Upload a new file to replace.</small>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('background_image') is-invalid @enderror"
                                   id="background_image" name="background_image" accept="image/jpeg,image/png,image/webp"
                                   onchange="if(this.files[0]){const r=new FileReader();r.onload=e=>window.dispatchEvent(new CustomEvent('bg-image-changed',{detail:{url:e.target.result}}));r.readAsDataURL(this.files[0])}">
                            @error('background_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">JPEG, PNG or WebP. Max 5 MB.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="template_overlay" class="form-label">
                                Video Mode Overlay <span class="text-muted small">(optional)</span>
                            </label>
                            @if($theme?->template_overlay_url)
                                <div class="mb-2">
                                    <img src="{{ $theme->template_overlay_url }}" alt="Current overlay"
                                         class="rounded border" style="max-height: 100px; max-width: 100%;">
                                    <small class="d-block text-muted mt-1">Upload a new file to replace.</small>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('template_overlay') is-invalid @enderror"
                                   id="template_overlay" name="template_overlay" accept="image/png">
                            @error('template_overlay')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">PNG with transparency. Layered over videos to create frame appearance.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="whiteboard_image" class="form-label">
                                Whiteboard Background <span class="text-muted small">(optional)</span>
                            </label>
                            @if($theme?->whiteboard_image_url)
                                <div class="mb-2">
                                    <img src="{{ $theme->whiteboard_image_url }}" alt="Current whiteboard"
                                         class="rounded border" style="max-height: 100px; max-width: 100%;">
                                    <small class="d-block text-muted mt-1">Upload a new file to replace.</small>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('whiteboard_image') is-invalid @enderror"
                                   id="whiteboard_image" name="whiteboard_image" accept="image/jpeg,image/png,image/webp"
                                   onchange="if(this.files[0]){const r=new FileReader();r.onload=e=>window.dispatchEvent(new CustomEvent('wb-image-changed',{detail:{url:e.target.result}}));r.readAsDataURL(this.files[0])}">
                            @error('whiteboard_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">JPEG, PNG or WebP. Max 5 MB. Used as the whiteboard background for idea capture.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Options --}}
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Options</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="aspect_ratio" class="form-label">Aspect Ratio</label>
                            <select class="form-select @error('aspect_ratio') is-invalid @enderror"
                                    id="aspect_ratio" name="aspect_ratio">
                                @php $currentRatio = old('aspect_ratio', $theme?->aspect_ratio ?? '16 / 9'); @endphp
                                <option value="16 / 9" {{ $currentRatio === '16 / 9' ? 'selected' : '' }}>16:9 (Widescreen)</option>
                                <option value="4 / 3" {{ $currentRatio === '4 / 3' ? 'selected' : '' }}>4:3 (Standard)</option>
                                <option value="21 / 9" {{ $currentRatio === '21 / 9' ? 'selected' : '' }}>21:9 (Ultra-wide)</option>
                                <option value="3 / 2" {{ $currentRatio === '3 / 2' ? 'selected' : '' }}>3:2 (Photo)</option>
                                <option value="1 / 1" {{ $currentRatio === '1 / 1' ? 'selected' : '' }}>1:1 (Square)</option>
                            </select>
                            @error('aspect_ratio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label d-block">Greyscale</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="greyscale" name="greyscale" value="1"
                                       {{ old('greyscale', $theme?->greyscale) ? 'checked' : '' }}>
                                <label class="form-check-label" for="greyscale">Apply B&W filter to videos</label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1"
                                       {{ old('is_published', $theme?->is_published) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">Published</label>
                            </div>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1"
                                       {{ old('is_default', $theme?->is_default) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_default">Default theme</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Frame Editor --}}
            @php
                $defaultSlot = [['left' => 10, 'top' => 10, 'width' => 20, 'height' => 25]];
                $initialSlots = old('frame_slots')
                    ? json_decode(old('frame_slots'), true)
                    : ($theme?->frame_slots ?? $defaultSlot);
            @endphp
            <div class="card shadow-0 mb-g" x-data="frameEditor(
                {{ json_encode($initialSlots) }},
                '{{ $theme?->background_image_url ?? '' }}',
                '{{ old('aspect_ratio', $theme?->aspect_ratio ?? '16 / 9') }}'
            )" x-init="detectRouteUrl = '{{ route('idea_room.admin.themes.detect-frames') }}'"
               @bg-image-changed.window="backgroundUrl = $event.detail.url">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0">Frame Layout</div>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-secondary" x-text="slots.length + ' frames'"></span>
                        <button type="button" class="btn btn-sm btn-outline-info"
                                :disabled="!backgroundUrl || detecting"
                                @click="detectFrames()"
                                x-show="backgroundUrl">
                            <template x-if="detecting">
                                <span><span class="spinner-border spinner-border-sm me-1" role="status"></span> Detecting...</span>
                            </template>
                            <template x-if="!detecting">
                                <span><i class="fal fa-wand-magic-sparkles me-1"></i> Detect Frames</span>
                            </template>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" @click="addSlot()">
                            <i class="fal fa-plus me-1"></i> Add Frame
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Canvas --}}
                    <div x-ref="canvas" class="frame-editor-canvas position-relative mb-3"
                         :style="'aspect-ratio: ' + aspectRatio"
                         @mousemove.window="onMouseMove($event)"
                         @mouseup.window="onMouseUp()">

                        {{-- Background preview --}}
                        <template x-if="backgroundUrl">
                            <img :src="backgroundUrl" alt="" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover pe-none rounded">
                        </template>

                        {{-- Frame slots --}}
                        <template x-for="(slot, i) in slots" :key="slot.id">
                            <div class="frame-editor-slot"
                                 :style="slotStyle(slot)"
                                 @mousedown="startMove($event, slot)">
                                <span class="frame-editor-slot-label" x-text="'#' + (i + 1)"></span>
                                <button type="button" class="frame-editor-remove" @click.stop="removeSlot(slot.id)">&times;</button>
                                <div class="frame-resize-handle" @mousedown.stop="startResize($event, slot)"></div>
                            </div>
                        </template>
                    </div>

                    {{-- Detection feedback --}}
                    <template x-if="detectMessage">
                        <div class="alert alert-info py-2 small mb-3" x-text="detectMessage"></div>
                    </template>
                    <template x-if="detectError">
                        <div class="alert alert-warning py-2 small mb-3" x-text="detectError"></div>
                    </template>

                    {{-- Fine-tune table --}}
                    <template x-if="slots.length > 0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px">#</th>
                                        <th>Left %</th>
                                        <th>Top %</th>
                                        <th>Width %</th>
                                        <th>Height %</th>
                                        <th style="width: 50px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(slot, i) in slots" :key="'row-'+slot.id">
                                        <tr>
                                            <td class="text-center align-middle" x-text="i + 1"></td>
                                            <td><input type="number" class="form-control form-control-sm" x-model.number="slot.left" min="0" max="100" step="any"></td>
                                            <td><input type="number" class="form-control form-control-sm" x-model.number="slot.top" min="0" max="100" step="any"></td>
                                            <td><input type="number" class="form-control form-control-sm" x-model.number="slot.width" min="1" max="100" step="any"></td>
                                            <td><input type="number" class="form-control form-control-sm" x-model.number="slot.height" min="1" max="100" step="any"></td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-sm btn-outline-danger p-1 lh-1" @click="removeSlot(slot.id)">
                                                    <i class="fal fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    {{-- Hidden input for form submission --}}
                    <input type="hidden" name="frame_slots" x-ref="hiddenInput"
                           :value="JSON.stringify(slots.map(({left, top, width, height}) => ({
                               left: Math.round(left * 100) / 100,
                               top: Math.round(top * 100) / 100,
                               width: Math.round(width * 100) / 100,
                               height: Math.round(height * 100) / 100,
                           })))">
                </div>
            </div>

            {{-- Writing Area Editor --}}
            @php
                $defaultWritingArea = ['left' => 14, 'top' => 13, 'width' => 72, 'height' => 41];
                $initialWritingArea = old('writing_area')
                    ? json_decode(old('writing_area'), true)
                    : ($theme?->writing_area ?? $defaultWritingArea);
            @endphp
            <div class="card shadow-0 mb-g" x-data="{
                wa: {{ json_encode($initialWritingArea) }},
                wbUrl: '{{ $theme?->whiteboard_image_url ?? '' }}',
                waActive: false,
                waDragState: null,
                waDragOffset: { x: 0, y: 0 },

                waStyle() {
                    return 'left:' + this.wa.left + '%;top:' + this.wa.top + '%;width:' + this.wa.width + '%;height:' + this.wa.height + '%';
                },

                waStartMove(event) {
                    if (event.target.closest('.wa-resize-handle')) { return; }
                    event.preventDefault();
                    this.waActive = true;
                    this.waDragState = 'move';
                    const rect = this.$refs.wbCanvas.getBoundingClientRect();
                    this.waDragOffset = {
                        x: event.clientX - rect.left - (this.wa.left / 100) * rect.width,
                        y: event.clientY - rect.top - (this.wa.top / 100) * rect.height,
                    };
                },

                waStartResize(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.waActive = true;
                    this.waDragState = 'resize';
                    this.waDragOffset = {
                        x: event.clientX,
                        y: event.clientY,
                        startWidth: this.wa.width,
                        startHeight: this.wa.height,
                    };
                },

                waMouseMove(event) {
                    if (!this.waActive || !this.waDragState) { return; }
                    const rect = this.$refs.wbCanvas.getBoundingClientRect();
                    if (this.waDragState === 'move') {
                        let nl = ((event.clientX - rect.left - this.waDragOffset.x) / rect.width) * 100;
                        let nt = ((event.clientY - rect.top - this.waDragOffset.y) / rect.height) * 100;
                        this.wa.left = Math.round(Math.max(0, Math.min(100 - this.wa.width, nl)) * 10) / 10;
                        this.wa.top = Math.round(Math.max(0, Math.min(100 - this.wa.height, nt)) * 10) / 10;
                    }
                    if (this.waDragState === 'resize') {
                        const dx = ((event.clientX - this.waDragOffset.x) / rect.width) * 100;
                        const dy = ((event.clientY - this.waDragOffset.y) / rect.height) * 100;
                        this.wa.width = Math.round(Math.max(5, Math.min(100 - this.wa.left, this.waDragOffset.startWidth + dx)) * 10) / 10;
                        this.wa.height = Math.round(Math.max(5, Math.min(100 - this.wa.top, this.waDragOffset.startHeight + dy)) * 10) / 10;
                    }
                },

                waMouseUp() {
                    this.waActive = false;
                    this.waDragState = null;
                }
            }" @wb-image-changed.window="wbUrl = $event.detail.url">
                <div class="card-header">
                    <div class="card-title mb-0">Writing Area</div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Position the text input area on the whiteboard background. Drag to move, use the handle to resize.</p>

                    {{-- Canvas --}}
                    <div x-ref="wbCanvas" class="frame-editor-canvas position-relative mb-3"
                         style="aspect-ratio: 3 / 2; background: linear-gradient(160deg, #f5f0e8 0%, #e8e0d0 100%);"
                         @mousemove.window="waMouseMove($event)"
                         @mouseup.window="waMouseUp()">

                        <template x-if="wbUrl">
                            <img :src="wbUrl" alt="" class="position-absolute top-0 start-0 w-100 h-100 pe-none rounded" style="object-fit: fill;">
                        </template>

                        {{-- Writing area rectangle --}}
                        <div class="writing-area-editor-slot"
                             :style="waStyle()"
                             @mousedown="waStartMove($event)">
                            <span class="writing-area-editor-label">Writing Area</span>
                            <div class="wa-resize-handle" @mousedown.stop="waStartResize($event)"></div>
                        </div>
                    </div>

                    {{-- Fine-tune inputs --}}
                    <div class="row g-2">
                        <div class="col-3">
                            <label class="form-label small mb-1">Left %</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="wa.left" min="0" max="100" step="any">
                        </div>
                        <div class="col-3">
                            <label class="form-label small mb-1">Top %</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="wa.top" min="0" max="100" step="any">
                        </div>
                        <div class="col-3">
                            <label class="form-label small mb-1">Width %</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="wa.width" min="1" max="100" step="any">
                        </div>
                        <div class="col-3">
                            <label class="form-label small mb-1">Height %</label>
                            <input type="number" class="form-control form-control-sm" x-model.number="wa.height" min="1" max="100" step="any">
                        </div>
                    </div>

                    {{-- Hidden input --}}
                    <input type="hidden" name="writing_area"
                           :value="JSON.stringify({
                               left: Math.round(wa.left * 100) / 100,
                               top: Math.round(wa.top * 100) / 100,
                               width: Math.round(wa.width * 100) / 100,
                               height: Math.round(wa.height * 100) / 100,
                           })">
                </div>
            </div>

        </div>

        {{-- Right column --}}
        <div class="col-xl-4">
            <div class="card shadow-0 mb-g position-sticky" style="top: 1rem;">
                <div class="card-header">
                    <div class="card-title">Actions</div>
                </div>
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fal fa-check me-1"></i> {{ $theme ? 'Update Theme' : 'Create Theme' }}
                    </button>

                    <a href="{{ route('idea_room.admin.themes.index') }}" class="btn btn-outline-secondary w-100">
                        Cancel
                    </a>
                </div>
            </div>

            {{-- Preview new background image --}}
            <div class="card shadow-0 mb-g" x-data="{ previewUrl: null }" @bg-image-changed.window="previewUrl = $event.detail.url">
                <div class="card-header">
                    <div class="card-title">Image Preview</div>
                </div>
                <div class="card-body text-center">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Preview" class="rounded border w-100" style="max-height: 200px; object-fit: cover;">
                    </template>
                    <template x-if="!previewUrl">
                        <div class="text-muted small py-4">
                            <i class="fal fa-image fa-2x d-block mb-2"></i>
                            Upload a background image to see a preview.
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/idea-room/frame-editor.js'])
@endsection
