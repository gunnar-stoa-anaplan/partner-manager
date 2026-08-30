@extends('layouts.vertical', ['pageTitle' => (\App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind).' survey'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> (\App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind).' survey', 'pageSubTitle1' => 'Survey templates', 'pageSubTitle1Url' => route('workshops.surveys.index', $template->workshop), 'pageSubText' => $template->workshop->name])
@endsection

@section('content')
<style>
    .question-card .card-header { background: #f8fafc; }
    .likert-note { font-size: 0.8rem; color: #6b7280; }
    .correct-answer-box textarea { background: #f0fdf4; border-color: #bbf7d0; }
</style>
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <form action="{{ route('workshops.surveys.update', $template) }}" method="POST" id="templateForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="questions_json" id="questionsJsonInput">

            <div class="card shadow-0 mb-g">
                <div class="card-header"><div class="card-title">Intro text</div></div>
                <div class="card-body">
                    <p class="text-muted fs-sm mb-2">Shown above the questions to participants.</p>
                    <textarea class="form-control" name="intro" rows="3">{{ old('intro', $template->intro) }}</textarea>
                </div>
            </div>

            @if($template->kind === 'knowledge')
            <div class="card shadow-0 mb-g">
                <div class="card-header"><div class="card-title">Scoring &amp; attempt</div></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Minimum passing score</label>
                            <p class="text-muted fs-sm mb-2">% of scoreable questions a participant must get right.</p>
                            <div class="input-group input-group-sm" style="max-width: 140px;">
                                <input type="number" class="form-control" name="passing_score" value="{{ old('passing_score', $template->passing_score) }}" min="0" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Attempt limit</label>
                            <p class="text-muted fs-sm mb-2">Empty = unlimited retakes (still counted).</p>
                            <input type="number" class="form-control form-control-sm" style="max-width: 140px;" name="max_attempts" value="{{ old('max_attempts', $template->max_attempts) }}" min="1" placeholder="empty = unlimited">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Questions per attempt</label>
                            <p class="text-muted fs-sm mb-2">Empty = use all. Set a number to randomly draw that many from the pool of {{ count($template->questions_json ?? []) }}.</p>
                            <input type="number" class="form-control form-control-sm" style="max-width: 140px;" name="questions_per_attempt" value="{{ old('questions_per_attempt', $template->questions_per_attempt) }}" min="1" placeholder="empty = use all">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Shuffle answer options</label>
                            <p class="text-muted fs-sm mb-2">Randomize option order per attempt while preserving correctness tracking.</p>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="shuffle_options" name="shuffle_options" value="1" @checked(old('shuffle_options', $template->shuffle_options))>
                                <label class="form-check-label" for="shuffle_options">Shuffle on every attempt</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
                <input type="hidden" name="passing_score" value="{{ $template->passing_score ?: 70 }}">
            @endif

            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Questions</div>
                    <button type="button" class="btn btn-primary btn-sm" id="addQuestionBtn">+ Add question</button>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-sm">Likert questions use a fixed 5-point comfort scale. Multi/single-select questions need one option per line.</p>
                    <div id="questionList"></div>
                </div>
            </div>

            <div class="card shadow-0 mb-g">
                <div class="card-body d-flex align-items-center">
                    <button type="submit" class="btn btn-primary">Save template</button>
                    <div class="flex-grow-1"></div>
                    <form action="{{ route('workshops.surveys.destroy', $template) }}" method="POST" onsubmit="return confirm('Delete this template? All its questions will be lost.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">Delete template</button>
                    </form>
                </div>
            </div>
        </form>

        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Bulk import questions (JSON)</div></div>
            <div class="card-body">
                <p class="text-muted fs-sm">Paste a JSON array of question objects. <strong>Appends</strong> to the existing pool — does not replace. This form submits independently; it doesn't save edits you've made above.</p>
                <pre class="bg-dark text-light p-2 rounded fs-xs">{ "text": "What is X?", "type": "single_select",
  "options": ["A", "B", "C", "D"], "correct": "B", "required": true }</pre>
                <form action="{{ route('workshops.surveys.import-questions', $template) }}" method="POST">
                    @csrf
                    <textarea class="form-control font-monospace mb-2" name="questions_json" rows="4" placeholder='[{"text":"...","type":"single_select","options":["A","B"],"correct":"A","required":true}]' required></textarea>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Append to pool</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
window.workshopSurveyQuestions = @json($template->questions_json ?? []);
window.workshopSurveyQuestionTypes = @json(\App\Models\WorkshopSurveyTemplate::QUESTION_TYPES);
</script>
@vite(['resources/scripts/pages/workshop-survey-builder.js'])
@endsection
