@extends('portal.layout')

@section('title', $page->title)

@section('content')
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
                    <div class="mt-3">{!! nl2br(e($page->body)) !!}</div>
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

            <div class="d-flex justify-content-between">
                @if($previousPage)
                    <a href="{{ route('portal.my-workshops.pages.show', [$session, $previousPage]) }}" class="btn btn-outline-secondary">&laquo; {{ $previousPage->title }}</a>
                @else
                    <span></span>
                @endif
                @if($nextPage)
                    <a href="{{ route('portal.my-workshops.pages.show', [$session, $nextPage]) }}" class="btn btn-primary">{{ $nextPage->title }} &raquo;</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
