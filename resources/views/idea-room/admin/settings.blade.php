@extends('layouts.vertical', ['pageTitle' => 'Idea Room — Settings'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Idea Room',
        'pageSubTitle1' => 'Admin Settings',
    ])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Configure AI providers, gallery defaults, and room behavior for the Idea Room module. Changes here affect all users.</p>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('idea_room.admin.settings.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        {{-- Left column --}}
        <div class="col-xl-8">

            {{-- AI Provider --}}
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">AI Provider</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ai_provider" class="form-label">Provider</label>
                            <select class="form-select @error('ai_provider') is-invalid @enderror"
                                    id="ai_provider" name="ai_provider">
                                <option value="" {{ old('ai_provider', $settings['idea_room.ai_provider'] ?? '') === '' ? 'selected' : '' }}>Simulated (built-in)</option>
                                <option value="anthropic" {{ old('ai_provider', $settings['idea_room.ai_provider'] ?? '') === 'anthropic' ? 'selected' : '' }}>Anthropic</option>
                                <option value="openai" {{ old('ai_provider', $settings['idea_room.ai_provider'] ?? '') === 'openai' ? 'selected' : '' }}>OpenAI</option>
                            </select>
                            @error('ai_provider')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Select the AI provider for idea generation and room conversations.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="ai_model" class="form-label">Model</label>
                            <input type="text" class="form-control @error('ai_model') is-invalid @enderror"
                                   id="ai_model" name="ai_model"
                                   value="{{ old('ai_model', $settings['idea_room.ai_model'] ?? '') }}"
                                   placeholder="e.g. claude-sonnet-4-5-20250929">
                            @error('ai_model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Leave blank to use the provider default.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ai_api_key" class="form-label">API Key</label>
                            <input type="password" class="form-control @error('ai_api_key') is-invalid @enderror"
                                   id="ai_api_key" name="ai_api_key"
                                   placeholder="{{ ($settings['idea_room.ai_provider'] ?? '') ? '••••••••  (leave blank to keep current)' : 'Not required for simulated mode' }}">
                            @error('ai_api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Stored encrypted. Leave blank to keep the existing key.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gallery --}}
            @php
                $galleryThemes = \App\Models\IdeaRoom\GalleryTheme::query()->published()->get();
                $themeThumbnails = $galleryThemes->mapWithKeys(fn($t) => [$t->slug => $t->background_image_url])->toJson();
                $selectedSlug = old('default_gallery_template', $settings['idea_room.default_gallery_template'] ?? '');
            @endphp
            <div class="card shadow-0 mb-g" x-data="{
                thumbnails: {{ $themeThumbnails }},
                selected: '{{ $selectedSlug }}',
                get thumbnailUrl() { return this.thumbnails[this.selected] || null; }
            }">
                <div class="card-header">
                    <div class="card-title">Gallery</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="default_gallery_template" class="form-label">Default Template</label>
                            <select class="form-select @error('default_gallery_template') is-invalid @enderror"
                                    id="default_gallery_template" name="default_gallery_template"
                                    x-model="selected">
                                @foreach($galleryThemes as $galleryTheme)
                                    <option value="{{ $galleryTheme->slug }}" {{ $selectedSlug === $galleryTheme->slug ? 'selected' : '' }}>
                                        {{ $galleryTheme->name }} ({{ $galleryTheme->max_frames }} frames)
                                    </option>
                                @endforeach
                            </select>
                            @error('default_gallery_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Template applied to new users.
                                <a href="{{ route('idea_room.admin.themes.index') }}">Manage gallery themes</a>
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preview</label>
                            <template x-if="thumbnailUrl">
                                <img :src="thumbnailUrl" alt="Template preview" class="rounded border w-100" style="max-height: 140px; object-fit: cover;">
                            </template>
                            <template x-if="!thumbnailUrl">
                                <div class="rounded border bg-light d-flex align-items-center justify-content-center text-muted small" style="height: 100px;">
                                    No preview available
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Room Settings --}}
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Rooms</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="max_rooms_per_user" class="form-label">Max Rooms per User</label>
                            <input type="number" class="form-control @error('max_rooms_per_user') is-invalid @enderror"
                                   id="max_rooms_per_user" name="max_rooms_per_user"
                                   value="{{ old('max_rooms_per_user', $settings['idea_room.max_rooms_per_user'] ?? 10) }}"
                                   min="1" max="100">
                            @error('max_rooms_per_user')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block">External Participants</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       id="allow_external_participants" name="allow_external_participants"
                                       value="1"
                                       {{ old('allow_external_participants', $settings['idea_room.allow_external_participants'] ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_external_participants">
                                    Allow inviting external participants to rooms
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right column — summary --}}
        <div class="col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Quick Links</div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <a href="{{ route('idea_room.dashboard') }}" class="text-decoration-none">
                                <i class="fal fa-image me-1"></i> Gallery
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('idea_room.chat') }}" class="text-decoration-none">
                                <i class="fal fa-comment me-1"></i> Chat
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('idea_room.constellation') }}" class="text-decoration-none">
                                <i class="fal fa-project-diagram me-1"></i> Constellation
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-4">
        <button type="submit" class="btn btn-primary">
            Save Settings
        </button>
    </div>
</form>
@endsection
