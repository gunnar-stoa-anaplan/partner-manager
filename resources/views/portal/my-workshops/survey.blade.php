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
            <div class="card portal-card mb-3">
                <div class="card-body">
                    <p class="fw-medium">{{ $question['text'] }}</p>

                    @if(($question['type'] ?? 'single') === 'text')
                        <textarea name="responses[{{ $question['id'] }}]" class="form-control" rows="2"></textarea>
                    @elseif(($question['type'] ?? 'single') === 'multiple')
                        @foreach($question['options'] ?? [] as $option)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="responses[{{ $question['id'] }}][]" value="{{ $option }}" id="{{ $question['id'] }}-{{ $loop->index }}">
                                <label class="form-check-label" for="{{ $question['id'] }}-{{ $loop->index }}">{{ $option }}</label>
                            </div>
                        @endforeach
                    @else
                        @foreach($question['options'] ?? [] as $option)
                            <div class="form-check">
                                <input type="radio" class="form-check-input" name="responses[{{ $question['id'] }}]" value="{{ $option }}" id="{{ $question['id'] }}-{{ $loop->index }}">
                                <label class="form-check-label" for="{{ $question['id'] }}-{{ $loop->index }}">{{ $option }}</label>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">Submit Survey</button>
        <a href="{{ route('portal.my-workshops.show', $session) }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
