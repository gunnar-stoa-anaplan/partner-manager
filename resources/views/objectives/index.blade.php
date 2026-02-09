@extends('layouts.vertical', ['pageTitle' => 'Objectives'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Objectives', 'pageSubTitle1' => 'Configuration'])
@endsection

@section('content')
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
