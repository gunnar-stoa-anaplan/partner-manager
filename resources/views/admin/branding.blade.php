@extends('layouts.vertical', ['pageTitle' => 'Branding'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Branding', 'pageSubTitle1' => 'System', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Customize how the application looks to your users. Set your organization's name, upload a logo and favicon, choose a default color theme, create a custom theme, or override brand colors. Individual users can still override the theme from their settings drawer.</p>

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

                        @php
                            $customThemeName = $settings['branding_custom_theme_name'] ?? '';
                            $customCssExists = ($settings['branding_custom_css_path'] ?? '') && Storage::disk('public')->exists($settings['branding_custom_css_path'] ?? '');
                        @endphp
                        @if($customCssExists && $customThemeName)
                            <label class="theme-swatch-label position-relative" style="cursor: pointer;">
                                <input type="radio" name="branding_theme" value="custom"
                                       class="position-absolute opacity-0"
                                       {{ $currentTheme === 'custom' ? 'checked' : '' }}>
                                <div class="theme-swatch rounded border {{ $currentTheme === 'custom' ? 'border-primary border-2' : 'border-secondary-subtle' }} d-flex align-items-center justify-content-center"
                                     style="width: 80px; height: 50px; background: linear-gradient(135deg, var(--bs-primary), var(--bs-info), var(--bs-success)); transition: border-color 0.15s;">
                                    @if($currentTheme === 'custom')
                                        <div class="position-absolute top-0 end-0 m-1">
                                            <span class="badge bg-primary rounded-pill" style="font-size: 0.6rem;">
                                                <svg class="sa-icon sa-icon-xs"><use href="/icons/sprite.svg#check"></use></svg>
                                            </span>
                                        </div>
                                    @endif
                                    <svg class="sa-icon text-white" style="opacity: 0.85;"><use href="/icons/sprite.svg#palette"></use></svg>
                                </div>
                                <small class="d-block text-center mt-1 fw-500 fs-xs">{{ $customThemeName }}</small>
                            </label>
                        @endif
                    </div>

                    @error('branding_theme')
                        <div class="text-danger fs-sm mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Custom Brand Colors Card --}}
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Custom Brand Colors</div>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-sm mb-3">Override specific colors to match your organization's branding. Leave a field empty to use the selected theme's default for that color.</p>

                    @php
                        $brandColors = [
                            'primary' => ['label' => 'Primary', 'default' => '#886ab5'],
                            'success' => ['label' => 'Success', 'default' => '#1dc9b7'],
                            'info' => ['label' => 'Info', 'default' => '#2196f3'],
                            'warning' => ['label' => 'Warning', 'default' => '#ffc241'],
                            'danger' => ['label' => 'Danger', 'default' => '#fd3995'],
                        ];
                    @endphp

                    <div class="row g-3">
                        @foreach($brandColors as $key => $color)
                            <div class="col-sm-6 col-lg">
                                <label for="branding_color_{{ $key }}" class="form-label fs-sm">{{ $color['label'] }}</label>
                                @php $savedColor = old('branding_color_'.$key, $settings['branding_color_'.$key] ?? ''); @endphp
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color border-end-0"
                                           id="picker_{{ $key }}"
                                           value="{{ $savedColor ?: $color['default'] }}"
                                           oninput="document.getElementById('branding_color_{{ $key }}').value = this.value">
                                    <input type="text" class="form-control font-monospace fs-sm"
                                           id="branding_color_{{ $key }}"
                                           name="branding_color_{{ $key }}"
                                           value="{{ $savedColor }}"
                                           placeholder="Not set"
                                           maxlength="7"
                                           oninput="if(this.value.match(/^#[0-9a-fA-F]{6}$/)) document.getElementById('picker_{{ $key }}').value = this.value">
                                </div>
                                @error('branding_color_'.$key)
                                    <div class="text-danger fs-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    @php $hasAnyCustomColor = collect($brandColors)->keys()->contains(fn($key) => ! empty($settings['branding_color_'.$key])); @endphp
                    @if($hasAnyCustomColor)
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="clear_custom_colors" name="clear_custom_colors" value="1">
                            <label class="form-check-label text-danger fs-sm" for="clear_custom_colors">Clear all custom colors</label>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Custom Theme Card --}}
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title">Custom Theme</div>
                    <a href="/css/sample-theme-template.css" download class="btn btn-outline-info btn-xs ms-auto">
                        <svg class="sa-icon sa-icon-xs me-1"><use href="/icons/sprite.svg#download"></use></svg>
                        Download Template
                    </a>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-sm mb-3">Create your own theme by editing the sample template, then upload it here with a name. The theme will appear in the theme picker above alongside the built-in themes.</p>

                    <div class="mb-3">
                        <label for="branding_custom_theme_name" class="form-label">Theme Name</label>
                        <input type="text" class="form-control @error('branding_custom_theme_name') is-invalid @enderror"
                               id="branding_custom_theme_name" name="branding_custom_theme_name"
                               value="{{ old('branding_custom_theme_name', $settings['branding_custom_theme_name'] ?? '') }}"
                               placeholder="e.g. Acme Corporate"
                               maxlength="50">
                        @error('branding_custom_theme_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">This name appears in the theme picker. Required to make the theme selectable.</small>
                    </div>

                    @php $currentCss = $settings['branding_custom_css_path'] ?? ''; @endphp
                    @if($currentCss && Storage::disk('public')->exists($currentCss))
                        <div class="mb-2 p-3 border rounded bg-light d-flex align-items-center gap-3">
                            <svg class="sa-icon"><use href="/icons/sprite.svg#file"></use></svg>
                            <span class="fs-sm font-monospace">{{ basename($currentCss) }}</span>
                            <div class="form-check ms-auto">
                                <input class="form-check-input" type="checkbox" id="remove_custom_css" name="remove_custom_css" value="1">
                                <label class="form-check-label text-danger fs-sm" for="remove_custom_css">Remove</label>
                            </div>
                        </div>
                    @endif

                    <input type="file" class="form-control @error('branding_custom_css') is-invalid @enderror"
                           id="branding_custom_css" name="branding_custom_css" accept=".css,text/css">
                    @error('branding_custom_css')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">CSS file only. Max 1 MB. Override Bootstrap CSS variables or add custom rules.</small>
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

                        <dt class="text-muted fs-sm">Custom Colors</dt>
                        @php
                            $customColorCount = collect(['primary', 'success', 'info', 'warning', 'danger'])
                                ->filter(fn($k) => ! empty($settings['branding_color_'.$k]))
                                ->count();
                        @endphp
                        <dd>{{ $customColorCount ? $customColorCount.' of 5 set' : 'None' }}</dd>

                        <dt class="text-muted fs-sm">Custom Theme</dt>
                        @php $previewThemeName = $settings['branding_custom_theme_name'] ?? ''; @endphp
                        <dd>{{ ($settings['branding_custom_css_path'] ?? '') ? ($previewThemeName ?: 'Uploaded (unnamed)') : 'None' }}</dd>

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

@push('scripts')
<script>
    document.querySelectorAll('input[name="branding_theme"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.theme-swatch-label .theme-swatch').forEach(function(swatch) {
                swatch.classList.remove('border-primary', 'border-2');
                swatch.classList.add('border-secondary-subtle');
                var badge = swatch.querySelector('.position-absolute');
                if (badge) {
                    badge.remove();
                }
            });
            var swatch = this.closest('.theme-swatch-label').querySelector('.theme-swatch');
            swatch.classList.remove('border-secondary-subtle');
            swatch.classList.add('border-primary', 'border-2');
            var checkHtml = '<div class="position-absolute top-0 end-0 m-1">' +
                '<span class="badge bg-primary rounded-pill" style="font-size: 0.6rem;">' +
                '<svg class="sa-icon sa-icon-xs"><use href="/icons/sprite.svg#check"></use></svg>' +
                '</span></div>';
            swatch.insertAdjacentHTML('afterbegin', checkHtml);
        });
    });
</script>
@endpush
