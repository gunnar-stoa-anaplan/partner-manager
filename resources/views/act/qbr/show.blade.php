@extends('layouts.vertical', ['pageTitle' => 'QBR: ' . $session->partner->name . ' — ' . $session->fiscal_quarter])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'QBR Session', 'pageSubTitle1' => 'Act', 'pageSubTitle1Url' => route('act.qbr.index'), 'pageSubTitle2' => $session->partner->name])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">This QBR session page captures everything discussed and agreed upon during the review — from performance metrics and talking points to action items and next steps. Use it to prepare before the meeting, take notes during, and track follow-through afterward so nothing falls through the cracks between quarters.</p>

{{-- Session Header --}}
<div class="card shadow-0 mb-g">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="m-0">{{ $session->partner->name }} — {{ $session->fiscal_quarter }}</h4>
                <small class="text-muted">
                    Status: <span class="badge bg-{{ $session->status === 'completed' ? 'success' : 'info' }}">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</span>
                    @if($session->scheduled_date) &middot; Scheduled: {{ $session->scheduled_date->format('M j, Y') }} @endif
                </small>
            </div>
            @if($session->status !== 'completed')
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#completeModal">Complete QBR</button>
            @endif
        </div>
    </div>
</div>

{{-- Sections --}}
@foreach($session->sections as $section)
@php
    $sectionLabels = \App\Models\QbrSection::SECTION_TYPES;
    $label = $sectionLabels[$section->section_type] ?? ucfirst(str_replace('_', ' ', $section->section_type));
    $ratingColors = ['strong' => 'success', 'satisfactory' => 'primary', 'needs_improvement' => 'warning', 'at_risk' => 'danger'];
@endphp
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">{{ $label }}</div>
        @if($section->rating)
        <span class="badge bg-{{ $ratingColors[$section->rating] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $section->rating)) }}</span>
        @endif
    </div>
    <div class="card-body">
        {{-- Auto-generated data --}}
        @if($section->auto_generated_data)
        <div class="bg-light rounded p-3 mb-3">
            <small class="text-muted d-block mb-1"><strong>System Data</strong></small>
            @foreach($section->auto_generated_data as $key => $value)
            <span class="d-inline-block me-3 fs-sm">
                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                @if(is_array($value) || is_object($value))
                    <em>{{ json_encode($value) }}</em>
                @else
                    {{ is_numeric($value) ? number_format($value, is_float($value + 0) ? 1 : 0) : ($value ?? '—') }}
                @endif
            </span>
            @endforeach
        </div>
        @endif

        {{-- Notes Form --}}
        <form action="{{ route('act.qbr.sections.update', [$session, $section]) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-5">
                    <label class="form-label fs-sm">PSM Notes</label>
                    <textarea name="psm_notes" class="form-control form-control-sm" rows="3">{{ $section->psm_notes }}</textarea>
                </div>
                <div class="col-md-5">
                    <label class="form-label fs-sm">Partner Feedback</label>
                    <textarea name="partner_feedback" class="form-control form-control-sm" rows="3">{{ $section->partner_feedback }}</textarea>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-sm">Rating</label>
                    <select name="rating" class="form-select form-select-sm">
                        <option value="">—</option>
                        @foreach(['strong', 'satisfactory', 'needs_improvement', 'at_risk'] as $r)
                        <option value="{{ $r }}" {{ $section->rating === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $r)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- Complete Modal --}}
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('act.qbr.complete', $session) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete QBR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Overall Rating</label>
                        <select name="overall_rating" class="form-select" required>
                            @foreach(['strong', 'satisfactory', 'needs_improvement', 'at_risk'] as $r)
                            <option value="{{ $r }}">{{ ucfirst(str_replace('_', ' ', $r)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Executive Summary</label>
                        <textarea name="executive_summary" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Complete QBR</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
