@extends('layouts.vertical', ['pageTitle' => 'Import #' . $import->id])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Import #' . $import->id, 'pageSubTitle1' => 'Data Imports'])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">Import Details #{{ $import->id }}</div>
                <a href="{{ route('imports.index') }}" class="btn btn-secondary btn-sm">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#arrow-left"></use></svg>
                    Back to List
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Import Type</th>
                                <td><span class="badge bg-info">{{ ucfirst($import->type) }}</span></td>
                            </tr>
                            <tr>
                                <th>File Name</th>
                                <td>{{ $import->file_name }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($import->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($import->status === 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @elseif($import->status === 'processing')
                                        <span class="badge bg-warning text-dark">Processing</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Imported By</th>
                                <td>{{ $import->user->name ?? 'System' }}</td>
                            </tr>
                            <tr>
                                <th>Started At</th>
                                <td>{{ $import->started_at?->format('M d, Y H:i:s') ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Completed At</th>
                                <td>{{ $import->completed_at?->format('M d, Y H:i:s') ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body text-center py-3">
                                        <h2 class="text-primary mb-0">{{ number_format($import->total_rows) }}</h2>
                                        <small class="text-muted">Total Rows</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="card border border-success">
                                    <div class="card-body text-center py-3">
                                        <h2 class="text-success mb-0">{{ number_format($import->successful_rows) }}</h2>
                                        <small class="text-muted">Successful</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="card border border-danger">
                                    <div class="card-body text-center py-3">
                                        <h2 class="text-danger mb-0">{{ number_format($import->failed_rows) }}</h2>
                                        <small class="text-muted">Failed</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="card border border-info">
                                    <div class="card-body text-center py-3">
                                        <h2 class="text-info mb-0">{{ number_format($import->partners_created) }}</h2>
                                        <small class="text-muted">Partners Created</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($import->summary && is_array($import->summary) && count($import->summary) > 0)
                    <div class="alert alert-info mt-3">
                        <strong>Summary Statistics</strong>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <strong>Total Unique Partners:</strong> {{ $import->summary['total_unique_partners'] ?? 0 }}
                            </div>
                            <div class="col-md-4">
                                <strong>From Partner Plan HQ:</strong> {{ $import->summary['partner_sources']['partner_plan_hq'] ?? 0 }}
                            </div>
                            <div class="col-md-4">
                                <strong>From Final Impl. Partner:</strong> {{ $import->summary['partner_sources']['final_implementation_partner'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                @endif

                @if($import->failed_rows > 0)
                    <div class="alert alert-danger mt-3">
                        <strong>Import Errors ({{ number_format($import->failed_rows) }} Failed Rows)</strong>
                        @if($import->error_log)
                            <pre class="mt-2 mb-0 p-3 rounded" style="max-height: 400px; overflow-y: auto; background-color: var(--bs-dark); color: var(--bs-light); font-size: 13px;">{{ $import->error_log }}</pre>
                        @else
                            <p class="mt-2 mb-0">Error details were not captured for this import (typical for seeded data).</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if($import->type === 'opportunities' && $import->opportunities->count() > 0)
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Imported Opportunities ({{ $import->opportunities->count() }})</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100">
                        <thead>
                            <tr>
                                <th>Opportunity Name</th>
                                <th>Account</th>
                                <th>Partner</th>
                                <th>Stage</th>
                                <th>Amount</th>
                                <th>Close Date</th>
                                <th>Owner</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($import->opportunities as $opportunity)
                            <tr>
                                <td>{{ $opportunity->opportunity_name }}</td>
                                <td>{{ $opportunity->account_name ?? 'N/A' }}</td>
                                <td>
                                    @if($opportunity->partner)
                                        <a href="{{ route('partners.show', $opportunity->partner->id) }}">{{ $opportunity->partner->name }}</a>
                                    @else
                                        <span class="text-muted">No Partner</span>
                                    @endif
                                </td>
                                <td>{{ $opportunity->stage ?? 'N/A' }}</td>
                                <td>{{ $opportunity->amount ? '$' . number_format($opportunity->amount, 2) : 'N/A' }}</td>
                                <td>{{ $opportunity->close_date?->format('M d, Y') ?? 'N/A' }}</td>
                                <td>{{ $opportunity->owner ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
