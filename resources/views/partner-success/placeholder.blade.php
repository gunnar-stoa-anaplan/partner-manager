@extends('layouts.vertical', [
    'pageTitle' => $pageTitle,
    'pageSubTitle1' => 'Insights',
    'pageSubTitle2' => 'PSM Engagement',
    'pageSubTitle2Url' => url('partner-success'),
])

@section('content')
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <svg class="sa-icon sa-icon-xl text-muted mb-3" style="width:48px;height:48px">
                <use href="/icons/sprite.svg#file-text"></use>
            </svg>
            <h4 class="fw-600">{{ $pageTitle }}</h4>
            <p class="text-muted mx-auto" style="max-width:560px">{{ $blurb }}</p>
        </div>
    </div>
</div>
@endsection
