@extends('layouts.vertical', ['pageTitle' => $workshop->name.' - Content Flags'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Content Flags', 'pageSubTitle1' => $workshop->name, 'pageSubTitle1Url' => route('workshops.show', $workshop), 'pageSubText' => 'Issues participants have reported on this workshop\'s content, across every session.'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        @forelse($flags as $flag)
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        {{ $flag->page?->title ?? 'General' }}
                        <span class="badge {{ $flag->status === 'answered' ? 'bg-success' : ($flag->status === 'hidden' ? 'bg-secondary' : 'bg-warning text-dark') }} ms-1">{{ ucfirst($flag->status) }}</span>
                    </div>
                    <span class="text-muted fs-sm me-2">{{ $flag->session?->name }}</span>
                    @if($flag->page)
                        <a href="{{ route('workshops.pages.edit', [$workshop, $flag->page]) }}" class="btn btn-sm btn-outline-secondary">Edit page</a>
                    @endif
                </div>
                <div class="card-body">
                    <p class="mb-1"><span class="fw-medium">{{ $flag->authorName() }}</span> <span class="text-muted fs-sm">{{ $flag->created_at->diffForHumans() }}</span></p>
                    <p>{{ $flag->body }}</p>

                    @foreach($flag->replies as $reply)
                        <div class="ms-3 mt-1 ps-2 border-start small">
                            <span class="fw-medium">{{ $reply->authorName() }}</span> {{ $reply->body }}
                        </div>
                    @endforeach

                    @if($flag->status !== 'hidden')
                    <form action="{{ route('workshops.sessions.threads.reply', [$workshop, $flag->session, $flag]) }}" method="POST" class="mt-3 d-flex gap-2">
                        @csrf
                        <input type="text" name="body" class="form-control form-control-sm" placeholder="Reply (e.g. &quot;fixed in the latest version&quot;)" required>
                        <button type="submit" class="btn btn-sm btn-primary text-nowrap">Reply &amp; mark answered</button>
                    </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card shadow-0"><div class="card-body text-center text-muted py-4">No content issues reported yet.</div></div>
        @endforelse
    </div>
</div>
@endsection
