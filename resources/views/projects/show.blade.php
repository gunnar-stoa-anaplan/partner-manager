@extends('layouts.vertical', ['pageTitle' => $project->name])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $project->name, 'pageSubTitle1' => 'Engage', 'pageSubTitle1Url' => route('welcome'), 'pageSubTitle2' => 'Project Health', 'pageSubTitle2Url' => route('projects.index'), 'pageSubText' => $project->partner->name ?? ''])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">This is the project detail view showing delivery status, milestones, and risk indicators. Use it to track project health, update milestone progress, log risks, and coordinate with the partner on upcoming deliverables. Changes made here are reflected in the partner's overall project health dashboard.</p>

    {{-- Project Info Card --}}
    <div class="row mb-g">
        <div class="col-lg-12">
            <div class="card shadow-0">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        {{ $project->name }}
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('partners.show', $project->partner_id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fal fa-briefcase me-1"></i> {{ $project->partner->name ?? 'Partner' }}
                        </a>
                        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fal fa-arrow-left me-1"></i> All Projects
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <small class="text-muted d-block">Client</small>
                            <strong>{{ $project->account->name ?? '-' }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Status</small>
                            @php
                                $statusColors = [
                                    'Draft' => 'secondary',
                                    'In Progress' => 'primary',
                                    'Completed' => 'success',
                                    'On Hold' => 'warning',
                                    'planning' => 'secondary',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$project->status] ?? 'secondary' }}">
                                {{ ucfirst($project->status) }}
                            </span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">ACV</small>
                            <strong>{{ $project->acv ? '$' . number_format($project->acv, 0) : '-' }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Kickoff</small>
                            <strong>{{ $project->start_date ? $project->start_date->format('M j, Y') : '-' }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">End Date</small>
                            <strong>{{ $project->end_date ? $project->end_date->format('M j, Y') : '-' }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Sprint Progress</small>
                            <strong>
                                @if($project->total_sprints > 0)
                                    {{ $project->current_sprint }} / {{ $project->total_sprints }}
                                @else
                                    -
                                @endif
                            </strong>
                        </div>
                    </div>

                    {{-- Completion Progress Bar --}}
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Overall Completion</small>
                            <small class="fw-500">{{ $project->percent_complete }}%</small>
                        </div>
                        @php
                            $pct = $project->percent_complete;
                            $barColor = $pct >= 75 ? 'success' : ($pct >= 40 ? 'primary' : ($pct >= 15 ? 'warning' : 'danger'));
                        @endphp
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-{{ $barColor }}" role="progressbar"
                                 style="width: {{ $pct }}%"
                                 aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Key Roles --}}
        <div class="col-lg-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Key Roles</div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        {{-- CAL --}}
                        <div class="list-group-item d-flex align-items-center">
                            <span class="d-inline-block rounded-circle bg-primary-100 text-primary text-center fw-500 me-3"
                                  style="width: 36px; height: 36px; line-height: 36px; font-size: 13px; flex-shrink: 0;">
                                @if($project->cal)
                                    {{ strtoupper(substr($project->cal->first_name, 0, 1)) }}{{ strtoupper(substr($project->cal->last_name, 0, 1)) }}
                                @else
                                    <i class="fal fa-user"></i>
                                @endif
                            </span>
                            <div>
                                <small class="text-muted d-block">Client Account Lead (CAL)</small>
                                @if($project->cal)
                                    <strong>{{ $project->cal->first_name }} {{ $project->cal->last_name }}</strong>
                                    @if($project->cal->title)
                                        <small class="text-muted d-block">{{ $project->cal->title }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </div>
                        </div>

                        {{-- TAL --}}
                        <div class="list-group-item d-flex align-items-center">
                            <span class="d-inline-block rounded-circle bg-info-100 text-info text-center fw-500 me-3"
                                  style="width: 36px; height: 36px; line-height: 36px; font-size: 13px; flex-shrink: 0;">
                                @if($project->tal)
                                    {{ strtoupper(substr($project->tal->first_name, 0, 1)) }}{{ strtoupper(substr($project->tal->last_name, 0, 1)) }}
                                @else
                                    <i class="fal fa-user"></i>
                                @endif
                            </span>
                            <div>
                                <small class="text-muted d-block">Technical Account Lead (TAL)</small>
                                @if($project->tal)
                                    <strong>{{ $project->tal->first_name }} {{ $project->tal->last_name }}</strong>
                                    @if($project->tal->title)
                                        <small class="text-muted d-block">{{ $project->tal->title }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </div>
                        </div>

                        {{-- AE --}}
                        <div class="list-group-item d-flex align-items-center">
                            <span class="d-inline-block rounded-circle bg-success-100 text-success text-center fw-500 me-3"
                                  style="width: 36px; height: 36px; line-height: 36px; font-size: 13px; flex-shrink: 0;">
                                @if($project->ae)
                                    {{ strtoupper(substr($project->ae->first_name, 0, 1)) }}{{ strtoupper(substr($project->ae->last_name, 0, 1)) }}
                                @else
                                    <i class="fal fa-user"></i>
                                @endif
                            </span>
                            <div>
                                <small class="text-muted d-block">Account Executive (AE)</small>
                                @if($project->ae)
                                    <strong>{{ $project->ae->first_name }} {{ $project->ae->last_name }}</strong>
                                    @if($project->ae->title)
                                        <small class="text-muted d-block">{{ $project->ae->title }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Delivery Team --}}
        <div class="col-lg-8">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Delivery Team ({{ $project->contacts->count() }})</div>
                </div>
                <div class="card-body p-0">
                    @if($project->contacts->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Title</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->contacts as $contact)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="d-inline-block rounded-circle bg-primary-100 text-primary text-center fw-500 me-2"
                                                          style="width: 30px; height: 30px; line-height: 30px; font-size: 11px;">
                                                        {{ strtoupper(substr($contact->first_name, 0, 1)) }}{{ strtoupper(substr($contact->last_name, 0, 1)) }}
                                                    </span>
                                                    <strong>{{ $contact->first_name }} {{ $contact->last_name }}</strong>
                                                </div>
                                            </td>
                                            <td>{{ $contact->title ?? '-' }}</td>
                                            <td>
                                                @if($contact->email)
                                                    <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $contact->phone ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fal fa-users fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0">No team members assigned to this project.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
