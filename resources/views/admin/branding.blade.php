@extends('layouts.vertical', ['pageTitle' => 'Branding'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Branding', 'pageSubTitle1' => 'System', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Customize how the application looks to your users. Set your organization's name, upload a logo and favicon, and choose a default color theme. Individual users can still override the theme from their settings drawer.</p>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.branding.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-xl-8">

            {{-- Identity Card --}}
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Identity</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="branding_app_name" class="form-label">Application Name</label>
                            <input type="text" class="form-control @error('branding_app_name') is-invalid @enderror"
                                   id="branding_app_name" name="branding_app_name"
                                   value="{{ old('branding_app_name', $settings['branding_app_name'] ?? '') }}"
                                   placeholder="Partner Manager">
                            @error('branding_app_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Displayed in the browser title bar and throughout the app. Leave blank to use the default.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="branding_logo" class="form-label">Logo</label>
                            @php $currentLogo = $settings['branding_logo_path'] ?? ''; @endphp
                            @if($currentLogo && Storage::disk('public')->exists($currentLogo))
                                <div class="mb-2 p-3 border rounded bg-light d-flex align-items-center gap-3">
                                    <img src="{{ Storage::url($currentLogo) }}" alt="Current logo" style="max-height: 40px; max-width: 200px;">
                                    <div class="form-check ms-auto">
                                        <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo" value="1">
                                        <label class="form-check-label text-danger fs-sm" for="remove_logo">Remove</label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('branding_logo') is-invalid @enderror"
                                   id="branding_logo" name="branding_logo" accept="image/*">
                            @error('branding_logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">PNG, JPG, GIF, or SVG. Max 2 MB. Displayed in the sidebar header.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="branding_favicon" class="form-label">Favicon</label>
                            @php $currentFavicon = $settings['branding_favicon_path'] ?? ''; @endphp
                            @if($currentFavicon && Storage::disk('public')->exists($currentFavicon))
                                <div class="mb-2 p-3 border rounded bg-light d-flex align-items-center gap-3">
                                    <img src="{{ Storage::url($currentFavicon) }}" alt="Current favicon" style="max-height: 32px; max-width: 32px;">
                                    <div class="form-check ms-auto">
                                        <input class="form-check-input" type="checkbox" id="remove_favicon" name="remove_favicon" value="1">
                                        <label class="form-check-label text-danger fs-sm" for="remove_favicon">Remove</label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('branding_favicon') is-invalid @enderror"
                                   id="branding_favicon" name="branding_favicon" accept="image/png,image/x-icon">
                            @error('branding_favicon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">PNG or ICO. Max 512 KB. Shown in the browser tab.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Default Theme Card --}}
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Default Theme</div>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-sm mb-3">Choose the default color theme for all users. Users can override this from their personal settings.</p>

                    @php $currentTheme = old('branding_theme', $settings['branding_theme'] ?? ''); @endphp

                    @php
                        $themeSwatches = [
                            '' => ['label' => 'Default', 'gradient' => 'linear-gradient(135deg, #FF6A00, #F6A2D5, #4C91BF, #7A8B92, #AB7C9A)'],
                            'nebula' => ['label' => 'Nebula', 'gradient' => 'linear-gradient(135deg, #2a7dbf, #2a9d8f, #766cbc, #f4a261, #e76f51)'],
                            'olive' => ['label' => 'Olive', 'gradient' => 'linear-gradient(135deg, #556B2F, #6B8E23, #8B9A3D, #A9B83E, #BDB76B)'],
                            'solar' => ['label' => 'Solar', 'gradient' => 'linear-gradient(135deg, #FF8C00, #FFD700, #FF4500, #F1C40F, #F39C12)'],
                            'lunar' => ['label' => 'Lunar', 'gradient' => 'linear-gradient(135deg, #2C3E50, #34495E, #5F6368, #AAB7B8, #E6E6FA, #F1F3F4)'],
                            'night' => ['label' => 'Night', 'gradient' => 'linear-gradient(135deg, #1e2a47, #2b3654, #363d6c, #4f5d79, #717b91, #b6c4d1)'],
                            'aurora' => ['label' => 'Aurora', 'gradient' => 'linear-gradient(135deg, #337e7e, #527a4a, #63279b, #7FFF00, #87CEFA, #B0E0E6)'],
                            'earth' => ['label' => 'Earth', 'gradient' => 'linear-gradient(135deg, #2198f3, #3173a5, #3f6888, #618d48, #52bf11)'],
                            'flare' => ['label' => 'Flare', 'gradient' => 'linear-gradient(135deg, #FF4500, #FF6347, #F44336, #D32F2F, #B71C1C)'],
                            'storm' => ['label' => 'Storm', 'gradient' => 'linear-gradient(135deg, #2F4F4F, #3B5360, #4B6A6E, #5A7980, #A9A9A9, #FFD700)'],
                        ];
                    @endphp

                    <div class="d-flex flex-wrap gap-3">
                        @foreach($themeSwatches as $value => $swatch)
                            <label class="theme-swatch-label position-relative" style="cursor: pointer;">
                                <input type="radio" name="branding_theme" value="{{ $value }}"
                                       class="position-absolute opacity-0"
                                       {{ $currentTheme === $value ? 'checked' : '' }}>
                                <div class="theme-swatch rounded border {{ $currentTheme === $value ? 'border-primary border-2' : 'border-secondary-subtle' }}"
                                     style="width: 80px; height: 50px; background: {{ $swatch['gradient'] }}; transition: border-color 0.15s;">
                                    @if($currentTheme === $value)
                                        <div class="position-absolute top-0 end-0 m-1">
                                            <span class="badge bg-primary rounded-pill" style="font-size: 0.6rem;">
                                                <svg class="sa-icon sa-icon-xs"><use href="/icons/sprite.svg#check"></use></svg>
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <small class="d-block text-center mt-1 fw-500 fs-xs">{{ $swatch['label'] }}</small>
                            </label>
                        @endforeach
                    </div>

                    @error('branding_theme')
                        <div class="text-danger fs-sm mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-g">
                <button type="submit" class="btn btn-primary">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#save"></use></svg>
                    Save Branding
                </button>
            </div>

        </div>

        <div class="col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Preview</div>
                </div>
                <div class="card-body">
                    @php $logoPath = $settings['branding_logo_path'] ?? ''; @endphp
                    @if($logoPath && Storage::disk('public')->exists($logoPath))
                        <div class="text-center mb-3 p-3 rounded" style="background: var(--bs-gray-800);">
                            <img src="{{ Storage::url($logoPath) }}" alt="Current logo" style="max-height: 48px; max-width: 100%;">
                        </div>
                    @else
                        <div class="text-center mb-3 p-3 rounded" style="background: var(--bs-gray-800);">
                            <svg class="custom-logo" style="height: 48px; width: auto;">
                                <use href="/img/app-logo.svg#custom-logo"></use>
                            </svg>
                        </div>
                    @endif

                    <dl class="mb-0">
                        <dt class="text-muted fs-sm">App Name</dt>
                        <dd>{{ $settings['branding_app_name'] ?? 'Partner Manager' ?: 'Partner Manager' }}</dd>

                        <dt class="text-muted fs-sm">Theme</dt>
                        <dd>{{ ucfirst($settings['branding_theme'] ?? '') ?: 'Default' }}</dd>

                        <dt class="text-muted fs-sm">Favicon</dt>
                        <dd>{{ ($settings['branding_favicon_path'] ?? '') ? 'Custom' : 'Default' }}</dd>
                    </dl>
                </div>
                <div class="card-footer">
                    <small class="text-muted">These settings define the deployment default. Individual users can override the color theme from their personal settings drawer.</small>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
