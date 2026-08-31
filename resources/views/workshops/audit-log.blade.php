@extends('layouts.vertical', ['pageTitle' => $workshop->name.' - Audit Log'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Audit Log', 'pageSubTitle1' => $workshop->name, 'pageSubTitle1Url' => route('workshops.show', $workshop), 'pageSubText' => 'Every recorded change to this workshop\'s pages, survey templates, and tenant credential pool.'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Who</th>
                            <th>Action</th>
                            <th>What</th>
                            <th>Changed fields</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            @php
                                $typeLabel = str($entry->auditable_type)->afterLast('\\');
                                $fields = collect(array_keys($entry->after ?? $entry->before ?? []))
                                    ->reject(fn ($f) => in_array($f, ['created_at', 'updated_at']))
                                    ->values();
                            @endphp
                            <tr>
                                <td class="text-muted fs-sm">{{ $entry->created_at?->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $entry->user?->name ?? 'System' }}</td>
                                <td>
                                    <span class="badge {{ $entry->action === 'created' ? 'bg-success' : ($entry->action === 'deleted' ? 'bg-danger' : 'bg-secondary') }} bg-opacity-75">{{ ucfirst($entry->action) }}</span>
                                </td>
                                <td>{{ $typeLabel }} #{{ $entry->auditable_id }}</td>
                                <td class="fs-sm">{{ $fields->implode(', ') ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No recorded changes yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                {{ $entries->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
