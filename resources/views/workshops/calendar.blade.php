@extends('layouts.vertical', ['pageTitle' => 'Workshops Calendar'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Workshops Calendar', 'pageSubTitle1' => 'Workshops', 'pageSubTitle1Url' => route('workshops.index'), 'pageSubText' => 'All scheduled sessions across every workshop'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <div id="workshops-calendar"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendar = new FullCalendar.Calendar(document.getElementById('workshops-calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
        height: 700,
        events: {
            url: '{{ route("workshops.calendar.events") }}',
            method: 'GET',
        },
        eventDidMount: function (info) {
            var props = info.event.extendedProps;
            var parts = [info.event.title];
            if (props.status_label) { parts.push('Status: ' + props.status_label); }
            parts.push(props.participant_count + ' participant' + (props.participant_count === 1 ? '' : 's'));
            if (props.notes) { parts.push(props.notes); }
            info.el.setAttribute('title', parts.join('\n'));
        },
    });
    calendar.render();
});
</script>
@endsection
