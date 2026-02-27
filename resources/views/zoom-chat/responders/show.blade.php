@extends('layouts.vertical', ['pageTitle' => $responder->display_name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> $responder->display_name,
        'pageSubTitle1' => 'Chat Responders',
        'pageSubTitle1Url' => route('zoom_chat.responders.index'),
    ])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <div>
            <span class="badge {{ $responder->is_active ? 'bg-success' : 'bg-secondary' }}">
                {{ $responder->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('zoom_chat.responders.toggle', $responder) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm {{ $responder->is_active ? 'btn-outline-warning' : 'btn-success' }}">
                    {{ $responder->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <a href="{{ route('zoom_chat.responders.edit', $responder) }}" class="btn btn-outline-primary btn-sm">Edit</a>
            <form action="{{ route('zoom_chat.responders.destroy', $responder) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this responder?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
            </form>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card shadow-0">
                <div class="card-body text-center">
                    <div class="h3 mb-0">{{ $stats['total'] }}</div>
                    <div class="small text-muted">Total Messages</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-0">
                <div class="card-body text-center">
                    <div class="h3 mb-0 text-success">{{ $stats['responded'] }}</div>
                    <div class="small text-muted">Responded</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-0">
                <div class="card-body text-center">
                    <div class="h3 mb-0 text-danger">{{ $stats['failed'] }}</div>
                    <div class="small text-muted">Failed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-0">
                <div class="card-body text-center">
                    <div class="h3 mb-0">{{ number_format($stats['avg_time'] / 1000, 1) }}s</div>
                    <div class="small text-muted">Avg Response Time</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Configuration --}}
            <div class="card shadow-0 mb-3">
                <div class="card-header fw-500">Configuration</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-4 text-muted">Meeting ID</dt>
                        <dd class="col-8 font-monospace">{{ $responder->zoom_meeting_id }}</dd>
                        <dt class="col-4 text-muted">Agent</dt>
                        <dd class="col-8">{{ \App\Models\ZoomChatResponder::allowedAgents()[$responder->agent_class] ?? class_basename($responder->agent_class) }}</dd>
                        <dt class="col-4 text-muted">Trigger Prefix</dt>
                        <dd class="col-8"><code>{{ $responder->trigger_prefix }}</code></dd>
                        <dt class="col-4 text-muted">Max Tokens</dt>
                        <dd class="col-8">{{ $responder->max_response_tokens }}</dd>
                        <dt class="col-4 text-muted">Rate Limit</dt>
                        <dd class="col-8">{{ $responder->rate_limit_per_minute }}/min</dd>
                        @if($responder->enablementSession)
                            <dt class="col-4 text-muted">Linked Session</dt>
                            <dd class="col-8">{{ $responder->enablementSession->event->title ?? '' }} — {{ $responder->enablementSession->start_at->format('M d, Y H:i') }}</dd>
                        @endif
                        <dt class="col-4 text-muted">Created By</dt>
                        <dd class="col-8">{{ $responder->creator->name ?? '—' }}</dd>
                        <dt class="col-4 text-muted">Created</dt>
                        <dd class="col-8">{{ $responder->created_at->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Recent Messages --}}
            <div class="card shadow-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-500">Recent Messages</span>
                    <a href="{{ route('zoom_chat.messages.index', ['responder_id' => $responder->id]) }}" class="btn btn-xs btn-outline-info">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($messages->isEmpty())
                        <div class="text-center text-muted py-4">No messages yet.</div>
                    @else
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Sender</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($messages as $msg)
                                    <tr>
                                        <td class="small text-muted text-nowrap">{{ $msg->created_at->format('H:i:s') }}</td>
                                        <td class="small">{{ $msg->sender_name }}</td>
                                        <td class="small">{{ Str::limit($msg->message_text, 60) }}</td>
                                        <td>
                                            @php
                                                $colors = ['received' => 'secondary', 'processing' => 'info', 'responded' => 'success', 'failed' => 'danger', 'skipped' => 'warning'];
                                            @endphp
                                            <span class="badge bg-{{ $colors[$msg->status->value] ?? 'secondary' }}">{{ $msg->status->value }}</span>
                                        </td>
                                        <td class="small text-muted">{{ $msg->processing_time_ms ? number_format($msg->processing_time_ms / 1000, 1) . 's' : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
