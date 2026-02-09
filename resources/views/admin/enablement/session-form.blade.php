@extends('layouts.vertical', ['pageTitle' => $session ? 'Edit Session' : 'Add Session'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> $session ? 'Edit Session' : 'Add Session',
        'pageSubTitle1' => $event->title,
        'pageSubText' => $session ? 'Update session details' : 'Schedule a new delivery session'
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-8 offset-xl-2">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">
                        {{ $session ? 'Edit Session' : 'New Session' }} for: {{ $event->title }}
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ $session ? route('enablement-sessions.update', $session->id) : route('enablement-events.sessions.store', $event->id) }}" method="POST">
                        @csrf
                        @if($session)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="title_override">Session Title Override</label>
                            <input type="text" class="form-control" id="title_override" name="title_override"
                                   value="{{ old('title_override', $session->title_override ?? '') }}"
                                   placeholder="Leave blank to use event title: {{ $event->title }}">
                            <div class="form-text">Override the event title for this specific session if needed.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label" for="start_at">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="start_at" name="start_at"
                                       value="{{ old('start_at', $session ? $session->start_at->format('Y-m-d\\TH:i') : '') }}" required>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label" for="end_at">End Date & Time</label>
                                <input type="datetime-local" class="form-control" id="end_at" name="end_at"
                                       value="{{ old('end_at', $session && $session->end_at ? $session->end_at->format('Y-m-d\\TH:i') : '') }}">
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end pb-2">
                                <div class="form-check">
                                    <input type="hidden" name="all_day" value="0">
                                    <input type="checkbox" class="form-check-input" id="all_day" name="all_day" value="1"
                                           {{ old('all_day', $session->all_day ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="all_day">All Day</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="timezone">Timezone</label>
                                <select class="form-select" id="timezone" name="timezone">
                                    @php
                                        $timezones = ['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Asia/Tokyo', 'Asia/Shanghai', 'Asia/Mumbai', 'Australia/Sydney'];
                                    @endphp
                                    @foreach($timezones as $tz)
                                        <option value="{{ $tz }}" {{ old('timezone', $session->timezone ?? 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="registration_deadline">Registration Deadline</label>
                                <input type="datetime-local" class="form-control" id="registration_deadline" name="registration_deadline"
                                       value="{{ old('registration_deadline', $session && $session->registration_deadline ? $session->registration_deadline->format('Y-m-d\\TH:i') : '') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="location">Location</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       value="{{ old('location', $session->location ?? '') }}" placeholder="e.g. Virtual, London Office, etc.">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="meeting_url">Meeting URL</label>
                                <input type="url" class="form-control" id="meeting_url" name="meeting_url"
                                       value="{{ old('meeting_url', $session->meeting_url ?? '') }}" placeholder="https://zoom.us/...">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="instructor">Instructor</label>
                                <input type="text" class="form-control" id="instructor" name="instructor"
                                       value="{{ old('instructor', $session->instructor ?? '') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="geography">Geography</label>
                                <input type="text" class="form-control" id="geography" name="geography"
                                       value="{{ old('geography', $session->geography ?? '') }}" placeholder="e.g. EMEA, NA, APAC">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="region">Region</label>
                                <input type="text" class="form-control" id="region" name="region"
                                       value="{{ old('region', $session->region ?? '') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="max_participants">Max Participants</label>
                                <input type="number" class="form-control" id="max_participants" name="max_participants"
                                       value="{{ old('max_participants', $session->max_participants ?? '') }}" min="1">
                                <div class="form-text">Leave blank to use event default ({{ $event->max_participants ?? 'unlimited' }}).</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="status">Status</label>
                                <select class="form-select" id="status" name="status">
                                    @foreach(\App\Models\EnablementSession::STATUSES as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', $session->status ?? 'scheduled') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="notes">Admin Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $session->notes ?? '') }}</textarea>
                        </div>

                        @if($session && in_array($session->status, ['completed', 'in_progress']))
                            <div class="mb-3">
                                <label class="form-label" for="summary">Session Summary</label>
                                <textarea class="form-control" id="summary" name="summary" rows="3" placeholder="Post-session summary and key takeaways...">{{ old('summary', $session->summary ?? '') }}</textarea>
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('enablement-events.show', $event->id) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#save"></use></svg>
                                {{ $session ? 'Save Changes' : 'Create Session' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
