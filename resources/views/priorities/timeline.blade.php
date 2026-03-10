@extends('layouts.vertical', ['pageTitle' => 'Progress Timeline'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Progress Timeline',
        'pageSubTitle1' => 'Partner Management',
        'pageSubTitle2' => 'Priorities',
        'pageSubTitle2Url' => route('priorities.index'),
    ])
@endsection

@section('content')

    <!-- Timeline Chart -->
    <div class="card mb-g">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#bar-chart"></use></svg>
                Weekly Progress (Last 8 Weeks)
            </h5>
        </div>
        <div class="card-body">
            <div id="timelineChart" style="min-height: 350px;"></div>
        </div>
    </div>

    <!-- Week-by-Week Breakdown -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#list"></use></svg>
                Weekly Breakdown
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Week Starting</th>
                            <th class="text-center">Objectives Completed</th>
                            <th class="text-center">Action Items Completed</th>
                            <th class="text-center">Total Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timelineData as $week)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($week['week_start'])->format('M j, Y') }}</td>
                                <td class="text-center">
                                    @if($week['objectives_completed'] > 0)
                                        <span class="badge bg-success">{{ $week['objectives_completed'] }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($week['items_completed'] > 0)
                                        <span class="badge bg-primary">{{ $week['items_completed'] }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="fw-500">{{ $week['objectives_completed'] + $week['items_completed'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ApexCharts === 'undefined') return;

        var timelineData = @json($timelineData);
        var categories = timelineData.map(function(w) {
            return new Date(w.week_start).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });

        var chart = new ApexCharts(document.querySelector('#timelineChart'), {
            chart: { type: 'bar', height: 350, stacked: true, toolbar: { show: false } },
            series: [
                { name: 'Objectives Completed', data: timelineData.map(function(w) { return w.objectives_completed; }) },
                { name: 'Action Items Completed', data: timelineData.map(function(w) { return w.items_completed; }) },
            ],
            xaxis: { categories: categories },
            yaxis: { title: { text: 'Count' }, forceNiceScale: true },
            colors: ['#28a745', '#007bff'],
            plotOptions: { bar: { columnWidth: '50%' } },
            legend: { position: 'top' },
        });
        chart.render();
    });
</script>
@endsection
