@extends('layouts.vertical', ['pageTitle' => 'Tables Style Generator'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Tables Style Generator', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'Tables', 'pageSubText' => 'Create custom Bootstrap table styles with live preview.'])
@endsection

@section('content')
    <div class="info-container mb-4">
        This tool helps you generate Bootstrap table styles by selecting different styling options.
        The table updates in real-time as you make selections. Copy the generated classes to use in your project.
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">Table Preview</div>
                    <div aria-label="Column display options" class="btn-group btn-group-sm" role="group">
                        <input autocomplete="off" class="btn-check" id="collapseColumnsBtn" name="columnsDisplay"
                               type="radio"/>
                        <label class="btn btn-outline-secondary" for="collapseColumnsBtn">Fewer Columns</label>
                        <input autocomplete="off" checked="" class="btn-check" id="expandColumnsBtn"
                               name="columnsDisplay"
                               type="radio"/>
                        <label class="btn btn-outline-secondary" for="expandColumnsBtn">More Columns</label>
                    </div>
                </div>
                <div class="card-body">
                    <div id="tableContainer">
                        <table class="table table-bordered table-striped table-hover" id="stylePreviewTable">
                            <caption>List of users and their details</caption>
                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th class="d-none d-md-table-cell" scope="col">Address</th>
                                <th class="d-none d-md-table-cell" scope="col">City</th>
                                <th class="d-none d-lg-table-cell" scope="col">State</th>
                                <th class="d-none d-lg-table-cell" scope="col">Country</th>
                                <th scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="table-primary">
                                <th scope="row">1</th>
                                <td>John Doe</td>
                                <td>john.doe@example.com</td>
                                <td>123-456-7890</td>
                                <td class="d-none d-md-table-cell">123 Main St</td>
                                <td class="d-none d-md-table-cell">Anytown</td>
                                <td class="d-none d-lg-table-cell">CA</td>
                                <td class="d-none d-lg-table-cell">USA</td>
                                <td><a href="javascript:void(0);">Edit</a></td>
                            </tr>
                            <tr class="table-secondary">
                                <th scope="row">2</th>
                                <td>Jane Smith</td>
                                <td>jane.smith@example.com</td>
                                <td>987-654-3210</td>
                                <td class="d-none d-md-table-cell">456 Park Ave</td>
                                <td class="d-none d-md-table-cell">Somecity</td>
                                <td class="d-none d-lg-table-cell">NY</td>
                                <td class="d-none d-lg-table-cell">USA</td>
                                <td><a href="javascript:void(0);">Edit</a></td>
                            </tr>
                            <tr class="table-success">
                                <th scope="row">3</th>
                                <td>Bob Johnson</td>
                                <td>bob.johnson@example.com</td>
                                <td>555-123-4567</td>
                                <td class="d-none d-md-table-cell">789 Oak Dr</td>
                                <td class="d-none d-md-table-cell">Otherville</td>
                                <td class="d-none d-lg-table-cell">TX</td>
                                <td class="d-none d-lg-table-cell">USA</td>
                                <td><a href="javascript:void(0);">Edit</a></td>
                            </tr>
                            <tr class="table-warning">
                                <th scope="row">4</th>
                                <td>Alice Brown</td>
                                <td>alice.brown@example.com</td>
                                <td>896-456-7890</td>
                                <td class="d-none d-md-table-cell">321 Maple St</td>
                                <td class="d-none d-md-table-cell">Newtown</td>
                                <td class="d-none d-lg-table-cell">FL</td>
                                <td class="d-none d-lg-table-cell">USA</td>
                                <td><a href="javascript:void(0);">Edit</a></td>
                            </tr>
                            <tr class="table-danger">
                                <th scope="row">5</th>
                                <td>Charlie Wilson</td>
                                <td>charlie.wilson@example.com</td>
                                <td>234-456-7890</td>
                                <td class="d-none d-md-table-cell">654 Pine Ln</td>
                                <td class="d-none d-md-table-cell">Lastcity</td>
                                <td class="d-none d-lg-table-cell">WA</td>
                                <td class="d-none d-lg-table-cell">USA</td>
                                <td><a href="javascript:void(0);">Edit</a></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <h6>Configuration Code</h6>
                        <pre class="body-bg p-3 border rounded"><code>&lt;table class="<span
                                    id="codeClasses">table</span>"&gt;
  &lt;caption&gt;List of users and their details&lt;/caption&gt;
  &lt;thead&gt;
    &lt;tr&gt;
      &lt;th scope="col"&gt;#&lt;/th&gt;
      &lt;th scope="col"&gt;Name&lt;/th&gt;
      &lt;th scope="col"&gt;Email&lt;/th&gt;
      &lt;!-- more columns --&gt;
    &lt;/tr&gt;
  &lt;/thead&gt;
  &lt;tbody&gt;
    &lt;tr&gt;
      &lt;th scope="row"&gt;1&lt;/th&gt;
      &lt;td&gt;John Doe&lt;/td&gt;
      &lt;td&gt;john.doe@example.com&lt;/td&gt;
      &lt;!-- more cells --&gt;
    &lt;/tr&gt;
    &lt;!-- more rows --&gt;
  &lt;/tbody&gt;
&lt;/table&gt;</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/tablesstylegenerator.js'])
@endsection
