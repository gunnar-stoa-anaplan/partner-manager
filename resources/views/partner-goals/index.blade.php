@extends('layouts.vertical', ['pageTitle' => 'Partner Goals'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Partner Goals',
        'pageSubTitle1' => 'Administration',
        'pageSubText' => 'Set annual NNACV targets for your partners'
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        FY{{ $selectedFY }} Goals
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0 me-1 text-muted">Fiscal Year:</label>
                        <select id="fy-selector" class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('partner-goals.index') }}?fy=' + this.value">
                            @foreach($fyOptions as $fy)
                                <option value="{{ $fy }}" {{ $fy === $selectedFY ? 'selected' : '' }}>
                                    FY{{ $fy }}{{ $fy === $currentFY ? ' (Current)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($partners->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="fal fa-target fa-3x text-muted opacity-50"></i></div>
                            <h5 class="text-muted">No partners assigned</h5>
                            <p class="text-muted">You don't have any partners assigned to set goals for.</p>
                        </div>
                    @else
                        <form action="{{ route('partner-goals.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="fiscal_year" value="{{ $selectedFY }}">

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped w-100 mb-0">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 200px;">Partner</th>
                                            <th style="min-width: 180px;">Sourced NNACV Target</th>
                                            <th style="min-width: 180px;">Influenced NNACV Target</th>
                                            <th style="min-width: 200px;">Notes</th>
                                            <th style="min-width: 150px;">Last Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($partners as $partner)
                                            @php
                                                $goal = $goals->get($partner->id);
                                            @endphp
                                            <tr>
                                                <td class="align-middle fw-500">
                                                    <a href="{{ route('partners.show', $partner->id) }}" class="text-decoration-none">
                                                        {{ $partner->name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number"
                                                               class="form-control form-control-sm"
                                                               name="goals[{{ $partner->id }}][sourced_nnacv_target]"
                                                               value="{{ $goal ? (int) $goal->sourced_nnacv_target : '' }}"
                                                               min="0"
                                                               step="1"
                                                               placeholder="0">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number"
                                                               class="form-control form-control-sm"
                                                               name="goals[{{ $partner->id }}][influenced_nnacv_target]"
                                                               value="{{ $goal ? (int) $goal->influenced_nnacv_target : '' }}"
                                                               min="0"
                                                               step="1"
                                                               placeholder="0">
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           class="form-control form-control-sm"
                                                           name="goals[{{ $partner->id }}][notes]"
                                                           value="{{ $goal->notes ?? '' }}"
                                                           placeholder="Optional notes">
                                                </td>
                                                <td class="align-middle text-muted small">
                                                    @if($goal && $goal->updated_at)
                                                        {{ $goal->updated_at->format('M j, Y') }}
                                                        @if($goal->updatedByUser)
                                                            <br>by {{ $goal->updatedByUser->name }}
                                                        @endif
                                                    @else
                                                        <span class="opacity-50">&mdash;</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fal fa-save me-1"></i> Save All Goals
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
