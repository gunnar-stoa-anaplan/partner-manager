@extends('layouts.vertical', ['pageTitle' => 'Edit Chat Responder'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Edit Chat Responder',
        'pageSubTitle1' => 'Chat Responders',
        'pageSubTitle1Url' => route('zoom_chat.responders.index'),
    ])
@endsection

@section('content')
    <div class="row mt-3">
        <div class="col-lg-8">
            <div class="card shadow-0">
                <div class="card-body">
                    <form action="{{ route('zoom_chat.responders.update', $responder) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="display_name" class="form-label">Display Name</label>
                                <input type="text" class="form-control" id="display_name" name="display_name" value="{{ old('display_name', $responder->display_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="zoom_meeting_id" class="form-label">Zoom Meeting ID</label>
                                <input type="text" class="form-control font-monospace" id="zoom_meeting_id" name="zoom_meeting_id" value="{{ old('zoom_meeting_id', $responder->zoom_meeting_id) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="agent_class" class="form-label">AI Agent</label>
                                <select class="form-select" id="agent_class" name="agent_class" required>
                                    @foreach($agents as $class => $label)
                                        <option value="{{ $class }}" {{ old('agent_class', $responder->agent_class) === $class ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="enablement_session_id" class="form-label">Linked Session (optional)</label>
                                <select class="form-select" id="enablement_session_id" name="enablement_session_id">
                                    <option value="">— None —</option>
                                    @foreach($sessions as $session)
                                        <option value="{{ $session->id }}" {{ old('enablement_session_id', $responder->enablement_session_id) == $session->id ? 'selected' : '' }}>
                                            {{ $session->event->title ?? 'Session' }} — {{ $session->start_at->format('M d, Y H:i') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="trigger_prefix" class="form-label">Trigger Prefix</label>
                                <input type="text" class="form-control font-monospace" id="trigger_prefix" name="trigger_prefix" value="{{ old('trigger_prefix', $responder->trigger_prefix) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="max_response_tokens" class="form-label">Max Response Tokens</label>
                                <input type="number" class="form-control" id="max_response_tokens" name="max_response_tokens" value="{{ old('max_response_tokens', $responder->max_response_tokens) }}" min="64" max="4096" required>
                            </div>
                            <div class="col-md-4">
                                <label for="rate_limit_per_minute" class="form-label">Rate Limit (per min)</label>
                                <input type="number" class="form-control" id="rate_limit_per_minute" name="rate_limit_per_minute" value="{{ old('rate_limit_per_minute', $responder->rate_limit_per_minute) }}" min="1" max="60" required>
                            </div>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                            <a href="{{ route('zoom_chat.responders.show', $responder) }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
