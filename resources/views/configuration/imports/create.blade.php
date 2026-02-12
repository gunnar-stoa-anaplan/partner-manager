@extends('layouts.vertical', ['pageTitle' => 'New Import'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'New Import', 'pageSubTitle1' => 'System', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubTitle2' => 'Data Imports', 'pageSubTitle2Url' => route('imports.index')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Upload an Excel file to import data into Partner Manager. Select the import type, choose your file, and the system will process it — extracting partners, opportunities, and other records based on the file format. Each import is logged so you can review results and troubleshoot any issues from the import history.</p>

<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Upload Data Import File</div>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('imports.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="type">Import Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Select Import Type</option>
                            <option value="opportunities" selected>Opportunities</option>
                            <option value="psm">PSM Assignments</option>
                            <option value="resources">Resources / Certifications</option>
                            <option value="accounts">Accounts</option>
                            <option value="partners">Partners</option>
                        </select>
                        <div class="form-text">Select what type of data you're importing</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="file">Excel File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                        <div class="form-text">Upload an Excel file (.xlsx or .xls). Maximum file size: 10MB</div>
                    </div>

                    <div class="alert alert-info" id="import-instructions">
                        <h6 class="alert-heading">Import Instructions</h6>
                        <hr>

                        <div id="opportunities-instructions">
                            <strong>Opportunities Import:</strong>
                            <ul class="mb-2">
                                <li>Partners extracted from <strong>Column E (Partner Plan HQ)</strong> and <strong>Column P (Final Implementation Partner)</strong></li>
                                <li>Duplicate partner names are merged into a single record</li>
                                <li>Fiscal quarter and year are calculated from close dates</li>
                                <li>Re-importing the same file updates existing opportunities</li>
                            </ul>
                            <strong>Expected Columns:</strong>
                            <p class="mb-0"><small>Opportunity Name, Account Name, Stage, Amount, Close Date, Type, Description, Lead Source, Probability, Owner, and partner columns</small></p>
                        </div>

                        <div id="psm-instructions" class="d-none">
                            <strong>PSM Assignments Import:</strong>
                            <ul class="mb-2">
                                <li>Assigns PSM, Geography, and Region to partners</li>
                                <li>Uses fuzzy matching to find existing partners</li>
                                <li>Supports both "Partner" and "Partner Plan HQ" column names</li>
                            </ul>
                            <strong>Expected Columns:</strong>
                            <p class="mb-0"><small>Partner (or Partner Plan HQ), PSM, Geography (or Geo), Region</small></p>
                        </div>

                        <div id="resources-instructions" class="d-none">
                            <strong>Resources / Certifications Import:</strong>
                            <ul class="mb-2">
                                <li>Imports partner resources and certifications</li>
                                <li>Wide format with certification columns ending in " Started" or " Complete"</li>
                                <li>Each certification becomes a separate resource record</li>
                            </ul>
                            <strong>Expected Columns:</strong>
                            <p class="mb-0"><small>Person name, Partner, Role, and certification program columns with " Started" or " Complete" suffix</small></p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('imports.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#upload"></use></svg>
                            Upload and Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('type').addEventListener('change', function() {
        const type = this.value;
        document.getElementById('opportunities-instructions').classList.add('d-none');
        document.getElementById('psm-instructions').classList.add('d-none');
        document.getElementById('resources-instructions').classList.add('d-none');

        if (type === 'opportunities') {
            document.getElementById('opportunities-instructions').classList.remove('d-none');
        } else if (type === 'psm') {
            document.getElementById('psm-instructions').classList.remove('d-none');
        } else if (type === 'resources') {
            document.getElementById('resources-instructions').classList.remove('d-none');
        }
    });
</script>
@endsection
