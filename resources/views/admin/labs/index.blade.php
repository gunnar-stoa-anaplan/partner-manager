@extends('layouts.vertical', ['pageTitle' => 'Workshop Labs'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Workshop Labs', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubText' => 'Create and manage workshop lab verification'])
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Labs</div>
                    <a href="{{ route('labs.create') }}" class="btn btn-primary btn-sm">
                        <i class="fal fa-plus me-1"></i> Create Lab
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="labs-table" class="table table-bordered table-hover table-striped w-100">
                            <thead>
                                <tr>
                                    <th>Lab Name</th>
                                    <th>Session</th>
                                    <th>Status</th>
                                    <th class="text-center">Checkpoints</th>
                                    <th class="text-center">Participants</th>
                                    <th>Golden Model</th>
                                    <th>Created</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($labs as $lab)
                                <tr>
                                    <td>
                                        <a href="{{ route('labs.show', $lab) }}" class="fw-bold text-decoration-none">{{ $lab->name }}</a>
                                    </td>
                                    <td>
                                        @if($lab->session)
                                            {{ $lab->session->display_title }}
                                            <br><small class="text-muted">{{ $lab->session->start_at->format('M j, Y') }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ \App\Models\Lab::STATUS_COLORS[$lab->status] ?? '#868e96' }}">
                                            {{ \App\Models\Lab::STATUSES[$lab->status] ?? $lab->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $lab->checkpoints->count() }}</td>
                                    <td class="text-center">
                                        @php $stats = $lab->completionStats(); @endphp
                                        <span class="badge bg-primary bg-opacity-25 text-primary">{{ $stats['total'] }}</span>
                                        @if($stats['completed'] > 0)
                                            <span class="badge bg-success bg-opacity-25 text-success">{{ $stats['completed'] }} done</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lab->goldenModel)
                                            <small>{{ Str::limit($lab->goldenModel->name, 30) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $lab->created_at->format('M j, Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('labs.show', $lab) }}" class="btn btn-xs btn-outline-primary" title="View">
                                            <i class="fal fa-eye"></i>
                                        </a>
                                        @if($lab->isPublished() && $lab->session)
                                        <a href="{{ route('sessions.lab-dashboard', $lab->session) }}" class="btn btn-xs btn-outline-info" title="Facilitator Dashboard">
                                            <i class="fal fa-chart-bar"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#labs-table').dataTable({
        responsive: true,
        order: [[6, 'desc']]
    });
});
</script>
@endsection
