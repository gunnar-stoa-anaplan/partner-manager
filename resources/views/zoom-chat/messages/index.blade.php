@extends('layouts.vertical', ['pageTitle' => 'Chat Message History'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Message History', 'pageSubTitle1' => 'Zoom Chat'])
@endsection

@section('content')
    <div class="row mt-3">
        <div class="col-xl-12">
            {{-- Filters --}}
            <form method="GET" class="d-flex gap-2 mb-3">
                <select name="responder_id" class="form-select form-select-sm" style="max-width:250px" onchange="this.form.submit()">
                    <option value="">All Responders</option>
                    @foreach($responders as $id => $name)
                        <option value="{{ $id }}" {{ request('responder_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select form-select-sm" style="max-width:180px" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(\App\Enums\ZoomChatMessageStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ ucfirst($status->value) }}</option>
                    @endforeach
                </select>
            </form>

            <div class="card shadow-0">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Responder</th>
                                <th>Sender</th>
                                <th>Message</th>
                                <th>Response</th>
                                <th>Status</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $msg)
                                <tr>
                                    <td class="small text-muted text-nowrap">{{ $msg->created_at->format('M d H:i:s') }}</td>
                                    <td class="small">{{ $msg->responder->display_name ?? '—' }}</td>
                                    <td class="small">{{ $msg->sender_name }}</td>
                                    <td class="small">
                                        <a href="{{ route('zoom_chat.messages.show', $msg) }}">{{ Str::limit($msg->message_text, 50) }}</a>
                                    </td>
                                    <td class="small text-muted">{{ Str::limit($msg->response_text ?? '—', 50) }}</td>
                                    <td>
                                        @php
                                            $colors = ['received' => 'secondary', 'processing' => 'info', 'responded' => 'success', 'failed' => 'danger', 'skipped' => 'warning'];
                                        @endphp
                                        <span class="badge bg-{{ $colors[$msg->status->value] ?? 'secondary' }}">{{ $msg->status->value }}</span>
                                    </td>
                                    <td class="small text-muted">{{ $msg->processing_time_ms ? number_format($msg->processing_time_ms / 1000, 1) . 's' : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No messages found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                {{ $messages->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
