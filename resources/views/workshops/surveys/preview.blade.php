@extends('layouts.vertical', ['pageTitle' => (\App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind).' survey preview'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> (\App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind).' survey', 'pageSubTitle1' => $template->workshop->name])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="alert alert-warning">
            <i class="fal fa-eye me-1"></i> Facilitator preview — submissions are blocked until you're enrolled in a session.
        </div>

        @if($template->intro)
            <div class="alert alert-info">{{ $template->intro }}</div>
        @endif

        @forelse($questions as $question)
            @include('workshops.surveys.partials.question-fields', ['question' => $question, 'index' => $loop->iteration, 'disabled' => true])
        @empty
            <p class="text-muted">This template has no questions yet.</p>
        @endforelse

        <a href="{{ route('workshops.surveys.edit', $template) }}" class="btn btn-outline-secondary">Back to editor</a>
    </div>
</div>
@endsection
