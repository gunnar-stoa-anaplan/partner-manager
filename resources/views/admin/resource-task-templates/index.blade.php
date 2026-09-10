@extends('layouts.vertical', ['pageTitle' => 'Resource Task Templates'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle' => 'Resource Task Templates', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('welcome'), 'pageSubText' => 'Task templates that seed resource path stages'])
@endsection

@section('content')
    <div class="card shadow-0 mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label fs-sm text-muted mb-1">Path</label>
                    <select name="path_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All paths</option>
                        @foreach($paths as $p)
                            <option value="{{ $p->id }}" @selected(request('path_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request()->hasAny(['path_id', 'category', 'requirement_level']))
                    <div class="col-auto"><a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">Clear</a></div>
                @endif
            </form>
        </div>
    </div>

    <div class="card shadow-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Path</th>
                            <th>Stage</th>
                            <th>Category</th>
                            <th>Requirement</th>
                            <th>Effort</th>
                            <th class="text-center">Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $t)
                            <tr>
                                <td class="fw-500">{{ $t->title ?? '—' }}</td>
                                <td>{{ $t->path?->name ?? '—' }}</td>
                                <td>{{ $t->stage?->name ?? '—' }}</td>
                                <td>{{ $t->category ?? '—' }}</td>
                                <td>{{ $t->requirement_level ?? '—' }}</td>
                                <td>{{ $t->effort_size ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $t->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $t->is_active ? 'Yes' : 'No' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No task templates found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
