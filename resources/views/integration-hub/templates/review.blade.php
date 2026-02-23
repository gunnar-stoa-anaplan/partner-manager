@extends('layouts.vertical', ['pageTitle' => 'Review Template'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Review: ' . $template->name,
        'pageSubTitle1' => 'Integration Hub',
        'pageSubTitle1Url' => route('integration-hub.dashboard'),
        'pageSubTitle2' => 'Templates',
        'pageSubTitle2Url' => route('integration-hub.templates.index'),
    ])
@endsection

@php
    $newFields = collect($diff)->where('status', 'new');
    $changedFields = collect($diff)->where('status', 'changed');
    $matchedFields = collect($diff)->where('status', 'matched');
    $customFields = collect($diff)->where('status', 'custom');
    $hasActionableFields = $newFields->isNotEmpty() || $changedFields->isNotEmpty();
@endphp

@section('content')

    <div class="row mb-3">
        <div class="col-xl-8">
            <div class="d-flex align-items-center gap-3">
                <h5 class="mb-0">
                    <span class="badge bg-info">{{ $template->target_model }}</span>
                    {{ $template->name }}
                    <span class="text-muted fw-normal">v{{ $template->version }}</span>
                </h5>
                <svg class="sa-icon sa-icon-sm text-muted"><use href="/icons/sprite.svg#arrow-right"></use></svg>
                <h5 class="mb-0">{{ $connection->name }}</h5>
            </div>
        </div>
        <div class="col-xl-4 text-end">
            @if($matchedFields->isNotEmpty())
                <span class="badge bg-success">{{ $matchedFields->count() }} Matched</span>
            @endif
            @if($newFields->isNotEmpty())
                <span class="badge bg-primary">{{ $newFields->count() }} New</span>
            @endif
            @if($changedFields->isNotEmpty())
                <span class="badge bg-warning text-dark">{{ $changedFields->count() }} Changed</span>
            @endif
            @if($customFields->isNotEmpty())
                <span class="badge bg-secondary">{{ $customFields->count() }} Custom</span>
            @endif
        </div>
    </div>

    @if($isUpdate && $template->changelog)
        <div class="alert alert-info">
            <strong>Changelog (v{{ $appliedVersion }} &rarr; v{{ $template->version }}):</strong>
            {{ $template->changelog }}
        </div>
    @endif

    <form method="POST" action="{{ route('integration-hub.templates.apply', $template) }}">
        @csrf
        <input type="hidden" name="connection_id" value="{{ $connection->id }}">

        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">Field Mapping Diff</div>
                @if($hasActionableFields)
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-xs" id="btn-select-new">Select All New</button>
                        @if($changedFields->isNotEmpty())
                            <button type="button" class="btn btn-outline-warning btn-xs" id="btn-select-changed">Select All Changed</button>
                        @endif
                    </div>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px"></th>
                                <th>Status</th>
                                <th>Target Field</th>
                                <th>Source Field</th>
                                <th>Transform</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- New fields --}}
                            @foreach($newFields as $item)
                            <tr class="table-primary bg-opacity-10">
                                <td class="text-center">
                                    <input type="checkbox" name="fields[]" value="{{ $item['key'] }}" class="form-check-input cb-new" checked>
                                </td>
                                <td><span class="badge bg-primary">New</span></td>
                                <td><code>{{ $item['template']['key'] }}</code></td>
                                <td><code>{{ $item['template']['source_field'] }}</code></td>
                                <td>{{ $item['template']['transform'] ?? '—' }}</td>
                                <td>
                                    @if($item['template']['is_required'] ?? false)
                                        <span class="badge bg-warning text-dark">Yes</span>
                                    @else
                                        <span class="text-muted">No</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $item['template']['description'] ?? '' }}</td>
                            </tr>
                            @endforeach

                            {{-- Changed fields --}}
                            @foreach($changedFields as $item)
                            <tr class="table-warning bg-opacity-10">
                                <td class="text-center">
                                    <input type="checkbox" name="fields[]" value="{{ $item['key'] }}" class="form-check-input cb-changed">
                                </td>
                                <td><span class="badge bg-warning text-dark">Changed</span></td>
                                <td><code>{{ $item['template']['key'] }}</code></td>
                                <td>
                                    <code>{{ $item['mapping']->source_field }}</code>
                                </td>
                                <td>
                                    @if(($item['mapping']->transform ?: null) !== ($item['template']['transform'] ?? null))
                                        <del class="text-muted">{{ $item['mapping']->transform ?: '—' }}</del>
                                        <svg class="sa-icon sa-icon-xs"><use href="/icons/sprite.svg#arrow-right"></use></svg>
                                        <strong>{{ $item['template']['transform'] ?? '—' }}</strong>
                                    @else
                                        {{ $item['template']['transform'] ?? '—' }}
                                    @endif
                                </td>
                                <td>
                                    @if((bool) $item['mapping']->is_required !== (bool) ($item['template']['is_required'] ?? false))
                                        <del class="text-muted">{{ $item['mapping']->is_required ? 'Yes' : 'No' }}</del>
                                        <svg class="sa-icon sa-icon-xs"><use href="/icons/sprite.svg#arrow-right"></use></svg>
                                        <strong>{{ ($item['template']['is_required'] ?? false) ? 'Yes' : 'No' }}</strong>
                                    @else
                                        {{ ($item['template']['is_required'] ?? false) ? 'Yes' : 'No' }}
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $item['template']['description'] ?? '' }}</td>
                            </tr>
                            @endforeach

                            {{-- Matched fields --}}
                            @foreach($matchedFields as $item)
                            <tr class="table-success bg-opacity-10">
                                <td class="text-center text-muted">
                                    <svg class="sa-icon sa-icon-sm text-success"><use href="/icons/sprite.svg#check"></use></svg>
                                </td>
                                <td><span class="badge bg-success">Matched</span></td>
                                <td><code>{{ $item['template']['key'] }}</code></td>
                                <td><code>{{ $item['mapping']->source_field }}</code></td>
                                <td>{{ $item['mapping']->transform ?: '—' }}</td>
                                <td>{{ $item['mapping']->is_required ? 'Yes' : 'No' }}</td>
                                <td class="text-muted small">{{ $item['template']['description'] ?? '' }}</td>
                            </tr>
                            @endforeach

                            {{-- Custom user mappings --}}
                            @foreach($customFields as $item)
                            <tr class="bg-light">
                                <td class="text-center text-muted">
                                    <svg class="sa-icon sa-icon-sm text-muted"><use href="/icons/sprite.svg#user"></use></svg>
                                </td>
                                <td><span class="badge bg-secondary">Custom</span></td>
                                <td><code>{{ $item['mapping']->target_field }}</code></td>
                                <td><code>{{ $item['mapping']->source_field }}</code></td>
                                <td>{{ $item['mapping']->transform ?: '—' }}</td>
                                <td>{{ $item['mapping']->is_required ? 'Yes' : 'No' }}</td>
                                <td class="text-muted small fst-italic">User-defined mapping (preserved)</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @error('fields')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <div class="d-flex justify-content-between">
            <a href="{{ route('integration-hub.templates.index') }}" class="btn btn-light">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#arrow-left"></use></svg>
                Back to Templates
            </a>

            @if($hasActionableFields)
                <button type="submit" class="btn btn-primary">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#check"></use></svg>
                    Apply Selected Fields
                </button>
            @else
                <span class="btn btn-success disabled">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#check-circle"></use></svg>
                    All fields are up to date
                </span>
            @endif
        </div>
    </form>

@endsection

@section('scripts')
<script>
document.getElementById('btn-select-new')?.addEventListener('click', function() {
    document.querySelectorAll('.cb-new').forEach(cb => cb.checked = true);
});
document.getElementById('btn-select-changed')?.addEventListener('click', function() {
    document.querySelectorAll('.cb-changed').forEach(cb => cb.checked = true);
});
</script>
@endsection
