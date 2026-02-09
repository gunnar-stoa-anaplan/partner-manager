@extends('layouts.vertical', ['pageTitle' => 'Blank Page'])


@section('page-title')

    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Blank Page', 'pageSubTitle1' => 'SmartAdmin'])

    <h1 class="subheader-title mt-4">

        <small> Can be the starting page for your application...
            <button type="button" class="btn btn-sm btn-outline-secondary" data-action="playsound"
                    data-soundpath="media/sound/" data-soundfile="blank_.mp3">
                <svg class="sa-icon">
                    <use href="/img/sprite.svg#volume-2"/>
                </svg>
            </button>
        </small>
    </h1>
@endsection

@section('content')
    <div class="info-container">
        You can start building your application by using this page as a starting point.
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Card title</div>
                </div>
                <div class="card-body">
                    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the
                        card's content.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Zip</th>
                                <th>Country</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>John Doe</td>
                                <td>john.doe@example.com</td>
                                <td>123-456-7890</td>
                                <td>123 Main St</td>
                                <td>Anytown</td>
                                <td>CA</td>
                                <td>12345</td>
                                <td>USA</td>
                                <td>Edit</td>
                            </tr>
                            <tr>
                                <td>Jane Smith</td>
                                <td>jane.smith@example.com</td>
                                <td>987-654-3210</td>
                                <td>123 Main St</td>
                                <td>Anytown</td>
                                <td>CA</td>
                                <td>12345</td>
                                <td>USA</td>
                                <td>Edit</td>
                            </tr>
                            <tr>
                                <td>Bob Johnson</td>
                                <td>bob.johnson@example.com</td>
                                <td>555-123-4567</td>
                                <td>123 Main St</td>
                                <td>Anytown</td>
                                <td>CA</td>
                                <td>12345</td>
                                <td>USA</td>
                                <td>Edit</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('extra-content')
    <div class="content-wrapper-right">
        <div class="right-content bg-faded d-flex flex-column h-100">
            <div class="flex-grow-1 p-4 py-4 ">
                <div class="info-container">
                    Right content goes here...
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/blank.js'])
@endsection
