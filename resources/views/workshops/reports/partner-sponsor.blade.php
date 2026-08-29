<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $workshop->name }} - {{ $partner->name }} Report</title>
    <style>
        body { font-family: -apple-system, Segoe UI, Roboto, sans-serif; color: #1f2937; max-width: 720px; margin: 40px auto; padding: 0 20px; }
        .header { border-bottom: 4px solid #0a2f46; padding-bottom: 16px; margin-bottom: 24px; }
        .header h1 { color: #0a2f46; margin: 0 0 4px; }
        .header p { color: #6b7280; margin: 0; }
        .stats { display: flex; gap: 16px; margin-bottom: 24px; }
        .stat { background: #f3f4f6; border-radius: 8px; padding: 16px; flex: 1; text-align: center; }
        .stat .value { font-size: 28px; font-weight: bold; color: #0a2f46; }
        .stat .label { color: #6b7280; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-muted { background: #f3f4f6; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $workshop->name }}</h1>
        <p>{{ $session->name }} &middot; {{ $session->start_date->format('F j') }} - {{ $session->end_date->format('F j, Y') }}</p>
        <p>Prepared for {{ $partner->name }}</p>
    </div>

    <div class="stats">
        <div class="stat"><div class="value">{{ $total }}</div><div class="label">Participants</div></div>
        <div class="stat"><div class="value">{{ $completed }}</div><div class="label">Completed</div></div>
        <div class="stat"><div class="value">{{ $completionRate }}%</div><div class="label">Completion Rate</div></div>
    </div>

    @if($surveyTotal > 0)
        <p><strong>Post-workshop survey:</strong> {{ $surveyPassed }}/{{ $surveyTotal }} passed, average score {{ $surveyAvgScore }}%.</p>
    @endif

    <h3>Your Team</h3>
    <table>
        <thead><tr><th>Name</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($participants as $participant)
                <tr>
                    <td>{{ $participant->contact->full_name }}</td>
                    <td>
                        @if($participant->completed_at)
                            <span class="badge badge-success">Completed</span>
                        @else
                            <span class="badge badge-muted">In progress</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 32px; color: #9ca3af; font-size: 12px;">Generated {{ now()->format('F j, Y') }}</p>
</body>
</html>
