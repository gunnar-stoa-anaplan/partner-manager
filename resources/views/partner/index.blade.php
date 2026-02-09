@extends('layouts.vertical', ['pageTitle' => 'Partner Assignments'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Partner Assignments', 'pageSubTitle1' => 'Configuration'])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Partner Assignments</div>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    View and manage partner-to-PSM assignments. Partners can be assigned via Data Imports (PSM import type).
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100">
                        <thead>
                            <tr>
                                <th>Partner Name</th>
                                <th>Assigned PSM</th>
                                <th>Geography</th>
                                <th>Region</th>
                                <th>Industry</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($partners as $partner)
                            <tr>
                                <td><strong>{{ $partner->name }}</strong></td>
                                <td>
                                    @if($partner->user)
                                        <span class="badge bg-success">{{ $partner->user->name }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Unassigned</span>
                                    @endif
                                </td>
                                <td>{{ $partner->geography ?? '-' }}</td>
                                <td>{{ $partner->region ?? '-' }}</td>
                                <td>{{ $partner->industry ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No partners found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
