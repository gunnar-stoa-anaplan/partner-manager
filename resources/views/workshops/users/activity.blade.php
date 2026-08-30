@extends('layouts.vertical', ['pageTitle' => $contact->full_name.' - Activity'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $contact->full_name.' — Activity', 'pageSubTitle1' => 'Workshop Users', 'pageSubTitle1Url' => route('workshops.users.index'), 'pageSubText' => $contact->email])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Activity</th>
                            <th>Session</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td class="text-nowrap">{{ $event['at']->format('Y-m-d H:i') }}</td>
                                <td>{{ $event['label'] }}</td>
                                <td class="text-muted fs-sm">{{ $event['context'] ?? '—' }}</td>
                                <td>
                                    @if($event['status'] === 'Passed')
                                        <span class="badge bg-success">Passed</span>
                                    @elseif($event['status'])
                                        <span class="badge bg-light text-dark">{{ $event['status'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No activity recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
