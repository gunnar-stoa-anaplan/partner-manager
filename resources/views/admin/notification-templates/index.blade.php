@extends('layouts.vertical', ['pageTitle' => 'Notification Templates'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Notification Templates', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Notification Templates define the messages sent to PSMs and partners when key events happen — like blueprint assignments, certification expirations, or QBR scheduling. Customize the subject and body for each event type and channel, using template variables to include dynamic content. Toggle templates active or inactive to control which notifications are sent.</p>

{{-- Create New Template --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">Create New Template</div>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#createForm">Toggle Form</button>
    </div>
    <div class="collapse" id="createForm">
        <div class="card-body">
            <form action="{{ route('admin.notification-templates.store') }}" method="POST">
                @csrf
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <label class="form-label fs-sm">Event Type</label>
                        <select name="event_type" class="form-select form-select-sm" required>
                            <option value="">Select event...</option>
                            @foreach($eventTypes as $type)
                            <option value="{{ $type }}">{{ \App\Models\NotificationTemplate::EVENT_TYPES[$type] ?? ucfirst(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-sm">Channel</label>
                        <select name="channel" class="form-select form-select-sm" required>
                            @foreach(\App\Models\NotificationTemplate::CHANNELS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fs-sm">Subject</label>
                        <input type="text" name="subject" class="form-control form-control-sm" placeholder="Use @{{variable_name}} for dynamic content" required>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-10">
                        <label class="form-label fs-sm">Body (HTML supported, use @{{variable_name}} for dynamic content)</label>
                        <textarea name="body" class="form-control form-control-sm" rows="4" required></textarea>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Create Template</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Templates List --}}
@php
    $grouped = $templates->groupBy('event_type');
@endphp

@foreach($grouped as $eventType => $eventTemplates)
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title">{{ \App\Models\NotificationTemplate::EVENT_TYPES[$eventType] ?? ucfirst(str_replace('_', ' ', $eventType)) }}</div>
    </div>
    <div class="card-body p-0">
        @foreach($eventTemplates as $template)
        <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <form action="{{ route('admin.notification-templates.update', $template) }}" method="POST">
                @csrf @method('PUT')
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-{{ $template->channel === 'email' ? 'primary' : ($template->channel === 'in_app' ? 'info' : 'success') }}">{{ \App\Models\NotificationTemplate::CHANNELS[$template->channel] ?? $template->channel }}</span>
                        <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }} ms-1">{{ $template->is_active ? 'Active' : 'Inactive' }}</span>
                        <small class="text-muted ms-2">ID: {{ $template->id }}</small>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button type="submit" class="btn btn-outline-primary btn-sm">Save</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="fetch('{{ route('admin.notification-templates.test', $template) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}).then(r=>r.json()).then(d=>alert('Preview:\n\nSubject: '+d.rendered.subject+'\n\nBody: '+d.rendered.body.replace(/<[^>]*>/g,'')))">
                            Preview
                        </button>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-2">
                        <label class="form-label fs-sm">Channel</label>
                        <select name="channel" class="form-select form-select-sm">
                            @foreach(\App\Models\NotificationTemplate::CHANNELS as $key => $label)
                            <option value="{{ $key }}" {{ $template->channel === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-sm">Subject</label>
                        <input type="text" name="subject" class="form-control form-control-sm" value="{{ $template->subject }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fs-sm">Body</label>
                        <textarea name="body" class="form-control form-control-sm" rows="3">{{ $template->body }}</textarea>
                    </div>
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $template->is_active ? 'checked' : '' }}>
                            <label class="form-check-label fs-sm">Active</label>
                        </div>
                    </div>
                </div>
            </form>
            <div class="mt-2 text-end">
                <form action="{{ route('admin.notification-templates.destroy', $template) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this template?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach

@if($templates->isEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-body text-center py-5 text-muted">
        No notification templates configured. Create one above, or run the NotificationTemplateSeeder.
    </div>
</div>
@endif

{{-- Variable Reference --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Template Variable Reference</div></div>
    <div class="card-body">
        <p class="fs-sm text-muted">Use <code>@{{variable_name}}</code> syntax in subject and body fields. Available variables depend on the event type.</p>
        <div class="row">
            <div class="col-md-4">
                <h6 class="fs-sm">Common Variables</h6>
                <ul class="list-unstyled fs-sm">
                    <li><code>@{{partner_name}}</code> — Partner name</li>
                    <li><code>@{{psm_name}}</code> — PSM name</li>
                    <li><code>@{{date}}</code> — Current date</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="fs-sm">Blueprint / Enablement</h6>
                <ul class="list-unstyled fs-sm">
                    <li><code>@{{blueprint_name}}</code> — Blueprint name</li>
                    <li><code>@{{resource_name}}</code> — Resource name</li>
                    <li><code>@{{due_date}}</code> — Due date</li>
                    <li><code>@{{event_name}}</code> — Event name</li>
                    <li><code>@{{event_date}}</code> — Event date</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="fs-sm">Path / Task / QBR</h6>
                <ul class="list-unstyled fs-sm">
                    <li><code>@{{path_name}}</code> — Path name</li>
                    <li><code>@{{stage_name}}</code> — Stage name</li>
                    <li><code>@{{task_title}}</code> — Task title</li>
                    <li><code>@{{cert_name}}</code> — Certification name</li>
                    <li><code>@{{fiscal_quarter}}</code> — Fiscal quarter</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection
