@extends('portal.layout')

@section('title', $session->name.' - Progress')

@section('content')
<style>
    .checkpoint-dot { display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 50%; }
    .checkpoint-dot.done { background: #15803d; }
    .checkpoint-dot.partial { background: #d97706; }
    .checkpoint-dot.todo { background: #cbd5e1; }
    .checkpoint-dot.failed { background: #dc2626; }
</style>
<div class="container py-4">
    <h4 class="mb-1">{{ $session->workshop->name }}</h4>
    <p class="text-muted mb-4">{{ $session->name }} &middot; progress for your organization's participants.</p>

    <div class="mb-3 fs-sm text-muted">
        <span class="checkpoint-dot done me-1"></span> Done
        <span class="checkpoint-dot partial ms-3 me-1"></span> In progress
        <span class="checkpoint-dot todo ms-3 me-1"></span> Not started
        <span class="checkpoint-dot failed ms-3 me-1"></span> Failed
    </div>

    <div class="card portal-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Participant</th>
                        @foreach($checkpoints as $checkpoint)
                            <th class="text-center fs-xs" title="{{ $checkpoint['label'] }}">{{ $checkpoint['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($progression as $row)
                        <tr>
                            <td>{{ $row['contact']->full_name }}</td>
                            @foreach($checkpoints as $checkpoint)
                                <td class="text-center"><span class="checkpoint-dot {{ $row['statuses'][$checkpoint['key']] ?? 'todo' }}"></span></td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($checkpoints) + 1 }}" class="text-center text-muted py-4">No participants from your organization yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('portal.workshops.show', $session) }}" class="btn btn-outline-secondary btn-sm mt-3">&laquo; Back to session</a>
</div>
@endsection
