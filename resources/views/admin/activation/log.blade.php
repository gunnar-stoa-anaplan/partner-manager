@extends('layouts.vertical', ['pageTitle' => 'Activation Log'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Activation Log',
        'pageSubTitle1' => 'Feature Activation',
        'pageSubTitle1Url' => route('admin.activation.paths'),
        'pageSubText' => 'Recent feature flag changes stored by Pennant',
    ])
@endsection

@section('content')

    <div class="card shadow-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped m-0">
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <th>Scope</th>
                            <th>Value</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                        <tr>
                            <td><code>{{ $entry->name }}</code></td>
                            <td class="text-muted small">{{ $entry->scope ?? 'null (global)' }}</td>
                            <td>
                                @if($entry->value === 'true' || $entry->value === '1')
                                    <span class="badge bg-success">Active</span>
                                @elseif($entry->value === 'false' || $entry->value === '0' || $entry->value === '')
                                    <span class="badge bg-secondary">Inactive</span>
                                @else
                                    <code class="small">{{ $entry->value }}</code>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $entry->updated_at }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No feature flag entries yet. Flags are created when first checked or toggled.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
