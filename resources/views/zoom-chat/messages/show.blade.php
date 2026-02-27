@extends('layouts.vertical', ['pageTitle' => 'Message Detail'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Message Detail',
        'pageSubTitle1' => 'Message History',
        'pageSubTitle1Url' => route('zoom_chat.messages.index'),
    ])
@endsection

@section('content')
    <div class="row mt-3">
        <div class="col-lg-8">
            <div class="card shadow-0 mb-3">
                <div class="card-header fw-500">Original Message</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>{{ $message->sender_name }}</strong>
                        <span class="text-muted small">{{ $message->created_at->format('M d, Y H:i:s') }}</span>
                    </div>
                    <p class="mb-0">{{ $message->message_text }}</p>
                </div>
            </div>

            @if($message->response_text)
                <div class="card shadow-0 mb-3">
                    <div class="card-header fw-500">AI Response</div>
                    <div class="card-body">
                        <p class="mb-0">{!! nl2br(e($message->response_text)) !!}</p>
                    </div>
                </div>
            @endif

            @if($message->error_message)
                <div class="card shadow-0 mb-3 border-danger">
                    <div class="card-header fw-500 text-danger">Error</div>
                    <div class="card-body">
                        <p class="mb-0 text-danger">{{ $message->error_message }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-0">
                <div class="card-header fw-500">Details</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Status</dt>
                        <dd class="col-7">
                            @php
                                $colors = ['received' => 'secondary', 'processing' => 'info', 'responded' => 'success', 'failed' => 'danger', 'skipped' => 'warning'];
                            @endphp
                            <span class="badge bg-{{ $colors[$message->status->value] ?? 'secondary' }}">{{ $message->status->value }}</span>
                        </dd>
                        <dt class="col-5 text-muted">Responder</dt>
                        <dd class="col-7">
                            <a href="{{ route('zoom_chat.responders.show', $message->responder) }}">{{ $message->responder->display_name }}</a>
                        </dd>
                        <dt class="col-5 text-muted">Agent</dt>
                        <dd class="col-7">{{ class_basename($message->agent_class) }}</dd>
                        <dt class="col-5 text-muted">Sender Email</dt>
                        <dd class="col-7">{{ $message->sender_email ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Zoom Message ID</dt>
                        <dd class="col-7 font-monospace small">{{ $message->zoom_message_id }}</dd>
                        @if($message->tokens_used)
                            <dt class="col-5 text-muted">Tokens Used</dt>
                            <dd class="col-7">{{ number_format($message->tokens_used) }}</dd>
                        @endif
                        @if($message->processing_time_ms)
                            <dt class="col-5 text-muted">Processing Time</dt>
                            <dd class="col-7">{{ number_format($message->processing_time_ms / 1000, 2) }}s</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
