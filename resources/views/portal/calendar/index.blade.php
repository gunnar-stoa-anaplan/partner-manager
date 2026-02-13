@extends('portal.layout', ['pageTitle' => 'Calendar'])

@section('content')
<h4 class="mb-4">Calendar — {{ $contact->partner->name }}</h4>

<div class="card portal-card">
    <div class="card-body">
        <div id="portal-calendar"></div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendar = new FullCalendar.Calendar(document.getElementById('portal-calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
        height: 650,
        events: {
            url: '{{ route("portal.calendar.events") }}',
            method: 'GET',
        },
        eventClick: function(info) {
            var props = info.event.extendedProps;
            alert(info.event.title + '\n' +
                (props.type_label ? 'Type: ' + props.type_label + '\n' : '') +
                (props.location ? 'Location: ' + props.location + '\n' : '') +
                (props.description ? '\n' + props.description : ''));
        },
    });
    calendar.render();
});
</script>
@endsection
