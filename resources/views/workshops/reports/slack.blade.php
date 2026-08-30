@extends('layouts.vertical', ['pageTitle' => 'Slack Post'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Slack Celebration Post', 'pageSubTitle1' => $session->name, 'pageSubTitle1Url' => route('workshops.sessions.show', [$workshop, $session])])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <p class="text-muted fs-sm">Drop this in your team's Slack channel after the session wraps. Numbers pre-filled from this session's data.</p>
                <button type="button" class="btn btn-primary btn-sm mb-2" id="copySlackPost">Copy to clipboard</button>
                <textarea class="form-control font-monospace" id="slackPostText" rows="16" readonly onclick="this.select()">{{ $text }}</textarea>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('copySlackPost').addEventListener('click', function () {
    var textarea = document.getElementById('slackPostText');
    textarea.select();
    navigator.clipboard.writeText(textarea.value).then(function () {
        var btn = document.getElementById('copySlackPost');
        var original = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(function () { btn.textContent = original; }, 1500);
    });
});
</script>
@endsection
