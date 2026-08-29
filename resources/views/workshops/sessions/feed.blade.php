@extends('layouts.vertical', ['pageTitle' => $session->name.' - Feed'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Activity Feed', 'pageSubTitle1' => $session->name, 'pageSubTitle1Url' => route('workshops.sessions.show', [$workshop, $session])])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title"><i class="fal fa-stream me-2"></i> Cohort Activity Feed</div></div>
            <div class="card-body">
                @forelse($items as $item)
                    @if($item['type'] === 'thread')
                        <div class="d-flex mb-3">
                            <div class="me-3 text-muted fs-sm" style="min-width: 90px;">{{ $item['at']->format('M j, g:ia') }}</div>
                            <div>
                                <span class="fw-medium">{{ $item['model']->authorName() }}</span>
                                @if($item['model']->page)
                                    <span class="badge bg-light text-dark">{{ $item['model']->page->title }}</span>
                                @endif
                                <div>{{ $item['model']->body }}</div>
                            </div>
                        </div>
                    @else
                        <div class="d-flex mb-3 ms-4">
                            <div class="me-3 text-muted fs-sm" style="min-width: 90px;">{{ $item['at']->format('M j, g:ia') }}</div>
                            <div>
                                <span class="fw-medium">{{ $item['model']->authorName() }}</span>
                                @if($item['model']->is_monitor_reply)<span class="badge bg-primary">Facilitator</span>@endif
                                <span class="text-muted fs-sm">replied to "{{ \Illuminate\Support\Str::limit($item['thread']->body, 40) }}"</span>
                                <div>{{ $item['model']->body }}</div>
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="text-muted mb-0">No activity yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
