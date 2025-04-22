@php
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('layouts/layoutMaster')

@section('title', 'Web Form')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('content')
    @if (false)
        <div class="card mb-5">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="card mb-3">
                <h5 class="card-header">Genral Settings</h5>
                <div class="row">
                    <form action="{{ route('update_records_per_page') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <!-- Basic Layout -->
                        <div class="col-xxl">
                            <div class="card">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">Per Page Record</h5>
                                    <div class="d-flex align-items-center">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-6">
                                        <label class="col-sm-2 col-form-label" for="name">Receive Payment</label>
                                        <div class="col-sm-10">
                                            <select id="rp_page" name="rp_page" class="form-control form-select">
                                                <option value="10"
                                                    {{ config('app.rp_per_page') == 10 ? 'selected' : '' }}>
                                                    10</option>
                                                <option value="20"
                                                    {{ config('app.rp_per_page') == 20 ? 'selected' : '' }}>
                                                    20</option>
                                                <option value="30"
                                                    {{ config('app.rp_per_page') == 30 ? 'selected' : '' }}>
                                                    30</option>
                                                <option value="40"
                                                    {{ config('app.rp_per_page') == 40 ? 'selected' : '' }}>
                                                    40</option>
                                                <option value="50"
                                                    {{ config('app.rp_per_page') == 50 ? 'selected' : '' }}>
                                                    50</option>
                                                <option value="100"
                                                    {{ config('app.rp_per_page') == 100 ? 'selected' : '' }}>
                                                    100</option>
                                            </select>
                                            @if ($errors->has('rp_page'))
                                                <span class="text-danger">
                                                    {{ $errors->first('rp_page') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label class="col-sm-2 col-form-label" for="name">Send Payment</label>
                                        <div class="col-sm-10">
                                            <select id="sp_page" name="sp_page" class="form-control form-select">
                                                <option value="10"
                                                    {{ config('app.sp_per_page') == 10 ? 'selected' : '' }}>
                                                    10</option>
                                                <option value="20"
                                                    {{ config('app.sp_per_page') == 20 ? 'selected' : '' }}>
                                                    20</option>
                                                <option value="30"
                                                    {{ config('app.sp_per_page') == 30 ? 'selected' : '' }}>
                                                    30</option>
                                                <option value="40"
                                                    {{ config('app.sp_per_page') == 40 ? 'selected' : '' }}>
                                                    40</option>
                                                <option value="50"
                                                    {{ config('app.sp_per_page') == 50 ? 'selected' : '' }}>
                                                    50</option>
                                                <option value="100"
                                                    {{ config('app.rp_per_page') == 100 ? 'selected' : '' }}>
                                                    100</option>
                                            </select>
                                            @if ($errors->has('sp_page'))
                                                <span class="text-danger">
                                                    {{ $errors->first('sp_page') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label class="col-sm-2 col-form-label" for="name">Payee</label>
                                        <div class="col-sm-10">
                                            <select id="payee_page" name="payee_page" class="form-control form-select">
                                                <option value="10"
                                                    {{ config('app.payee_per_page') == 10 ? 'selected' : '' }}>
                                                    10</option>
                                                <option value="20"
                                                    {{ config('app.payee_per_page') == 20 ? 'selected' : '' }}>
                                                    20</option>
                                                <option value="30"
                                                    {{ config('app.payee_per_page') == 30 ? 'selected' : '' }}>
                                                    30</option>
                                                <option value="40"
                                                    {{ config('app.payee_per_page') == 40 ? 'selected' : '' }}>
                                                    40</option>
                                                <option value="50"
                                                    {{ config('app.payee_per_page') == 50 ? 'selected' : '' }}>
                                                    50</option>
                                                <option value="100"
                                                    {{ config('app.payee_per_page') == 100 ? 'selected' : '' }}>
                                                    100</option>
                                            </select>
                                            @if ($errors->has('payee_page'))
                                                <span class="text-danger">
                                                    {{ $errors->first('payee_page') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label class="col-sm-2 col-form-label" for="name">Payor</label>
                                        <div class="col-sm-10">
                                            <select id="payor_page" name="payor_page" class="form-control form-select">
                                                <option value="10"
                                                    {{ config('app.payor_per_page') == 10 ? 'selected' : '' }}>
                                                    10</option>
                                                <option value="20"
                                                    {{ config('app.payor_per_page') == 20 ? 'selected' : '' }}>
                                                    20</option>
                                                <option value="30"
                                                    {{ config('app.payor_per_page') == 30 ? 'selected' : '' }}>
                                                    30</option>
                                                <option value="40"
                                                    {{ config('app.payor_per_page') == 40 ? 'selected' : '' }}>
                                                    40</option>
                                                <option value="50"
                                                    {{ config('app.payor_per_page') == 50 ? 'selected' : '' }}>
                                                    50</option>
                                                <option value="100"
                                                    {{ config('app.payor_per_page') == 100 ? 'selected' : '' }}>
                                                    100</option>
                                            </select>
                                            @if ($errors->has('payor_page'))
                                                <span class="text-danger">
                                                    {{ $errors->first('payor_page') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label class="col-sm-2 col-form-label" for="name">Check History</label>
                                        <div class="col-sm-10">
                                            <select id="history_page" name="history_page" class="form-control form-select">
                                                <option value="10"
                                                    {{ config('app.history_per_page') == 10 ? 'selected' : '' }}>
                                                    10</option>
                                                <option value="20"
                                                    {{ config('app.history_per_page') == 20 ? 'selected' : '' }}>
                                                    20</option>
                                                <option value="30"
                                                    {{ config('app.history_per_page') == 30 ? 'selected' : '' }}>
                                                    30</option>
                                                <option value="40"
                                                    {{ config('app.history_per_page') == 40 ? 'selected' : '' }}>
                                                    40</option>
                                                <option value="50"
                                                    {{ config('app.history_per_page') == 50 ? 'selected' : '' }}>
                                                    50</option>
                                                <option value="100"
                                                    {{ config('app.history_per_page') == 100 ? 'selected' : '' }}>
                                                    100</option>
                                            </select>
                                            @if ($errors->has('history_page'))
                                                <span class="text-danger">
                                                    {{ $errors->first('history_page') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-5">
        @if (session('sign_success'))
            <div class="alert alert-success">
                {{ session('sign_success') }}
            </div>
        @endif
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Signatures</h5>
            <a href="{{ route('add_sign') }}" class="btn btn-primary mr-4"
                style="height: 40px !important;margin-right: 25px !important;">
                <i class="fa-solid fa-plus"></i> &nbsp; Add Signature
            </a>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table id="signTable" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Sign</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @if (!empty($is_web_form))
        <div class="card">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-header">Web Forms</h5>
                <a href="{{ route('new_web_form') }}" class="btn btn-primary mr-4"
                    style="height: 40px !important;margin-right: 25px !important;">
                    <i class="fa-solid fa-plus"></i> &nbsp; Add Web Form
                </a>
            </div>
            <div class="card-datatable table-responsive pt-0">
                <table id="webFormTable" class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Company Name</th>
                            <th>Page URL</th>
                            <th>Logo</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    @endif
@endsection
@section('page-script')
    <script>
        $(document).ready(function() {
            $('#webFormTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('get_web_forms') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'company_name',
                        name: 'company_name'
                    },
                    {
                        data: 'page_url',
                        name: 'page_url'
                    },
                    {
                        data: 'logo',
                        name: 'logo',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#signTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('get_sign') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'Name',
                        name: 'Name'
                    },
                    {
                        data: 'Sign',
                        name: 'Sign',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('body').on('click', '.copy-link', function(e) {
                e.preventDefault(); // Prevent default link action

                var link = $(this).attr("data-link");
                // Get the link from data attribute
                var tempInput = $("<input>");
                $("body").append(tempInput);
                tempInput.val(link).select();
                document.execCommand("copy"); // Copy text
                tempInput.remove();
            });
        });
    </script>
@endsection
