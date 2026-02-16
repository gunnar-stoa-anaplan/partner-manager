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
                    Dev Focus
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted fs-sm mb-3">Select a feature to focus on. The sidebar will show only the screens relevant to that feature.</p>

                <form id="dev-focus-form" action="{{ route('configuration.update-dev-focus') }}" method="POST">
                    @csrf
                    @method('PUT')

                    @php
                        $currentFocus = $settings['dev_focus_feature'] ?? '';
                        $focusOptions = [
                            '' => ['label' => 'Show All', 'desc' => 'Full navigation — all sections visible'],
                            'blueprints' => ['label' => 'Blueprints', 'desc' => 'Enablement Blueprints, partner blueprint tab'],
                            'labs' => ['label' => 'Workshop Labs', 'desc' => 'Lab admin, checkpoints, facilitator dashboard'],
                            'launch_programs' => ['label' => 'Launch Programs', 'desc' => 'Program milestones, partner nominations'],
                            'enablement_events' => ['label' => 'Enablement Events', 'desc' => 'Events, sessions, calendars, registration'],
                            'partner_paths' => ['label' => 'Partner Paths', 'desc' => 'Stages, gates, path config, path reviews'],
                            'goals_scorecards' => ['label' => 'Goals & Scorecards', 'desc' => 'Goals, scorecard weights, templates, benchmarks'],
                            'health_alerts' => ['label' => 'Health & Alerts', 'desc' => 'Partner health, alerts, QBR workbench'],
                            'deal_registration' => ['label' => 'Deal Registration', 'desc' => 'Deals, leads, solution lifecycle, funding'],
                            'anaplan' => ['label' => 'Anaplan Integration', 'desc' => 'Workspace sync, versions, provisioning'],
                        ];
                    @endphp

                    @foreach($focusOptions as $value => $option)
                        <label class="d-flex align-items-start gap-2 py-2 cursor-pointer {{ !$loop->last ? 'border-bottom' : '' }}" style="cursor:pointer">
                            <input type="radio" name="dev_focus_feature" value="{{ $value }}"
                                   class="form-check-input mt-1"
                                   {{ $currentFocus === $value ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <div>
                                <span class="fw-500 fs-sm">{{ $option['label'] }}</span>
                                <small class="d-block text-muted">{{ $option['desc'] }}</small>
                            </div>
                        </label>
                    @endforeach
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
