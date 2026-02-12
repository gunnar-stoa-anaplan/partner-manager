@extends('layouts.vertical', ['pageTitle' => 'Objectives'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Objectives', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Objectives define the strategic goals you set for your partner engagements. Create and manage objectives here, then assign them to specific partners to track progress. Use objectives to align your partner program with broader business outcomes and measure success over time.</p>

<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Partner Objectives</div>
            </div>
            <div class="card-body">
                @if($objectives->isEmpty())
                    <div class="alert alert-info">
                        No objectives defined yet. Objectives will appear here once created.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($objectives as $objective)
                                <tr>
                                    <td>{{ $objective->id }}</td>
                                    <td>{{ $objective->name }}</td>
                                    <td>{{ $objective->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
