@extends('portal.layout')

@section('title', 'Survey')

@section('content')
<div class="container py-4">
    <h4 class="mb-1">{{ \App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind }} Survey</h4>
    <p class="text-muted mb-4">{{ $session->workshop->name }} &middot; {{ $session->name }}</p>

    @if($template->intro)
        <p class="mb-4">{{ $template->intro }}</p>
    @endif

    <form action="{{ route('portal.my-workshops.surveys.submit', [$session, $template]) }}" method="POST">
        @csrf

        @foreach($questions as $question)
            <input type="hidden" name="presented_ids[]" value="{{ $question['id'] }}">
            @include('workshops.surveys.partials.question-fields', ['question' => $question, 'index' => $loop->iteration, 'disabled' => false])
        @endforeach

        <button type="submit" class="btn btn-primary">Submit Survey</button>
        <a href="{{ route('portal.my-workshops.show', $session) }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
