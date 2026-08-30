@extends('layouts.vertical', ['pageTitle' => 'Partner Sponsor Reports'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Partner Sponsor Reports', 'pageSubTitle1' => $session->name, 'pageSubTitle1Url' => route('workshops.reports.index', [$workshop, $session])])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <p class="text-muted fs-sm flex-grow-1 mb-0">One report per partner with their team's participation. Click into each to copy or download.</p>
                @if($rows->isNotEmpty())
                    <a href="{{ route('workshops.reports.partners-zip', [$workshop, $session]) }}" class="btn btn-primary btn-sm">Download all (.zip) ↓</a>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th class="text-end">Sent</th>
                            <th class="text-end">All 3 Done</th>
                            <th class="text-end">Completion</th>
                            <th>Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td class="fw-medium">{{ $row['partner'] }}</td>
                                <td class="text-end">{{ $row['sent'] }}</td>
                                <td class="text-end">{{ $row['all_three_done'] }}</td>
                                <td class="text-end fw-medium {{ $row['completion_rate'] >= 100 ? 'text-success' : ($row['completion_rate'] > 0 ? 'text-warning' : 'text-danger') }}">{{ $row['completion_rate'] }}%</td>
                                <td>
                                    @if($row['partner_model'])
                                        <a href="{{ route('workshops.reports.partner', [$workshop, $session, $row['partner_model']]) }}" class="btn btn-sm btn-outline-secondary">Open</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No partners with participants in this session yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
