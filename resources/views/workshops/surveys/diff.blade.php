@extends('layouts.vertical', ['pageTitle' => 'Review: '.(\App\Models\WorkshopSurveyTemplate::KINDS[$survey->kind] ?? $survey->kind)])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Review draft', 'pageSubTitle1' => 'Survey templates', 'pageSubTitle1Url' => route('workshops.surveys.index', $workshop), 'pageSubText' => $workshop->name.' - '.(\App\Models\WorkshopSurveyTemplate::KINDS[$survey->kind] ?? $survey->kind).($before ? ' (revision of v'.$before->version.')' : ' (new template)')])
@endsection

@section('content')
<style>
    .diff-body ins { background: #d1fae5; text-decoration: none; }
    .diff-body del { background: #fee2e2; text-decoration: line-through; }
    .diff-field-changed { background: #fffbeb; }
</style>
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">
                    {{ \App\Models\WorkshopSurveyTemplate::KINDS[$survey->kind] ?? $survey->kind }}
                    <span class="badge bg-warning text-dark ms-1">Draft v{{ $survey->version }}</span>
                </div>
                <form action="{{ route('workshops.surveys.publish', $survey) }}" method="POST" class="d-inline me-2" onsubmit="return confirm('Publish this draft? It will become the live version immediately.')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">Publish</button>
                </form>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
            </div>
            <div class="card-body">
                <p class="text-muted fs-sm mb-0">
                    @if($before)
                        Comparing against the current live version (v{{ $before->version }}). Publishing this draft will supersede it.
                    @else
                        This is a brand-new template with no prior version to compare against.
                    @endif
                </p>
            </div>
        </div>

        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Intro</div></div>
            <div class="card-body diff-body">
                @forelse($introDiff as $segment)
                    @if($segment['type'] === 'insert')<ins>{{ $segment['text'] }}</ins>
                    @elseif($segment['type'] === 'delete')<del>{{ $segment['text'] }}</del>
                    @else{{ $segment['text'] }}
                    @endif
                @empty
                    <p class="text-muted mb-0">No intro text.</p>
                @endforelse
            </div>
        </div>

        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Questions</div></div>
            <div class="card-body">
                @if(empty($questionsDiff['added']) && empty($questionsDiff['removed']) && empty($questionsDiff['changed']))
                    <p class="text-muted mb-0">No changes to questions.</p>
                @endif

                @foreach($questionsDiff['added'] as $question)
                    <div class="mb-2"><span class="badge bg-success bg-opacity-25 text-success me-2">Added</span>{{ $question['text'] ?? $question['id'] }}</div>
                @endforeach
                @foreach($questionsDiff['removed'] as $question)
                    <div class="mb-2"><span class="badge bg-danger bg-opacity-25 text-danger me-2">Removed</span>{{ $question['text'] ?? $question['id'] }}</div>
                @endforeach
                @foreach($questionsDiff['changed'] as $change)
                    <div class="mb-2 p-2 diff-field-changed rounded">
                        <span class="badge bg-warning bg-opacity-50 text-dark me-2">Changed</span>{{ $change['after']['text'] ?? $change['id'] }}
                        <ul class="mb-0 fs-sm">
                            @foreach($change['fields'] as $field)
                                <li><strong>{{ $field }}</strong>: {{ is_scalar($change['before'][$field] ?? null) ? $change['before'][$field] : json_encode($change['before'][$field] ?? null) }} → {{ is_scalar($change['after'][$field] ?? null) ? $change['after'][$field] : json_encode($change['after'][$field] ?? null) }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Review comments</div></div>
            <div class="card-body">
                <form action="{{ route('workshops.surveys.comments', $survey) }}" method="POST" class="mb-3">
                    @csrf
                    <textarea name="body" class="form-control form-control-sm mb-2" rows="2" placeholder="Leave a comment for whoever's reviewing this..." required></textarea>
                    <button type="submit" class="btn btn-sm btn-primary">Comment</button>
                </form>

                @forelse($comments as $comment)
                    <div class="mb-2 pb-2 border-bottom small">
                        <span class="fw-medium">{{ $comment->author?->name ?? 'Unknown' }}</span>
                        <span class="text-muted">{{ $comment->created_at->diffForHumans() }}</span>
                        <div>{{ $comment->body }}</div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No comments yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('workshops.surveys.reject', $survey) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject this draft</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Reason (optional)</label>
                    <textarea name="rejection_reason" class="form-control" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
