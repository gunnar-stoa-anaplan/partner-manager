@extends('layouts.vertical', ['pageTitle' => 'Zoom Chat Responders'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Chat Responders', 'pageSubTitle1' => 'Zoom Chat'])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <div></div>
        <a href="{{ route('zoom_chat.responders.create') }}" class="btn btn-primary btn-sm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
            New Responder
        </a>
    </div>

    <div class="card shadow-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Display Name</th>
                        <th>Meeting ID</th>
                        <th>Agent</th>
                        <th>Trigger</th>
                        <th class="text-center">Active</th>
                        <th class="text-center">Messages</th>
                        <th>Created By</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($responders as $responder)
                        <tr>
                            <td>
                                <a href="{{ route('zoom_chat.responders.show', $responder) }}" class="fw-500">
                                    {{ $responder->display_name }}
                                </a>
                                @if($responder->enablementSession)
                                    <div class="small text-muted">{{ $responder->enablementSession->event->title ?? '' }}</div>
                                @endif
                            </td>
                            <td class="font-monospace small">{{ $responder->zoom_meeting_id }}</td>
                            <td class="small">{{ \App\Models\ZoomChatResponder::allowedAgents()[$responder->agent_class] ?? class_basename($responder->agent_class) }}</td>
                            <td><code>{{ $responder->trigger_prefix }}</code></td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           {{ $responder->is_active ? 'checked' : '' }}
                                           onchange="fetch('{{ route('zoom_chat.responders.toggle', $responder) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json'}})">
                                </div>
                            </td>
                            <td class="text-center">{{ $responder->messages_count }}</td>
                            <td>{{ $responder->creator->name ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('zoom_chat.responders.show', $responder) }}" class="btn btn-xs btn-outline-info" title="View">
                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#eye"></use></svg>
                                </a>
                                <a href="{{ route('zoom_chat.responders.edit', $responder) }}" class="btn btn-xs btn-outline-primary" title="Edit">
                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit"></use></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No chat responders yet. <a href="{{ route('zoom_chat.responders.create') }}">Create one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
