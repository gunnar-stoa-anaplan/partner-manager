@extends('portal.layout')

@section('title', $page->title)

@section('content')
<style>
    .workshop-content-body h2 { margin-top: 1.5rem; font-size: 1.3rem; }
    .workshop-content-body h2:first-child { margin-top: 0; }
    .workshop-content-body .callout-tip,
    .workshop-content-body .callout-info { background: #eff6ff; border-left: 4px solid #0066cc; border-radius: 0 6px 6px 0; padding: 0.85rem 1.1rem; margin: 1rem 0; }
    .workshop-content-body .callout-warning { background: #fff7ed; border-left: 4px solid #c2410c; border-radius: 0 6px 6px 0; padding: 0.85rem 1.1rem; margin: 1rem 0; }
    .workshop-content-body .callout-label { display: block; font-weight: 600; margin-bottom: 0.35rem; }
    .workshop-content-body .content-badge,
    .workshop-content-body .badge-row span { display: inline-block; background: #e5e7eb; color: #374151; border-radius: 4px; padding: 0.15rem 0.6rem; font-size: 0.8rem; margin-right: 0.35rem; }
    .workshop-content-body .table-wrap { overflow-x: auto; margin: 1rem 0; }
    .workshop-content-body table { width: 100%; border-collapse: collapse; }
    .workshop-content-body th, .workshop-content-body td { border: 1px solid #e5e7eb; padding: 0.5rem; text-align: left; }
    .workshop-content-body .lab-check { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 1rem; margin: 1rem 0; }
    .workshop-content-body .lab-check-button { display: none; }
    .workshop-content-body .lab-check-results { display: none; }
    .workshop-content-body code { background: #f3f4f6; padding: 0.1rem 0.35rem; border-radius: 3px; }
</style>
<div class="container py-4">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card portal-card">
                <div class="card-header"><h6 class="mb-0">{{ $session->workshop->name }}</h6></div>
                <ul class="list-group list-group-flush">
                    @foreach($pages as $navPage)
                        <li class="list-group-item {{ $navPage->id === $page->id ? 'active' : '' }}">
                            <a href="{{ route('portal.my-workshops.pages.show', [$session, $navPage]) }}" class="{{ $navPage->id === $page->id ? 'text-white' : '' }}">{{ $navPage->title }}</a>
                        </li>
                    @endforeach
                </ul>
                <div class="card-body">
                    <a href="{{ route('portal.my-workshops.progress', $session) }}" class="btn btn-sm btn-outline-primary w-100">View Progress</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif

            <div class="card portal-card mb-4">
                <div class="card-body">
                    <h4>{{ $page->title }}</h4>
                    <div class="mt-3 workshop-content-body">{!! $page->body !!}</div>
                </div>
            </div>

            @if(!empty($page->checks()))
            <div class="card portal-card mb-4">
                <div class="card-header"><h6 class="mb-0">Checks ({{ $completion['passed'] }}/{{ $completion['total'] }})</h6></div>
                <div class="card-body">
                    @foreach($page->checks() as $check)
                        <form action="{{ route('portal.my-workshops.pages.check', [$session, $page]) }}" method="POST" class="d-flex justify-content-between align-items-center mb-2">
                            @csrf
                            <input type="hidden" name="check_id" value="{{ $check['id'] }}">
                            <span>{{ $check['label'] }}</span>
                            <span>
                                <button type="submit" name="passed" value="1" class="btn btn-sm btn-outline-success">Pass</button>
                                <button type="submit" name="passed" value="0" class="btn btn-sm btn-outline-danger">Fail</button>
                            </span>
                        </form>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="d-flex justify-content-between mb-4">
                @if($previousPage)
                    <a href="{{ route('portal.my-workshops.pages.show', [$session, $previousPage]) }}" class="btn btn-outline-secondary">&laquo; {{ $previousPage->title }}</a>
                @else
                    <span></span>
                @endif
                @if($nextPage)
                    <a href="{{ route('portal.my-workshops.pages.show', [$session, $nextPage]) }}" class="btn btn-primary">{{ $nextPage->title }} &raquo;</a>
                @endif
            </div>

            <div class="card portal-card">
                <div class="card-header"><h6 class="mb-0">Discuss this page</h6></div>
                <div class="card-body">
                    <form action="{{ route('portal.my-workshops.threads.store', $session) }}" method="POST" class="mb-3">
                        @csrf
                        <input type="hidden" name="workshop_page_id" value="{{ $page->id }}">
                        <textarea name="body" class="form-control form-control-sm mb-2" rows="2" placeholder="Ask about this page..." required></textarea>
                        <button type="submit" class="btn btn-sm btn-primary">Post</button>
                    </form>

                    @forelse($pageThreads as $thread)
                        <div class="mb-2 pb-2 border-bottom small">
                            <span class="fw-medium">{{ $thread->authorName() }}</span> {{ $thread->body }}
                            @foreach($thread->replies as $reply)
                                <div class="ms-3 mt-1 ps-2 border-start">
                                    <span class="fw-medium">{{ $reply->authorName() }}</span> {{ $reply->body }}
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No questions on this page yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
