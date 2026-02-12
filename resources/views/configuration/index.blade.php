@extends('layouts.vertical', ['pageTitle' => 'Configuration'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Configuration', 'pageSubTitle1' => 'System', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Configuration manages the system-level settings that control how Partner Manager behaves — navigation visibility, fiscal year boundaries, Salesforce integration, and default values. Changes here affect all users, so update with care. Use the sections below to fine-tune the platform to match your program's operating model.</p>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-xl-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Application Settings</div>
            </div>
            <div class="card-body">
                <form action="{{ route('configuration.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                   id="company_name" name="company_name"
                                   value="{{ old('company_name', $settings['company_name'] ?? 'Anaplan') }}">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="default_currency" class="form-label">Default Currency</label>
                            <input type="text" class="form-control @error('default_currency') is-invalid @enderror"
                                   id="default_currency" name="default_currency"
                                   value="{{ old('default_currency', $settings['default_currency'] ?? 'USD') }}"
                                   maxlength="10">
                            @error('default_currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Currency code (e.g. USD, EUR, GBP)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fiscal_year_start_month" class="form-label">Fiscal Year Start Month</label>
                            <select class="form-select @error('fiscal_year_start_month') is-invalid @enderror"
                                    id="fiscal_year_start_month" name="fiscal_year_start_month">
                                @php
                                    $months = [
                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                                    ];
                                    $selected = old('fiscal_year_start_month', $settings['fiscal_year_start_month'] ?? 2);
                                @endphp
                                @foreach($months as $num => $name)
                                    <option value="{{ $num }}" {{ (int)$selected === $num ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('fiscal_year_start_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">The month your fiscal year begins (used for quarter calculations)</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="historical_lookback_weeks" class="form-label">Historical Lookback (Weeks)</label>
                            <input type="number" class="form-control @error('historical_lookback_weeks') is-invalid @enderror"
                                   id="historical_lookback_weeks" name="historical_lookback_weeks"
                                   value="{{ old('historical_lookback_weeks', $settings['historical_lookback_weeks'] ?? 12) }}"
                                   min="1" max="52">
                            @error('historical_lookback_weeks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Number of weeks shown in trend analysis charts</small>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#save"></use></svg>
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">
                    Navigation Visibility
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted fs-sm mb-3">Toggle navigation sections on/off during development. Hidden sections won't appear in the sidebar for any user.</p>
                <form action="{{ route('configuration.update-navigation') }}" method="POST">
                    @csrf
                    @method('PUT')

                    @php
                        $navSections = [
                            'nav_visible_analysis' => ['label' => 'Analysis Tools', 'desc' => 'Custom Analysis, Guided Analysis, Partner Coach'],
                            'nav_visible_plan' => ['label' => 'Plan', 'desc' => 'Partner Paths, Scorecard, Competency Matrix, Task Board, Goals & Targets, Action Plans'],
                            'nav_visible_engage' => ['label' => 'Engage', 'desc' => 'Project Health, Field Engagements, Field Enablement, Solution Lifecycle'],
                            'nav_visible_key_metrics' => ['label' => 'Key Metrics', 'desc' => 'Partner Profile, Summary, Insights, Calendar, Blueprints'],
                            'nav_visible_business_dev' => ['label' => 'Business Development', 'desc' => 'Sourced Leads, Influenced Leads, Development Funding'],
                            'nav_visible_measure' => ['label' => 'Measure', 'desc' => 'Full Summary, Full Insights, At a Glance, Pipeline, Lead Flow, Funding, Revenue, Enablement, Delivery'],
                            'nav_visible_act' => ['label' => 'Act', 'desc' => 'Alerts & Recommendations, QBR Workbench, Path Reviews'],
                            'nav_visible_administration' => ['label' => 'Administration', 'desc' => 'Admin Dashboard, Partner Goals, Assignments, Leads, Users, Path Config, Templates'],
                            'nav_visible_program_admin' => ['label' => 'Partner Enablement', 'desc' => 'Enablement Events, Calendar, Blueprints'],
                            'nav_visible_system' => ['label' => 'System', 'desc' => 'Configuration, Imports, Salesforce, Pulse'],
                        ];
                    @endphp

                    @foreach($navSections as $key => $section)
                        <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <span class="fw-500 fs-sm">{{ $section['label'] }}</span>
                                <small class="d-block text-muted">{{ $section['desc'] }}</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="{{ $key }}" value="0">
                                <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1"
                                       {{ ($settings[$key] ?? '1') === '1' ? 'checked' : '' }}>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#eye"></use></svg>
                            Update Visibility
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
