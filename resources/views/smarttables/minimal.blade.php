@extends('layouts.vertical', ['pageTitle' => 'SmartTables'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Minimal Settings', 'pageSubTitle1' => 'Data Visualization', 'pageSubTitle2'=> 'Smart Tables', 'pageSubText' => 'Loading the table with minimal settings & static data.'])
@endsection

@section('content')
    <div class="info-container">
        By default, most of the features are disabled, including the responsive design. We inilialize the table by using
        <code>const table = new SmartTables('myTable', {});</code>
        Only <strong>"Search"</strong>, <strong>"Pagination"</strong>, <strong>"Sorting"</strong>, and
        <strong>"Entries"</strong> are enabled by default.
        <strong class="text-danger">Note: This is a non-responsive example of SmartTables. </strong>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <table id="myTable" class="table table-striped table-bordered table-hover d-none">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Due</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>John Doe</td>
                    <td>john.doe@example.com</td>
                    <td>123-456-7890</td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td>$100.00</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Jane Smith</td>
                    <td>jane.smith@example.com</td>
                    <td>987-654-3210</td>
                    <td><span class="badge bg-secondary">Inactive</span></td>
                    <td>$200.00</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Jim Beam</td>
                    <td>jim.beam@example.com</td>
                    <td>555-123-4567</td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td>$300.00</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Alice Johnson</td>
                    <td>alice.johnson@example.com</td>
                    <td>415-555-0101</td>
                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                    <td>$150.00</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Robert Lee</td>
                    <td>robert.lee@example.com</td>
                    <td>310-987-6543</td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td>$75.00</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Sarah Kim</td>
                    <td>sarah.kim@example.com</td>
                    <td>212-345-6789</td>
                    <td><span class="badge bg-secondary">Inactive</span></td>
                    <td>$250.00</td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>Michael Brown</td>
                    <td>michael.brown@example.com</td>
                    <td>617-890-1234</td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td>$400.00</td>
                </tr>
                <tr>
                    <td>8</td>
                    <td>Emily Davis</td>
                    <td>emily.davis@example.com</td>
                    <td>720-456-7891</td>
                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                    <td>$50.00</td>
                </tr>
                <tr>
                    <td>9</td>
                    <td>David Wilson</td>
                    <td>david.wilson@example.com</td>
                    <td>503-234-5678</td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td>$125.00</td>
                </tr>
                <tr>
                    <td>10</td>
                    <td>Linda Carter</td>
                    <td>linda.carter@example.com</td>
                    <td>858-901-2345</td>
                    <td><span class="badge bg-secondary">Inactive</span></td>
                    <td>$175.00</td>
                </tr>
                <tr>
                    <td>11</td>
                    <td>Thomas Green</td>
                    <td>thomas.green@example.com</td>
                    <td>206-789-0123</td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td>$90.00</td>
                </tr>
                <tr>
                    <td>12</td>
                    <td>Nancy White</td>
                    <td>nancy.white@example.com</td>
                    <td>404-567-8901</td>
                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                    <td>$225.00</td>
                </tr>
                <tr>
                    <td>13</td>
                    <td>Paul Adams</td>
                    <td>paul.adams@example.com</td>
                    <td>713-345-6789</td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td>$350.00</td>
                </tr>
                <tr>
                    <td>14</td>
                    <td>Clara Evans</td>
                    <td>clara.evans@example.com</td>
                    <td>916-123-4567</td>
                    <td><span class="badge bg-secondary">Inactive</span></td>
                    <td>$80.00</td>
                </tr>
                <tr>
                    <td>15</td>
                    <td>Oliver Harris</td>
                    <td>oliver.harris@example.com</td>
                    <td>312-890-1234</td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td>$500.00</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/smarttableminimal.js'])
@endsection
