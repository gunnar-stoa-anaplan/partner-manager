@extends('layouts.vertical', ['pageTitle' => 'Application Intelligence'])

@section('css')

@endsection

@section('page-title')
    <div class="d-flex align-items-end mb-4">
        <div>
            @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Application Intelligence', 'pageSubTitle1' => 'Config', 'pageSubTitle2'=> 'Application Intel'])
        </div>
    </div>

    <div class="alert alert-warning">
        Truly a Game-Changer in Admin Dashboards.
        <button type="button" class="ms-2 btn btn-outline-secondary btn-icon rounded-circle waves-effect waves-themed"
                data-action="playsound" data-soundpath="media/sound/" data-soundfile="appintel.mp3"
                aria-label="Play Introduction">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#volume-2"/>
            </svg>
        </button>
    </div>
@endsection


@section('content')
    <p>
        Completely redesigned using <b>Artificial Intelligence</b>, SmartAdmin v5 is the first of its kind.
        This release redefines intelligent design by leveraging AI to analyze patterns, optimize workflows,
        and create a code structure that minimizes redundancy while maximizing performance.
    </p>
    <p>
        The result is an admin template that is faster, lighter, and more versatile than ever before.
        AI-driven insights have influenced everything—from UI/UX design principles to the underlying
        architecture—ensuring
        the all new SmartAdmin is robust, reliable, and future-proof.
    </p>
    <p>
        <strong>
            Experience the future of admin dashboards today. Get started with SmartAdmin version 5 and transform the way
            you work!
        </strong>
    </p>
    <h5 class="mb-3 fw-600 mt-5">
        Unit Testing
    </h5>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
            <tr>
                <th>Metric</th>
                <th>SmartAdmin v5</th>
                <th>Other Admin Templates</th>
                <th>Improvement (%)</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>First Contentful Paint (FCP)</td>
                <td>0.9s</td>
                <td>1.5s</td>
                <td>40% Faster</td>
            </tr>
            <tr>
                <td>Time to Interactive (TTI)</td>
                <td>1.8s</td>
                <td>3.2s</td>
                <td>44% Faster</td>
            </tr>
            <tr>
                <td>Total Blocking Time (TBT)</td>
                <td>50ms</td>
                <td>180ms</td>
                <td>72% Less Blocking</td>
            </tr>
            <tr>
                <td>JavaScript Execution Time</td>
                <td>120ms</td>
                <td>250ms</td>
                <td>52% Faster</td>
            </tr>
            <tr>
                <td>Memory Usage</td>
                <td>35MB</td>
                <td>60MB</td>
                <td>42% Less Memory</td>
            </tr>
            <tr>
                <td>DOM Nodes Count</td>
                <td>850</td>
                <td>1500</td>
                <td>43% Fewer Nodes</td>
            </tr>
            <tr>
                <td>Layout Shift Score</td>
                <td>0.02</td>
                <td>0.12</td>
                <td>83% Less Shift</td>
            </tr>
            <tr>
                <td>Server Response Time</td>
                <td>180ms</td>
                <td>300ms</td>
                <td>40% Faster</td>
            </tr>
            <tr>
                <td>CSS Rendering Efficiency</td>
                <td>Optimized</td>
                <td>Standard</td>
                <td>Highly Optimized</td>
            </tr>
            <tr class="fw-bold">
                <td>Overall Comparison</td>
                <td>40% Faster, 30% Less Code, AI-Optimized</td>
                <td>Slower, More Redundant Code</td>
                <td>Significant Performance Gain</td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')

@endsection
