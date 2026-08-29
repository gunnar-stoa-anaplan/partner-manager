@extends('layouts.vertical', ['pageTitle' => 'Slack Post'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Slack Post', 'pageSubTitle1' => $session->name, 'pageSubTitle1Url' => route('workshops.sessions.show', [$workshop, $session])])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Copy-paste ready Slack message (mrkdwn)</div></div>
            <div class="card-body">
                <textarea class="form-control font-monospace" rows="14" readonly onclick="this.select()">{{ $text }}</textarea>
            </div>
        </div>
    </div>
</div>
@endsection
